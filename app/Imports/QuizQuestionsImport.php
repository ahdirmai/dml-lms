<?php

namespace App\Imports;

use App\Models\Lms\Quiz;
use App\Models\Lms\QuizOption;
use App\Models\Lms\QuizQuestion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Throwable;

class QuizQuestionsImport implements
    ToCollection,
    WithHeadingRow,
    WithValidation,
    SkipsOnError,
    SkipsOnFailure,
    WithBatchInserts,
    WithChunkReading
{
    use SkipsErrors, SkipsFailures;

    /** @var array<string> */
    protected array $errorsCaught = [];

    /** ringkasan hasil */
    protected int $insertedQuestions = 0;
    protected int $insertedOptions = 0;

    public function __construct(
        protected Quiz $quiz,
        protected bool $replaceExisting = false
    ) {}

    public function collection(Collection $rows)
    {
        if (!$this->quiz || !$this->quiz->id) {
            $this->errorsCaught[] = 'Quiz target tidak ditemukan. Buat pretest/posttest dulu.';
            return;
        }

        DB::transaction(function () use ($rows) {
            if ($this->replaceExisting) {
                $questionIds = $this->quiz->questions()->pluck('id');
                QuizOption::whereIn('question_id', $questionIds)->delete();
                QuizQuestion::whereIn('id', $questionIds)->delete();
            }

            // untuk default order jika kolom 'order' kosong
            $nextOrder = (int) ($this->quiz->questions()->max('order') ?? 0) + 1;

            foreach ($rows as $idx => $row) {
                try {
                    $question = trim((string)($row['question'] ?? ''));
                    if ($question === '') {
                        // biar rules juga kerja, tapi guard extra di sini
                        throw new \RuntimeException('Kolom question kosong.');
                    }

                    $optionsRaw = (string)($row['options'] ?? '');
                    $correctRaw = (string)($row['correct'] ?? '');
                    $points     = (int)($row['points']  ?? 0);
                    $order      = (int)($row['order']   ?? 0);
                    if ($order <= 0) {
                        $order = $nextOrder++;
                    }

                    // buat question
                    $q = QuizQuestion::create([
                        'id'            => (string) Str::uuid(),
                        'quiz_id'       => $this->quiz->id,
                        'question_text' => $question,
                        'question_type' => 'mcq',   // pastikan kolom ini ada di DB/model
                        'score'         => $points,
                        'order'         => $order,
                    ]);
                    $this->insertedQuestions++;

                    // QuizOption::create([
                    //     'question_id' => $q->id,
                    //     'option_text' => 'sdas',
                    //     'is_correct'  => false,
                    //     'order_no'    => 1,   // ← WAJIB pakai order_no
                    // ]);

                    // parse opsi
                    $optList = collect(array_filter(array_map('trim', explode(';', $optionsRaw))));
                    if ($optList->isEmpty()) {
                        // tidak fatal: biarkan pertanyaan tanpa opsi jika memang begitu
                        continue;
                    }

                    // dukung dua format 'correct':
                    // 1) berbasis TEKS: "B" atau "B;D"
                    // 2) berbasis INDEX (1-based): "2" atau "2;4"
                    $correctTokens = collect(array_filter(array_map('trim', explode(';', $correctRaw))));
                    $correctByText = $correctTokens->filter(fn($t) => !ctype_digit($t))
                        ->map(fn($t) => mb_strtolower($t));
                    $correctByIdx  = $correctTokens->filter(fn($t) => ctype_digit($t))
                        ->map(fn($t) => (int)$t); // 1-based

                    $i = 1;

                    foreach ($optList as $opt) {
                        $isCorrect = false;

                        // cocokkan teks (case-insensitive)
                        if ($correctByText->isNotEmpty()) {
                            $isCorrect = $correctByText->contains(mb_strtolower($opt));
                        }

                        // atau cocokkan index (1-based)
                        if (!$isCorrect && $correctByIdx->isNotEmpty()) {
                            $isCorrect = $correctByIdx->contains($i);
                        }

                        QuizOption::create([
                            'question_id' => $q->id,
                            'option_text' => $opt,
                            'is_correct'  => $isCorrect,
                            'order_no'    => $i++,   // ← WAJIB pakai order_no
                        ]);
                        $this->insertedOptions++;
                    }
                } catch (Throwable $e) {
                    $rowNumber = $idx + 2; // +1 header, +1 index base 0
                    $this->errorsCaught[] = "Row {$rowNumber}: " . $e->getMessage();
                }
            }
        });
    }

    public function rules(): array
    {
        return [
            '*.question' => ['required', 'string', 'max:1000'],
            '*.options'  => ['nullable', 'string'],
            '*.correct'  => ['nullable', 'string'],
            '*.points'   => ['nullable', 'integer', 'min:0', 'max:1000'],
            '*.order'    => ['nullable', 'integer', 'min:0', 'max:100000'],
        ];
    }

    public function customValidationMessages()
    {
        return [
            '*.question.required' => 'Kolom question wajib diisi.',
            '*.points.integer'    => 'Kolom points harus berupa angka.',
            '*.order.integer'     => 'Kolom order harus berupa angka.',
        ];
    }

    public function batchSize(): int
    {
        return 500;
    }
    public function chunkSize(): int
    {
        return 500;
    }

    public function onError(Throwable $e)
    {
        $this->errorsCaught[] = $e->getMessage();
    }

    /** expose hasil untuk controller */
    public function getCaughtErrors(): array
    {
        return $this->errorsCaught;
    }
    public function getSummary(): array
    {
        return [
            'questions' => $this->insertedQuestions,
            'options'   => $this->insertedOptions,
        ];
    }
}
