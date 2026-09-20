<?php

namespace App\Http\Requests\Admin;

use App\Enums\Gender;
use Illuminate\Validation\Rule;

class TeacherRequest extends AdminRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'nip' => $this->filled('nip') ? preg_replace('/\s+/', '', (string) $this->input('nip')) : null,
            'is_active' => $this->boolean('is_active'),
            'is_public' => $this->boolean('is_public'),
        ]);
    }

    public function rules(): array
    {
        $teacher = $this->route('teacher');

        return [
            'nip' => ['nullable', 'string', 'max:40', Rule::unique('teachers', 'nip')->ignore($teacher)],
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', Rule::enum(Gender::class)],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'address' => ['nullable', 'string', 'max:2000'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'public_title' => ['required', 'string', 'max:255'],
            'is_public' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function attributes(): array
    {
        return parent::attributes() + [
            'nip' => 'NIP',
            'gender' => 'jenis kelamin',
            'birth_place' => 'tempat lahir',
            'birth_date' => 'tanggal lahir',
            'address' => 'alamat',
            'phone' => 'telepon',
            'email' => 'email',
            'public_title' => 'bidang atau judul publik',
            'is_public' => 'status direktori publik',
            'photo' => 'foto guru',
        ];
    }
}
