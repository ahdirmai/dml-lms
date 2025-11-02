<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage tags') ?? false;
    }
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150', 'unique:tags,name'],
            'slug' => ['nullable', 'string', 'max:191', 'unique:tags,slug'],
        ];
    }
}
