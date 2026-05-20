<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { useAdsense } from '@/composables/useAdsense';
import { cn } from '@/lib/utils';
import type { BlogAdPlacement } from '@/types/blog-ads';

const props = withDefaults(
    defineProps<{
        placement: BlogAdPlacement;
        format?: 'auto' | 'horizontal' | 'vertical' | 'rectangle';
        class?: string;
        showLabel?: boolean;
    }>(),
    {
        format: 'auto',
        showLabel: true,
    },
);

const page = usePage();
const adRef = ref<HTMLElement | null>(null);
const { config, hasSlot, slotId, pushAd } = useAdsense();

const isVisible = computed(() => hasSlot(props.placement));

const adClient = computed(() => config.value?.client ?? '');

const adSlot = computed(() => slotId(props.placement) ?? '');

async function refreshAd(): Promise<void> {
    await nextTick();
    pushAd(adRef.value);
}

onMounted(() => {
    refreshAd();
});

watch(
    () => page.url,
    () => {
        refreshAd();
    },
);
</script>

<template>
    <div
        v-if="isVisible"
        :class="cn('flex flex-col items-center gap-1', props.class)"
        data-ad-placement
        :data-ad-placement-name="placement"
    >
        <p
            v-if="showLabel"
            class="w-full text-center text-[10px] font-semibold tracking-widest text-muted-foreground uppercase"
        >
            Publicidade
        </p>
        <div
            ref="adRef"
            class="flex w-full min-h-[90px] items-center justify-center overflow-hidden rounded-lg border border-dashed border-black/10 bg-[#fafafa]"
        >
            <ins
                class="adsbygoogle block w-full"
                :style="{ display: 'block' }"
                :data-ad-client="adClient"
                :data-ad-slot="adSlot"
                :data-ad-format="format"
                data-full-width-responsive="true"
            />
        </div>
    </div>
</template>
