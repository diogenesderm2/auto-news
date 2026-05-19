<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Posts\SyncPostFeaturedImage;
use App\Enums\PostStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePostRequest;
use App\Http\Requests\Admin\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function index(Request $request): Response
    {
        $view = $this->resolveListView($request);

        $posts = Post::query()
            ->when($view === 'trash', fn ($query) => $query->onlyTrashed())
            ->when($view === 'draft', fn ($query) => $query->where('status', PostStatus::Draft))
            ->when($view === 'published', fn ($query) => $query->where('status', PostStatus::Published))
            ->with(['category', 'author'])
            ->latest($view === 'trash' ? 'deleted_at' : 'updated_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/posts/Index', [
            'posts' => PostResource::collection($posts),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'total' => $posts->total(),
            ],
            'view' => $view,
            'counts' => $this->postListCounts(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/posts/Form', [
            'post' => null,
            'categories' => $this->categoryOptions(),
        ]);
    }

    public function store(StorePostRequest $request, SyncPostFeaturedImage $syncImage): RedirectResponse
    {
        $validated = $request->validated();

        $post = new Post([
            'title' => $validated['title'],
            'slug' => $validated['slug'] ?? Str::slug($validated['title']),
            'excerpt' => $validated['excerpt'],
            'body' => $validated['body'],
            'category_id' => $validated['category_id'],
            'user_id' => $request->user()->id,
            'status' => PostStatus::from($validated['status']),
            'is_featured' => $request->boolean('is_featured'),
            'is_trending' => $request->boolean('is_trending'),
            'published_at' => $validated['published_at'] ?? ($validated['status'] === PostStatus::Published->value ? now() : null),
        ]);

        $syncImage->handle(
            $post,
            $request->file('featured_image'),
            $validated['featured_image_url'] ?? null,
        );

        $post->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Post criado com sucesso.']);

        return to_route('admin.posts.index');
    }

    public function edit(Post $post): Response
    {
        $post->load(['category', 'author']);

        return Inertia::render('admin/posts/Form', [
            'post' => (new PostResource($post))->resolve(),
            'categories' => $this->categoryOptions(),
        ]);
    }

    public function update(UpdatePostRequest $request, Post $post, SyncPostFeaturedImage $syncImage): RedirectResponse
    {
        $validated = $request->validated();

        $post->fill([
            'title' => $validated['title'],
            'slug' => $validated['slug'] ?? Str::slug($validated['title']),
            'excerpt' => $validated['excerpt'],
            'body' => $validated['body'],
            'category_id' => $validated['category_id'],
            'status' => PostStatus::from($validated['status']),
            'is_featured' => $request->boolean('is_featured'),
            'is_trending' => $request->boolean('is_trending'),
            'published_at' => $validated['published_at'] ?? $post->published_at,
        ]);

        if ($post->status === PostStatus::Published && $post->published_at === null) {
            $post->published_at = now();
        }

        $syncImage->handle(
            $post,
            $request->file('featured_image'),
            $validated['featured_image_url'] ?? null,
            $request->boolean('remove_featured_image'),
        );

        $post->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Post atualizado com sucesso.']);

        return to_route('admin.posts.index');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);

        $post->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Post movido para a lixeira. Você pode restaurá-lo quando quiser.',
        ]);

        return to_route('admin.posts.index');
    }

    public function restore(int $post): RedirectResponse
    {
        $model = Post::onlyTrashed()->findOrFail($post);

        $this->authorize('restore', $model);

        $model->restore();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Post restaurado com sucesso.']);

        return to_route('admin.posts.index', ['view' => 'trash']);
    }

    public function forceDestroy(int $post): RedirectResponse
    {
        $model = Post::onlyTrashed()->findOrFail($post);

        $this->authorize('forceDelete', $model);

        $model->forceDelete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Post excluído permanentemente.',
        ]);

        return to_route('admin.posts.index', ['view' => 'trash']);
    }

  /**
     * @return 'all'|'published'|'draft'|'trash'
     */
    private function resolveListView(Request $request): string
    {
        return match ($request->string('view')->toString()) {
            'published', 'draft', 'trash' => $request->string('view')->toString(),
            default => 'all',
        };
    }

    /**
     * @return array{all: int, published: int, draft: int, trash: int}
     */
    private function postListCounts(): array
    {
        return [
            'all' => Post::query()->count(),
            'published' => Post::query()->where('status', PostStatus::Published)->count(),
            'draft' => Post::query()->where('status', PostStatus::Draft)->count(),
            'trash' => Post::query()->onlyTrashed()->count(),
        ];
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    private function categoryOptions(): array
    {
        return Category::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
            ])
            ->all();
    }
}
