<script setup lang="ts">
import { useFormContext } from '@inertiajs/vue3';
import { ChevronDown, TriangleAlert } from '@lucide/vue';
import { ref } from 'vue';
import BuyCreditsDialog from '@/components/credits/BuyCreditsDialog.vue';
import TokenBreakdown from '@/components/studio/design-form/TokenBreakdown.vue';
import { useFlashOnChange } from '@/composables/useFlashOnChange';
import { formatUsd } from '@/lib/money';
import type { Estimate } from '@/lib/money';
import { cn } from '@/lib/utils';
import type { GenerationForm } from '@/types/generation';

const props = defineProps<{
    estimate: Estimate | null;
    affordable: boolean;
}>();

const form = useFormContext<GenerationForm & { credits: string }>();
const showBreakdown = ref(false);
const buying = ref(false);
const changed = useFlashOnChange(() => props.estimate?.expected);
</script>

<template>
    <div class="border bg-white">
        <div v-if="estimate" class="space-y-2 p-4">
            <div class="flex items-baseline justify-between gap-3">
                <span class="text-sm text-ink">Estimated cost</span>
                <span
                    :class="
                        cn(
                            'text-3xl font-medium tracking-tight tabular-nums transition-colors duration-500',
                            changed ? 'text-primary' : 'text-ink',
                        )
                    "
                    :title="`$${estimate.expected} exactly`"
                    aria-live="polite"
                >
                    {{ formatUsd(estimate.expected) }}
                </span>
            </div>
            <p class="text-sm leading-relaxed text-muted-foreground">
                You'll pay the actual cost, never more than
                <strong class="font-medium text-ink tabular-nums">{{
                    formatUsd(estimate.maximum)
                }}</strong
                >. If the render fails, it's free.
            </p>
        </div>

        <div v-else class="space-y-3 p-4" aria-label="Loading prices">
            <div class="flex justify-between">
                <div class="h-4 w-28 skeleton-shimmer" />
                <div class="h-8 w-24 skeleton-shimmer" />
            </div>
            <div class="h-4 w-full skeleton-shimmer" />
        </div>

        <div v-if="estimate" class="border-t">
            <button
                type="button"
                class="flex w-full items-center justify-between px-4 py-2.5 text-sm text-muted-foreground transition-colors hover:bg-muted hover:text-ink"
                :aria-expanded="showBreakdown"
                @click="showBreakdown = !showBreakdown"
            >
                How is this calculated?
                <ChevronDown
                    :class="
                        cn('size-4 transition', showBreakdown && 'rotate-180')
                    "
                />
            </button>
            <Transition
                enter-from-class="opacity-0 -translate-y-1"
                leave-to-class="opacity-0"
                enter-active-class="transition duration-200"
                leave-active-class="transition duration-100"
            >
                <div v-if="showBreakdown" class="space-y-2 px-4 pb-4">
                    <TokenBreakdown :tokens="estimate.tokens" />
                    <p class="text-xs leading-relaxed text-muted-foreground">
                        Each token is priced at the render engine's published
                        rate, plus a 25% studio fee. Ranges narrow to an exact
                        count once your design is done.
                    </p>
                </div>
            </Transition>
        </div>

        <div
            v-if="!affordable || form?.errors.credits"
            class="flex items-start gap-2.5 border-t bg-warning/10 px-4 py-3 text-sm text-ink"
            role="alert"
        >
            <TriangleAlert class="mt-0.5 size-4 shrink-0 text-warning" />
            <span class="flex-1">
                {{
                    form?.errors.credits ??
                    'Your balance doesn’t cover the maximum price yet.'
                }}
            </span>
            <button
                type="button"
                class="shrink-0 font-medium text-primary underline-offset-4 hover:underline"
                @click="buying = true"
            >
                Buy credits
            </button>
        </div>
        <BuyCreditsDialog v-model:open="buying" />
    </div>
</template>
