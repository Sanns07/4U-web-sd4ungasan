<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrganizationCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrganizationMemberRequest;
use App\Models\OrganizationMember;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class OrganizationMemberController extends Controller
{
    public function index(): View
    {
        $members = OrganizationMember::query()
            ->with('teacher')
            ->orderBy('display_order')
            ->orderBy('id')
            ->paginate(20);

        return view('admin.organization.index', compact('members'));
    }

    public function create(): View
    {
        return $this->formView(new OrganizationMember);
    }

    public function store(OrganizationMemberRequest $request): RedirectResponse
    {
        return $this->persist($request, new OrganizationMember, 'ditambahkan');
    }

    public function edit(OrganizationMember $organizationMember): View
    {
        return $this->formView($organizationMember);
    }

    public function update(OrganizationMemberRequest $request, OrganizationMember $organizationMember): RedirectResponse
    {
        return $this->persist($request, $organizationMember, 'diperbarui');
    }

    public function destroy(OrganizationMember $organizationMember): RedirectResponse
    {
        $photo = $organizationMember->photo_path;
        $id = $organizationMember->id;
        $organizationMember->delete();

        if ($photo) {
            Storage::disk('public')->delete($photo);
        }

        Log::info('Anggota struktur organisasi dihapus.', ['admin_id' => request()->user()->id, 'organization_member_id' => $id]);

        return to_route('admin.organization-members.index')->with('success', 'Anggota struktur organisasi berhasil dihapus.');
    }

    private function formView(OrganizationMember $organizationMember): View
    {
        return view('admin.organization.form', [
            'member' => $organizationMember,
            'teachers' => Teacher::query()->where('is_active', true)->orderBy('name')->get(),
            'categories' => OrganizationCategory::cases(),
        ]);
    }

    private function persist(OrganizationMemberRequest $request, OrganizationMember $organizationMember, string $action): RedirectResponse
    {
        $oldPhoto = $organizationMember->photo_path;
        $newPhoto = $request->file('photo')?->store('organization-members', 'public');

        try {
            $data = Arr::except($request->validated(), ['photo']);

            if ($data['teacher_id']) {
                $data['name'] = null;
            }

            if ($newPhoto) {
                $data['photo_path'] = $newPhoto;
            }

            $organizationMember->fill($data)->save();

            if ($newPhoto && $oldPhoto) {
                Storage::disk('public')->delete($oldPhoto);
            }

            Log::info("Anggota struktur organisasi {$action}.", [
                'admin_id' => $request->user()->id,
                'organization_member_id' => $organizationMember->id,
            ]);

            return to_route('admin.organization-members.index')->with('success', "Anggota struktur organisasi berhasil {$action}.");
        } catch (Throwable $exception) {
            if ($newPhoto) {
                Storage::disk('public')->delete($newPhoto);
            }

            report($exception);

            return back()->withInput()->with('error', 'Data organisasi belum dapat disimpan. Silakan coba lagi.');
        }
    }
}
