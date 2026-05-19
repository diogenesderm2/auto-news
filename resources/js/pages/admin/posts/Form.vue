<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Calendar,
    Flame,
    ImageIcon,
    Link2,
    Save,
    Sparkles,
    Star,
    Type,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { cn } from '@/lib/utils';
import { index, store, update } from '@/routes/admin/posts';
import type { BlogPost } from '@/types/blog';

type CategoryOption = {
    id: number;
    name: string;
};

const props = defineProps<{
    post: BlogPost | null;
    categories: CategoryOption[];
}>();

const isEditing = computed(() => props.post !== null);

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Posts', href: '/admin/posts' },
            { title: 'Editor', href: '/admin/posts/create' },
        ],
    },
});

const formAction = computed(() =>
    isEditing.value ? update.url(props.post!.id) : store.url(),
);

const formMethod = computed(() => (isEditing.value ? 'put' : 'post'));

const imagePreview = ref<string | null>(props.post?.featured_image ?? null);
const removeImage = ref(false);
const isFeatured = ref(props.post?.is_featured ?? false);
const isTrending = ref(props.post?.is_trending ?? false);

const pageTitle = computed(() =>
    isEditing.value ? 'Editar publicação' : 'Nova publicação',
);

const statusLabel = computed(() =>
    props.post?.status === 'published' ? 'Publicado' : 'Rascunho',
);

const fieldClass = cn(
    'flex w-full rounded-lg border border-input bg-background px-3 py-2 text-sm shadow-xs transition-colors',
    'placeholder:text-muted-foreground focus-visible:border-[#e10600] focus-visible:ring-[3px] focus-visible:ring-[#e10600]/20 focus-visible:outline-none',
);

function onImageChange(event: Event): void {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (!file) {
        return;
    }

    imagePreview.value = URL.createObjectURL(file);
    removeImage.value = false;
}

function clearImagePreview(): void {
    imagePreview.value = null;
    removeImage.value = true;
}
</script>

<template>
    <Head :title="isEditing ? 'Editar post' : 'Novo post'" />

    <div class="mx-auto max-w-6xl space-y-6 p-4 pb-24 md:p-6 lg:pb-8">
        <div
            class="relative overflow-hidden rounded-2xl border bg-gradient-to-br from-[#0a0a0a] via-[#141414] to-[#1a1a1a] px-6 py-8 text-white shadow-lg"
        >
            <div
                class="pointer-events-none absolute -top-16 -right-16 size-48 rounded-full bg-[#e10600]/20 blur-3xl"
            />
            <div class="relative flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="space-y-3">
                    <Link
                        :href="index.url()"
                        class="inline-flex items-center gap-1.5 text-sm text-white/70 transition hover:text-white"
                    >
                        <ArrowLeft class="size-4" />
                        Voltar para posts
                    </Link>
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="text-2xl font-black tracking-tight md:text-3xl">
                            {{ pageTitle }}
                        </h1>
                        <Badge
                            v-if="isEditing"
                            class="border-0 uppercase"
                            :class="
                                post?.status === 'published'
                                    ? 'bg-[#e10600] text-white'
                                    : 'bg-white/15 text-white'
                            "
                        >
                            {{ statusLabel }}
                        </Badge>
                    </div>
                    <p class="max-w-xl text-sm text-white/65">
                        {{
                            isEditing
                                ? 'Atualize o conteúdo, imagem e opções de exibição no blog.'
                                : 'Preencha os campos abaixo para publicar uma nova matéria no Auto News.'
                        }}
                    </p>
                </div>
            </div>
        </div>

        <Form
            :action="formAction"
            :method="formMethod"
            enctype="multipart/form-data"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-6 lg:grid-cols-3 lg:items-start">
                <div class="space-y-6 lg:col-span-2">
                    <Card class="gap-0 py-0">
                        <CardHeader class="border-b bg-muted/30 py-5">
                            <div class="flex items-center gap-2">
                                <div
                                    class="flex size-9 items-center justify-center rounded-lg bg-[#e10600]/10 text-[#e10600]"
                                >
                                    <Type class="size-4" />
                                </div>
                                <div>
                                    <CardTitle>Conteúdo</CardTitle>
                                    <CardDescription>
                                        Título, resumo e corpo da matéria
                                    </CardDescription>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent class="space-y-5 py-6">
                            <div class="grid gap-2">
                                <Label for="title" class="text-sm font-semibold"
                                    >Título</Label
                                >
                                <Input
                                    id="title"
                                    name="title"
                                    :default-value="post?.title"
                                    :class="fieldClass"
                                    class="h-11 text-base font-medium"
                                    placeholder="Ex.: Brasil bate recorde em vendas de elétricos"
                                    required
                                />
                                <InputError :message="errors.title" />
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="grid gap-2">
                                    <Label
                                        for="slug"
                                        class="flex items-center gap-1.5 text-sm font-semibold"
                                    >
                                        <Link2 class="size-3.5 text-muted-foreground" />
                                        Slug
                                        <span class="font-normal text-muted-foreground"
                                            >(opcional)</span
                                        >
                                    </Label>
                                    <Input
                                        id="slug"
                                        name="slug"
                                        :default-value="post?.slug"
                                        :class="fieldClass"
                                        placeholder="gerado-automaticamente"
                                    />
                                    <InputError :message="errors.slug" />
                                </div>
                                <div class="grid gap-2">
                                    <Label
                                        for="category_id"
                                        class="text-sm font-semibold"
                                        >Categoria</Label
                                    >
                                    <select
                                        id="category_id"
                                        name="category_id"
                                        :class="cn(fieldClass, 'h-11')"
                                        required
                                    >
                                        <option value="" disabled>
                                            Selecione uma categoria
                                        </option>
                                        <option
                                            v-for="category in categories"
                                            :key="category.id"
                                            :value="category.id"
                                            :selected="
                                                category.id === post?.category?.id
                                            "
                                        >
                                            {{ category.name }}
                                        </option>
                                    </select>
                                    <InputError :message="errors.category_id" />
                                </div>
                            </div>

                            <div class="grid gap-2">
                                <Label for="excerpt" class="text-sm font-semibold"
                                    >Resumo</Label
                                >
                                <textarea
                                    id="excerpt"
                                    name="excerpt"
                                    rows="3"
                                    :class="cn(fieldClass, 'min-h-[88px] resize-y')"
                                    placeholder="Breve descrição exibida nos cards e listagens..."
                                    required
                                    :default-value="post?.excerpt"
                                />
                                <InputError :message="errors.excerpt" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="body" class="text-sm font-semibold"
                                    >Conteúdo</Label
                                >
                                <p class="text-xs text-muted-foreground">
                                    HTML permitido — use tags como
                                    <code class="rounded bg-muted px-1 py-0.5"
                                        >&lt;p&gt;</code
                                    >,
                                    <code class="rounded bg-muted px-1 py-0.5"
                                        >&lt;strong&gt;</code
                                    >,
                                    <code class="rounded bg-muted px-1 py-0.5"
                                        >&lt;ul&gt;</code
                                    >
                                </p>
                                <textarea
                                    id="body"
                                    name="body"
                                    rows="14"
                                    :class="
                                        cn(
                                            fieldClass,
                                            'min-h-[280px] resize-y font-mono text-[13px] leading-relaxed',
                                        )
                                    "
                                    placeholder="<p>Escreva o conteúdo completo da notícia...</p>"
                                    required
                                    :default-value="post?.body"
                                />
                                <InputError :message="errors.body" />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div class="space-y-6 lg:sticky lg:top-6">
                    <Card class="gap-0 py-0">
                        <CardHeader class="border-b bg-muted/30 py-5">
                            <div class="flex items-center gap-2">
                                <div
                                    class="flex size-9 items-center justify-center rounded-lg bg-[#0a0a0a]/10 text-[#0a0a0a] dark:bg-white/10 dark:text-white"
                                >
                                    <Sparkles class="size-4" />
                                </div>
                                <div>
                                    <CardTitle>Publicação</CardTitle>
                                    <CardDescription>
                                        Status e data de exibição
                                    </CardDescription>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent class="space-y-5 py-6">
                            <div class="grid gap-2">
                                <Label for="status" class="text-sm font-semibold"
                                    >Status</Label
                                >
                                <select
                                    id="status"
                                    name="status"
                                    :class="cn(fieldClass, 'h-11')"
                                    required
                                >
                                    <option
                                        value="draft"
                                        :selected="post?.status === 'draft'"
                                    >
                                        Rascunho
                                    </option>
                                    <option
                                        value="published"
                                        :selected="
                                            post?.status === 'published' || !post
                                        "
                                    >
                                        Publicado
                                    </option>
                                </select>
                                <InputError :message="errors.status" />
                            </div>

                            <div class="grid gap-2">
                                <Label
                                    for="published_at"
                                    class="flex items-center gap-1.5 text-sm font-semibold"
                                >
                                    <Calendar class="size-3.5 text-muted-foreground" />
                                    Data de publicação
                                </Label>
                                <Input
                                    id="published_at"
                                    name="published_at"
                                    type="datetime-local"
                                    :class="cn(fieldClass, 'h-11')"
                                    :default-value="
                                        post?.published_at
                                            ? post.published_at.slice(0, 16)
                                            : undefined
                                    "
                                />
                                <InputError :message="errors.published_at" />
                            </div>

                            <Separator />

                            <div class="space-y-3">
                                <p class="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                    Destaques no blog
                                </p>

                                <label
                                    :class="
                                        cn(
                                            'flex cursor-pointer items-start gap-3 rounded-xl border p-3 transition hover:bg-muted/40',
                                            isFeatured &&
                                                'border-[#e10600] bg-[#e10600]/5',
                                        )
                                    "
                                >
                                    <input
                                        type="hidden"
                                        name="is_featured"
                                        :value="isFeatured ? '1' : '0'"
                                    />
                                    <Checkbox
                                        :checked="isFeatured"
                                        class="mt-0.5"
                                        @update:checked="
                                            (v) => (isFeatured = v === true)
                                        "
                                    />
                                    <div class="min-w-0 flex-1">
                                        <span
                                            class="flex items-center gap-1.5 text-sm font-semibold"
                                        >
                                            <Star class="size-3.5 text-amber-500" />
                                            Recomendados
                                        </span>
                                        <span
                                            class="mt-0.5 block text-xs text-muted-foreground"
                                        >
                                            Exibe na seção de destaques da home
                                        </span>
                                    </div>
                                </label>

                                <label
                                    :class="
                                        cn(
                                            'flex cursor-pointer items-start gap-3 rounded-xl border p-3 transition hover:bg-muted/40',
                                            isTrending &&
                                                'border-[#e10600] bg-[#e10600]/5',
                                        )
                                    "
                                >
                                    <input
                                        type="hidden"
                                        name="is_trending"
                                        :value="isTrending ? '1' : '0'"
                                    />
                                    <Checkbox
                                        :checked="isTrending"
                                        class="mt-0.5"
                                        @update:checked="
                                            (v) => (isTrending = v === true)
                                        "
                                    />
                                    <div class="min-w-0 flex-1">
                                        <span
                                            class="flex items-center gap-1.5 text-sm font-semibold"
                                        >
                                            <Flame class="size-3.5 text-[#e10600]" />
                                            Bombando
                                        </span>
                                        <span
                                            class="mt-0.5 block text-xs text-muted-foreground"
                                        >
                                            Aparece no carrossel “Bombando”
                                        </span>
                                    </div>
                                </label>
                            </div>
                        </CardContent>
                    </Card>

                    <Card class="gap-0 py-0">
                        <CardHeader class="border-b bg-muted/30 py-5">
                            <div class="flex items-center gap-2">
                                <div
                                    class="flex size-9 items-center justify-center rounded-lg bg-[#e10600]/10 text-[#e10600]"
                                >
                                    <ImageIcon class="size-4" />
                                </div>
                                <div>
                                    <CardTitle>Imagem de destaque</CardTitle>
                                    <CardDescription>
                                        Upload ou URL externa
                                    </CardDescription>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent class="space-y-4 py-6">
                            <div
                                v-if="imagePreview && !removeImage"
                                class="group relative overflow-hidden rounded-xl border"
                            >
                                <img
                                    :src="imagePreview"
                                    alt="Preview da imagem"
                                    class="aspect-video w-full object-cover"
                                />
                                <Button
                                    type="button"
                                    variant="secondary"
                                    size="sm"
                                    class="absolute top-2 right-2 opacity-0 transition group-hover:opacity-100"
                                    @click="clearImagePreview"
                                >
                                    Remover preview
                                </Button>
                            </div>

                            <label
                                v-else
                                class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-input bg-muted/20 px-4 py-8 text-center transition hover:border-[#e10600]/50 hover:bg-[#e10600]/5"
                            >
                                <ImageIcon class="size-8 text-muted-foreground" />
                                <span class="text-sm font-medium"
                                    >Clique para enviar imagem</span
                                >
                                <span class="text-xs text-muted-foreground"
                                    >JPG, PNG ou WebP</span
                                >
                                <input
                                    type="file"
                                    name="featured_image"
                                    accept="image/*"
                                    class="sr-only"
                                    @change="onImageChange"
                                />
                            </label>

                            <label
                                v-if="imagePreview && !removeImage"
                                class="flex cursor-pointer flex-col items-center justify-center gap-1 rounded-xl border border-dashed px-4 py-3 text-center text-sm transition hover:bg-muted/40"
                            >
                                <span class="font-medium">Trocar imagem</span>
                                <input
                                    type="file"
                                    name="featured_image"
                                    accept="image/*"
                                    class="sr-only"
                                    @change="onImageChange"
                                />
                            </label>

                            <Separator />

                            <div class="grid gap-2">
                                <Label
                                    for="featured_image_url"
                                    class="text-sm font-semibold"
                                    >URL da imagem</Label
                                >
                                <Input
                                    id="featured_image_url"
                                    name="featured_image_url"
                                    type="url"
                                    :class="fieldClass"
                                    placeholder="https://..."
                                />
                            </div>

                            <label
                                v-if="isEditing && post?.featured_image"
                                class="flex items-center gap-2 rounded-lg border border-dashed px-3 py-2.5 text-sm"
                            >
                                <input
                                    type="checkbox"
                                    name="remove_featured_image"
                                    value="1"
                                    class="size-4 rounded border-input"
                                    @change="
                                        removeImage = (
                                            $event.target as HTMLInputElement
                                        ).checked
                                    "
                                />
                                <span>Remover imagem salva no servidor</span>
                            </label>

                            <InputError :message="errors.featured_image" />
                        </CardContent>
                    </Card>

                    <Card class="border-[#0a0a0a]/10 bg-muted/20 py-4">
                        <CardContent class="flex flex-col gap-3 px-4">
                            <Button
                                type="submit"
                                class="h-11 w-full bg-[#e10600] font-semibold hover:bg-[#c10500]"
                                :disabled="processing"
                            >
                                <Save class="mr-2 size-4" />
                                {{
                                    processing
                                        ? 'Salvando...'
                                        : isEditing
                                          ? 'Salvar alterações'
                                          : 'Criar publicação'
                                }}
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                class="h-10 w-full"
                                as-child
                            >
                                <Link :href="index.url()">Cancelar</Link>
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </Form>
    </div>
</template>
