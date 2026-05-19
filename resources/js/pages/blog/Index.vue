<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import PostCard from '@/components/blog/PostCard.vue';
import SectionTitle from '@/components/blog/SectionTitle.vue';
import BlogLayout from '@/layouts/blog/BlogLayout.vue';
import { postsList } from '@/lib/blogPosts';
import type { BlogCategory, BlogPost } from '@/types/blog';

const props = defineProps<{
    trending: { data: BlogPost[] } | BlogPost[];
    latest: { data: BlogPost[] } | BlogPost[];
    featured: { data: BlogPost[] } | BlogPost[];
    moreNews: { data: BlogPost[] } | BlogPost[];
    categories: BlogCategory[];
}>();

const trendingPosts = computed(() => postsList(props.trending));
const latestPosts = computed(() => postsList(props.latest));
const featuredPosts = computed(() => postsList(props.featured));
const moreNewsPosts = computed(() => postsList(props.moreNews));
</script>

<template>
    <Head title="Auto News — Carros elétricos e mobilidade" />

    <BlogLayout :categories="categories">
        <section class="bg-[#0a0a0a] py-2">
            <div class="mx-auto max-w-7xl px-4 md:px-6">
                <p
                    class="text-xs font-black tracking-[0.2em] text-[#e10600] uppercase"
                >
                    Bombando
                </p>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 py-8 md:px-6">
            <div class="grid gap-4 lg:grid-cols-2">
                <PostCard
                    v-if="trendingPosts[0]"
                    :post="trendingPosts[0]"
                    variant="hero"
                    class="lg:col-span-2"
                />
            </div>
            <div
                v-if="trendingPosts.length > 1"
                class="mt-4 grid gap-4 md:grid-cols-3"
            >
                <Link
                    v-for="post in trendingPosts.slice(1)"
                    :key="post.id"
                    :href="`/noticias/${post.slug}`"
                    class="group border border-black/10 p-4 transition hover:border-[#e10600] hover:bg-[#fafafa]"
                >
                    <span
                        v-if="post.category"
                        class="text-xs font-bold text-[#e10600] uppercase"
                        >{{ post.category.name }}</span
                    >
                    <h3
                        class="mt-2 text-sm leading-snug font-bold group-hover:text-[#e10600] md:text-base"
                    >
                        {{ post.title }}
                    </h3>
                </Link>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 pb-12 md:px-6">
            <div class="grid gap-10 lg:grid-cols-[1fr_340px]">
                <div>
                    <SectionTitle title="Últimas notícias" />
                    <PostCard
                        v-for="post in latestPosts"
                        :key="post.id"
                        :post="post"
                        variant="horizontal"
                    />
                </div>

                <aside class="lg:sticky lg:top-4 lg:self-start">
                    <SectionTitle title="Recomendados" />
                    <div class="space-y-4">
                        <PostCard
                            v-for="post in featuredPosts"
                            :key="post.id"
                            :post="post"
                            variant="vertical"
                        />
                    </div>
                </aside>
            </div>
        </section>

        <section
            v-if="moreNewsPosts.length"
            class="border-t border-black/10 bg-[#f5f5f5] py-12"
        >
            <div class="mx-auto max-w-7xl px-4 md:px-6">
                <SectionTitle title="Mais de Auto News" />
                <div class="grid gap-6 md:grid-cols-2">
                    <PostCard
                        v-for="post in moreNewsPosts"
                        :key="post.id"
                        :post="post"
                        variant="vertical"
                    />
                </div>
            </div>
        </section>
    </BlogLayout>
</template>
