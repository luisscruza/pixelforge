<script setup lang="ts">
import { TriangleAlert } from '@lucide/vue';
import ReconciliationBadge from '@/components/report/ReconciliationBadge.vue';
import MoneyValue from '@/components/studio/MoneyValue.vue';
import StatTile from '@/components/studio/StatTile.vue';
import type { SpendReport } from '@/types/generation';

defineProps<{
    report: SpendReport;
}>();
</script>

<template>
    <div class="space-y-3">
        <div
            v-if="report.unresolved_submissions > 0"
            class="flex items-start gap-2 rounded-lg border border-warning/40 bg-warning/10 px-4 py-3 text-sm"
            role="status"
        >
            <TriangleAlert class="mt-0.5 size-4 shrink-0 text-warning" />
            <p>
                <strong>{{ report.unresolved_submissions }}</strong>
                submission{{ report.unresolved_submissions === 1 ? '' : 's' }}
                still waiting on Pixelforge after a timeout. Their credits stay
                held and the reconciler keeps retrying with the same idempotency
                key.
            </p>
        </div>

        <div class="grid grid-cols-2 border-t border-l lg:grid-cols-4">
            <StatTile label="Charged to users"
                ><MoneyValue :value="report.totals.charged"
            /></StatTile>
            <StatTile label="Provider cost (ours)"
                ><MoneyValue :value="report.totals.provider_cost"
            /></StatTile>
            <StatTile label="Profit"
                ><MoneyValue :value="report.totals.profit" class="text-success"
            /></StatTile>
            <StatTile label="Pixelforge reports">
                <span class="flex flex-col gap-0.5">
                    <MoneyValue :value="report.totals.pixelforge_cost" />
                    <ReconciliationBadge
                        :difference="report.totals.difference"
                    />
                </span>
            </StatTile>
        </div>
    </div>
</template>
