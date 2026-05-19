<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
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
        <main>
            <slot />
        </main>
        <BlogFooter />
    </div>
</template>
