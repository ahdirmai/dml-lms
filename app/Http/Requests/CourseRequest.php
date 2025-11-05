<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'         => ['required', 'string', 'max:180'],
            'subtitle'      => ['nullable', 'string', 'max:180'],
            'description'   => ['required', 'string'],
            'category_id'   => ['required', 'exists:categories,id'],
            'level'         => ['nullable', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'instructor_id' => ['required', 'exists:users,id'],
            'thumbnail'     => ['nullable', 'image', 'max:2048'],
        ];
    }
}
