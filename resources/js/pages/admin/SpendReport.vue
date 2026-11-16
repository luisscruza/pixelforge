<script setup lang="ts">
import { Deferred, Head, router } from '@inertiajs/vue3';
import { RotateCw } from '@lucide/vue';
import ReportDaysTable from '@/components/report/ReportDaysTable.vue';
import ReportFilters from '@/components/report/ReportFilters.vue';
import ReportSkeleton from '@/components/report/ReportSkeleton.vue';
import ReportSummary from '@/components/report/ReportSummary.vue';
import ReportUsersTable from '@/components/report/ReportUsersTable.vue';
import { Button } from '@/components/ui/button';
import type { SpendReport } from '@/types/generation';

defineProps<{
    filters?: { start_date: string; end_date: string };
    report?: SpendReport;
    maxDays?: number;
}>();
</script>

<template>
    <Head title="Spend report" />

    <div class="flex flex-1 flex-col gap-8 px-4 py-12 md:px-10">
        <header class="space-y-3">
            <p class="text-sm font-medium text-primary">
                Operators · Reconciliation
            </p>
            <h1
                class="text-4xl leading-[1.05] font-medium tracking-tight text-ink md:text-5xl"
            >
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
