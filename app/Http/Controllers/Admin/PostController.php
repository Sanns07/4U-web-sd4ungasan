<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PostActionRequest;
use App\Http\Requests\Admin\PostRequest;
use App\Models\Post;
use App\Services\PostContentService;
use App\Services\PostHtmlSanitizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PostController extends Controller
{
    public function __construct(
        private readonly PostContentService $contentService,
        private readonly PostHtmlSanitizer $sanitizer,
    ) {}

    public function index(Request $request): View
    {
        $posts = Post::query()
            ->with('author')
            ->withCount('images')
            ->when($request->string('q')->trim()->isNotEmpty(), function (Builder $query) use ($request): void {
                $term = '%'.$request->string('q')->trim().'%';
                $query->where(fn (Builder $nested) => $nested
                    ->where('title', 'like', $term)
                    ->orWhere('excerpt', 'like', $term)
                    ->orWhereHas('author', fn (Builder $author) => $author->where('username', 'like', $term)));
            })
            ->when(PostType::tryFrom((string) $request->input('type')), fn (Builder $query, PostType $type) => $query->where('type', $type))
            ->when(PostStatus::tryFrom((string) $request->input('status')), fn (Builder $query, PostStatus $status) => $query->where('status', $status))
            ->when($this->selectedMonth($request), function (Builder $query, Carbon $month): void {
                $query->whereBetween('published_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()]);
            })
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        $months = Post::query()
            ->whereNotNull('published_at')
            ->orderByDesc('published_at')
            ->pluck('published_at')
            ->map(fn (mixed $date): Carbon => Carbon::parse($date))
            ->unique(fn (Carbon $date): string => $date->format('Y-m'))
            ->mapWithKeys(fn (Carbon $date): array => [
                $date->format('Y-m') => $date->locale('id')->translatedFormat('F Y'),
            ]);

        return view('admin.posts.index', [
            'posts' => $posts,
            'types' => PostType::cases(),
            'statuses' => PostStatus::cases(),
            'months' => $months,
        ]);
    }

    public function create(): View
    {
        return view('admin.posts.form', $this->editorViewData(new Post));
    }

    public function store(PostRequest $request): RedirectResponse
    {
        $post = $this->contentService->save(new Post, $request->validated(), $request->user());
        Log::info('Konten website ditambahkan.', ['admin_id' => $request->user()->id, 'post_id' => $post->id]);

        return to_route('admin.posts.edit', $post)->with('success', 'Konten berhasil disimpan.');
    }

    public function edit(Post $post): View
    {
        return view('admin.posts.form', $this->editorViewData($post->load('images')));
    }

    public function update(PostRequest $request, Post $post): RedirectResponse
    {
        $post = $this->contentService->save($post, $request->validated(), $request->user());
        Log::info('Konten website diperbarui.', ['admin_id' => $request->user()->id, 'post_id' => $post->id]);

        return to_route('admin.posts.edit', $post)->with('success', 'Konten berhasil diperbarui.');
    }

    public function preview(PostActionRequest $request, Post $post): View
    {
        $post->load(['author', 'images']);

        return view('admin.posts.preview', [
            'post' => $post,
            'renderedBodyHtml' => $post->renderedBodyHtml(),
        ]);
    }

    public function publish(PostActionRequest $request, Post $post): RedirectResponse
    {
        $this->contentService->publish($post);
        Log::info('Konten website diterbitkan.', ['admin_id' => $request->user()->id, 'post_id' => $post->id]);

        return back()->with('success', 'Konten berhasil diterbitkan.');
    }

    public function archive(PostActionRequest $request, Post $post): RedirectResponse
    {
        $this->contentService->archive($post);
        Log::info('Konten website diarsipkan.', ['admin_id' => $request->user()->id, 'post_id' => $post->id]);

        return back()->with('success', 'Konten berhasil diarsipkan dan tidak lagi tampil di halaman publik.');
    }

    public function destroy(PostActionRequest $request, Post $post): RedirectResponse
    {
        $postId = $post->id;
        $this->contentService->deleteArchived($post);
        Log::info('Konten website dihapus permanen.', ['admin_id' => $request->user()->id, 'post_id' => $postId]);

        return to_route('admin.posts.index')->with('success', 'Konten arsip berhasil dihapus permanen.');
    }

    /** @return array<string, mixed> */
    private function editorViewData(Post $post): array
    {
        $newImageSlots = old('inline_images', []);
        $newImageSlots = is_array($newImageSlots) ? $newImageSlots : [];
        $tokens = array_map('strval', array_keys($newImageSlots));
        $oldBody = old('body_html');

        if (is_string($oldBody)) {
            $safeBody = $this->sanitizer->sanitize($oldBody, $post->images->modelKeys(), $tokens);
            $editorHtml = $this->sanitizer->renderImages($safeBody, $post->images, true, $tokens);
        } elseif ($post->exists) {
            $editorHtml = $post->editorBodyHtml();
        } else {
            $editorHtml = '<p><br></p>';
        }

        return [
            'post' => $post,
            'types' => PostType::cases(),
            'statuses' => PostStatus::cases(),
            'editorHtml' => $editorHtml,
            'newImageSlots' => $newImageSlots,
        ];
    }

    private function selectedMonth(Request $request): ?Carbon
    {
        $value = (string) $request->input('month');

        $year = (int) substr($value, 0, 4);

        if (
            preg_match('/\A\d{4}-(?:0[1-9]|1[0-2])\z/', $value) !== 1
            || $year < 2000
            || $year > now()->year + 1
        ) {
            return null;
        }

        return Carbon::createFromFormat('!Y-m', $value);
    }
}
