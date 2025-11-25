<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Lms\Lesson;
use App\Models\Lms\Quiz;
use App\Models\Lms\QuizQuestion;
use App\Models\Lms\QuizOption;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Lms\Course;
use App\Imports\QuizQuestionsImport;
use Maatwebsite\Excel\Facades\Excel as FacadesExcel;
use Throwable;

class QuizController extends Controller
{
    // Upsert quiz untuk lesson.kind = quiz
    public function upsert(Request $request, Lesson $lesson)
    {
        abort_if($lesson->kind !== 'quiz', 422, 'Lesson is not a quiz type');
        // Hanya pemilik course yang boleh
        abort_unless($lesson->course && $lesson->course->instructor_id === Auth::id(), 403);

        $data = $request->validate([
            'title'               => ['required', 'string', 'max:200'],
            'time_limit_seconds'  => ['nullable', 'integer', 'min:10', 'max:86400'],
            'shuffle_questions'   => ['nullable', 'boolean'],
        ]);

        try {
            DB::beginTransaction();

            // Kunci lesson agar konsisten
            $freshLesson = Lesson::query()
                ->whereKey($lesson->id)
                ->lockForUpdate()
                ->firstOrFail();

            $quiz = Quiz::firstOrCreate(
                ['lesson_id' => $freshLesson->id],
                [
                    'id'                 => (string) Str::uuid(),
                    'title'              => $data['title'],
                    'time_limit_seconds' => $data['time_limit_seconds'] ?? null,
                    'shuffle_questions'  => $data['shuffle_questions'] ?? true,
                ]
            );

            if (!$quiz->wasRecentlyCreated) {
                $quiz->update([
                    'title'              => $data['title'],
                    'time_limit_seconds' => $data['time_limit_seconds'] ?? null,
                    'shuffle_questions'  => $data['shuffle_questions'] ?? $quiz->shuffle_questions,
                ]);
            }

            DB::commit();

            return response()->json(['ok' => true, 'quiz_id' => $quiz->id]);
        } catch (Throwable $e) {
            DB::rollBack();
            return response()->json(['ok' => false, 'error' => 'Failed to upsert quiz: ' . $e->getMessage()], 500);
        }
    }

    public function storeQuestion(Request $request, Quiz $quiz)
    {
        // Cek kepemilikan lewat lesson->course
        abort_unless(
            $quiz->lesson && $quiz->lesson->course && $quiz->lesson->course->instructor_id === Auth::id(),
            403
        );

        $data = $request->validate([
            'question' => ['required', 'string'],
            'qtype'    => ['required', Rule::in(['mcq', 'truefalse', 'shortanswer'])],
            'score'    => ['nullable', 'numeric', 'min:0'],
            'options'  => ['array'],
            'correct'  => ['nullable'],
        ]);

        // Validasi ekstra untuk MCQ (minimal 2 opsi)
        if ($data['qtype'] === 'mcq') {
            $opts = $data['options'] ?? [];
            if (!is_array($opts) || count($opts) < 2) {
                return response()->json(['ok' => false, 'error' => 'MCQ requires at least 2 options.'], 422);
            }
        }

        try {
            DB::beginTransaction();

            // Kunci quiz agar penentuan order aman
            $freshQuiz = Quiz::query()
                ->whereKey($quiz->id)
                ->lockForUpdate()
                ->firstOrFail();

            $nextOrder = (int) (QuizQuestion::where('quiz_id', $freshQuiz->id)->max('order') ?? 0) + 1;

            $question = QuizQuestion::create([
                'id'       => (string) Str::uuid(),
                'quiz_id'  => $freshQuiz->id,
                'question' => $data['question'],
                'qtype'    => $data['qtype'],
                'score'    => $data['score'] ?? 1.0,
                'order'    => $nextOrder,
            ]);

            if (in_array($data['qtype'], ['mcq', 'truefalse'], true)) {
                $opts = $data['qtype'] === 'truefalse'
                    ? ['true', 'false']
                    : array_values($data['options'] ?? []);

                foreach ($opts as $idx => $text) {
                    QuizOption::create([
                        'id'          => (string) Str::uuid(),
                        'question_id' => $question->id,
                        'option_text' => (string) $text,
                        'is_correct'  => $this->isOptionCorrect($data['qtype'], $data['correct'] ?? null, $idx, (string) $text),
                    ]);
                }
            }

            DB::commit();

            return response()->json(['ok' => true, 'question_id' => $question->id], 201);
        } catch (Throwable $e) {
            DB::rollBack();
            return response()->json(['ok' => false, 'error' => 'Failed to create question: ' . $e->getMessage()], 500);
        }
    }

    public function updateQuestion(Request $request, QuizQuestion $question)
    {
        // Cek kepemilikan lewat chain relation
        abort_unless(
            $question->quiz && $question->quiz->lesson && $question->quiz->lesson->course
                && $question->quiz->lesson->course->instructor_id === Auth::id(),
            403
        );

        $data = $request->validate([
            'question' => ['required', 'string'],
            'qtype'    => ['required', Rule::in(['mcq', 'truefalse', 'shortanswer'])],
            'score'    => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            DB::beginTransaction();

            $freshQuestion = QuizQuestion::query()
                ->whereKey($question->id)
                ->lockForUpdate()
                ->firstOrFail();

            $freshQuestion->update([
                'question' => $data['question'],
                'qtype'    => $data['qtype'],
                'score'    => $data['score'] ?? $freshQuestion->score,
            ]);

            DB::commit();

            return response()->json(['ok' => true]);
        } catch (Throwable $e) {
            DB::rollBack();
            return response()->json(['ok' => false, 'error' => 'Failed to update question: ' . $e->getMessage()], 500);
        }
    }

    public function destroyQuestion(QuizQuestion $question)
    {
        // Cek kepemilikan
        abort_unless(
            $question->quiz && $question->quiz->lesson && $question->quiz->lesson->course
                && $question->quiz->lesson->course->instructor_id === Auth::id(),
            403
        );

        try {
            DB::beginTransaction();

            $freshQuestion = QuizQuestion::query()
                ->whereKey($question->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Jika belum pakai FK cascade pada quiz_options.question_id, hapus opsi manual:
            // $freshQuestion->options()->delete();

            $freshQuestion->delete();

            DB::commit();

            return response()->json(['ok' => true]);
        } catch (Throwable $e) {
            DB::rollBack();
            return response()->json(['ok' => false, 'error' => 'Failed to delete question: ' . $e->getMessage()], 500);
        }
    }

    private function isOptionCorrect(string $type, $correct, int $idx, string $text): bool
    {
        if ($type === 'truefalse') {
            return strtolower((string) $correct) === strtolower($text);
        }
        if (is_numeric($correct)) return (int) $correct === $idx;
        return is_string($correct) && trim($correct) === trim($text);
    }

    // === Pre/Posttest Logic (Copied & Adapted from Admin) ===

    public function storePretest(Request $request, Course $course)
    {
        abort_unless($course->instructor_id === Auth::id(), 403);
        return $this->upsertQuizForCourse($request, $course, 'pretest');
    }

    public function storePosttest(Request $request, Course $course)
    {
        abort_unless($course->instructor_id === Auth::id(), 403);
        return $this->upsertQuizForCourse($request, $course, 'posttest');
    }

    private function upsertQuizForCourse(Request $request, Course $course, string $kind)
    {
        $data = $request->validate([
            'title'               => ['required', 'string', 'max:200'],
            'time_limit_seconds'  => ['nullable', 'integer', 'min:10', 'max:86400'],
            'shuffle_questions'   => ['nullable', 'boolean'],
            'shuffle_options'     => ['nullable', 'boolean'],
            'passing_score'       => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        try {
            DB::beginTransaction();

            $quiz = Quiz::query()
                ->where('quizzable_type', Course::class)
                ->where('quizzable_id',   $course->id)
                ->where('quiz_kind',      $kind)
                ->lockForUpdate()
                ->first();

            $payload = [
                'title'               => $data['title'],
                'time_limit_seconds'  => $data['time_limit_seconds'] ?? null,
                'shuffle_questions'   => (int) $request->boolean('shuffle_questions'),
                'shuffle_options'     => (int) $request->boolean('shuffle_options'),
            ];

            if (array_key_exists('passing_score', $data)) {
                $payload['passing_score'] = $data['passing_score'];
            }

            if ($quiz) {
                $quiz->update($payload);
            } else {
                $quiz = Quiz::create(array_merge($payload, [
                    'id'              => (string) Str::uuid(),
                    'quiz_kind'       => $kind,
                    'quizzable_type'  => Course::class,
                    'quizzable_id'    => $course->id,
                ]));
            }

            if ($kind === 'pretest') {
                $course->update([
                    'default_passing_score' => $payload['passing_score'],
                    'pretest_passing_score' => $payload['passing_score']
                ]);
            } else {
                $course->update(['posttest_passing_score' => $payload['passing_score']]);
            }

            DB::commit();
            $tab = $quiz->quiz_kind === 'posttest' ? 'posttest' : 'pretest';

            return redirect()
                ->route('instructor.courses.edit', [$quiz->quizzable_id, 'tab' => $tab])
                ->with('success', ucfirst($kind) . ' berhasil disimpan.');
        } catch (Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan ' . $kind . ': ' . $e->getMessage());
        }
    }

    public function syncFromPretest(Request $request, Course $course)
    {
        abort_unless($course->instructor_id === Auth::id(), 403);

        $pretest  = $course->pretest;
        $posttest = $course->posttest;

        if (!$pretest) return back()->with('error', 'Pretest belum dibuat untuk kursus ini.');
        if (!$posttest) return back()->with('error', 'Posttest belum dibuat untuk kursus ini.');

        DB::transaction(function () use ($pretest, $posttest) {
            $postQIds = $posttest->questions()->pluck('id');
            if ($postQIds->isNotEmpty()) {
                QuizOption::whereIn('question_id', $postQIds)->delete();
                QuizQuestion::whereIn('id', $postQIds)->delete();
            }

            $preQuestions = $pretest->questions()->with('options')->orderBy('order')->get();

            foreach ($preQuestions as $q) {
                $newQ = QuizQuestion::create([
                    'id'            => (string) Str::uuid(),
                    'quiz_id'       => $posttest->id,
                    'question_text' => $q->question_text,
                    'points'        => $q->points,
                    'order'         => $q->order,
                ]);

                foreach ($q->options as $opt) {
                    QuizOption::create([
                        'id'          => (string) Str::uuid(),
                        'question_id' => $newQ->id,
                        'option_text' => $opt->option_text,
                        'is_correct'  => $opt->is_correct,
                    ]);
                }
            }
            $posttest->total_questions = $pretest->total_questions;
            $posttest->save();
        });

        return redirect()->back()->with('success', 'Posttest berhasil disinkronkan dari Pretest.');
    }

    public function importByKind(Request $request, Course $course, string $kind)
    {
        abort_unless($course->instructor_id === Auth::id(), 403);

        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
            'replace_existing' => ['nullable', 'boolean'],
        ]);

        $quiz = Quiz::query()
            ->where('quizzable_type', Course::class)
            ->where('quizzable_id', $course->id)
            ->where('quiz_kind', $kind)
            ->first();

        if (!$quiz) {
            return back()->with('error', "Quiz {$kind} belum dibuat untuk kursus ini.");
        }

        $replace = (bool)($data['replace_existing'] ?? false);
        $import = new QuizQuestionsImport($quiz, $replace);

        try {
            FacadesExcel::import($import, $data['file']);
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }

        $messages = [];
        foreach ($import->failures() as $failure) {
            $attr = $failure->attribute();
            $row  = $failure->row();
            foreach ($failure->errors() as $err) {
                $messages[] = "Row {$row} [{$attr}]: {$err}";
            }
        }
        foreach ($import->getCaughtErrors() as $msg) {
            $messages[] = $msg;
        }

        if (!empty($messages)) {
            return back()->with('error', "Beberapa baris gagal diimport:")->with('import_errors', $messages);
        }
        return back()->with('success', "Berhasil import pertanyaan untuk {$kind}.");
    }
}
