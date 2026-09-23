<?php

namespace App\Http\Requests\Admin;

class EndStudentEnrollmentRequest extends AdminRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->filled('ended_at')) {
            $this->merge(['ended_at' => now()->toDateString()]);
        }
    }

    public function rules(): array
    {
        return [
            'ended_at' => ['required', 'date'],
        ];
    }

    public function attributes(): array
    {
        return parent::attributes() + ['ended_at' => 'tanggal selesai'];
    }
}
