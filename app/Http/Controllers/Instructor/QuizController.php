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

class QuizController extends Controller
{
    // upsert quiz untuk lesson.kind = quiz
    public function upsert(Request $request, Lesson $lesson)
    {
        abort_if($lesson->kind !== 'quiz', 422, 'Lesson is not a quiz type');

        $data = $request->validate([
            'title'              => ['required', 'string', 'max:200'],
            'time_limit_seconds'  => ['nullable', 'integer', 'min:10', 'max:86400'],
            'shuffle_questions'   => ['nullable', 'boolean'],
        ]);

        $quiz = Quiz::firstOrCreate(
            ['lesson_id' => $lesson->id],
            [
                'id' => (string) Str::uuid(),
                'title' => $data['title'],
                'time_limit_seconds' => $data['time_limit_seconds'] ?? null,
                'shuffle_questions'  => $data['shuffle_questions'] ?? true,
            ]
        );

        // kalau sudah ada, update
        if (!$quiz->wasRecentlyCreated) {
            $quiz->update([
                'title' => $data['title'],
                'time_limit_seconds' => $data['time_limit_seconds'] ?? null,
                'shuffle_questions'  => $data['shuffle_questions'] ?? $quiz->shuffle_questions,
            ]);
        }

        return response()->json(['ok' => true, 'quiz_id' => $quiz->id]);
    }

    public function storeQuestion(Request $request, Quiz $quiz)
    {
        $data = $request->validate([
            'question' => ['required', 'string'],
            'qtype'    => ['required', Rule::in(['mcq', 'truefalse', 'shortanswer'])],
            'score'    => ['nullable', 'numeric', 'min:0'],
            'options'  => ['array'],
            'correct'  => ['nullable'],
        ]);

        $order = (int) (QuizQuestion::where('quiz_id', $quiz->id)->max('order') ?? 0) + 1;

        $question = QuizQuestion::create([
            'id'      => (string) Str::uuid(),
            'quiz_id' => $quiz->id,
            'question' => $data['question'],
            'qtype'   => $data['qtype'],
            'score'   => $data['score'] ?? 1.0,
            'order'   => $order,
        ]);

        if (in_array($data['qtype'], ['mcq', 'truefalse'], true)) {
            $opts = $data['qtype'] === 'truefalse' ? ['true', 'false'] : ($data['options'] ?? []);
            foreach ($opts as $idx => $text) {
                QuizOption::create([
                    'id'          => (string) Str::uuid(),
                    'question_id' => $question->id,
                    'option_text' => (string) $text,
                    'is_correct'  => $this->isOptionCorrect($data['qtype'], $data['correct'] ?? null, $idx, $text),
                ]);
            }
        }

        return response()->json(['ok' => true, 'question_id' => $question->id], 201);
    }

    public function updateQuestion(Request $request, QuizQuestion $question)
    {
        $data = $request->validate([
            'question' => ['required', 'string'],
            'qtype'    => ['required', Rule::in(['mcq', 'truefalse', 'shortanswer'])],
            'score'    => ['nullable', 'numeric', 'min:0'],
        ]);
        $question->update([
            'question' => $data['question'],
            'qtype' => $data['qtype'],
            'score' => $data['score'] ?? $question->score,
        ]);
        return response()->json(['ok' => true]);
    }

    public function destroyQuestion(QuizQuestion $question)
    {
        $question->delete();
        return response()->json(['ok' => true]);
    }

    private function isOptionCorrect(string $type, $correct, int $idx, string $text): bool
    {
        if ($type === 'truefalse') {
            return strtolower((string)$correct) === strtolower($text);
        }
        if (is_numeric($correct)) return (int)$correct === $idx;
        return is_string($correct) && trim($correct) === trim($text);
    }
}
