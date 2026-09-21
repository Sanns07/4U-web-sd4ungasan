<?php

namespace App\Http\Requests\Admin;

use App\Enums\Semester;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AcademicPeriodRequest extends AdminRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    public function rules(): array
    {
        $period = $this->route('period');

        return [
            'academic_year' => [
                'required', 'regex:/^\d{4}\/\d{4}$/',
                Rule::unique('academic_periods', 'academic_year')
                    ->where(fn ($query) => $query->where('semester', $this->input('semester')))
                    ->ignore($period),
            ],
            'semester' => ['required', Rule::enum(Semester::class)],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if (! preg_match('/^(\d{4})\/(\d{4})$/', (string) $this->input('academic_year'), $matches)) {
                return;
            }

            if ((int) $matches[2] !== (int) $matches[1] + 1) {
                $validator->errors()->add('academic_year', 'Tahun ajaran harus berurutan, misalnya 2026/2027.');
            }

            $period = $this->route('period');

            if (! $period || ! $period->classes()->exists()) {
                return;
            }

            foreach (['academic_year', 'semester', 'start_date', 'end_date'] as $field) {
                $current = $period->{$field};
                $currentValue = $current instanceof \BackedEnum
                    ? $current->value
                    : ($current instanceof \DateTimeInterface ? $current->format('Y-m-d') : (string) $current);

                if ((string) $this->input($field) !== $currentValue) {
                    $validator->errors()->add($field, 'Identitas dan rentang periode yang sudah digunakan tidak dapat diubah.');
                }
            }
        }];
    }

    public function messages(): array
    {
        return [
            'academic_year.regex' => 'Format tahun ajaran harus seperti 2026/2027.',
            'end_date.after' => 'Tanggal selesai harus setelah tanggal mulai.',
        ];
    }

    public function attributes(): array
    {
        return parent::attributes() + [
            'academic_year' => 'tahun ajaran',
            'semester' => 'semester',
        ];
    }
}
