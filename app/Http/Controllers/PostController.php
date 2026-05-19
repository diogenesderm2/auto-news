<?php

namespace App\Http\Controllers;

use App\Services\Blog\BlogCacheService;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function show(string $slug, BlogCacheService $blogCache): Response
    {
        return Inertia::render('blog/Show', $blogCache->postShow($slug));
    }
}
