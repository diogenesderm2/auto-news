import type { BlogPost } from '@/types/blog';

type PostsPayload = { data?: BlogPost[] } | BlogPost[] | undefined | null;

export function postsList(payload: PostsPayload): BlogPost[] {
    if (payload === undefined || payload === null) {
        return [];
    }

    if (Array.isArray(payload)) {
        return payload;
    }

    return payload.data ?? [];
}
