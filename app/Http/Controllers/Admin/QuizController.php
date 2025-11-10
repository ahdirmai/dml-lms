<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\QuizQuestionsImport;
use App\Models\Lms\Course;
use App\Models\Lms\Lesson;
use App\Models\Lms\Quiz;
use App\Models\Lms\QuizQuestion;
use App\Models\Lms\QuizOption;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Excel;
use Throwable;

class QuizController extends Controller
{


    public function storeQuestion(Request $request, Quiz $quiz)
    {
        // return [$request->all(), $quiz];

        $data = $request->validate([
            'prompt'        => ['required', 'string', 'max:1000'],
            'points'        => ['nullable', 'integer', 'min:0', 'max:1000'],
            'order'         => ['nullable', 'integer', 'min:0', 'max:10000'],
            'options'       => ['required', 'array', 'min:2', 'max:10'],
            'options.*.text' => ['required', 'string', 'max:500'],
            'correct_index' => ['required', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($quiz, $data) {
            $question = QuizQuestion::create([
                'id'      => (string) Str::uuid(),
                'quiz_id' => $quiz->id,
                'question_text'  => $data['prompt'],
                'score'  => $data['points'] ?? 1,
                'order'   => $data['order'] ?? ($quiz->questions()->max('order') + 1),
            ]);

            foreach ($data['options'] as $idx => $opt) {
                QuizOption::create([
                    'id'          => (string) Str::uuid(),
                    'question_id' => $question->id,
                    'option_text'        => $opt['text'],
                    'is_correct'  => $idx === (int) $data['correct_index'],
                ]);
            }

            $quiz->update([
                'total_questions' => $quiz->total_questions += 1
            ]);
        });
        $tab = $quiz->quiz_kind === 'posttest' ? 'posttest' : 'pretest';
        return redirect()
            ->route('admin.courses.edit', [$quiz->quizzable_id, 'tab' => $tab]) // quizzable_id = course_id
            ->with('success', 'Pertanyaan berhasil disimpan.');
    }

    public function updateQuestion(Request $request, Quiz $quiz, QuizQuestion $question)
    {
        abort_unless($question->quiz_id === $quiz->id, 404);
        $data = $request->validate([
            'prompt'        => ['required', 'string', 'max:1000'],
            'points'        => ['nullable', 'integer', 'min:0', 'max:1000'],
            'order'         => ['nullable', 'integer', 'min:0', 'max:10000'],
            'options'       => ['required', 'array', 'min:2', 'max:10'],
            'options.*.id'  => ['nullable', 'uuid'],     // boleh kosong untuk opsi baru
            'options.*.text' => ['required', 'string', 'max:500'],
            'correct_index' => ['required', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($question, $data) {
            $question->update([
                'option_text' => $data['prompt'],
                'score' => $data['points'] ?? 1,
                'order'  => $data['order'] ?? $question->order,
            ]);

            $keepIds = [];
            foreach ($data['options'] as $idx => $opt) {
                $isCorrect = $idx === (int) $data['correct_index'];

                if (!empty($opt['id'])) {
                    // update existing
                    $option = QuizOption::where('question_id', $question->id)->where('id', $opt['id'])->first();
                    if ($option) {
                        $option->update([
                            'option_text'       => $opt['text'],
                            'is_correct' => $isCorrect,
                        ]);
                        $keepIds[] = $option->id;
                    }
                } else {
                    // create new
                    $option = QuizOption::create([
                        'id'          => (string) Str::uuid(),
                        'question_id' => $question->id,
                        'option_text'        => $opt['text'],
                        'is_correct'  => $isCorrect,
                    ]);
                    $keepIds[] = $option->id;
                }
            }

            // hapus opsi yang tidak ada di payload
            QuizOption::where('question_id', $question->id)
                ->whereNotIn('id', $keepIds)
                ->delete();
        });
        $tab = $quiz->quiz_kind === 'posttest' ? 'posttest' : 'pretest';

        return redirect()
            ->route('admin.courses.edit', [$quiz->quizzable_id, 'tab' => $tab]) // quizzable_id = course_id
            ->with('success', 'Pertanyaan berhasil disimpan.');
    }

    public function destroyQuestion(Quiz $quiz, QuizQuestion $question)
    {
        abort_unless($question->quiz_id === $quiz->id, 404);

        DB::transaction(function () use ($question) {
            $question->options()->delete();
            $question->delete();
        });

        $quiz->update([
            'total_questions' => $quiz->total_questions -= 1
        ]);
        $tab = $quiz->quiz_kind === 'posttest' ? 'posttest' : 'pretest';

        return redirect()
            ->route('admin.courses.edit', [$quiz->quizzable_id, 'tab' => $tab]) // quizzable_id = course_id
            ->with('success', 'Pertanyaan berhasil disimpan.');
    }


    // Opsional: drag-sort urutan pertanyaan
    public function reorderQuestions(Request $request, Quiz $quiz)
    {
        $payload = $request->validate([
            'orders'   => ['required', 'array'],
            'orders.*.id'    => ['required', 'uuid'],
            'orders.*.order' => ['required', 'integer', 'min:0', 'max:10000'],
        ]);

        DB::transaction(function () use ($quiz, $payload) {
            foreach ($payload['orders'] as $row) {
                QuizQuestion::where('quiz_id', $quiz->id)
                    ->where('id', $row['id'])
                    ->update(['order' => $row['order']]);
            }
        });

        return response()->json(['ok' => true]);
    }

    private function isOptionCorrect(string $type, $correct, int $idx, string $text): bool
    {
        if ($type === 'truefalse') {
            return strtolower((string) $correct) === strtolower($text);
        }
        if (is_numeric($correct)) return (int) $correct === $idx;
        return is_string($correct) && trim($correct) === trim($text);
    }


    // app/Http/Controllers/Admin/QuizController.php
    public function storePretest(Request $request, Course $course)
    {
        // return 'x';
        return $this->upsertQuizForCourse($request, $course, 'pretest');
    }

    public function storePosttest(Request $request, Course $course)
    {
        return $this->upsertQuizForCourse($request, $course, 'posttest');
    }

    /**
     * Upsert header quiz (judul, waktu, shuffle, passing_score) untuk Pretest/Posttest
     * terikat ke Course (polymorphic: quizzable_type, quizzable_id).
     */
    private function upsertQuizForCourse(Request $request, Course $course, string $kind)
    {
        // Validasi field form dari _quiz-form.blade.php
        $data = $request->validate([
            'title'               => ['required', 'string', 'max:200'],
            'time_limit_seconds'  => ['nullable', 'integer', 'min:10', 'max:86400'],
            'shuffle_questions'   => ['nullable', 'boolean'],
            'shuffle_options'     => ['nullable', 'boolean'],
            'passing_score'       => ['nullable', 'integer', 'min:0', 'max:100'], // opsional kalau kamu pakai
        ]);

        try {
            DB::beginTransaction();

            // Cari quiz existing untuk course + kind
            /** @var Quiz|null $quiz */
            $quiz = Quiz::query()
                ->where('quizzable_type', Course::class)
                ->where('quizzable_id',   $course->id)
                ->where('quiz_kind',      $kind)            // 'pretest' | 'posttest'
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

            // return $payload;
            if ($quiz) {
                // Update header quiz
                $quiz->update($payload);
            } else {
                // Create baru terikat ke Course
                $quiz = Quiz::create(array_merge($payload, [
                    'id'              => (string) Str::uuid(),
                    'quiz_kind'       => $kind,                   // 'pretest' / 'posttest'
                    'quizzable_type'  => Course::class,
                    'quizzable_id'    => $course->id,
                ]));
            }

            // update course setting set crouse passing grade (pretest postest)
            if ($kind === 'pretest') {
                # code...
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
                ->route('admin.courses.edit', [$quiz->quizzable_id, 'tab' => $tab]) // quizzable_id = course_id
                ->with('success', ucfirst($kind) . ' berhasil disimpan.');
        } catch (Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan ' . $kind . ': ' . $e->getMessage());
        }
    }

    public function syncFromPretest(Request $request, Course $course)
    {
        $pretest  = $course->pretest;
        $posttest = $course->posttest;

        if (!$pretest) {
            return back()->with('error', 'Pretest belum dibuat untuk kursus ini.');
        }
        if (!$posttest) {
            return back()->with('error', 'Posttest belum dibuat untuk kursus ini.');
        }

        DB::transaction(function () use ($pretest, $posttest) {
            // 1) Bersihkan semua pertanyaan & opsi di posttest (hard sync)
            $postQIds = $posttest->questions()->pluck('id');
            if ($postQIds->isNotEmpty()) {
                QuizOption::whereIn('question_id', $postQIds)->delete();
                QuizQuestion::whereIn('id', $postQIds)->delete();
            }

            // 2) Ambil pertanyaan pretest lengkap dengan opsi
            $preQuestions = $pretest->questions()->with('options')->orderBy('order')->get();

            // 3) Duplikasi ke posttest (pertahankan order & points)
            foreach ($preQuestions as $q) {
                /** @var QuizQuestion $newQ */
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

        // return $posttest;



        // Jika ingin tetap di tab posttest, boleh tambahkan query ?tab=posttest
        return redirect()
            ->back()
            ->with('success', 'Posttest berhasil disinkronkan dari Pretest.');
    }

    public function importByKind(Request $request, Course $course, string $kind)
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
            'replace_existing' => ['nullable', 'boolean'],
        ]);

        $quiz = \App\Models\Lms\Quiz::query()
            ->where('quizzable_type', \App\Models\Lms\Course::class)
            ->where('quizzable_id', $course->id)
            ->where('quiz_kind', $kind)
            ->first();

        if (!$quiz) {
            return back()->with('error', "Quiz {$kind} belum dibuat untuk kursus ini.");
        }

        $replace = (bool)($data['replace_existing'] ?? false);
        $import = new \App\Imports\QuizQuestionsImport($quiz, $replace);

        try {
            // return 'x';
            Excel::import($import, $data['file']);
        } catch (\Throwable $e) {
            // Error global (misal file corrupt)
            return $e->getMessage();

            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }

        // Kumpulkan semua error & failure agar ditampilkan
        $messages = [];

        // 1) Failure validasi per baris (dari SkipsFailures)
        foreach ($import->failures() as $failure) {
            // $failure->row(), $failure->attribute(), $failure->errors()
            $attr = $failure->attribute(); // nama kolom
            $row  = $failure->row();       // nomor baris (termasuk header)
            foreach ($failure->errors() as $err) {
                $messages[] = "Row {$row} [{$attr}]: {$err}";
            }
        }

        // 2) Error runtime/exception lain (ditangkap manual)
        foreach ($import->getCaughtErrors() as $msg) {
            $messages[] = $msg;
        }

        if (!empty($messages)) {
            // Tampilkan daftar error ke user
            return back()->with('error', "Beberapa baris gagal diimport:")
                ->with('import_errors', $messages);
        }

        return back()->with('success', "Berhasil import pertanyaan untuk {$kind}.");
    }
}
