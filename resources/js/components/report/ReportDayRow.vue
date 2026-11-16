<script setup lang="ts">
import { ChevronRight } from '@lucide/vue';
import { ref } from 'vue';
import ReconciliationBadge from '@/components/report/ReconciliationBadge.vue';
import MoneyValue from '@/components/studio/MoneyValue.vue';
import { formatDay } from '@/lib/time';
import { cn } from '@/lib/utils';
import type { SpendReportDay } from '@/types/generation';

defineProps<{
    day: SpendReportDay;
}>();

const expanded = ref(false);
</script>

<template>
    <tr
        :class="
            cn(
                'border-t transition-colors',
                day.generations > 0
                    ? 'cursor-pointer hover:bg-accent/40'
                    : 'text-muted-foreground',
            )
        "
        :aria-expanded="day.generations > 0 ? expanded : undefined"
        @click="day.generations > 0 && (expanded = !expanded)"
    >
        <td class="py-2.5 pl-4">
            <span class="inline-flex items-center gap-1.5">
                <ChevronRight
                    :class="
                        cn(
                            'size-3.5 transition',
                            expanded && 'rotate-90',
                            day.generations === 0 && 'invisible',
                        )
                    "
                />
                <span class="font-mono text-xs">{{ formatDay(day.date) }}</span>
            </span>
        </td>
        <td class="text-right font-mono text-xs tabular-nums">
            {{ day.generations }}
        </td>
        <td class="text-right text-xs">
            <MoneyValue :value="day.charged" :decimals="6" />
        </td>
        <td class="text-right text-xs">
            <MoneyValue :value="day.provider_cost" :decimals="6" />
        </td>
        <td class="text-right text-xs">
            <MoneyValue :value="day.profit" :decimals="6" />
        </td>
        <td class="text-right text-xs">
            <MoneyValue :value="day.pixelforge_cost" :decimals="6" />
        </td>
        <td class="pr-4 text-right">
            <ReconciliationBadge :difference="day.difference" compact />
        </td>
    </tr>
    <tr
        v-for="user in expanded ? day.users : []"
        :key="`${day.date}-${user.user_id}`"
        class="bg-muted/40 text-xs"
    >
        <td class="py-2 pl-10">
            <span class="font-medium">{{ user.name }}</span>
            <span class="ml-1.5 text-muted-foreground">{{ user.email }}</span>
        </td>
        <td class="text-right font-mono tabular-nums">
            {{ user.generations }}
        </td>
        <td class="text-right">
            <MoneyValue :value="user.charged" :decimals="6" />
        </td>
        <td class="text-right">
            <MoneyValue :value="user.provider_cost" :decimals="6" />
        </td>
        <td class="text-right">
            <MoneyValue :value="user.profit" :decimals="6" />
        </td>
        <td colspan="2" />
    </tr>
</template>
