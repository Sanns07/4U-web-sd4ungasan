<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class StudentTransferRequest extends AdminRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->filled('enrolled_at')) {
            $this->merge(['enrolled_at' => now()->toDateString()]);
        }
    }

    public function rules(): array
    {
        return [
            'target_class_id' => [
                'required', 'integer',
                Rule::exists('classes', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'enrolled_at' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'target_class_id.exists' => 'Kelas tujuan harus merupakan kelas aktif.',
        ];
    }

    public function attributes(): array
    {
        return parent::attributes() + [
            'target_class_id' => 'kelas tujuan',
            'enrolled_at' => 'tanggal pindah',
        ];
    }
}
