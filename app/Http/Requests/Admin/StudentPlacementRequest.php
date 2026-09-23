<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class StudentPlacementRequest extends AdminRequest
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
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => [
                'required', 'integer', 'distinct',
                Rule::exists('students', 'id')->where(fn ($query) => $query->where('status', 'aktif')->whereNull('deleted_at')),
            ],
            'enrolled_at' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'student_ids.required' => 'Pilih minimal satu siswa untuk ditempatkan.',
            'student_ids.min' => 'Pilih minimal satu siswa untuk ditempatkan.',
            'student_ids.*.exists' => 'Siswa yang dipilih harus berstatus aktif.',
            'student_ids.*.distinct' => 'Daftar siswa tidak boleh berisi pilihan duplikat.',
        ];
    }

    public function attributes(): array
    {
        return parent::attributes() + [
            'student_ids' => 'daftar siswa',
            'enrolled_at' => 'tanggal penempatan',
        ];
    }
}
