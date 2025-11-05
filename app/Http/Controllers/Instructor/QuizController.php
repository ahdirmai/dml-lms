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
}
