<?php

namespace App\Http\Requests\Instructor;


use Illuminate\Foundation\Http\FormRequest;

class StoreTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();
        return $u && (($u->active_role === 'instructor') || $u->can('categories.manage'));
    }
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150', 'unique:tags,name'],
            'slug' => ['nullable', 'string', 'max:191', 'unique:tags,slug'],
        ];
    }
}
