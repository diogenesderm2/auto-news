export type BlogCategory = {
    id: number;
    name: string;
    slug: string;
    posts_count?: number;
};

export type BlogPost = {
    id: number;
    title: string;
    slug: string;
    excerpt: string;
    body?: string;
    featured_image: string | null;
    status: string;
    is_featured: boolean;
    is_trending: boolean;
    published_at: string | null;
    published_at_formatted: string | null;
    updated_at?: string | null;
    updated_at_formatted?: string | null;
    deleted_at?: string | null;
    deleted_at_formatted?: string | null;
    category?: BlogCategory;
    author?: {
        id: number;
        name: string;
    };
};

export type PaginationMeta = {
    current_page: number;
    last_page: number;
    per_page?: number;
    total: number;
};
