<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\OrganizationMember;
use App\Models\SchoolProfile;
use Illuminate\Contracts\View\View;

class ProfileController extends Controller
{
    public function __invoke(): View
    {
        $schoolProfile = SchoolProfile::query()->first();
        $missionItems = $schoolProfile?->mission
            ? preg_split('/\r\n|\r|\n/', trim($schoolProfile->mission), -1, PREG_SPLIT_NO_EMPTY)
            : [];

        return view('public.profile', [
            'schoolProfile' => $schoolProfile,
            'missionItems' => $missionItems,
            'organizationMembers' => OrganizationMember::query()
                ->with('teacher')
                ->where('is_active', true)
                ->orderBy('display_order')
                ->orderBy('id')
                ->get(),
        ]);
    }
}
