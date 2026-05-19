<?php

use App\Enums\PostStatus;
use App\Jobs\WarmBlogCache;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use App\Services\Blog\BlogCacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
});

test('blog home page uses cache', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    Post::factory()->for($user)->for($category)->create([
        'title' => 'Post em cache',
        'slug' => 'post-em-cache',
        'status' => PostStatus::Published,
        'published_at' => now(),
    ]);

    $this->get(route('home'))->assertOk();

    expect(Cache::has(BlogCacheService::HOME_KEY))->toBeTrue();

    Post::query()->delete();

    $this->get(route('home'))->assertOk();

    $cached = Cache::get(BlogCacheService::HOME_KEY);

    expect($cached['latest']['data'])->not->toBeEmpty();
});

test('post changes invalidate blog cache and dispatch warm job', function () {
    Queue::fake();

    $user = User::factory()->create();
    $category = Category::factory()->create(['slug' => 'mercado']);

    $post = Post::factory()->for($user)->for($category)->create([
        'title' => 'Original',
        'slug' => 'original-cache',
        'status' => PostStatus::Published,
        'published_at' => now(),
    ]);

    app(BlogCacheService::class)->home();

    expect(Cache::has(BlogCacheService::HOME_KEY))->toBeTrue();

    $post->update(['title' => 'Atualizado']);

    expect(Cache::has(BlogCacheService::HOME_KEY))->toBeFalse();

    Queue::assertPushed(WarmBlogCache::class);
});

test('blog home normalizes legacy cached post lists', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $post = Post::factory()->for($user)->for($category)->create([
        'title' => 'Post legado',
        'slug' => 'post-legado',
        'status' => PostStatus::Published,
        'published_at' => now(),
    ]);

    $legacyPost = [
        'id' => $post->id,
        'title' => $post->title,
        'slug' => $post->slug,
    ];

    Cache::put(BlogCacheService::HOME_KEY, [
        'trending' => [$legacyPost],
        'latest' => [$legacyPost],
        'featured' => [],
        'moreNews' => [],
        'categories' => [],
    ], 3600);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('blog/Index')
            ->has('trending.data', 1)
            ->has('latest.data', 1)
        );
});

test('warm blog cache job rebuilds home cache', function () {
    $user = User::factory()->create();

    Post::factory()->for($user)->create([
        'status' => PostStatus::Published,
        'published_at' => now(),
    ]);

    WarmBlogCache::dispatchSync();

    expect(Cache::has(BlogCacheService::HOME_KEY))->toBeTrue()
        ->and(Cache::has(BlogCacheService::CATEGORIES_NAV_KEY))->toBeTrue();
});
