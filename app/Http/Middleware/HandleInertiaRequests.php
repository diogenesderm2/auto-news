<?php

namespace App\Http\Middleware;

use App\Services\Blog\BlogCacheService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'blogCategories' => fn () => $request->routeIs('home', 'posts.show', 'categories.show')
                ? app(BlogCacheService::class)->categoriesNav()
                : [],
            'blogAds' => fn () => $request->routeIs('home', 'posts.show', 'categories.show')
                ? $this->blogAdsConfig()
                : null,
        ];
    }

    /**
     * @return array{enabled: bool, client: string|null, slots: array<string, string|null>}|null
     */
    private function blogAdsConfig(): ?array
    {
        $client = config('blog.adsense.client');

        if (! config('blog.adsense.enabled') || ! is_string($client) || $client === '') {
            return [
                'enabled' => false,
                'client' => null,
                'slots' => [],
            ];
        }

        /** @var array<string, string|null> $slots */
        $slots = config('blog.adsense.slots', []);

        return [
            'enabled' => true,
            'client' => $client,
            'slots' => $slots,
        ];
    }
}
