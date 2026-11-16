<script setup lang="ts">
import { ChevronRight } from '@lucide/vue';
import { ref } from 'vue';
import ReconciliationBadge from '@/components/report/ReconciliationBadge.vue';
import MoneyValue from '@/components/studio/MoneyValue.vue';
import { formatDay } from '@/lib/time';
import { cn } from '@/lib/utils';
import type { SpendReportDay } from '@/types/generation';

const props = defineProps<{
    day: SpendReportDay;
}>();

const expanded = ref(false);

function toggle(): void {
    if (props.day.generations > 0) {
        expanded.value = !expanded.value;
    }
}
</script>

<template>
    <tr
        :class="
            cn(
                'border-t transition-colors',
                day.generations > 0
                    ? 'cursor-pointer hover:bg-muted/60'
                    : 'text-muted-foreground',
            )
        "
        :aria-expanded="day.generations > 0 ? expanded : undefined"
        @click="toggle"
    >
        <td class="py-2.5 pl-5">
            <span class="inline-flex items-center gap-1.5">
                <ChevronRight
                    :class="
                        cn(
                            'size-3.5 text-muted-foreground transition',
                            expanded && 'rotate-90',
                            day.generations === 0 && 'invisible',
                        )
                    "
                />
                {{ formatDay(day.date) }}
            </span>
        </td>
        <td class="px-3 text-right">{{ day.generations }}</td>
        <td class="px-3 text-right"><MoneyValue :value="day.charged" /></td>
        <td class="px-3 text-right">
            <MoneyValue :value="day.provider_cost" />
        </td>
        <td class="px-3 text-right"><MoneyValue :value="day.profit" /></td>
        <td class="px-3 text-right">
            <MoneyValue :value="day.pixelforge_cost" />
        </td>
        <td class="pr-5 text-right">
            <ReconciliationBadge :difference="day.difference" />
        </td>
    </tr>
    <tr
        v-for="user in expanded ? day.users : []"
        :key="`${day.date}-${user.user_id}`"
        class="bg-muted/50 text-muted-foreground"
    >
        <td class="py-2 pl-11">
            <span class="text-ink">{{ user.name }}</span>
            <span class="ml-1.5 text-xs">{{ user.email }}</span>
        </td>
        <td class="px-3 text-right">{{ user.generations }}</td>
        <td class="px-3 text-right"><MoneyValue :value="user.charged" /></td>
        <td class="px-3 text-right">
            <MoneyValue :value="user.provider_cost" />
        </td>
        <td class="px-3 text-right"><MoneyValue :value="user.profit" /></td>
        <td colspan="2" />
    </tr>
</template>
