<script setup lang="ts">
import { useFormContext } from '@inertiajs/vue3';
import { ChevronDown, Info } from '@lucide/vue';
import { ref } from 'vue';
import BuyCreditsDialog from '@/components/credits/BuyCreditsDialog.vue';
import MoneyValue from '@/components/studio/MoneyValue.vue';
import { useFlashOnChange } from '@/composables/useFlashOnChange';
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
    <div class="corner-marks rounded-lg border bg-blueprint p-4">
        <div v-if="estimate" class="space-y-3">
            <div class="flex items-end justify-between gap-3">
                <div>
                    <p
                        class="font-mono text-[10px] tracking-wider text-muted-foreground uppercase"
                    >
                        Estimated cost
                    </p>
                    <p
                        :class="
                            cn(
                                'text-2xl font-semibold transition-colors duration-500',
                                changed && 'text-primary',
                            )
                        "
                    >
                        <MoneyValue :value="estimate.expected" />
                    </p>
                </div>
                <div class="text-right">
                    <p
                        class="font-mono text-[10px] tracking-wider text-muted-foreground uppercase"
                    >
                        At most
                    </p>
                    <p class="text-sm">
                        <MoneyValue :value="estimate.maximum" />
                    </p>
                </div>
            </div>

            <p
                class="flex gap-1.5 text-[11px] leading-snug text-muted-foreground"
            >
                <Info class="mt-px size-3 shrink-0" />
                We hold the maximum while it renders and charge only the actual
                cost. Failed renders are free.
            </p>

            <button
                type="button"
                class="flex items-center gap-1 text-[11px] text-primary hover:underline"
                @click="showBreakdown = !showBreakdown"
            >
                Token breakdown
                <ChevronDown
                    :class="
                        cn('size-3 transition', showBreakdown && 'rotate-180')
                    "
                />
            </button>
            <Transition
                enter-from-class="opacity-0 -translate-y-1"
                leave-to-class="opacity-0"
                enter-active-class="transition duration-200"
                leave-active-class="transition duration-100"
            >
                <dl
                    v-if="showBreakdown"
                    class="grid grid-cols-2 gap-x-4 gap-y-1 font-mono text-[11px]"
                >
                    <dt class="text-muted-foreground">Prompt</dt>
                    <dd class="text-right tabular-nums">
                        {{ estimate.tokens.text }}–{{ estimate.tokens.maxText }}
                    </dd>
                    <dt class="text-muted-foreground">Inspiration</dt>
                    <dd class="text-right tabular-nums">
                        {{ estimate.tokens.images.toLocaleString() }}
                    </dd>
                    <dt class="text-muted-foreground">Render</dt>
                    <dd class="text-right tabular-nums">
                        {{ estimate.tokens.output.toLocaleString() }}
                    </dd>
                    <dt class="text-muted-foreground">Reasoning</dt>
                    <dd class="text-right tabular-nums">
                        {{ estimate.tokens.reasoning.min.toLocaleString() }}–{{
                            estimate.tokens.reasoning.max.toLocaleString()
                        }}
                    </dd>
                </dl>
            </Transition>
        </div>

        <div v-else class="space-y-2" aria-label="Loading prices">
            <div class="h-3 w-24 skeleton-shimmer rounded" />
            <div class="h-7 w-32 skeleton-shimmer rounded" />
        </div>

        <div
            v-if="!affordable || form?.errors.credits"
            class="mt-3 flex items-center justify-between gap-2 rounded-md border border-warning/40 bg-warning/10 px-3 py-2 text-xs"
        >
            <span>{{
                form?.errors.credits ??
                'Not enough credits for the maximum price.'
            }}</span>
            <button
                type="button"
                class="shrink-0 font-medium text-primary hover:underline"
                @click="buying = true"
            >
                Top up
            </button>
        </div>
        <BuyCreditsDialog v-model:open="buying" />
    </div>
</template>
