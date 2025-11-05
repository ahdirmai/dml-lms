<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();
        // lolos jika role aktif admin ATAU punya permission manage categories
        return $u && (($u->active_role === 'instructor') || $u->can('categories.manage'));
    }

    public function rules(): array
    {
        $id = $this->route('category')->id ?? null;
        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:191', Rule::unique('categories', 'slug')->ignore($id, 'id')],
            'description' => ['nullable', 'string'],
            'created_by' => ['required', 'exists:users,id']
        ];
    }
}
