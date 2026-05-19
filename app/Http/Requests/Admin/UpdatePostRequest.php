<?php

namespace App\Http\Requests\Admin;

use App\Enums\PostStatus;
use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Post $post */
        $post = $this->route('post');

        return $this->user()?->can('update', $post) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Post $post */
        $post = $this->route('post');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('posts', 'slug')->ignore($post->id)],
            'excerpt' => ['required', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'status' => ['required', Rule::enum(PostStatus::class)],
            'is_featured' => ['boolean'],
            'is_trending' => ['boolean'],
            'published_at' => ['nullable', 'date'],
            'featured_image' => ['nullable', 'image', 'max:5120'],
            'featured_image_url' => ['nullable', 'url', 'max:2048'],
            'remove_featured_image' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'O título é obrigatório.',
            'excerpt.required' => 'O resumo é obrigatório.',
            'body.required' => 'O conteúdo é obrigatório.',
            'category_id.required' => 'Selecione uma categoria.',
        ];
    }
}
