<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:200'],
            'kind'  => ['required', Rule::in(['youtube', 'gdrive', 'quiz'])],
            'content_url' => ['nullable', 'string', 'max:2048'],
        ];

        if ($this->input('kind') === 'youtube') {
            $rules['content_url'][] = 'required';
            $rules['content_url'][] = 'url';
        }
        if ($this->input('kind') === 'gdrive') {
            $rules['content_url'][] = 'required';
            $rules['content_url'][] = 'url';
        }
        // quiz: content_url nullable

        return $rules;
    }
}
