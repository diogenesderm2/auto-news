<?php

return [

    'cache' => [
        'ttl' => (int) env('BLOG_CACHE_TTL', 3600),
        'categories_nav_ttl' => (int) env('BLOG_CATEGORIES_NAV_CACHE_TTL', 3600),
    ],

    'dashboard' => [
        'cache_ttl' => (int) env('DASHBOARD_CACHE_TTL', 300),
        'refresh_queue' => env('DASHBOARD_REFRESH_QUEUE', 'default'),
    ],

];
