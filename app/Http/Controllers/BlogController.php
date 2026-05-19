<?php

namespace App\Http\Controllers;

use App\Services\Blog\BlogCacheService;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    public function index(BlogCacheService $blogCache): Response
    {
        return Inertia::render('blog/Index', $blogCache->home());
    }
}
