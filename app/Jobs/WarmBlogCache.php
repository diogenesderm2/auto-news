<?php

namespace App\Jobs;

use App\Services\Blog\BlogCacheService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class WarmBlogCache implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly ?string $postSlug = null,
        public readonly ?string $categorySlug = null,
    ) {
        $this->onQueue(config('blog.dashboard.refresh_queue', 'default'));
    }

    public function handle(BlogCacheService $blogCache): void
    {
        $blogCache->warm();

        if ($this->postSlug !== null) {
            $blogCache->postShow($this->postSlug);
        }

        if ($this->categorySlug !== null) {
            $blogCache->categoryShow($this->categorySlug, 1);
        }
    }
}
