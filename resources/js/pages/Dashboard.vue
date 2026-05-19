<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowUpRight,
    FileText,
    Flame,
    FolderOpen,
    Newspaper,
    Sparkles,
    Star,
    TrendingUp,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import type { DashboardData } from '@/types/dashboard';

const props = defineProps<{
    dashboard?: DashboardData;
}>();

const data = computed<DashboardData>(() => ({
    summary: {
        total_posts: 0,
        published_posts: 0,
        draft_posts: 0,
        featured_posts: 0,
        trending_posts: 0,
        categories_count: 0,
        published_last_7_days: 0,
        published_last_30_days: 0,
        ...props.dashboard?.summary,
    },
    posts_by_month: props.dashboard?.posts_by_month ?? [],
    top_categories: props.dashboard?.top_categories ?? [],
    recent_posts: props.dashboard?.recent_posts ?? [],
    meta: {
        cached_at: '',
        cached_at_formatted: '—',
        source: 'live',
        ...props.dashboard?.meta,
    },
}));

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

const maxMonthlyCount = computed(() =>
    Math.max(...data.value.posts_by_month.map((item) => item.count), 1),
);

const statCards = computed(() => [
    {
        label: 'Posts publicados',
        value: data.value.summary.published_posts,
        hint: `${data.value.summary.published_last_7_days} nos últimos 7 dias`,
        icon: Newspaper,
        accent: 'bg-[#e10600]',
    },
    {
        label: 'Rascunhos',
        value: data.value.summary.draft_posts,
        hint: `${data.value.summary.total_posts} posts no total`,
        icon: FileText,
        accent: 'bg-[#0a0a0a]',
    },
    {
        label: 'Em destaque',
        value: data.value.summary.featured_posts,
        hint: `${data.value.summary.trending_posts} em bombando`,
        icon: Star,
        accent: 'bg-[#e10600]/90',
    },
    {
        label: 'Categorias',
        value: data.value.summary.categories_count,
        hint: `${data.value.summary.published_last_30_days} publicados em 30 dias`,
        icon: FolderOpen,
        accent: 'bg-[#0a0a0a]',
    },
]);
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-1 flex-col gap-6 overflow-x-auto p-4 md:p-6">
        <section
            class="relative overflow-hidden rounded-2xl bg-[#0a0a0a] p-6 text-white md:p-8"
        >
            <div
                class="pointer-events-none absolute -top-16 -right-16 size-48 rounded-full bg-[#e10600]/30 blur-3xl"
            />
            <div
                class="pointer-events-none absolute -bottom-20 -left-10 size-56 rounded-full bg-[#e10600]/20 blur-3xl"
            />
            <div class="relative flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p
                        class="text-xs font-bold tracking-[0.2em] text-[#e10600] uppercase"
                    >
                        Auto News Admin
                    </p>
                    <h1 class="mt-2 text-3xl font-black tracking-tight md:text-4xl">
                        Dashboard editorial
                    </h1>
                    <p class="mt-2 max-w-xl text-sm text-white/70">
                        Visão geral do blog com métricas reais do banco de dados.
                        Os números são atualizados via cache e filas para manter
                        o painel rápido.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <Badge
                        variant="outline"
                        class="border-white/20 bg-white/5 text-white"
                    >
                        <Sparkles class="mr-1 size-3" />
                        {{ data.meta.source === 'cache' ? 'Cache' : 'Ao vivo' }}
                        · {{ data.meta.cached_at_formatted }}
                    </Badge>
                    <Button
                        as-child
                        class="bg-[#e10600] font-bold text-white hover:bg-[#b80500]"
                    >
                        <Link href="/admin/posts">
                            Gerenciar posts
                            <ArrowUpRight class="ml-1 size-4" />
                        </Link>
                    </Button>
                    <Button
                        as-child
                        variant="outline"
                        class="border-white/20 bg-transparent text-white hover:bg-white/10"
                    >
                        <Link href="/" target="_blank">Ver site</Link>
                    </Button>
                </div>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article
                v-for="card in statCards"
                :key="card.label"
                class="group relative overflow-hidden rounded-xl border bg-card p-5 shadow-sm transition hover:shadow-md"
            >
                <div
                    class="absolute top-0 right-0 h-1 w-full opacity-80"
                    :class="card.accent"
                />
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            {{ card.label }}
                        </p>
                        <p class="mt-2 text-3xl font-black tracking-tight">
                            {{ card.value }}
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{ card.hint }}
                        </p>
                    </div>
                    <div
                        class="flex size-11 items-center justify-center rounded-lg text-white"
                        :class="card.accent"
                    >
                        <component :is="card.icon" class="size-5" />
                    </div>
                </div>
            </article>
        </section>

        <section class="grid gap-6 lg:grid-cols-3">
            <article
                class="rounded-xl border bg-card p-6 shadow-sm lg:col-span-2"
            >
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold">Publicações por mês</h2>
                        <p class="text-sm text-muted-foreground">
                            Últimos 6 meses — posts publicados
                        </p>
                    </div>
                    <TrendingUp class="size-5 text-[#e10600]" />
                </div>
                <div class="flex h-48 items-end gap-3">
                    <div
                        v-for="item in data.posts_by_month"
                        :key="`${item.year}-${item.month}`"
                        class="flex flex-1 flex-col items-center gap-2"
                    >
                        <span class="text-xs font-semibold text-foreground">
                            {{ item.count }}
                        </span>
                        <div
                            class="w-full rounded-t-md bg-[#e10600]/15 transition group-hover:bg-[#e10600]/25"
                            :style="{
                                height: `${Math.max(8, (item.count / maxMonthlyCount) * 100)}%`,
                            }"
                        >
                            <div
                                class="h-full w-full rounded-t-md bg-[#e10600]"
                                :style="{
                                    height: `${Math.max(item.count > 0 ? 12 : 0, (item.count / maxMonthlyCount) * 100)}%`,
                                }"
                            />
                        </div>
                        <span class="text-xs text-muted-foreground">
                            {{ item.month }}
                        </span>
                    </div>
                </div>
            </article>

            <article class="rounded-xl border bg-card p-6 shadow-sm">
                <h2 class="text-lg font-bold">Top categorias</h2>
                <p class="text-sm text-muted-foreground">
                    Por posts publicados
                </p>
                <ul class="mt-6 space-y-4">
                    <li
                        v-for="(category, index) in data.top_categories"
                        :key="category.id"
                        class="flex items-center gap-3"
                    >
                        <span
                            class="flex size-8 shrink-0 items-center justify-center rounded-full bg-[#0a0a0a] text-xs font-bold text-white"
                        >
                            {{ index + 1 }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <Link
                                :href="`/categoria/${category.slug}`"
                                class="truncate font-semibold hover:text-[#e10600]"
                            >
                                {{ category.name }}
                            </Link>
                            <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-muted">
                                <div
                                    class="h-full rounded-full bg-[#e10600]"
                                    :style="{
                                        width: `${data.summary.published_posts > 0 ? (category.count / data.summary.published_posts) * 100 : 0}%`,
                                    }"
                                />
                            </div>
                        </div>
                        <span class="text-sm font-bold">{{ category.count }}</span>
                    </li>
                    <li
                        v-if="data.top_categories.length === 0"
                        class="text-sm text-muted-foreground"
                    >
                        Nenhuma categoria com posts publicados.
                    </li>
                </ul>
            </article>
        </section>

        <section class="rounded-xl border bg-card shadow-sm">
            <div
                class="flex flex-col gap-3 border-b px-6 py-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h2 class="text-lg font-bold">Posts recentes</h2>
                    <p class="text-sm text-muted-foreground">
                        Últimas alterações no blog
                    </p>
                </div>
                <Button as-child variant="outline" size="sm">
                    <Link href="/admin/posts/create">Novo post</Link>
                </Button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-muted/40 text-left">
                        <tr>
                            <th class="px-6 py-3 font-medium">Título</th>
                            <th class="px-6 py-3 font-medium">Categoria</th>
                            <th class="px-6 py-3 font-medium">Status</th>
                            <th class="px-6 py-3 font-medium">Data</th>
                            <th class="px-6 py-3 text-right font-medium">
                                Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="post in data.recent_posts"
                            :key="post.id"
                            class="border-t"
                        >
                            <td class="px-6 py-4">
                                <p class="max-w-xs truncate font-semibold">
                                    {{ post.title }}
                                </p>
                                <div
                                    v-if="post.is_featured || post.is_trending"
                                    class="mt-1 flex gap-1"
                                >
                                    <Badge
                                        v-if="post.is_featured"
                                        class="bg-[#e10600] text-white"
                                    >
                                        <Star class="mr-1 size-3" />
                                        Destaque
                                    </Badge>
                                    <Badge
                                        v-if="post.is_trending"
                                        variant="outline"
                                    >
                                        <Flame class="mr-1 size-3" />
                                        Bombando
                                    </Badge>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-muted-foreground">
                                {{ post.category?.name ?? '—' }}
                            </td>
                            <td class="px-6 py-4">
                                <Badge
                                    :class="
                                        post.status === 'published'
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'
                                            : 'bg-muted text-muted-foreground'
                                    "
                                >
                                    {{
                                        post.status === 'published'
                                            ? 'Publicado'
                                            : 'Rascunho'
                                    }}
                                </Badge>
                            </td>
                            <td class="px-6 py-4 text-muted-foreground">
                                {{ post.published_at_formatted ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <Button as-child variant="ghost" size="sm">
                                    <Link :href="`/admin/posts/${post.id}/edit`">
                                        Editar
                                    </Link>
                                </Button>
                            </td>
                        </tr>
                        <tr v-if="data.recent_posts.length === 0">
                            <td
                                colspan="5"
                                class="px-6 py-10 text-center text-muted-foreground"
                            >
                                Nenhum post cadastrado ainda.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</template>
