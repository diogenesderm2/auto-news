<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Menu, Search, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { cn } from '@/lib/utils';
import { show as categoryShow } from '@/routes/categories';
import { home } from '@/routes';
import type { BlogCategory, BlogPost } from '@/types/blog';

defineProps<{
    categories: BlogCategory[];
}>();

const page = usePage();
const mobileOpen = ref(false);
const isAuthenticated = computed(() => !!page.props.auth?.user);
const { isCurrentUrl, isCurrentOrParentUrl } = useCurrentUrl();

const activeCategorySlug = computed<string | null>(() => {
    if (page.component === 'blog/Category') {
        return (page.props.category as BlogCategory | undefined)?.slug ?? null;
    }

    if (page.component === 'blog/Show') {
        return (page.props.post as BlogPost | undefined)?.category?.slug ?? null;
    }

    return null;
});

const isHomeActive = computed(
    () => page.component === 'blog/Index' || isCurrentUrl(home.url()),
);

function isCategoryActive(slug: string): boolean {
    if (activeCategorySlug.value === slug) {
        return true;
    }

    return isCurrentOrParentUrl(categoryShow.url(slug));
}

function navLinkClass(isActive: boolean, nowrap = false): string {
    return cn(
        'shrink-0 border-b-2 px-3 py-3 text-sm tracking-wide uppercase transition',
        nowrap && 'whitespace-nowrap',
        isActive
            ? 'border-[#e10600] font-bold text-[#e10600]'
            : 'border-transparent font-semibold hover:border-[#e10600] hover:text-[#e10600]',
    );
}
</script>

<template>
    <header class="border-b border-black/10">
        <div class="bg-[#0a0a0a] text-white">
            <div
                class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 md:px-6"
            >
                <Link href="/" class="group flex items-center gap-2">
                    <span
                        class="bg-[#e10600] px-2 py-1 text-xs font-black tracking-widest uppercase"
                        >Auto</span
                    >
                    <span class="text-2xl font-black tracking-tight uppercase"
                        >News</span
                    >
                </Link>

                <div class="hidden items-center gap-6 md:flex">
                    <button
                        type="button"
                        class="text-white/80 transition hover:text-[#e10600]"
                        aria-label="Buscar"
                    >
                        <Search class="size-5" />
                    </button>
                    <Link
                        v-if="isAuthenticated"
                        href="/admin/posts"
                        class="text-sm font-semibold tracking-wide text-white uppercase transition hover:text-[#e10600]"
                    >
                        Admin
                    </Link>
                    <Link
                        v-else
                        href="/login"
                        class="text-sm font-semibold tracking-wide text-white uppercase transition hover:text-[#e10600]"
                    >
                        Entrar
                    </Link>
                </div>

                <button
                    type="button"
                    class="md:hidden"
                    @click="mobileOpen = !mobileOpen"
                >
                    <X v-if="mobileOpen" class="size-6" />
                    <Menu v-else class="size-6" />
                </button>
            </div>
        </div>

        <nav class="border-b border-black/10 bg-white">
            <div
                class="mx-auto flex max-w-7xl items-center gap-1 overflow-x-auto px-4 md:px-6"
            >
                <Link
                    :href="home.url()"
                    :class="navLinkClass(isHomeActive)"
                    :aria-current="isHomeActive ? 'page' : undefined"
                >
                    Início
                </Link>
                <Link
                    v-for="category in categories"
                    :key="category.id"
                    :href="categoryShow.url(category.slug)"
                    :class="navLinkClass(isCategoryActive(category.slug), true)"
                    :aria-current="
                        isCategoryActive(category.slug) ? 'page' : undefined
                    "
                >
                    {{ category.name }}
                </Link>
            </div>
        </nav>

        <div
            v-if="mobileOpen"
            class="border-b border-black/10 bg-[#0a0a0a] px-4 py-4 text-white md:hidden"
        >
            <Link
                v-if="isAuthenticated"
                href="/admin/posts"
                class="block py-2 font-semibold uppercase"
                @click="mobileOpen = false"
            >
                Painel Admin
            </Link>
            <Link
                v-else
                href="/login"
                class="block py-2 font-semibold uppercase"
                @click="mobileOpen = false"
            >
                Entrar
            </Link>
        </div>
    </header>
</template>
