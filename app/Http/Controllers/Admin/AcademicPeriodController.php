<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Semester;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AcademicPeriodRequest;
use App\Models\AcademicPeriod;
use App\Services\AcademicPeriodService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AcademicPeriodController extends Controller
{
    public function __construct(private readonly AcademicPeriodService $periods) {}

    public function index(): View
    {
        $periods = AcademicPeriod::query()->withCount('classes')->latest('start_date')->paginate(20);

        return view('admin.periods.index', compact('periods'));
    }

    public function create(): View
    {
        return view('admin.periods.form', ['period' => new AcademicPeriod, 'semesters' => Semester::cases()]);
    }

    public function store(AcademicPeriodRequest $request): RedirectResponse
    {
        $period = $this->periods->create($request->validated());
        Log::info('Periode akademik dibuat.', ['admin_id' => $request->user()->id, 'period_id' => $period->id]);

        return to_route('admin.periods.index')->with('success', 'Periode akademik berhasil ditambahkan.');
    }

    public function edit(AcademicPeriod $period): View
    {
        return view('admin.periods.form', [
            'period' => $period,
            'semesters' => Semester::cases(),
            'referenced' => $period->classes()->exists(),
        ]);
    }

    public function update(AcademicPeriodRequest $request, AcademicPeriod $period): RedirectResponse
    {
        $this->periods->update($period, $request->validated());
        Log::info('Periode akademik diperbarui.', ['admin_id' => $request->user()->id, 'period_id' => $period->id]);

        return to_route('admin.periods.index')->with('success', 'Periode akademik berhasil diperbarui.');
    }

    public function activate(Request $request, AcademicPeriod $period): RedirectResponse
    {
        $this->periods->activate($period);
        Log::info('Periode akademik diaktifkan.', ['admin_id' => $request->user()->id, 'period_id' => $period->id]);

        return back()->with('success', 'Periode aktif berhasil diubah. Periode sebelumnya telah dinonaktifkan.');
    }

    public function deactivate(Request $request, AcademicPeriod $period): RedirectResponse
    {
        $this->periods->deactivate($period);
        Log::info('Periode akademik dinonaktifkan.', ['admin_id' => $request->user()->id, 'period_id' => $period->id]);

        return back()->with('success', 'Periode akademik berhasil dinonaktifkan.');
    }

    public function destroy(Request $request, AcademicPeriod $period): RedirectResponse
    {
        if ($period->classes()->exists()) {
            return back()->with('error', 'Periode tidak dapat dihapus karena sudah digunakan oleh kelas atau data akademik. Nonaktifkan periode sebagai gantinya.');
        }

        $id = $period->id;
        $period->delete();
        Log::info('Periode akademik dihapus.', ['admin_id' => $request->user()->id, 'period_id' => $id]);

        return to_route('admin.periods.index')->with('success', 'Periode akademik berhasil dihapus.');
    }
}
