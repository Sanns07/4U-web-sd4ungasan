<?php

namespace Tests\Feature\Admin;

use App\Enums\Semester;
use App\Models\AcademicPeriod;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicPeriodManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_deactivate_and_delete_unused_period(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/admin/periode', $this->validData())
            ->assertRedirect('/admin/periode')->assertSessionHas('success');

        $period = AcademicPeriod::query()->where('academic_year', '2027/2028')->firstOrFail();
        $this->actingAs($admin)->put("/admin/periode/{$period->id}", $this->validData(['end_date' => '2027-12-20']))->assertSessionHas('success');
        $this->assertSame('2027-12-20', $period->fresh()->end_date->format('Y-m-d'));

        $this->actingAs($admin)->patch("/admin/periode/{$period->id}/nonaktifkan")->assertSessionHas('success');
        $this->assertFalse($period->fresh()->is_active);
        $this->actingAs($admin)->delete("/admin/periode/{$period->id}")->assertSessionHas('success');
        $this->assertDatabaseMissing('academic_periods', ['id' => $period->id]);
    }

    public function test_activating_period_disables_previous_period_atomically(): void
    {
        $admin = User::factory()->admin()->create();
        $old = AcademicPeriod::factory()->create(['academic_year' => '2026/2027', 'semester' => Semester::Odd, 'is_active' => true]);
        $new = AcademicPeriod::factory()->create(['academic_year' => '2027/2028', 'semester' => Semester::Odd, 'is_active' => false, 'start_date' => '2027-07-01', 'end_date' => '2027-12-20']);

        $this->actingAs($admin)->patch("/admin/periode/{$new->id}/aktifkan")->assertSessionHas('success');

        $this->assertFalse($old->fresh()->is_active);
        $this->assertTrue($new->fresh()->is_active);
        $this->assertSame(1, AcademicPeriod::query()->where('is_active', true)->count());
    }

    public function test_invalid_period_dates_year_and_duplicate_combination_are_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        AcademicPeriod::factory()->create(['academic_year' => '2027/2028', 'semester' => Semester::Odd]);

        $this->actingAs($admin)->post('/admin/periode', $this->validData(['end_date' => '2027-06-01']))->assertSessionHasErrors(['academic_year', 'end_date']);
        $this->actingAs($admin)->post('/admin/periode', $this->validData(['academic_year' => '2027/2029']))->assertSessionHasErrors('academic_year');
    }

    public function test_referenced_period_cannot_be_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $period = AcademicPeriod::factory()->create();
        SchoolClass::factory()->create(['academic_period_id' => $period->id]);

        $this->actingAs($admin)->delete("/admin/periode/{$period->id}")->assertSessionHas('error');
        $this->assertDatabaseHas('academic_periods', ['id' => $period->id]);
    }

    public function test_referenced_period_identity_and_date_range_are_immutable_but_status_can_change(): void
    {
        $admin = User::factory()->admin()->create();
        $period = AcademicPeriod::factory()->create();
        SchoolClass::factory()->create(['academic_period_id' => $period->id]);

        foreach ([
            'academic_year' => '2027/2028',
            'semester' => Semester::Even->value,
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-20',
        ] as $field => $value) {
            $this->actingAs($admin)->put("/admin/periode/{$period->id}", $this->validData([
                'academic_year' => $period->academic_year,
                'semester' => $period->semester->value,
                'start_date' => $period->start_date->format('Y-m-d'),
                'end_date' => $period->end_date->format('Y-m-d'),
                $field => $value,
            ]))->assertSessionHasErrors($field);
        }

        $this->assertSame('2026-07-13', $period->fresh()->start_date->format('Y-m-d'));

        $this->actingAs($admin)->put("/admin/periode/{$period->id}", $this->validData([
            'academic_year' => $period->academic_year,
            'semester' => $period->semester->value,
            'start_date' => $period->start_date->format('Y-m-d'),
            'end_date' => $period->end_date->format('Y-m-d'),
            'is_active' => '0',
        ]))->assertSessionDoesntHaveErrors();
        $this->assertFalse($period->fresh()->is_active);
    }

    private function validData(array $overrides = []): array
    {
        return array_merge([
            'academic_year' => '2027/2028',
            'semester' => Semester::Odd->value,
            'start_date' => '2027-07-01',
            'end_date' => '2027-12-19',
            'is_active' => '1',
        ], $overrides);
    }
}
