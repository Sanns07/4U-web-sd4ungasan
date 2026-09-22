<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class SubjectRequest extends AdminRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => mb_strtoupper(trim((string) $this->input('code'))),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:30', 'regex:/^[A-Z0-9-]+$/', Rule::unique('subjects', 'code')->ignore($this->route('subject'))],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return ['code.regex' => 'Kode hanya boleh berisi huruf kapital, angka, dan tanda hubung.'];
    }

    public function attributes(): array
    {
        return parent::attributes() + [
            'code' => 'kode mata pelajaran',
            'description' => 'deskripsi',
        ];
    }
}
