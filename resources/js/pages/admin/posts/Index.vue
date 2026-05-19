<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { FileText, Newspaper, Pencil, Plus, RotateCcw, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import {
    create,
    destroy,
    forceDestroy,
    index,
    restore,
} from '@/routes/admin/posts';
import type { BlogPost, PaginationMeta } from '@/types/blog';

type PostListView = 'all' | 'published' | 'draft' | 'trash';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Posts', href: '/admin/posts' }],
    },
});

const props = defineProps<{
    posts: { data: BlogPost[] };
    meta: PaginationMeta;
    view: PostListView;
    counts: {
        all: number;
        published: number;
        draft: number;
        trash: number;
    };
}>();

const isTrashView = computed(() => props.view === 'trash');
const isDraftView = computed(() => props.view === 'draft');
const isPublishedView = computed(() => props.view === 'published');

const tabs: { id: PostListView; label: string; count: () => number }[] = [
    { id: 'all', label: 'Todos', count: () => props.counts.all },
    { id: 'published', label: 'Publicados', count: () => props.counts.published },
    { id: 'draft', label: 'Rascunhos', count: () => props.counts.draft },
    { id: 'trash', label: 'Lixeira', count: () => props.counts.trash },
];

const pageTitle = computed(() => {
    const titles: Record<PostListView, string> = {
        all: 'Todos os posts',
        published: 'Posts publicados',
        draft: 'Rascunhos',
        trash: 'Lixeira',
    };

    return titles[props.view];
});

const pageDescription = computed(() => {
    const total = props.meta.total;

    if (isTrashView.value) {
        return `${props.counts.trash} ${props.counts.trash === 1 ? 'item na lixeira' : 'itens na lixeira'}`;
    }

    if (isDraftView.value) {
        return `${total} ${total === 1 ? 'rascunho nesta lista' : 'rascunhos nesta lista'}`;
    }

    if (isPublishedView.value) {
        return `${total} ${total === 1 ? 'post publicado' : 'posts publicados'}`;
    }

    return `${props.counts.all} ${props.counts.all === 1 ? 'post no total' : 'posts no total'}`;
});

const emptyMessage = computed(() => {
    const messages: Record<PostListView, string> = {
        all: 'Nenhum post encontrado. Crie o primeiro.',
        published: 'Nenhum post publicado ainda.',
        draft: 'Nenhum rascunho. Salve um post como rascunho ao editar.',
        trash: 'A lixeira está vazia.',
    };

    return messages[props.view];
});

const showStatusColumn = computed(
    () => !isTrashView.value && !isDraftView.value && !isPublishedView.value,
);

const dateColumnLabel = computed(() => {
    if (isTrashView.value) {
        return 'Excluído em';
    }

    if (isDraftView.value) {
        return 'Atualizado';
    }

    return 'Publicado em';
});

function tabClass(active: boolean): string {
    return cn(
        'border-b-2 px-1 py-3 text-sm font-semibold transition',
        active
            ? 'border-[#e10600] text-[#e10600]'
            : 'border-transparent text-muted-foreground hover:border-black/20 hover:text-foreground',
    );
}

function listUrl(view: PostListView, page?: number): string {
    const query: Record<string, string | number> = {};

    if (view !== 'all') {
        query.view = view;
    }

    if (page !== undefined) {
        query.page = page;
    }

    return index.url({ query });
}

function moveToTrash(id: number): void {
    if (
        !confirm(
            'Mover este post para a lixeira? Você poderá restaurá-lo depois.',
        )
    ) {
        return;
    }

    router.delete(destroy.url(id));
}

function restorePost(id: number): void {
    router.post(restore.url(id));
}

function permanentlyDelete(id: number): void {
    if (
        !confirm(
            'Excluir este post permanentemente? Esta ação não pode ser desfeita.',
        )
    ) {
        return;
    }

    router.delete(forceDestroy.url(id));
}
</script>

<template>
    <Head :title="`${pageTitle} — Admin`" />

    <div class="flex flex-col gap-6 p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">{{ pageTitle }}</h1>
                <p class="text-sm text-muted-foreground">
                    {{ pageDescription }}
                </p>
            </div>
            <Button v-if="!isTrashView" as-child>
                <Link :href="create.url()">
                    <Plus class="mr-2 size-4" />
                    Novo post
                </Link>
            </Button>
        </div>

        <nav
            class="flex flex-wrap gap-x-6 gap-y-1 border-b"
            aria-label="Filtrar posts"
        >
            <Link
                v-for="tab in tabs"
                :key="tab.id"
                :href="listUrl(tab.id)"
                :class="tabClass(view === tab.id)"
                :aria-current="view === tab.id ? 'page' : undefined"
            >
                {{ tab.label }}
                <span class="ml-1 text-muted-foreground"
                    >({{ tab.count() }})</span
                >
            </Link>
        </nav>

        <p
            v-if="isTrashView"
            class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900"
        >
            Posts na lixeira não aparecem no site. Restaure para publicar
            novamente ou exclua permanentemente.
        </p>

        <p
            v-else-if="isDraftView"
            class="flex items-start gap-2 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900"
        >
            <FileText class="mt-0.5 size-4 shrink-0" />
            <span>
                Rascunhos não são exibidos no blog público. Edite e altere o
                status para <strong>Publicado</strong> quando estiver pronto.
            </span>
        </p>

        <div
            v-if="posts.data.length > 0"
            class="overflow-hidden rounded-xl border"
        >
            <table class="w-full text-sm">
                <thead class="bg-muted/50 text-left">
                    <tr>
                        <th class="px-4 py-3 font-medium">Título</th>
                        <th class="px-4 py-3 font-medium">Categoria</th>
                        <th v-if="showStatusColumn" class="px-4 py-3 font-medium">
                            Status
                        </th>
                        <th class="px-4 py-3 font-medium">
                            {{ dateColumnLabel }}
                        </th>
                        <th class="px-4 py-3 text-right font-medium">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="post in posts.data"
                        :key="post.id"
                        class="border-t"
                    >
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2 font-medium">
                                <Newspaper
                                    v-if="post.status === 'published'"
                                    class="size-4 shrink-0 text-green-600"
                                />
                                <FileText
                                    v-else-if="!isTrashView"
                                    class="size-4 shrink-0 text-muted-foreground"
                                />
                                {{ post.title }}
                            </div>
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{ post.category?.name ?? '—' }}
                        </td>
                        <td v-if="showStatusColumn" class="px-4 py-3">
                            <span
                                class="rounded-full px-2 py-0.5 text-xs font-semibold uppercase"
                                :class="
                                    post.status === 'published'
                                        ? 'bg-green-100 text-green-800'
                                        : 'bg-gray-100 text-gray-700'
                                "
                            >
                                {{
                                    post.status === 'published'
                                        ? 'Publicado'
                                        : 'Rascunho'
                                }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{
                                isTrashView
                                    ? (post.deleted_at_formatted ?? '—')
                                    : isDraftView ||
                                        post.status === 'draft'
                                      ? (post.updated_at_formatted ?? '—')
                                      : (post.published_at_formatted ?? '—')
                            }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <template v-if="isTrashView">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        @click="restorePost(post.id)"
                                    >
                                        <RotateCcw class="mr-1 size-4" />
                                        Restaurar
                                    </Button>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        class="text-destructive hover:text-destructive"
                                        @click="permanentlyDelete(post.id)"
                                    >
                                        <Trash2 class="mr-1 size-4" />
                                        Excluir
                                    </Button>
                                </template>
                                <template v-else>
                                    <Button variant="outline" size="icon" as-child>
                                        <Link
                                            :href="`/admin/posts/${post.id}/edit`"
                                        >
                                            <Pencil class="size-4" />
                                        </Link>
                                    </Button>
                                    <Button
                                        variant="outline"
                                        size="icon"
                                        @click="moveToTrash(post.id)"
                                    >
                                        <Trash2
                                            class="size-4 text-destructive"
                                        />
                                    </Button>
                                </template>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p
            v-else
            class="rounded-xl border border-dashed py-12 text-center text-muted-foreground"
        >
            {{ emptyMessage }}
        </p>

        <div
            v-if="meta.last_page > 1"
            class="flex justify-center gap-4 text-sm"
        >
            <Link
                v-if="meta.current_page > 1"
                :href="listUrl(view, meta.current_page - 1)"
                class="underline"
            >
                Anterior
            </Link>
            <span class="text-muted-foreground">
                Página {{ meta.current_page }} de {{ meta.last_page }}
            </span>
            <Link
                v-if="meta.current_page < meta.last_page"
                :href="listUrl(view, meta.current_page + 1)"
                class="underline"
            >
                Próxima
            </Link>
        </div>
    </div>
</template>
