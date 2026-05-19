<?php

namespace App\Services\Dashboard;

use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Support\Facades\Cache;

class DashboardStatsService
{
    public const CACHE_KEY = 'dashboard:stats';

    public function ttl(): int
    {
        return config('blog.dashboard.cache_ttl', 300);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function get(): ?array
    {
        /** @var array<string, mixed>|null $stats */
        $stats = Cache::get(self::CACHE_KEY);

        return $stats;
    }

    /**
     * @return array<string, mixed>
     */
    public function refresh(): array
    {
        $stats = $this->build();

        Cache::put(self::CACHE_KEY, $stats, $this->ttl());

        return $stats;
    }

    /**
     * @return array<string, mixed>
     */
    private function build(): array
    {
        $now = now();

        $summary = [
            'total_posts' => Post::query()->count(),
            'published_posts' => Post::query()->published()->count(),
            'draft_posts' => Post::query()->where('status', PostStatus::Draft)->count(),
            'featured_posts' => Post::query()->where('is_featured', true)->count(),
            'trending_posts' => Post::query()->where('is_trending', true)->count(),
            'categories_count' => Category::query()->count(),
            'published_last_7_days' => Post::query()
                ->published()
                ->where('published_at', '>=', $now->copy()->subDays(7))
                ->count(),
            'published_last_30_days' => Post::query()
                ->published()
                ->where('published_at', '>=', $now->copy()->subDays(30))
                ->count(),
        ];

        $chart = collect(range(5, 0))
            ->map(function (int $monthsAgo) use ($now): array {
                $start = $now->copy()->subMonths($monthsAgo)->startOfMonth();
                $end = $start->copy()->endOfMonth();

                return [
                    'month' => $start->translatedFormat('M'),
                    'year' => $start->year,
                    'count' => Post::query()
                        ->published()
                        ->whereBetween('published_at', [$start, $end])
                        ->count(),
                ];
            })
            ->values()
            ->all();

        $topCategories = Category::query()
            ->withCount(['posts' => fn ($query) => $query->published()])
            ->orderByDesc('posts_count')
            ->limit(5)
            ->get()
            ->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'count' => $category->posts_count,
            ])
            ->values()
            ->all();

        $recentPosts = Post::query()
            ->with('category')
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn (Post $post) => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'status' => $post->status->value,
                'is_featured' => $post->is_featured,
                'is_trending' => $post->is_trending,
                'published_at' => $post->published_at?->toISOString(),
                'published_at_formatted' => $post->published_at?->translatedFormat('d M Y'),
                'category' => $post->category ? [
                    'name' => $post->category->name,
                    'slug' => $post->category->slug,
                ] : null,
            ])
            ->values()
            ->all();

        return [
            'summary' => $summary,
            'posts_by_month' => $chart,
            'top_categories' => $topCategories,
            'recent_posts' => $recentPosts,
            'meta' => [
                'cached_at' => $now->toISOString(),
                'cached_at_formatted' => $now->translatedFormat('d M Y, H:i'),
            ],
        ];
    }
}
