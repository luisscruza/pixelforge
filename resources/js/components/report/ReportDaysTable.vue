<script setup lang="ts">
import { computed } from 'vue';
import ReconciliationBadge from '@/components/report/ReconciliationBadge.vue';
import ReportDayRow from '@/components/report/ReportDayRow.vue';
import MoneyValue from '@/components/studio/MoneyValue.vue';
import type { SpendReport } from '@/types/generation';

const props = defineProps<{
    report: SpendReport;
}>();

const newestFirst = computed(() => [...props.report.days].reverse());
const renders = computed(() =>
    props.report.days.reduce((total, day) => total + day.generations, 0),
);
</script>

<template>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[860px] text-sm tabular-nums">
            <thead class="bg-muted/60 text-xs text-muted-foreground">
                <tr>
                    <th class="py-2.5 pl-5 text-left font-medium">
                        Date (UTC)
                    </th>
                    <th class="px-3 text-right font-medium">Renders</th>
                    <th class="px-3 text-right font-medium">Revenue</th>
                    <th class="px-3 text-right font-medium">Provider cost</th>
                    <th class="px-3 text-right font-medium">Profit</th>
                    <th class="px-3 text-right font-medium">
                        Billed by provider
                    </th>
                    <th class="pr-5 text-right font-medium">Status</th>
                </tr>
            </thead>
            <tbody>
                <ReportDayRow
                    v-for="day in newestFirst"
                    :key="day.date"
                    :day="day"
                />
            </tbody>
            <tfoot class="border-t-2 font-medium text-ink">
                <tr>
                    <td class="py-3 pl-5">Total</td>
                    <td class="px-3 text-right">{{ renders }}</td>
                    <td class="px-3 text-right">
                        <MoneyValue :value="report.totals.charged" />
                    </td>
                    <td class="px-3 text-right">
                        <MoneyValue :value="report.totals.provider_cost" />
                    </td>
                    <td class="px-3 text-right">
                        <MoneyValue :value="report.totals.profit" />
                    </td>
                    <td class="px-3 text-right">
                        <MoneyValue :value="report.totals.pixelforge_cost" />
                    </td>
                    <td class="pr-5 text-right">
                        <ReconciliationBadge
                            :difference="report.totals.difference"
                        />
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</template>
