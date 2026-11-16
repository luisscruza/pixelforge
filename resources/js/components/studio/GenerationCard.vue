<script setup lang="ts">
import GenerationCost from '@/components/studio/GenerationCost.vue';
import GenerationPreview from '@/components/studio/GenerationPreview.vue';
import StatusPill from '@/components/studio/StatusPill.vue';
import { useRelativeTime } from '@/composables/useNow';
import { QUALITY_OPTIONS, SIZE_OPTIONS } from '@/lib/studio';
import { cn } from '@/lib/utils';
import type { Generation } from '@/types/generation';

const props = defineProps<{
    generation: Generation;
}>();

defineEmits<{
    open: [generation: Generation];
}>();

const createdAgo = useRelativeTime(() => props.generation.created_at);
const sizeLabel = (value: string) =>
    SIZE_OPTIONS.find((option) => option.value === value)?.label ?? value;
const qualityLabel = (value: string) =>
    QUALITY_OPTIONS.find((option) => option.value === value)?.label ?? value;
</script>

<template>
    <article
        :class="
            cn(
                'group animate-rise overflow-hidden rounded-lg border bg-card shadow-xs transition duration-300 hover:-translate-y-0.5 hover:border-primary/40 hover:shadow-md',
                generation.optimistic && 'opacity-80',
            )
        "
    >
        <GenerationPreview
            :generation="generation"
            @open="$emit('open', generation)"
        />

        <div class="space-y-3 p-3.5">
            <div class="flex items-center justify-between gap-2">
                <StatusPill
                    :status="generation.status"
                    :confirming="generation.is_confirming"
                />
                <span class="font-mono text-[11px] text-muted-foreground">{{
                    createdAgo
                }}</span>
            </div>

            <p
                class="line-clamp-2 text-sm leading-snug text-foreground/90"
                :title="generation.prompt"
            >
                {{ generation.prompt }}
            </p>

            <div
                class="flex flex-wrap gap-1.5 font-mono text-[10px] tracking-wide text-muted-foreground uppercase"
            >
                <span class="rounded border px-1.5 py-0.5">{{
                    sizeLabel(generation.size)
                }}</span>
                <span class="rounded border px-1.5 py-0.5">{{
                    qualityLabel(generation.quality)
                }}</span>
                <span
                    v-if="generation.reference_images.length"
                    class="rounded border px-1.5 py-0.5"
                >
                    {{ generation.reference_images.length }} inspo
                </span>
            </div>

            <GenerationCost :generation="generation" />
        </div>
    </article>
</template>
