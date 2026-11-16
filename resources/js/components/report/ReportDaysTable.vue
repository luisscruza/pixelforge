<script setup lang="ts">
import ReportDayRow from '@/components/report/ReportDayRow.vue';
import type { SpendReportDay } from '@/types/generation';

defineProps<{
    days: SpendReportDay[];
}>();

const HEADINGS = [
    'Generations',
    'Charged',
    'Provider cost',
    'Profit',
    'Pixelforge',
    '',
];
</script>

<template>
    <div class="overflow-x-auto rounded-lg border bg-card">
        <table class="w-full min-w-[720px] text-sm">
            <thead
                class="bg-muted/60 font-mono text-[10px] tracking-wider text-muted-foreground uppercase"
            >
                <tr>
                    <th class="py-2.5 pl-4 text-left font-medium">Day (UTC)</th>
                    <th
                        v-for="heading in HEADINGS"
                        :key="heading"
                        class="py-2.5 text-right font-medium last:pr-4"
                    >
                        {{ heading }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <ReportDayRow
                    v-for="day in [...days].reverse()"
                    :key="day.date"
                    :day="day"
                />
            </tbody>
        </table>
    </div>
</template>
