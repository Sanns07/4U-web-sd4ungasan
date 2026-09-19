<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SchoolProfileRequest;
use App\Models\SchoolProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class SchoolProfileController extends Controller
{
    public function edit(): View
    {
        return view('admin.school-profile.edit', ['profile' => SchoolProfile::query()->first()]);
    }

    public function update(SchoolProfileRequest $request): RedirectResponse
    {
        $profile = SchoolProfile::query()->first();
        $oldLogo = $profile?->logo_path;
        $newLogo = $request->file('logo')?->store('school-logos', 'public');

        try {
            $data = Arr::except($request->validated(), ['logo']);
            $data['updated_by'] = $request->user()->id;

            if ($newLogo) {
                $data['logo_path'] = $newLogo;
            }

            $profile = SchoolProfile::query()->updateOrCreate(['id' => $profile?->id ?? 1], $data);

            if ($newLogo && $oldLogo) {
                Storage::disk('public')->delete($oldLogo);
            }

            Log::info('Profil sekolah diperbarui.', ['admin_id' => $request->user()->id, 'profile_id' => $profile->id]);

            return to_route('admin.school-profile.edit')->with('success', 'Profil sekolah berhasil disimpan.');
        } catch (Throwable $exception) {
            if ($newLogo) {
                Storage::disk('public')->delete($newLogo);
            }

            report($exception);

            return back()->withInput()->with('error', 'Profil sekolah belum dapat disimpan. Silakan coba lagi.');
        }
    }
}
