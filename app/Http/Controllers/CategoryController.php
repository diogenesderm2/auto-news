<?php

namespace App\Http\Controllers;

use App\Services\Blog\BlogCacheService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function show(Request $request, string $slug, BlogCacheService $blogCache): Response
    {
        $page = max(1, (int) $request->integer('page', 1));

        return Inertia::render('blog/Category', $blogCache->categoryShow($slug, $page));
    }
}
