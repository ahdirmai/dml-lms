<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{

    public function authorize(): bool
    {
        $u = $this->user();
        return $u && (($u->active_role === 'instructor') || $u->can('categories.manage'));
    }

    // public function authorize(): bool
    // {
    //     return $this->user()?->can('manage ') ?? false;
    // }
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:191', 'unique:categories,slug'],
            'description' => ['nullable', 'string'],
        ];
    }
}
