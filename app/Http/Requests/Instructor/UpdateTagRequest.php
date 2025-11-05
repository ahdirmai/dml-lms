<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();
        return $u && (($u->active_role === 'instructor') || $u->can('tags.manage'));
    }

    public function rules(): array
    {
        $id = $this->route('tag')->id ?? null;
        return [
            'name' => ['required', 'string', 'max:150', Rule::unique('tags', 'name')->ignore($id, 'id')],
            'slug' => ['nullable', 'string', 'max:191', Rule::unique('tags', 'slug')->ignore($id, 'id')],
        ];
    }
}
