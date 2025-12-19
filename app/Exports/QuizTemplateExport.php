<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class QuizTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'question',
            'options',
            'correct',
            'points',
            'order',
        ];
    }

    public function array(): array
    {
        return [
            [
                'Contoh Pertanyaan Pilihan Ganda?',
                'Pilihan A;Pilihan B;Pilihan C',
                'Pilihan A', // Correct answer (text match)
                '10',
                '1'
            ],
            [
                'Ibukota Indonesia adalah?',
                'Jakarta;Bandung;Surabaya;Medan',
                '1', // Correct answer (index 1-based)
                '5',
                '2'
            ]
        ];
    }
}
