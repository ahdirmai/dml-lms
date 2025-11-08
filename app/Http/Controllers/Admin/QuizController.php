<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\Course;
use App\Models\Lms\Lesson;
use App\Models\Lms\Quiz;
use App\Models\Lms\QuizQuestion;
use App\Models\Lms\QuizOption;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
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
                'points'  => $data['points'] ?? 1,
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
        });
        $tab = $quiz->quiz_kind === 'posttest' ? 'posttest' : 'pretest';
        return redirect()
            ->route('admin.courses.edit', [$quiz->quizzable_id, 'tab' => $tab]) // quizzable_id = course_id
            ->with('success', 'Pertanyaan berhasil disimpan.');
    }

    public function updateQuestion(Request $request, Quiz $quiz, QuizQuestion $question)
    {
        // return [
        //     $question->quiz_id === $quiz->id
        // ];
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
                'points' => $data['points'] ?? 1,
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
}
