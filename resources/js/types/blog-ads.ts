export type BlogAdPlacement =
    | 'leaderboard'
    | 'home_sidebar'
    | 'home_in_feed'
    | 'article_top'
    | 'article_bottom'
    | 'article_sidebar'
    | 'category_top'
    | 'category_in_feed'
    | 'footer';

export type BlogAdsConfig = {
    enabled: boolean;
    client: string | null;
    slots: Partial<Record<BlogAdPlacement, string | null>>;
};
