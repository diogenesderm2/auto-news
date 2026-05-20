<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import AdSlot from '@/components/blog/AdSlot.vue';
import PostCard from '@/components/blog/PostCard.vue';
import PostMeta from '@/components/blog/PostMeta.vue';
import SectionTitle from '@/components/blog/SectionTitle.vue';
import BlogLayout from '@/layouts/blog/BlogLayout.vue';
import { postsList } from '@/lib/blogPosts';
import type { BlogPost } from '@/types/blog';

const props = defineProps<{
    post: BlogPost;
    related: { data: BlogPost[] } | BlogPost[];
}>();

const relatedPosts = computed(() => postsList(props.related));
</script>

<template>
    <Head :title="post.title" />

    <BlogLayout>
        <div class="mx-auto max-w-7xl px-4 py-8 md:px-6 md:py-12">
            <div class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_300px] lg:items-start">
                <article class="min-w-0 max-w-4xl lg:max-w-none">
                    <PostMeta :post="post" class="text-sm" />
                    <h1
                        class="mt-4 text-3xl leading-tight font-black text-[#0a0a0a] md:text-5xl"
                    >
                        {{ post.title }}
                    </h1>
                    <p class="mt-4 text-lg text-gray-600 md:text-xl">
                        {{ post.excerpt }}
                    </p>

                    <AdSlot
                        placement="article_top"
                        format="horizontal"
                        class="mt-6"
                    />

                    <div
                        v-if="post.featured_image"
                        class="mt-8 overflow-hidden rounded-sm"
                    >
                        <img
                            :src="post.featured_image"
                            :alt="post.title"
                            class="w-full object-cover"
                        />
                    </div>

                    <div
                        class="prose prose-lg mt-10 max-w-none prose-headings:font-black prose-a:text-[#e10600] prose-img:rounded-sm"
                        v-html="post.body"
                    />

                    <div
                        v-if="post.author"
                        class="mt-10 border-t border-black/10 pt-6 text-sm text-gray-500"
                    >
                        Por
                        <span class="font-semibold text-[#0a0a0a]">{{
                            post.author.name
                        }}</span>
                    </div>
                </article>

                <aside class="hidden space-y-6 lg:block">
                    <div class="sticky top-4">
                        <AdSlot placement="article_sidebar" format="vertical" />
                    </div>
                </aside>
            </div>
        </div>

        <div class="mx-auto max-w-4xl px-4 md:px-6">
            <AdSlot placement="article_bottom" format="horizontal" class="mb-8" />
        </div>

        <section
            v-if="relatedPosts.length"
            class="border-t border-black/10 bg-[#f5f5f5] py-12"
        >
            <div class="mx-auto max-w-7xl px-4 md:px-6">
                <SectionTitle title="Leia também" />
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                    <PostCard
                        v-for="item in relatedPosts"
                        :key="item.id"
                        :post="item"
                        variant="vertical"
                    />
                </div>
                <div class="mt-8 text-center">
                    <Link
                        v-if="post.category"
                        :href="`/categoria/${post.category.slug}`"
                        class="inline-block bg-[#0a0a0a] px-6 py-3 text-sm font-bold tracking-wide text-white uppercase transition hover:bg-[#e10600]"
                    >
                        Mais em {{ post.category.name }}
                    </Link>
                </div>
            </div>
        </section>
    </BlogLayout>
</template>
