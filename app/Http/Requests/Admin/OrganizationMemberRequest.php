<?php

namespace App\Http\Requests\Admin;

use App\Enums\OrganizationCategory;
use Illuminate\Validation\Rule;

class OrganizationMemberRequest extends AdminRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    public function rules(): array
    {
        return [
            'teacher_id' => ['nullable', 'integer', Rule::exists('teachers', 'id')->whereNull('deleted_at')],
            'name' => ['nullable', 'required_without:teacher_id', 'string', 'max:255'],
            'position_name' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::enum(OrganizationCategory::class)],
            'display_order' => ['required', 'integer', 'min:0', 'max:10000'],
            'is_active' => ['required', 'boolean'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function attributes(): array
    {
        return parent::attributes() + [
            'teacher_id' => 'guru terkait',
            'name' => 'nama anggota',
            'position_name' => 'jabatan',
            'category' => 'kategori',
            'display_order' => 'urutan tampil',
            'photo' => 'foto anggota',
        ];
    }
}
