<?php

namespace App\Http\Requests\Admin;

class SchoolProfileRequest extends AdminRequest
{
    public function rules(): array
    {
        return [
            'school_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:2000'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'vision' => ['required', 'string', 'max:5000'],
            'mission' => ['required', 'string', 'max:10000'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function attributes(): array
    {
        return parent::attributes() + [
            'school_name' => 'nama sekolah',
            'address' => 'alamat',
            'phone' => 'telepon',
            'email' => 'email',
            'vision' => 'visi',
            'mission' => 'misi',
            'logo' => 'logo sekolah',
        ];
    }
}
