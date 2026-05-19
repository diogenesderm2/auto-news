<?php

namespace App\Observers;

use App\Jobs\RefreshDashboardStatsCache;
use App\Jobs\WarmBlogCache;
use App\Enums\PostStatus;
use App\Models\Post;
use App\Services\Blog\BlogCacheService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostObserver
{
    public function saved(Post $post): void
    {
        $this->invalidateCaches($post);
    }

    public function deleted(Post $post): void
    {
        $this->invalidateCaches($post);
    }

    public function restored(Post $post): void
    {
        $this->invalidateCaches($post);
    }

    public function forceDeleted(Post $post): void
    {
        if (
            $post->featured_image !== null
            && ! Str::startsWith($post->featured_image, ['http://', 'https://'])
        ) {
            Storage::disk('public')->delete($post->featured_image);
        }

        $this->invalidateCaches($post);
    }

    private function invalidateCaches(Post $post): void
    {
        $blogCache = app(BlogCacheService::class);

        $blogCache->forgetAll();

        if ($post->wasChanged('slug') && is_string($post->getOriginal('slug'))) {
            $blogCache->forgetPost($post->getOriginal('slug'));
        }

        $blogCache->forgetPost($post->slug);

        $post->loadMissing('category');

        if ($post->category !== null) {
            $blogCache->forgetCategory($post->category->slug);
        }

        RefreshDashboardStatsCache::dispatch();
        WarmBlogCache::dispatch(
            postSlug: $this->shouldWarmPostShow($post) ? $post->slug : null,
            categorySlug: $post->category?->slug,
        );
    }

    private function shouldWarmPostShow(Post $post): bool
    {
        return ! $post->trashed()
            && $post->status === PostStatus::Published;
    }
}
