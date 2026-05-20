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

    'adsense' => [
        'enabled' => (bool) env('ADSENSE_ENABLED', false),
        'client' => env('ADSENSE_CLIENT_ID'),
        'slots' => [
            'leaderboard' => env('ADSENSE_SLOT_LEADERBOARD'),
            'home_sidebar' => env('ADSENSE_SLOT_HOME_SIDEBAR'),
            'home_in_feed' => env('ADSENSE_SLOT_HOME_IN_FEED'),
            'article_top' => env('ADSENSE_SLOT_ARTICLE_TOP'),
            'article_bottom' => env('ADSENSE_SLOT_ARTICLE_BOTTOM'),
            'article_sidebar' => env('ADSENSE_SLOT_ARTICLE_SIDEBAR'),
            'category_top' => env('ADSENSE_SLOT_CATEGORY_TOP'),
            'category_in_feed' => env('ADSENSE_SLOT_CATEGORY_IN_FEED'),
            'footer' => env('ADSENSE_SLOT_FOOTER'),
        ],
    ],

];
