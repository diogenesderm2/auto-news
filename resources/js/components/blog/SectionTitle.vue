<script setup lang="ts">
defineProps<{
    title: string;
    href?: string;
    linkLabel?: string;
}>();
</script>

<template>
    <div class="mb-6 flex items-end justify-between border-b-2 border-[#0a0a0a] pb-2">
        <h2 class="text-lg font-black tracking-wide text-[#0a0a0a] uppercase md:text-xl">
            {{ title }}
        </h2>
        <a
            v-if="href"
            :href="href"
            class="text-xs font-bold tracking-wide text-[#e10600] uppercase hover:underline"
        >
            {{ linkLabel ?? 'Ver tudo' }}
        </a>
    </div>
</template>
