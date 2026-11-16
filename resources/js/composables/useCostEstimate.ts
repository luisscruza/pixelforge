import { computed } from 'vue';
import type { ComputedRef } from 'vue';
import { useCredits } from '@/composables/useCredits';
import { estimateCost } from '@/lib/money';
import type { Estimate } from '@/lib/money';
import type { GenerationForm, Pricing } from '@/types/generation';

export function useCostEstimate(
    draft: () => GenerationForm,
    pricing: () => Pricing | undefined,
    markup: () => string,
): {
    estimate: ComputedRef<Estimate | null>;
    affordable: ComputedRef<boolean>;
} {
    const { canAfford } = useCredits();

    const estimate = computed(() => {
        const currentPricing = pricing();

        if (currentPricing === undefined) {
            return null;
        }

        return estimateCost(
            {
                prompt: draft().prompt,
                size: draft().size,
                quality: draft().quality,
                referenceImages: draft().reference_images.filter(
                    (url) => url.trim() !== '',
                ).length,
            },
            currentPricing,
            markup(),
        );
    });

    const affordable = computed(
        () => estimate.value === null || canAfford(estimate.value.maximum),
    );

    return { estimate, affordable };
}
