<script setup lang="ts">
import { useElementSize } from '@vueuse/core';
import { computed, ref, useTemplateRef } from 'vue';
import { useRevenueChart } from '@/composables/useRevenueChart';
import type { ChartBar } from '@/composables/useRevenueChart';
import { formatUsd } from '@/lib/money';
import { CHART_COLORS, compactUsd } from '@/lib/report';
import { formatDay } from '@/lib/time';
import type { SpendReportDay } from '@/types/generation';

const HEIGHT = 240;

const props = defineProps<{
    days: SpendReportDay[];
}>();

const container = useTemplateRef<HTMLElement>('container');
const { width } = useElementSize(container);
const { bars, ticks, labelEvery, plot } = useRevenueChart(
    () => props.days,
    width,
    HEIGHT,
);

const hovered = ref<ChartBar | null>(null);
const tooltipLeft = computed(() =>
    hovered.value
        ? Math.min(
              Math.max(hovered.value.x + hovered.value.width / 2, 90),
              width.value - 90,
          )
        : 0,
);

function profitPath(bar: ChartBar): string {
    const radius = Math.min(4, bar.width / 2, bar.profitHeight);
    const { x, profitY: y, width: w, profitHeight: h } = bar;

    return `M${x},${y + h} V${y + radius} Q${x},${y} ${x + radius},${y} H${x + w - radius} Q${x + w},${y} ${x + w},${y + radius} V${y + h} Z`;
}
</script>

<template>
    <div class="space-y-3 p-5">
        <div class="flex items-center gap-4 text-xs text-muted-foreground">
            <span class="inline-flex items-center gap-1.5">
                <span
                    class="size-2.5"
                    :style="{ background: CHART_COLORS.profit }"
                />
                Profit
            </span>
            <span class="inline-flex items-center gap-1.5">
                <span
                    class="size-2.5"
                    :style="{ background: CHART_COLORS.providerCost }"
                />
                Provider cost
            </span>
            <span class="ml-auto">Bar height is revenue</span>
        </div>

        <div
            ref="container"
            class="relative w-full"
            :style="{ height: `${HEIGHT}px` }"
        >
            <svg
                v-if="width > 0"
                :width="width"
                :height="HEIGHT"
                role="img"
                aria-label="Daily revenue split into provider cost and profit"
                @mouseleave="hovered = null"
            >
                <g class="text-muted-foreground">
                    <g v-for="tick in ticks" :key="tick.value">
                        <line
                            :x1="plot.left"
                            :x2="plot.right.value"
                            :y1="tick.y"
                            :y2="tick.y"
                            stroke="currentColor"
                            stroke-opacity="0.15"
                        />
                        <text
                            :x="plot.left - 8"
                            :y="tick.y"
                            text-anchor="end"
                            dominant-baseline="middle"
                            class="fill-current text-[11px] tabular-nums"
                        >
                            {{ compactUsd(tick.value) }}
                        </text>
                    </g>
                </g>

                <g v-for="(bar, index) in bars" :key="bar.day.date">
                    <rect
                        v-if="bar.costHeight > 0"
                        :x="bar.x"
                        :y="bar.costY"
                        :width="bar.width"
                        :height="bar.costHeight"
                        :fill="CHART_COLORS.providerCost"
                        :opacity="hovered && hovered !== bar ? 0.45 : 1"
                    />
                    <path
                        v-if="bar.profitHeight > 0"
                        :d="profitPath(bar)"
                        :fill="CHART_COLORS.profit"
                        :opacity="hovered && hovered !== bar ? 0.45 : 1"
                    />
                    <text
                        v-if="index % labelEvery === 0"
                        :x="bar.x + bar.width / 2"
                        :y="plot.bottom + 16"
                        text-anchor="middle"
                        class="fill-muted-foreground text-[11px]"
                    >
                        {{ formatDay(bar.day.date).replace(/^\w+, /, '') }}
                    </text>
                    <rect
                        :x="bar.x - 4"
                        :y="plot.top"
                        :width="bar.width + 8"
                        :height="plot.bottom - plot.top"
                        fill="transparent"
                        @mouseenter="hovered = bar"
                    />
                </g>
                <line
                    :x1="plot.left"
                    :x2="plot.right.value"
                    :y1="plot.bottom"
                    :y2="plot.bottom"
                    class="stroke-border"
                />
            </svg>

            <div
                v-if="hovered"
                class="pointer-events-none absolute top-0 z-10 w-44 -translate-x-1/2 border bg-white p-3 text-xs shadow-lg"
                :style="{ left: `${tooltipLeft}px` }"
            >
                <p class="mb-2 font-medium text-ink">
                    {{ formatDay(hovered.day.date) }}
                </p>
                <dl class="space-y-1 tabular-nums">
                    <div class="flex justify-between gap-3">
                        <dt class="text-muted-foreground">Revenue</dt>
                        <dd class="text-ink">
                            {{ formatUsd(hovered.day.charged) }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-muted-foreground">Provider cost</dt>
                        <dd class="text-ink">
                            {{ formatUsd(hovered.day.provider_cost) }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-muted-foreground">Profit</dt>
                        <dd class="text-ink">
                            {{ formatUsd(hovered.day.profit) }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-3 border-t pt-1">
                        <dt class="text-muted-foreground">Renders</dt>
                        <dd class="text-ink">{{ hovered.day.generations }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</template>
