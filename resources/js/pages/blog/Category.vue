<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import AdSlot from '@/components/blog/AdSlot.vue';
import PostCard from '@/components/blog/PostCard.vue';
import BlogLayout from '@/layouts/blog/BlogLayout.vue';
import { postsList } from '@/lib/blogPosts';
import type { BlogCategory, BlogPost, PaginationMeta } from '@/types/blog';

const props = defineProps<{
    category: BlogCategory;
    posts: { data: BlogPost[] } | BlogPost[];
    meta: PaginationMeta;
}>();

const categoryPosts = computed(() => postsList(props.posts));
</script>

<template>
    <Head :title="`${category.name} — Auto News`" />

    <BlogLayout>
        <section class="border-b border-black/10 bg-[#0a0a0a] py-10 text-white">
            <div class="mx-auto max-w-7xl px-4 md:px-6">
                <p class="text-xs font-black tracking-[0.2em] text-[#e10600] uppercase">
                    Categoria
                </p>
                <h1 class="mt-2 text-3xl font-black uppercase md:text-5xl">
                    {{ category.name }}
                </h1>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 py-10 md:px-6">
            <AdSlot
                placement="category_top"
                format="horizontal"
                class="mb-8"
            />

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <template
                    v-for="(post, index) in categoryPosts"
                    :key="post.id"
                >
                    <AdSlot
                        v-if="index === 6"
                        placement="category_in_feed"
                        format="rectangle"
                        class="md:col-span-2 lg:col-span-3"
                    />
                    <PostCard :post="post" variant="vertical" />
                </template>
            </div>

            <div
                v-if="meta.last_page > 1"
                class="mt-10 flex items-center justify-center gap-4"
            >
                <Link
                    v-if="meta.current_page > 1"
                    :href="`/categoria/${category.slug}?page=${meta.current_page - 1}`"
                    class="border border-black px-4 py-2 text-sm font-bold uppercase hover:bg-[#0a0a0a] hover:text-white"
                    preserve-scroll
                >
                    Anterior
                </Link>
                <span class="text-sm text-gray-500">
                    Página {{ meta.current_page }} de {{ meta.last_page }}
                </span>
                <Link
                    v-if="meta.current_page < meta.last_page"
                    :href="`/categoria/${category.slug}?page=${meta.current_page + 1}`"
                    class="border border-black px-4 py-2 text-sm font-bold uppercase hover:bg-[#0a0a0a] hover:text-white"
                    preserve-scroll
                >
                    Próxima
                </Link>
            </div>
        </section>
    </BlogLayout>
</template>
