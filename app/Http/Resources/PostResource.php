<?php

namespace App\Http\Resources;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Post
 */
class PostResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'body' => $this->when(
                $request->routeIs('posts.show', 'admin.posts.edit', 'admin.posts.create'),
                $this->body,
            ),
            'featured_image' => $this->featuredImageUrl(),
            'status' => $this->status->value,
            'is_featured' => $this->is_featured,
            'is_trending' => $this->is_trending,
            'published_at' => $this->published_at?->toISOString(),
            'published_at_formatted' => $this->published_at?->translatedFormat('d M'),
            'updated_at' => $this->updated_at?->toISOString(),
            'updated_at_formatted' => $this->updated_at?->translatedFormat('d M Y, H:i'),
            'deleted_at' => $this->deleted_at?->toISOString(),
            'deleted_at_formatted' => $this->deleted_at?->translatedFormat('d M Y, H:i'),
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
            'author' => $this->whenLoaded('author', fn () => [
                'id' => $this->author->id,
                'name' => $this->author->name,
            ]),
        ];
    }
}
