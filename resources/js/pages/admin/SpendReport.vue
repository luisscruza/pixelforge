<script setup lang="ts">
import { Deferred, Head, router } from '@inertiajs/vue3';
import { RotateCw } from '@lucide/vue';
import ReportDaysTable from '@/components/report/ReportDaysTable.vue';
import ReportFilters from '@/components/report/ReportFilters.vue';
import ReportSkeleton from '@/components/report/ReportSkeleton.vue';
import ReportSummary from '@/components/report/ReportSummary.vue';
import ReportUsersTable from '@/components/report/ReportUsersTable.vue';
import { Button } from '@/components/ui/button';
import { index } from '@/routes/admin/spend-report';
import type { SpendReport } from '@/types/generation';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Spend report', href: index() }],
    },
});

defineProps<{
    filters?: { start_date: string; end_date: string };
    report?: SpendReport;
    maxDays?: number;
}>();
</script>

<template>
    <Head title="Spend report" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <header class="space-y-1">
            <p
                class="font-mono text-[11px] tracking-[0.2em] text-primary uppercase"
            >
                Operators · Reconciliation
            </p>
            <h1 class="text-2xl font-semibold tracking-tight md:text-3xl">
                Credit spend, provider cost & profit
            </h1>
            <p class="max-w-2xl text-sm text-muted-foreground">
                Every amount is exact to the twelfth decimal. Provider costs are
                checked day by day against Pixelforge's own usage report.
            </p>
        </header>

        <ReportFilters
            v-if="filters"
            :filters="filters"
            :max-days="maxDays ?? 92"
        />
        <div v-else class="h-14 w-full max-w-xl skeleton-shimmer rounded-md" />

        <Deferred data="report">
            <template #fallback>
                <ReportSkeleton />
            </template>

            <template #rescue="{ reloading: retrying }">
                <div
                    class="flex flex-col items-center gap-3 rounded-lg border border-dashed bg-blueprint py-12 text-center"
                >
                    <p class="text-sm text-muted-foreground">
                        The report couldn't be built just now.
                    </p>
                    <Button
                        variant="outline"
                        :disabled="retrying"
                        @click="router.reload({ only: ['report'] })"
                    >
                        <RotateCw :class="retrying ? 'animate-spin' : ''" /> Try
                        again
                    </Button>
                </div>
            </template>

            <template #default="{ reloading }">
                <div
                    v-if="report"
                    :class="[
                        'space-y-6 transition-opacity duration-300',
                        reloading && 'opacity-50',
                    ]"
                >
                    <ReportSummary :report="report" />

                    <section class="space-y-2">
                        <h2 class="text-sm font-semibold">
                            By day
                            <span class="font-normal text-muted-foreground"
                                >· click a day for its users</span
                            >
                        </h2>
                        <ReportDaysTable :days="report.days" />
                    </section>

                    <section class="space-y-2">
                        <h2 class="text-sm font-semibold">By user</h2>
                        <ReportUsersTable :users="report.user_totals" />
                    </section>
                </div>
            </template>
        </Deferred>
    </div>
</template>
