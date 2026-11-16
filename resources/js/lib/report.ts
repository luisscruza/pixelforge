import { isZero, parseDecimal } from '@/lib/money';

export const CHART_COLORS = {
    providerCost: '#7A62C9',
    profit: '#12977A',
} as const;

export function toNumber(value: string | null | undefined): number {
    if (value === null || value === undefined) {
        return 0;
    }

    const decimal = parseDecimal(value);

    return Number(decimal.units) / 10 ** decimal.scale;
}

export function percentOf(part: string, whole: string): string {
    if (isZero(whole)) {
        return '—';
    }

    return `${((toNumber(part) / toNumber(whole)) * 100).toFixed(1)}%`;
}

export function niceCeiling(value: number): number {
    if (value <= 0) {
        return 1;
    }

    const magnitude = 10 ** Math.floor(Math.log10(value));
    const step =
        [1, 2, 2.5, 5, 10].find((multiple) => multiple * magnitude >= value) ??
        10;

    return step * magnitude;
}

export function compactUsd(value: number): string {
    if (value === 0) {
        return '$0';
    }

    if (value < 0.01) {
        return `$${value.toPrecision(2)}`;
    }

    return `$${value.toFixed(value < 1 ? 2 : value < 100 ? 1 : 0)}`;
}
