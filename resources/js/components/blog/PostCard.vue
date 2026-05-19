<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import PostMeta from '@/components/blog/PostMeta.vue';
import type { BlogPost } from '@/types/blog';

withDefaults(
    defineProps<{
        post: BlogPost;
        variant?: 'horizontal' | 'vertical' | 'compact' | 'hero';
    }>(),
    {
        variant: 'horizontal',
    },
);
</script>

<template>
    <article
        v-if="variant === 'hero'"
        class="group relative overflow-hidden rounded-sm bg-[#0a0a0a]"
    >
        <Link :href="`/noticias/${post.slug}`" class="block">
            <div class="aspect-[16/9] overflow-hidden">
                <img
                    v-if="post.featured_image"
                    :src="post.featured_image"
                    :alt="post.title"
                    class="h-full w-full object-cover opacity-90 transition duration-500 group-hover:scale-105 group-hover:opacity-100"
                />
                <div
                    v-else
                    class="flex h-full w-full items-center justify-center bg-[#1a1a1a] text-white/40"
                >
                    Sem imagem
                </div>
            </div>
            <div
                class="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-transparent"
            />
            <div class="absolute right-0 bottom-0 left-0 p-6 md:p-8">
                <span
                    v-if="post.category"
                    class="mb-2 inline-block bg-[#e10600] px-2 py-0.5 text-xs font-bold tracking-wider text-white uppercase"
                >
                    {{ post.category.name }}
                </span>
                <h3
                    class="text-2xl leading-tight font-black text-white md:text-4xl"
                >
                    {{ post.title }}
                </h3>
                <p class="mt-2 line-clamp-2 text-sm text-white/80 md:text-base">
                    {{ post.excerpt }}
                </p>
            </div>
        </Link>
    </article>

    <article
        v-else-if="variant === 'vertical'"
        class="group flex flex-col overflow-hidden border border-black/10 bg-white transition hover:border-[#e10600]"
    >
        <Link :href="`/noticias/${post.slug}`" class="flex flex-1 flex-col">
            <div class="aspect-[16/10] overflow-hidden bg-[#f5f5f5]">
                <img
                    v-if="post.featured_image"
                    :src="post.featured_image"
                    :alt="post.title"
                    class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                />
            </div>
            <div class="flex flex-1 flex-col p-4">
                <PostMeta :post="post" />
                <h3
                    class="mt-2 text-base leading-snug font-bold group-hover:text-[#e10600] md:text-lg"
                >
                    {{ post.title }}
                </h3>
                <p class="mt-2 line-clamp-2 text-sm text-gray-600">
                    {{ post.excerpt }}
                </p>
            </div>
        </Link>
    </article>

    <article
        v-else-if="variant === 'compact'"
        class="group border-b border-black/10 py-4 last:border-0"
    >
        <Link
            :href="`/noticias/${post.slug}`"
            class="flex gap-4 hover:text-[#e10600]"
        >
            <h3 class="flex-1 text-sm leading-snug font-bold md:text-base">
                {{ post.title }}
            </h3>
        </Link>
        <PostMeta :post="post" class="mt-1" />
    </article>

    <article
        v-else
        class="group grid gap-4 border-b border-black/10 py-6 last:border-0 md:grid-cols-[220px_1fr] md:gap-6"
    >
        <Link
            :href="`/noticias/${post.slug}`"
            class="overflow-hidden rounded-sm bg-[#f5f5f5]"
        >
            <div class="aspect-[16/10] md:aspect-[4/3]">
                <img
                    v-if="post.featured_image"
                    :src="post.featured_image"
                    :alt="post.title"
                    class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                />
            </div>
        </Link>
        <div>
            <PostMeta :post="post" />
            <Link :href="`/noticias/${post.slug}`">
                <h3
                    class="mt-2 text-lg leading-snug font-bold group-hover:text-[#e10600] md:text-xl"
                >
                    {{ post.title }}
                </h3>
                <p class="mt-2 line-clamp-2 text-sm text-gray-600">
                    {{ post.excerpt }}
                </p>
            </Link>
        </div>
    </article>
</template>
