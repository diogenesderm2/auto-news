<?php

namespace App\Services\Blog;

use App\Http\Resources\PostResource;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class BlogCacheService
{
    public const HOME_KEY = 'blog:v2:home';

    public const CATEGORIES_NAV_KEY = 'blog:v2:categories:nav';

    public static function postKey(string $slug): string
    {
        return "blog:v2:post:{$slug}";
    }

    public static function categoryKey(string $slug, int $page): string
    {
        return "blog:v2:category:{$slug}:page:{$page}";
    }

    public function ttl(): int
    {
        return config('blog.cache.ttl', 3600);
    }

    public function categoriesNavTtl(): int
    {
        return config('blog.cache.categories_nav_ttl', 3600);
    }

    /**
     * @return array<string, mixed>
     */
    public function home(): array
    {
        $data = Cache::remember(self::HOME_KEY, $this->ttl(), fn (): array => $this->buildHomeData());

        return $this->normalizeHomeData($data);
    }

    /**
     * @return array<string, mixed>
     */
    public function postShow(string $slug): array
    {
        $data = Cache::remember(
            self::postKey($slug),
            $this->ttl(),
            fn (): array => $this->buildPostShowData($slug),
        );

        return [
            'post' => $data['post'],
            'related' => $this->normalizePostsPayload($data['related'] ?? []),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function categoryShow(string $slug, int $page): array
    {
        $data = Cache::remember(
            self::categoryKey($slug, $page),
            $this->ttl(),
            fn (): array => $this->buildCategoryShowData($slug, $page),
        );

        return [
            ...$data,
            'posts' => $this->normalizePostsPayload($data['posts'] ?? []),
        ];
    }

    /**
     * @return list<array{id: int, name: string, slug: string, posts_count: int}>
     */
    public function categoriesNav(): array
    {
        return Cache::remember(
            self::CATEGORIES_NAV_KEY,
            $this->categoriesNavTtl(),
            fn (): array => $this->buildCategoriesNav(),
        );
    }

    public function forgetAll(): void
    {
        Cache::forget(self::HOME_KEY);
        Cache::forget(self::CATEGORIES_NAV_KEY);
    }

    public function forgetPost(string $slug): void
    {
        Cache::forget(self::postKey($slug));
    }

    public function forgetCategory(string $slug): void
    {
        $category = Category::query()->where('slug', $slug)->first();

        if ($category === null) {
            return;
        }

        $lastPage = Post::query()
            ->published()
            ->where('category_id', $category->id)
            ->paginate(12)
            ->lastPage();

        for ($page = 1; $page <= max(1, $lastPage); $page++) {
            Cache::forget(self::categoryKey($slug, $page));
        }
    }

    public function warm(): void
    {
        $this->forgetAll();
        $this->home();
        $this->categoriesNav();
    }

    /**
     * @return array<string, mixed>
     */
    private function buildHomeData(): array
    {
        $trending = Post::query()
            ->published()
            ->with(['category', 'author'])
            ->where('is_trending', true)
            ->latest('published_at')
            ->limit(4)
            ->get();

        if ($trending->isEmpty()) {
            $trending = Post::query()
                ->published()
                ->with(['category', 'author'])
                ->latest('published_at')
                ->limit(4)
                ->get();
        }

        $latest = Post::query()
            ->published()
            ->with(['category', 'author'])
            ->latest('published_at')
            ->limit(12)
            ->get();

        $featured = Post::query()
            ->published()
            ->with(['category', 'author'])
            ->where('is_featured', true)
            ->latest('published_at')
            ->limit(3)
            ->get();

        $moreNews = Post::query()
            ->published()
            ->with(['category', 'author'])
            ->latest('published_at')
            ->skip(12)
            ->limit(8)
            ->get();

        $categories = $this->buildCategoriesNav();

        return [
            'trending' => $this->postsPayload($trending),
            'latest' => $this->postsPayload($latest),
            'featured' => $this->postsPayload($featured),
            'moreNews' => $this->postsPayload($moreNews),
            'categories' => $categories,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPostShowData(string $slug): array
    {
        $post = Post::query()
            ->published()
            ->where('slug', $slug)
            ->with(['category', 'author'])
            ->firstOrFail();

        $related = Post::query()
            ->published()
            ->with(['category'])
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->latest('published_at')
            ->limit(4)
            ->get();

        return [
            'post' => (new PostResource($post))->resolve(),
            'related' => $this->postsPayload($related),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildCategoryShowData(string $slug, int $page): array
    {
        $category = Category::query()
            ->where('slug', $slug)
            ->firstOrFail();

        /** @var LengthAwarePaginator<int, Post> $posts */
        $posts = Post::query()
            ->published()
            ->with(['category', 'author'])
            ->where('category_id', $category->id)
            ->latest('published_at')
            ->paginate(12, ['*'], 'page', $page);

        return [
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ],
            'posts' => $this->postsPayload($posts->getCollection()),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>|list<array<string, mixed>>  $data
     * @return array<string, mixed>
     */
    private function normalizeHomeData(array $data): array
    {
        return [
            'trending' => $this->normalizePostsPayload($data['trending'] ?? []),
            'latest' => $this->normalizePostsPayload($data['latest'] ?? []),
            'featured' => $this->normalizePostsPayload($data['featured'] ?? []),
            'moreNews' => $this->normalizePostsPayload($data['moreNews'] ?? []),
            'categories' => $data['categories'] ?? [],
        ];
    }

    /**
     * @param  array<string, mixed>|list<array<string, mixed>>  $payload
     * @return array{data: list<array<string, mixed>>}
     */
    private function normalizePostsPayload(array $payload): array
    {
        if (isset($payload['data']) && is_array($payload['data'])) {
            return ['data' => array_values($payload['data'])];
        }

        if ($payload === [] || array_is_list($payload)) {
            return ['data' => array_values($payload)];
        }

        return ['data' => []];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Post>  $posts
     * @return array{data: list<array<string, mixed>>}
     */
    private function postsPayload(\Illuminate\Support\Collection $posts): array
    {
        return [
            'data' => $posts
                ->map(fn (Post $post) => (new PostResource($post))->resolve())
                ->values()
                ->all(),
        ];
    }

    /**
     * @return list<array{id: int, name: string, slug: string, posts_count: int}>
     */
    private function buildCategoriesNav(): array
    {
        return Category::query()
            ->withCount(['posts' => fn ($query) => $query->published()])
            ->orderBy('name')
            ->get()
            ->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'posts_count' => $category->posts_count,
            ])
            ->values()
            ->all();
    }
}
