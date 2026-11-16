<script setup lang="ts">
import { computed } from 'vue';
import type { Estimate } from '@/lib/money';

const props = defineProps<{
    tokens: Estimate['tokens'];
}>();

const range = (min: number, max: number): string =>
    min === max
        ? min.toLocaleString()
        : `${min.toLocaleString()} – ${max.toLocaleString()}`;

const rows = computed(() => [
    {
        label: 'Your description',
        value: range(props.tokens.text, props.tokens.maxText),
    },
    {
        label: 'Inspiration photos',
        value: props.tokens.images.toLocaleString(),
    },
    { label: 'The render itself', value: props.tokens.output.toLocaleString() },
    {
        label: 'Design reasoning',
        value: range(props.tokens.reasoning.min, props.tokens.reasoning.max),
    },
]);
</script>

<template>
    <table class="w-full text-sm">
        <caption class="sr-only">
            Tokens used to price this design
        </caption>
        <tbody class="divide-y">
            <tr v-for="row in rows" :key="row.label">
                <th
                    scope="row"
                    class="py-2 text-left font-normal text-muted-foreground"
                >
                    {{ row.label }}
                </th>
                <td class="py-2 text-right text-ink tabular-nums">
                    {{ row.value }} tokens
                </td>
            </tr>
        </tbody>
    </table>
</template>
