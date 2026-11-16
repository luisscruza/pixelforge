<script setup lang="ts">
import { createForm } from '@inertiajs/vue3';
import type { PageProps } from '@inertiajs/core';
import { LoaderCircle, Sparkles } from '@lucide/vue';
import GenerationController from '@/actions/App/Http/Controllers/GenerationController';
import CostEstimatePanel from '@/components/studio/design-form/CostEstimatePanel.vue';
import OptionGroup from '@/components/studio/design-form/OptionGroup.vue';
import PromptField from '@/components/studio/design-form/PromptField.vue';
import ProviderSimulations from '@/components/studio/design-form/ProviderSimulations.vue';
import ReferenceImagesField from '@/components/studio/design-form/ReferenceImagesField.vue';
import { Button } from '@/components/ui/button';
import { useCostEstimate } from '@/composables/useCostEstimate';
import { useRememberedState } from '@/composables/useRememberedState';
import { addDecimals, negateDecimal } from '@/lib/money';
import {
    appendToPrompt,
    optimisticGeneration,
    QUALITY_OPTIONS,
    SIZE_OPTIONS,
} from '@/lib/studio';
import type {
    Generation,
    GenerationForm,
    Pricing,
    ResourceCollection,
} from '@/types/generation';

const props = defineProps<{
    pricing?: Pricing;
    markup: string;
    maxReferenceImages: number;
}>();

const DesignFormComponent = createForm<GenerationForm>();

const draft = useRememberedState<GenerationForm>(
    { prompt: '', size: '1536x1024', quality: 'medium', reference_images: [] },
    'studio-design-draft',
);

const { estimate, affordable } = useCostEstimate(
    () => draft,
    () => props.pricing,
    () => props.markup,
);

function optimistic(pageProps: PageProps): Partial<PageProps> {
    const feed = pageProps.generations as
        | ResourceCollection<Generation>
        | undefined;

    if (feed === undefined || estimate.value === null) {
        return {};
    }

    return {
        generations: {
            ...feed,
            data: [optimisticGeneration(draft, estimate.value), ...feed.data],
        },
        credits: addDecimals(
            String(pageProps.credits ?? '0'),
            negateDecimal(estimate.value.maximum),
        ),
    };
}

function resetDraft(): void {
    draft.prompt = '';
    draft.reference_images = [];
}
</script>

<template>
    <DesignFormComponent
        v-bind="GenerationController.store.form()"
        :optimistic="optimistic"
        :options="{ preserveScroll: true, reset: ['generations'] }"
        class="space-y-5"
        #default="{ processing }"
        @success="resetDraft"
    >
        <PromptField v-model="draft.prompt" />

        <OptionGroup
            v-model="draft.size"
            name="size"
            legend="Format"
            :options="SIZE_OPTIONS"
        />
        <OptionGroup
            v-model="draft.quality"
            name="quality"
            legend="Finish"
            :options="QUALITY_OPTIONS"
        />

        <ReferenceImagesField
            v-model="draft.reference_images"
            :max="maxReferenceImages"
            :tokens-per-image="pricing?.tokens.per_reference_image ?? 765"
        />

        <CostEstimatePanel :estimate="estimate" :affordable="affordable" />

        <Button
            type="submit"
            size="lg"
            class="w-full transition active:scale-[0.99]"
            :disabled="processing || !affordable || draft.prompt.trim() === ''"
        >
            <LoaderCircle v-if="processing" class="animate-spin" />
            <Sparkles v-else />
            {{ processing ? 'Sending to the studio…' : 'Design this room' }}
        </Button>

        <ProviderSimulations
            @insert="
                (keyword) =>
                    (draft.prompt = appendToPrompt(draft.prompt, keyword))
            "
        />
    </DesignFormComponent>
</template>
