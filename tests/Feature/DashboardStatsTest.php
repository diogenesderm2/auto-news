<?php

use App\Enums\PostStatus;
use App\Jobs\RefreshDashboardStatsCache;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use App\Services\Dashboard\DashboardStatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
});

test('dashboard shows real stats from database', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['name' => 'Elétricos', 'slug' => 'eletricos']);

    Post::factory()->for($user)->for($category)->create([
        'title' => 'Post publicado dashboard',
        'slug' => 'post-publicado-dashboard',
        'status' => PostStatus::Published,
        'published_at' => now(),
        'is_featured' => true,
    ]);

    Post::factory()->for($user)->for($category)->draft()->create([
        'title' => 'Rascunho dashboard',
        'slug' => 'rascunho-dashboard',
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('dashboard.summary')
            ->where('dashboard.summary.published_posts', 1)
            ->where('dashboard.summary.draft_posts', 1)
            ->where('dashboard.summary.featured_posts', 1)
            ->where('dashboard.summary.categories_count', 1)
            ->has('dashboard.recent_posts', 2)
            ->has('dashboard.posts_by_month', 6)
        );
});

test('dashboard stats are cached and refreshed via queue', function () {
    Queue::fake();

    $user = User::factory()->create();
    Post::factory()->for($user)->create([
        'status' => PostStatus::Published,
        'published_at' => now(),
    ]);

    $this->actingAs($user)->get(route('dashboard'))->assertOk();

    expect(Cache::has(DashboardStatsService::CACHE_KEY))->toBeTrue();

    Queue::assertPushed(RefreshDashboardStatsCache::class);
});

test('refresh dashboard stats job populates cache', function () {
    $user = User::factory()->create();

    Post::factory()->count(3)->for($user)->create([
        'status' => PostStatus::Published,
        'published_at' => now(),
    ]);

    RefreshDashboardStatsCache::dispatchSync();

    $stats = Cache::get(DashboardStatsService::CACHE_KEY);

    expect($stats)->toBeArray()
        ->and($stats['summary']['published_posts'])->toBe(3);
});
