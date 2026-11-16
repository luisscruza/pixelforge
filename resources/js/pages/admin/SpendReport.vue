<script setup lang="ts">
import { Deferred, Head, router } from '@inertiajs/vue3';
import { Clock, RotateCw } from '@lucide/vue';
import AdminCard from '@/components/report/AdminCard.vue';
import ReportDaysTable from '@/components/report/ReportDaysTable.vue';
import ReportFilters from '@/components/report/ReportFilters.vue';
import ReportKpis from '@/components/report/ReportKpis.vue';
import ReportSkeleton from '@/components/report/ReportSkeleton.vue';
import ReportUsersTable from '@/components/report/ReportUsersTable.vue';
import RevenueChart from '@/components/report/RevenueChart.vue';
import { Button } from '@/components/ui/button';
import { formatDay } from '@/lib/time';
import type { SpendReport } from '@/types/generation';

defineProps<{
    filters?: { start_date: string; end_date: string };
    report?: SpendReport;
    maxDays?: number;
}>();
</script>

<template>
    <Head title="Revenue" />

    <div class="flex flex-1 flex-col bg-sand/60">
        <header
            class="flex flex-wrap items-end justify-between gap-4 border-b bg-white px-4 py-6 md:px-8"
        >
            <div class="space-y-1">
                <p class="text-sm font-medium text-primary">Admin</p>
                <h1 class="text-3xl font-medium tracking-tight text-ink">
                    Revenue
                </h1>
                <p v-if="filters" class="text-sm text-muted-foreground">
                    {{ formatDay(filters.start_date) }} –
                    {{ formatDay(filters.end_date) }} · UTC
                </p>
            </div>
            <ReportFilters
                v-if="filters"
                :filters="filters"
                :max-days="maxDays ?? 92"
            />
        </header>

        <div class="space-y-6 px-4 py-6 md:px-8">
            <Deferred data="report">
                <template #fallback>
                    <ReportSkeleton />
                </template>

                <template #rescue="{ reloading: retrying }">
                    <div
                        class="flex flex-col items-center gap-3 border bg-white py-14 text-center"
                    >
                        <p class="text-sm text-muted-foreground">
                            The report couldn't be loaded.
                        </p>
                        <Button
                            variant="outline"
                            :disabled="retrying"
                            @click="router.reload({ only: ['report'] })"
                        >
                            <RotateCw :class="retrying ? 'animate-spin' : ''" />
                            Try again
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
                        <div
                            v-if="report.unresolved_submissions > 0"
                            class="flex items-start gap-2.5 border border-warning/40 bg-warning/10 px-4 py-3 text-sm text-ink"
                            role="status"
                        >
                            <Clock
                                class="mt-0.5 size-4 shrink-0 text-warning"
                            />
                            <p>
                                <strong class="font-medium">
                                    {{ report.unresolved_submissions }}
                                    render{{
                                        report.unresolved_submissions === 1
                                            ? ''
                                            : 's'
                                    }}
                                </strong>
                                awaiting confirmation from the provider. Their
                                credits stay on hold and are retried
                                automatically.
                            </p>
                        </div>

                        <ReportKpis :report="report" />

                        <AdminCard
                            title="Daily revenue"
                            description="Provider cost and profit per completion day"
                        >
                            <RevenueChart :days="report.days" />
                        </AdminCard>

                        <AdminCard
                            title="Daily breakdown"
                            description="Select a day to see its customers"
                        >
                            <ReportDaysTable :report="report" />
                        </AdminCard>

                        <AdminCard
                            title="Top customers"
                            description="Ranked by revenue in this period"
                        >
                            <ReportUsersTable
                                :users="report.user_totals"
                                :revenue="report.totals.charged"
                            />
                        </AdminCard>
                    </div>
                </template>
            </Deferred>
        </div>
    </div>
</template>
