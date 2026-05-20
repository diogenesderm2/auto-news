<?php

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('blog pages share ads config on public routes', function () {
    config([
        'blog.adsense.enabled' => true,
        'blog.adsense.client' => 'ca-pub-1234567890',
        'blog.adsense.slots' => [
            'leaderboard' => '1111111111',
            'article_top' => '2222222222',
        ],
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('blogAds')
            ->where('blogAds.enabled', true)
            ->where('blogAds.client', 'ca-pub-1234567890')
            ->where('blogAds.slots.leaderboard', '1111111111')
        );
});

test('admin pages do not share blog ads config', function () {
    config([
        'blog.adsense.enabled' => true,
        'blog.adsense.client' => 'ca-pub-1234567890',
    ]);

    $user = \App\Models\User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.posts.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('blogAds', null));
});

test('blog ads are disabled when client id is missing', function () {
    config([
        'blog.adsense.enabled' => true,
        'blog.adsense.client' => null,
    ]);

    Post::factory()->create([
        'status' => \App\Enums\PostStatus::Published,
        'published_at' => now(),
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('blogAds.enabled', false)
        );
});

test('post page includes blog ads config', function () {
    config([
        'blog.adsense.enabled' => true,
        'blog.adsense.client' => 'ca-pub-test',
        'blog.adsense.slots' => ['article_sidebar' => '999'],
    ]);

    $post = Post::factory()->create([
        'status' => \App\Enums\PostStatus::Published,
        'published_at' => now(),
    ]);

    $this->get(route('posts.show', $post->slug))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('blogAds.slots.article_sidebar', '999')
        );
});
