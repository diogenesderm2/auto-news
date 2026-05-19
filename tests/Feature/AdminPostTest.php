<?php

use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('guests cannot access admin posts', function () {
    $this->get(route('admin.posts.index'))->assertRedirect(route('login'));
});

test('authenticated users can manage posts', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $category = Category::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.posts.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/posts/Index')
            ->where('view', 'all')
            ->has('counts'));

    $this->actingAs($user)
        ->get(route('admin.posts.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('admin/posts/Form'));

    $this->actingAs($user)
        ->post(route('admin.posts.store'), [
            'title' => 'Novo elétrico no Brasil',
            'excerpt' => 'Resumo da notícia sobre elétricos.',
            'body' => '<p>Conteúdo completo da notícia.</p>',
            'category_id' => $category->id,
            'status' => PostStatus::Published->value,
            'is_featured' => true,
            'is_trending' => false,
            'featured_image' => UploadedFile::fake()->image('carro.jpg'),
        ])
        ->assertRedirect(route('admin.posts.index'));

    $post = Post::query()->where('slug', 'novo-eletrico-no-brasil')->first();

    expect($post)->not->toBeNull()
        ->and($post->is_featured)->toBeTrue()
        ->and($post->featured_image)->not->toBeNull();

    $this->actingAs($user)
        ->put(route('admin.posts.update', $post), [
            'title' => 'Título atualizado',
            'excerpt' => 'Resumo atualizado da notícia.',
            'body' => '<p>Conteúdo atualizado.</p>',
            'category_id' => $category->id,
            'status' => PostStatus::Published->value,
        ])
        ->assertRedirect(route('admin.posts.index'));

    expect($post->fresh()->title)->toBe('Título atualizado');

    $this->actingAs($user)
        ->delete(route('admin.posts.destroy', $post))
        ->assertRedirect(route('admin.posts.index'));

    expect(Post::query()->find($post->id))->toBeNull();

    $trashed = Post::withTrashed()->find($post->id);

    expect($trashed)->not->toBeNull()
        ->and($trashed->trashed())->toBeTrue();
});

test('soft deleted posts can be restored from trash', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user)->create([
        'status' => PostStatus::Published,
        'published_at' => now(),
    ]);

    $post->delete();

    $this->actingAs($user)
        ->get(route('admin.posts.index', ['view' => 'trash']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('view', 'trash')
            ->has('posts.data', 1)
            ->where('counts.trash', 1)
        );

    $this->actingAs($user)
        ->post(route('admin.posts.restore', $post))
        ->assertRedirect(route('admin.posts.index', ['view' => 'trash']));

    expect(Post::query()->find($post->id))->not->toBeNull()
        ->and(Post::query()->find($post->id)->trashed())->toBeFalse();
});

test('trashed posts can be permanently deleted', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $post = Post::factory()->for($user)->create([
        'featured_image' => 'posts/imagem.jpg',
        'status' => PostStatus::Published,
        'published_at' => now(),
    ]);

    Storage::disk('public')->put('posts/imagem.jpg', 'conteudo');

    $post->delete();

    $this->actingAs($user)
        ->delete(route('admin.posts.force-destroy', $post))
        ->assertRedirect(route('admin.posts.index', ['view' => 'trash']));

    expect(Post::withTrashed()->find($post->id))->toBeNull()
        ->and(Storage::disk('public')->exists('posts/imagem.jpg'))->toBeFalse();
});

test('admin posts index can filter drafts and published posts', function () {
    $user = User::factory()->create();

    Post::factory()->for($user)->create([
        'title' => 'Post publicado',
        'status' => PostStatus::Published,
        'published_at' => now(),
    ]);

    Post::factory()->for($user)->draft()->create([
        'title' => 'Post rascunho',
    ]);

    $this->actingAs($user)
        ->get(route('admin.posts.index', ['view' => 'draft']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('view', 'draft')
            ->has('posts.data', 1)
            ->where('posts.data.0.title', 'Post rascunho')
            ->where('counts.draft', 1)
            ->where('counts.published', 1)
            ->where('counts.all', 2)
        );

    $this->actingAs($user)
        ->get(route('admin.posts.index', ['view' => 'published']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('view', 'published')
            ->has('posts.data', 1)
            ->where('posts.data.0.title', 'Post publicado')
        );
});

test('trashed posts are hidden from the public site', function () {
    $post = Post::factory()->create([
        'status' => PostStatus::Published,
        'published_at' => now(),
    ]);

    $post->delete();

    $this->get(route('posts.show', $post->slug))->assertNotFound();
    $this->get(route('home'))->assertOk();
});
