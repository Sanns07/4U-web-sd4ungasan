<?php

namespace App\Http\Requests\Admin;

use App\Enums\Gender;
use App\Enums\Religion;
use App\Enums\StudentStatus;
use App\Models\SchoolClass;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class StudentRequest extends AdminRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'username' => mb_strtolower(trim((string) $this->input('username'))),
            'nis' => $this->filled('nis') ? trim((string) $this->input('nis')) : null,
            'is_active' => $this->boolean('is_active'),
            'class_id' => $this->filled('class_id') ? (int) $this->input('class_id') : null,
            'enrolled_at' => $this->filled('enrolled_at')
                ? $this->input('enrolled_at')
                : now()->toDateString(),
        ]);
    }

    public function rules(): array
    {
        $student = $this->route('student');
        $currentClassId = $student?->activeEnrollment()->value('class_id');
        $passwordRules = $this->isMethod('post')
            ? ['required', 'confirmed', Password::min(8)]
            : ['nullable', 'confirmed', Password::min(8)];

        return [
            'name' => ['required', 'string', 'max:255'],
            'nisn' => ['required', 'digits:10', Rule::unique('students', 'nisn')->ignore($student)],
            'nis' => ['nullable', 'string', 'max:30', Rule::unique('students', 'nis')->ignore($student)],
            'gender' => ['required', Rule::enum(Gender::class)],
            'religion' => ['nullable', Rule::enum(Religion::class)],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'address' => ['nullable', 'string', 'max:2000'],
            'parent_name' => ['nullable', 'string', 'max:255'],
            'parent_phone' => ['nullable', 'string', 'max:30'],
            'status' => ['required', Rule::enum(StudentStatus::class)],
            'graduation_year' => ['nullable', 'required_if:status,alumni', 'integer', 'min:2000', 'max:2100'],
            'username' => [
                'required', 'string', 'max:80', 'regex:/^[a-z0-9._]+$/',
                Rule::unique('users', 'username')->ignore($student?->user_id),
            ],
            'password' => $passwordRules,
            'is_active' => ['required', 'boolean'],
            'class_id' => [
                'nullable', 'integer',
                Rule::exists('classes', 'id')->where(function ($query) use ($currentClassId): void {
                    $query->where('is_active', true);

                    if ($currentClassId) {
                        $query->orWhere('id', $currentClassId);
                    }
                }),
            ],
            'enrolled_at' => ['required', 'date'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $classId = $this->integer('class_id');

            if ($classId) {
                $schoolClass = SchoolClass::query()->with('academicPeriod')->find($classId);

                if (! $schoolClass?->academicPeriod->is_active) {
                    $validator->errors()->add('class_id', 'Kelas harus berasal dari periode akademik aktif.');
                }
            }

        }];
    }

    public function messages(): array
    {
        return [
            'username.regex' => 'Nama pengguna hanya boleh berisi huruf kecil, angka, titik, dan garis bawah.',
            'graduation_year.required_if' => 'Tahun kelulusan wajib diisi untuk siswa alumni.',
            'class_id.exists' => 'Kelas yang dipilih harus berstatus aktif.',
        ];
    }

    public function attributes(): array
    {
        return parent::attributes() + [
            'nisn' => 'NISN',
            'nis' => 'NIS',
            'gender' => 'jenis kelamin',
            'religion' => 'agama',
            'birth_place' => 'tempat lahir',
            'birth_date' => 'tanggal lahir',
            'address' => 'alamat',
            'parent_name' => 'nama orang tua atau wali',
            'parent_phone' => 'kontak orang tua atau wali',
            'status' => 'status siswa',
            'graduation_year' => 'tahun kelulusan',
            'username' => 'nama pengguna',
            'password' => 'kata sandi',
            'class_id' => 'kelas aktif',
            'enrolled_at' => 'tanggal efektif penempatan',
        ];
    }
}
