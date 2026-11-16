<script setup lang="ts">
import MoneyValue from '@/components/studio/MoneyValue.vue';
import { useInitials } from '@/composables/useInitials';
import { percentOf, toNumber } from '@/lib/report';
import type { SpendReportUser } from '@/types/generation';

const props = defineProps<{
    users: SpendReportUser[];
    revenue: string;
}>();

const { getInitials } = useInitials();

function share(user: SpendReportUser): number {
    const total = toNumber(props.revenue);

    return total > 0 ? (toNumber(user.charged) / total) * 100 : 0;
}
</script>

<template>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[760px] text-sm tabular-nums">
            <thead class="bg-muted/60 text-xs text-muted-foreground">
                <tr>
                    <th class="py-2.5 pl-5 text-left font-medium">Customer</th>
                    <th class="px-3 text-right font-medium">Renders</th>
                    <th class="px-3 text-right font-medium">Revenue</th>
                    <th class="px-3 text-right font-medium">Provider cost</th>
                    <th class="px-3 text-right font-medium">Profit</th>
                    <th class="w-48 pr-5 text-left font-medium">
                        Share of revenue
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="users.length === 0">
                    <td
                        colspan="6"
                        class="py-10 text-center text-muted-foreground"
                    >
                        No completed renders in this period.
                    </td>
                </tr>
                <tr
                    v-for="user in users"
                    :key="user.user_id"
                    class="border-t transition-colors hover:bg-muted/60"
                >
                    <td class="py-2.5 pl-5">
                        <span class="flex items-center gap-2.5">
                            <span
                                class="grid size-7 shrink-0 place-items-center bg-ink text-[11px] font-medium text-white"
                            >
                                {{ getInitials(user.name) }}
                            </span>
                            <span class="min-w-0">
                                <span class="block truncate text-ink">
                                    {{ user.name }}
                                </span>
                                <span
                                    class="block truncate text-xs text-muted-foreground"
                                >
                                    {{ user.email }}
                                </span>
                            </span>
                        </span>
                    </td>
                    <td class="px-3 text-right">{{ user.generations }}</td>
                    <td class="px-3 text-right">
                        <MoneyValue :value="user.charged" />
                    </td>
                    <td class="px-3 text-right">
                        <MoneyValue :value="user.provider_cost" />
                    </td>
                    <td class="px-3 text-right">
                        <MoneyValue :value="user.profit" />
                    </td>
                    <td class="pr-5">
                        <span class="flex items-center gap-2">
                            <span class="h-1.5 flex-1 bg-muted">
                                <span
                                    class="block h-full bg-primary"
                                    :style="{ width: `${share(user)}%` }"
                                />
                            </span>
                            <span
                                class="w-12 text-right text-xs text-muted-foreground"
                            >
                                {{ percentOf(user.charged, revenue) }}
                            </span>
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
