<?php

namespace App\Services;

use App\Models\AcademicPeriod;
use Illuminate\Support\Facades\DB;

class AcademicPeriodService
{
    public function create(array $data): AcademicPeriod
    {
        return DB::transaction(function () use ($data): AcademicPeriod {
            $this->lockPeriods();

            if ($data['is_active']) {
                AcademicPeriod::query()->update(['is_active' => false]);
            }

            return AcademicPeriod::query()->create($data);
        });
    }

    public function update(AcademicPeriod $period, array $data): AcademicPeriod
    {
        return DB::transaction(function () use ($period, $data): AcademicPeriod {
            $this->lockPeriods();

            if ($data['is_active']) {
                AcademicPeriod::query()->whereKeyNot($period->getKey())->update(['is_active' => false]);
            }

            $period->update($data);

            return $period->refresh();
        });
    }

    public function activate(AcademicPeriod $period): void
    {
        DB::transaction(function () use ($period): void {
            $this->lockPeriods();
            AcademicPeriod::query()->update(['is_active' => false]);
            $period->update(['is_active' => true]);
        });
    }

    public function deactivate(AcademicPeriod $period): void
    {
        $period->update(['is_active' => false]);
    }

    private function lockPeriods(): void
    {
        AcademicPeriod::query()->lockForUpdate()->get(['id']);
    }
}
