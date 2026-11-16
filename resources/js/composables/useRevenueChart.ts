import { computed } from 'vue';
import type { ComputedRef, Ref } from 'vue';
import { niceCeiling, toNumber } from '@/lib/report';
import type { SpendReportDay } from '@/types/generation';

export type ChartBar = {
    day: SpendReportDay;
    x: number;
    width: number;
    costY: number;
    costHeight: number;
    profitY: number;
    profitHeight: number;
};

export type ChartTick = { y: number; value: number };

const PADDING = { top: 12, right: 8, bottom: 26, left: 52 };
const SEGMENT_GAP = 2;
const TICK_COUNT = 4;

export function useRevenueChart(
    days: () => SpendReportDay[],
    width: Ref<number>,
    height: number,
): {
    bars: ComputedRef<ChartBar[]>;
    ticks: ComputedRef<ChartTick[]>;
    labelEvery: ComputedRef<number>;
    plot: {
        top: number;
        bottom: number;
        left: number;
        right: ComputedRef<number>;
    };
} {
    const plotBottom = height - PADDING.bottom;
    const plotHeight = plotBottom - PADDING.top;
    const right = computed(() =>
        Math.max(width.value - PADDING.right, PADDING.left + 1),
    );

    const maximum = computed(() =>
        niceCeiling(Math.max(0, ...days().map((day) => toNumber(day.charged)))),
    );

    const scale = (value: number): number =>
        (value / maximum.value) * plotHeight;

    const bars = computed(() => {
        const list = days();
        const band = (right.value - PADDING.left) / Math.max(list.length, 1);
        const barWidth = Math.max(2, Math.min(28, band * 0.62));

        return list.map((day, index) => {
            const costHeight = scale(toNumber(day.provider_cost));
            const profitHeight = Math.max(
                0,
                scale(toNumber(day.profit)) -
                    (costHeight > 0 ? SEGMENT_GAP : 0),
            );
            const costY = plotBottom - costHeight;

            return {
                day,
                x: PADDING.left + band * index + (band - barWidth) / 2,
                width: barWidth,
                costY,
                costHeight,
                profitY: costY - SEGMENT_GAP - profitHeight,
                profitHeight,
            };
        });
    });

    const ticks = computed(() =>
        Array.from({ length: TICK_COUNT + 1 }, (_, index) => {
            const value = (maximum.value / TICK_COUNT) * index;

            return { value, y: plotBottom - scale(value) };
        }),
    );

    const labelEvery = computed(() =>
        Math.max(1, Math.ceil(days().length / 8)),
    );

    return {
        bars,
        ticks,
        labelEvery,
        plot: {
            top: PADDING.top,
            bottom: plotBottom,
            left: PADDING.left,
            right,
        },
    };
}
