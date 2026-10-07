<?php

namespace App\Http\Controllers\PublicSite;

use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\SchoolProfile;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class InformationController extends Controller
{
    public function index(Request $request): View
    {
        $search = mb_substr(trim((string) $request->query('q')), 0, 120);
        $type = PostType::tryFrom((string) $request->query('tipe'));
        $year = filter_var($request->query('tahun'), FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 2000, 'max_range' => now()->year + 1],
        ]) ?: null;

        $visiblePosts = $this->visiblePosts();
        $typeCounts = [
            PostType::News->value => (clone $visiblePosts)->where('type', PostType::News)->count(),
            PostType::Announcement->value => (clone $visiblePosts)->where('type', PostType::Announcement)->count(),
        ];
        $years = (clone $visiblePosts)
            ->get(['published_at'])
            ->pluck('published_at')
            ->filter()
            ->map(fn ($date) => $date->year)
            ->unique()
            ->sortDesc()
            ->values();

        $posts = (clone $visiblePosts)
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('title', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%");
                });
            })
            ->when($type, fn ($query) => $query->where('type', $type))
            ->when($year, fn ($query) => $query->whereYear('published_at', $year))
            ->latest('published_at')
            ->paginate(7)
            ->withQueryString();

        return view('public.posts.index', [
            'schoolProfile' => SchoolProfile::query()->first(),
            'posts' => $posts,
            'featuredPost' => $posts->first(),
            'remainingPosts' => collect($posts->items())->slice(1),
            'publishedCount' => array_sum($typeCounts),
            'typeCounts' => $typeCounts,
            'years' => $years,
            'search' => $search,
            'selectedType' => $type?->value,
            'selectedYear' => $year,
            'hasFilters' => $search !== '' || $type !== null || $year !== null,
        ]);
    }

    public function show(Post $post): View
    {
        abort_unless(
            $post->status === PostStatus::Published
            && $post->published_at !== null
            && $post->published_at->lte(now()),
            404,
        );

        $relatedPosts = $this->visiblePosts()
            ->whereKeyNot($post->getKey())
            ->latest('published_at')
            ->limit(2)
            ->get();

        return view('public.posts.show', [
            'schoolProfile' => SchoolProfile::query()->first(),
            'post' => $post->load('images'),
            'relatedPosts' => $relatedPosts,
            'readingMinutes' => max(1, (int) ceil(str_word_count(strip_tags($post->body_html)) / 200)),
        ]);
    }

    private function visiblePosts(): Builder
    {
        return Post::query()
            ->where('status', PostStatus::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}
