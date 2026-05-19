export type DashboardSummary = {
    total_posts: number;
    published_posts: number;
    draft_posts: number;
    featured_posts: number;
    trending_posts: number;
    categories_count: number;
    published_last_7_days: number;
    published_last_30_days: number;
};

export type DashboardMonthStat = {
    month: string;
    year: number;
    count: number;
};

export type DashboardCategoryStat = {
    id: number;
    name: string;
    slug: string;
    count: number;
};

export type DashboardRecentPost = {
    id: number;
    title: string;
    slug: string;
    status: string;
    is_featured: boolean;
    is_trending: boolean;
    published_at: string | null;
    published_at_formatted: string | null;
    category: {
        name: string;
        slug: string;
    } | null;
};

export type DashboardData = {
    summary: DashboardSummary;
    posts_by_month: DashboardMonthStat[];
    top_categories: DashboardCategoryStat[];
    recent_posts: DashboardRecentPost[];
    meta: {
        cached_at: string;
        cached_at_formatted: string;
        source?: 'cache' | 'live';
    };
};
