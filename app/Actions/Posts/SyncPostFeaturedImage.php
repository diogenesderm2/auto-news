<?php

namespace App\Actions\Posts;

use App\Models\Post;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SyncPostFeaturedImage
{
    public function handle(Post $post, ?UploadedFile $file, ?string $url, bool $remove = false): void
    {
        if ($remove) {
            $this->deleteStoredImage($post);
            $post->featured_image = null;

            return;
        }

        if ($file instanceof UploadedFile) {
            $this->deleteStoredImage($post);
            $path = $file->store('posts', 'public');
            $post->featured_image = $path;

            return;
        }

        if ($url !== null && $url !== '') {
            $this->deleteStoredImage($post);
            $post->featured_image = $url;
        }
    }

    private function deleteStoredImage(Post $post): void
    {
        if ($post->featured_image === null || Str::startsWith($post->featured_image, ['http://', 'https://'])) {
            return;
        }

        Storage::disk('public')->delete($post->featured_image);
    }
}
