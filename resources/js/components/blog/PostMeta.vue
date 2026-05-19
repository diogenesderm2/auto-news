<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { BlogPost } from '@/types/blog';

defineProps<{
    post: BlogPost;
    class?: string;
}>();
</script>

<template>
    <p :class="['text-xs font-medium text-gray-500', $props.class]">
        <Link
            v-if="post.category"
            :href="`/categoria/${post.category.slug}`"
            class="font-bold text-[#e10600] uppercase hover:underline"
        >
            {{ post.category.name }}
        </Link>
        <span v-if="post.category && post.published_at_formatted"> · </span>
        <span v-if="post.published_at_formatted">{{
            post.published_at_formatted
        }}</span>
    </p>
</template>
