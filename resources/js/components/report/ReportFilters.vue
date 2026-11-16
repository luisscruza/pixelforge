<script setup lang="ts">
import { createForm, Link } from '@inertiajs/vue3';
import { CalendarRange, LoaderCircle } from '@lucide/vue';
import SpendReportController from '@/actions/App/Http/Controllers/Admin/SpendReportController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';

type ReportFilters = { start_date: string; end_date: string };

defineProps<{
    filters: ReportFilters;
    maxDays: number;
}>();

const FiltersForm = createForm<ReportFilters>();

const QUICK_RANGES = [7, 14, 30, 90];

function rangeFor(days: number): ReportFilters {
    const end = new Date();
    const start = new Date(end.getTime() - (days - 1) * 86_400_000);

    return {
        start_date: start.toISOString().slice(0, 10),
        end_date: end.toISOString().slice(0, 10),
    };
}
</script>

<template>
    <FiltersForm
        v-bind="SpendReportController.index.form()"
        :options="{ preserveScroll: true, only: ['report', 'filters'] }"
        class="flex flex-wrap items-end gap-3"
        #default="{ errors, processing }"
    >
        <label class="grid gap-1 text-xs">
            <span class="text-muted-foreground">From (UTC)</span>
            <input
                type="date"
                name="start_date"
                :value="filters.start_date"
                class="h-9 rounded-md border bg-card px-2 font-mono text-xs shadow-xs outline-none focus:border-primary"
            />
        </label>
        <label class="grid gap-1 text-xs">
            <span class="text-muted-foreground">To (UTC)</span>
            <input
                type="date"
                name="end_date"
                :value="filters.end_date"
                class="h-9 rounded-md border bg-card px-2 font-mono text-xs shadow-xs outline-none focus:border-primary"
            />
        </label>
        <Button
            type="submit"
            variant="outline"
            size="default"
            :disabled="processing"
        >
            <LoaderCircle v-if="processing" class="animate-spin" />
            <CalendarRange v-else />
            Apply
        </Button>

        <div class="flex gap-1">
            <Link
                v-for="days in QUICK_RANGES"
                :key="days"
                :href="SpendReportController.index({ query: rangeFor(days) })"
                :only="['report', 'filters']"
                preserve-scroll
                class="rounded-md border px-2 py-1.5 font-mono text-[11px] text-muted-foreground transition hover:border-primary/50 hover:text-foreground"
            >
                {{ days }}d
            </Link>
        </div>

        <InputError
            class="basis-full"
            :message="errors.end_date ?? errors.start_date"
        />
        <p class="basis-full font-mono text-[10px] text-muted-foreground">
            Days are UTC completion days, like Pixelforge bills them. Up to
            {{ maxDays }} days.
        </p>
    </FiltersForm>
</template>
