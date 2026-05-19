<?php

use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('homepage displays published posts', function () {
    $category = Category::factory()->create();
    $user = User::factory()->create();

    Post::factory()->for($category)->for($user)->create([
        'title' => 'Post de teste público',
        'slug' => 'post-de-teste-publico',
        'status' => PostStatus::Published,
        'published_at' => now(),
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('blog/Index')
            ->has('latest.data', 1)
        );
});

test('guests can view a published post', function () {
    $post = Post::factory()->create([
        'status' => PostStatus::Published,
        'published_at' => now(),
        'slug' => 'meu-post-publicado',
    ]);

    $this->get(route('posts.show', $post->slug))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('blog/Show')
            ->where('post.title', $post->title)
        );
});

test('draft posts are not visible publicly', function () {
    $post = Post::factory()->draft()->create([
        'slug' => 'post-rascunho-secreto',
    ]);

    $this->get(route('posts.show', $post->slug))->assertNotFound();
});

test('category page lists published posts', function () {
    $category = Category::factory()->create(['slug' => 'eletricos']);

    Post::factory()->count(2)->for($category)->create([
        'status' => PostStatus::Published,
        'published_at' => now(),
    ]);

    $this->get(route('categories.show', $category->slug))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('blog/Category')
            ->has('posts.data', 2)
            ->has('blogCategories', 1)
        );
});
