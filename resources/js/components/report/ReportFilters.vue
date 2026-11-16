<script setup lang="ts">
import { createForm, Link } from '@inertiajs/vue3';
import { LoaderCircle } from '@lucide/vue';
import { computed } from 'vue';
import SpendReportController from '@/actions/App/Http/Controllers/Admin/SpendReportController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

type ReportFilters = { start_date: string; end_date: string };

const props = defineProps<{
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

const activeRange = computed(() =>
    QUICK_RANGES.find((days) => {
        const range = rangeFor(days);

        return (
            range.start_date === props.filters.start_date &&
            range.end_date === props.filters.end_date
        );
    }),
);
</script>

<template>
    <div class="flex flex-wrap items-center gap-3">
        <nav class="flex border bg-white" aria-label="Quick ranges">
            <Link
                v-for="days in QUICK_RANGES"
                :key="days"
                :href="SpendReportController.index({ query: rangeFor(days) })"
                :only="['report', 'filters']"
                preserve-scroll
                :class="
                    cn(
                        'border-r px-3 py-1.5 text-sm transition-colors last:border-r-0',
                        activeRange === days
                            ? 'bg-ink text-white'
                            : 'text-muted-foreground hover:bg-muted hover:text-ink',
                    )
                "
            >
                {{ days }}d
            </Link>
        </nav>

        <FiltersForm
            v-bind="SpendReportController.index.form()"
            :options="{ preserveScroll: true, only: ['report', 'filters'] }"
            class="flex flex-wrap items-center gap-2"
            #default="{ errors, processing }"
        >
            <label class="sr-only" for="start_date">From</label>
            <input
                id="start_date"
                type="date"
                name="start_date"
                :value="filters.start_date"
                class="h-8 border bg-white px-2 text-sm tabular-nums outline-none focus:border-primary"
            />
            <span class="text-sm text-muted-foreground">to</span>
            <label class="sr-only" for="end_date">To</label>
            <input
                id="end_date"
                type="date"
                name="end_date"
                :value="filters.end_date"
                class="h-8 border bg-white px-2 text-sm tabular-nums outline-none focus:border-primary"
            />
            <Button
                type="submit"
                size="sm"
                variant="outline"
                :disabled="processing"
            >
                <LoaderCircle v-if="processing" class="animate-spin" />
                Apply
            </Button>
            <InputError
                class="basis-full"
                :message="errors.end_date ?? errors.start_date"
            />
        </FiltersForm>
    </div>
</template>
