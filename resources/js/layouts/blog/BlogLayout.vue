<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AdSlot from '@/components/blog/AdSlot.vue';
import BlogFooter from '@/components/blog/BlogFooter.vue';
import BlogHeader from '@/components/blog/BlogHeader.vue';
import type { BlogCategory } from '@/types/blog';

const props = defineProps<{
    categories?: BlogCategory[];
}>();

const page = usePage();

const navCategories = computed(() => {
    const shared = page.props.blogCategories as BlogCategory[] | undefined;

    return props.categories?.length ? props.categories : (shared ?? []);
});
</script>

<template>
    <div class="min-h-screen bg-white text-[#0a0a0a]">
        <BlogHeader :categories="navCategories" />
        <div class="mx-auto max-w-7xl px-4 md:px-6">
            <AdSlot
                placement="leaderboard"
                format="horizontal"
                class="border-b border-black/5 py-3"
            />
        </div>
        <main>
            <slot />
        </main>
        <div class="mx-auto max-w-7xl px-4 py-6 md:px-6">
            <AdSlot placement="footer" format="horizontal" />
        </div>
        <BlogFooter />
    </div>
</template>
