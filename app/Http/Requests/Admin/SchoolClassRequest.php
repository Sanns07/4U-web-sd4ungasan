<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SchoolClassRequest extends AdminRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'room' => $this->filled('room') ? trim((string) $this->input('room')) : null,
            'homeroom_teacher_id' => $this->filled('homeroom_teacher_id') ? $this->input('homeroom_teacher_id') : null,
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        $schoolClass = $this->route('school_class');

        return [
            'academic_period_id' => ['required', 'integer', Rule::exists('academic_periods', 'id')],
            'name' => [
                'required', 'string', 'max:80',
                Rule::unique('classes', 'name')
                    ->where(fn ($query) => $query->where('academic_period_id', $this->input('academic_period_id')))
                    ->ignore($schoolClass),
            ],
            'grade_level' => ['required', 'integer', 'between:10,12'],
            'room' => ['nullable', 'string', 'max:80'],
            'homeroom_teacher_id' => [
                'nullable', 'integer',
                Rule::exists('teachers', 'id')->where(fn ($query) => $query->where('is_active', true)->whereNull('deleted_at')),
            ],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $schoolClass = $this->route('school_class');

            if (! $schoolClass || (int) $this->input('academic_period_id') === $schoolClass->academic_period_id) {
                return;
            }

            if ($schoolClass->enrollments()->exists() || $schoolClass->teachingAssignments()->exists()) {
                $validator->errors()->add('academic_period_id', 'Periode kelas yang sudah memiliki penempatan atau penugasan tidak dapat diubah.');
            }
        }];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Nama kelas sudah digunakan pada periode yang dipilih.',
            'homeroom_teacher_id.exists' => 'Wali kelas harus berasal dari data guru aktif.',
        ];
    }

    public function attributes(): array
    {
        return parent::attributes() + [
            'academic_period_id' => 'periode akademik',
            'name' => 'nama kelas',
            'grade_level' => 'tingkat',
            'room' => 'ruang utama',
            'homeroom_teacher_id' => 'wali kelas',
        ];
    }
}
