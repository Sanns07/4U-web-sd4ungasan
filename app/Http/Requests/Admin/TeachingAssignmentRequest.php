<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class TeachingAssignmentRequest extends AdminRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $assignment = $this->route('teaching_assignment');

            if (! $assignment || ! $assignment->schedules()->exists()) {
                return;
            }

            foreach (['teacher_id', 'subject_id', 'class_id'] as $field) {
                if ((int) $this->input($field) !== (int) $assignment->{$field}) {
                    $validator->errors()->add($field, 'Identitas penugasan yang sudah digunakan pada jadwal tidak dapat diubah.');
                }
            }
        }];
    }

    public function rules(): array
    {
        $assignment = $this->route('teaching_assignment');

        return [
            'teacher_id' => [
                'required', 'integer',
                Rule::exists('teachers', 'id')->where(function ($query) use ($assignment): void {
                    $query->where(function ($activeQuery): void {
                        $activeQuery->where('is_active', true)->whereNull('deleted_at');
                    });

                    if ($assignment?->teacher_id) {
                        $query->orWhere('id', $assignment->teacher_id);
                    }
                }),
                Rule::unique('teaching_assignments', 'teacher_id')
                    ->where(fn ($query) => $query
                        ->where('subject_id', $this->input('subject_id'))
                        ->where('class_id', $this->input('class_id')))
                    ->ignore($assignment),
            ],
            'subject_id' => [
                'required', 'integer',
                Rule::exists('subjects', 'id')->where(function ($query) use ($assignment): void {
                    $query->where('is_active', true);

                    if ($assignment?->subject_id) {
                        $query->orWhere('id', $assignment->subject_id);
                    }
                }),
            ],
            'class_id' => [
                'required', 'integer',
                Rule::exists('classes', 'id')->where(function ($query) use ($assignment): void {
                    $query->where('is_active', true);

                    if ($assignment?->class_id) {
                        $query->orWhere('id', $assignment->class_id);
                    }
                }),
            ],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'teacher_id.exists' => 'Guru pengampu harus berasal dari data guru aktif.',
            'subject_id.exists' => 'Mata pelajaran harus berstatus aktif.',
            'class_id.exists' => 'Kelas harus berstatus aktif.',
            'teacher_id.unique' => 'Kombinasi guru, mata pelajaran, dan kelas tersebut sudah memiliki penugasan.',
        ];
    }

    public function attributes(): array
    {
        return parent::attributes() + [
            'teacher_id' => 'guru pengampu',
            'subject_id' => 'mata pelajaran',
            'class_id' => 'kelas',
        ];
    }
}
