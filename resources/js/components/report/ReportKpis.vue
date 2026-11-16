<script setup lang="ts">
import { computed } from 'vue';
import KpiCard from '@/components/report/KpiCard.vue';
import ReconciliationBadge from '@/components/report/ReconciliationBadge.vue';
import MoneyValue from '@/components/studio/MoneyValue.vue';
import { percentOf } from '@/lib/report';
import type { SpendReport } from '@/types/generation';

const props = defineProps<{
    report: SpendReport;
}>();

const renders = computed(() =>
    props.report.days.reduce((total, day) => total + day.generations, 0),
);
const activeCustomers = computed(() => props.report.user_totals.length);
</script>

<template>
    <div class="grid border-t border-l sm:grid-cols-2 xl:grid-cols-4">
        <KpiCard label="Revenue">
            <MoneyValue :value="report.totals.charged" />
            <template #footer>Charged to customers</template>
        </KpiCard>
        <KpiCard label="Provider cost">
            <MoneyValue :value="report.totals.provider_cost" />
            <template #footer>
                <ReconciliationBadge :difference="report.totals.difference" />
            </template>
        </KpiCard>
        <KpiCard label="Profit">
            <MoneyValue :value="report.totals.profit" />
            <template #footer>
                {{ percentOf(report.totals.profit, report.totals.charged) }}
                margin
            </template>
        </KpiCard>
        <KpiCard label="Renders">
            {{ renders.toLocaleString() }}
            <template #footer>
                {{ activeCustomers }} paying customer{{
                    activeCustomers === 1 ? '' : 's'
                }}
            </template>
        </KpiCard>
    </div>
</template>
