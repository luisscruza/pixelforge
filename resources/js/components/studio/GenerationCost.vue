<script setup lang="ts">
import { computed } from 'vue';
import MoneyValue from '@/components/studio/MoneyValue.vue';
import { isZero } from '@/lib/money';
import type { Generation } from '@/types/generation';

const props = defineProps<{
    generation: Generation;
}>();

const notCharged = computed(
    () =>
        props.generation.status === 'failed' || isZero(props.generation.price),
);
</script>

<template>
    <dl
        class="grid grid-cols-2 gap-px overflow-hidden rounded-md border bg-border text-xs"
    >
        <div class="bg-card px-2.5 py-1.5">
            <dt
                class="text-[10px] tracking-wider text-muted-foreground uppercase"
            >
                Estimate
            </dt>
            <dd><MoneyValue :value="generation.estimated_price" /></dd>
        </div>
        <div class="bg-card px-2.5 py-1.5">
            <dt
                class="text-[10px] tracking-wider text-muted-foreground uppercase"
            >
                Charged
            </dt>
            <dd v-if="notCharged" class="font-mono text-success">$0 · free</dd>
            <dd v-else-if="generation.price">
                <MoneyValue :value="generation.price" class="text-foreground" />
            </dd>
            <dd v-else class="font-mono text-muted-foreground">
                <span
                    class="inline-block h-3 w-14 translate-y-0.5 skeleton-shimmer rounded-sm"
                />
            </dd>
        </div>
    </dl>
</template>
