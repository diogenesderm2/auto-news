import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { BlogAdPlacement, BlogAdsConfig } from '@/types/blog-ads';

declare global {
    interface Window {
        adsbygoogle?: { push: (config: Record<string, unknown>) => void }[];
    }
}

let scriptLoaded = false;

export function useAdsense() {
    const page = usePage();

    const config = computed(
        () => (page.props.blogAds as BlogAdsConfig | null | undefined) ?? null,
    );

    const isEnabled = computed(
        () => config.value?.enabled === true && !!config.value.client,
    );

    function slotId(placement: BlogAdPlacement): string | null {
        const id = config.value?.slots?.[placement];

        return typeof id === 'string' && id !== '' ? id : null;
    }

    function hasSlot(placement: BlogAdPlacement): boolean {
        return isEnabled.value && slotId(placement) !== null;
    }

    function loadScript(client: string): void {
        if (typeof document === 'undefined' || scriptLoaded) {
            return;
        }

        if (document.querySelector('script[data-adsense="true"]')) {
            scriptLoaded = true;

            return;
        }

        const script = document.createElement('script');
        script.async = true;
        script.src = `https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=${client}`;
        script.crossOrigin = 'anonymous';
        script.setAttribute('data-adsense', 'true');
        document.head.appendChild(script);
        scriptLoaded = true;
    }

    function pushAd(element: HTMLElement | null): void {
        if (!element || !isEnabled.value || !config.value?.client) {
            return;
        }

        loadScript(config.value.client);

        try {
            (window.adsbygoogle = window.adsbygoogle || []).push({});
        } catch {
            // AdSense may throw if the slot is not ready yet.
        }
    }

    return {
        config,
        isEnabled,
        slotId,
        hasSlot,
        loadScript,
        pushAd,
    };
}
