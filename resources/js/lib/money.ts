import type { GenerationForm, Pricing } from '@/types/generation';

const RATE_SCALE = 2;
const TOKENS_PER_RATE_UNIT_DIGITS = 6;

export type Decimal = { units: bigint; scale: number };

export function parseDecimal(value: string): Decimal {
    const trimmed = value.trim();
    const negative = trimmed.startsWith('-');
    const [whole, fraction = ''] = trimmed.replace(/^[-+]/, '').split('.');
    const units = BigInt((whole || '0') + fraction);

    return { units: negative ? -units : units, scale: fraction.length };
}

function rescale(decimal: Decimal, scale: number): bigint {
    return scale >= decimal.scale
        ? decimal.units * 10n ** BigInt(scale - decimal.scale)
        : decimal.units / 10n ** BigInt(decimal.scale - scale);
}

export function toDecimalString({ units, scale }: Decimal): string {
    const negative = units < 0n;
    const digits = (negative ? -units : units)
        .toString()
        .padStart(scale + 1, '0');
    const whole = digits.slice(0, digits.length - scale);
    const fraction = digits.slice(digits.length - scale);

    return (negative ? '-' : '') + whole + (scale > 0 ? '.' + fraction : '');
}

export function compareDecimals(a: string, b: string): number {
    const left = parseDecimal(a);
    const right = parseDecimal(b);
    const scale = Math.max(left.scale, right.scale);
    const difference = rescale(left, scale) - rescale(right, scale);

    if (difference === 0n) {
        return 0;
    }

    return difference > 0n ? 1 : -1;
}

export function addDecimals(a: string, b: string): string {
    const left = parseDecimal(a);
    const right = parseDecimal(b);
    const scale = Math.max(left.scale, right.scale);

    return toDecimalString({
        units: rescale(left, scale) + rescale(right, scale),
        scale,
    });
}

export function negateDecimal(value: string): string {
    const decimal = parseDecimal(value);

    return toDecimalString({ units: -decimal.units, scale: decimal.scale });
}

export function isZero(value: string | null | undefined): boolean {
    return (
        value !== null &&
        value !== undefined &&
        parseDecimal(value).units === 0n
    );
}

export function formatUsd(
    value: string | null | undefined,
    decimals = 4,
): string {
    if (value === null || value === undefined) {
        return '—';
    }

    const decimal = parseDecimal(value);
    const absolute = decimal.units < 0n ? -decimal.units : decimal.units;
    let rounded: bigint;

    if (decimal.scale > decimals) {
        const factor = 10n ** BigInt(decimal.scale - decimals);
        rounded = (absolute + factor / 2n) / factor;
    } else {
        rounded = absolute * 10n ** BigInt(decimals - decimal.scale);
    }

    const sign = decimal.units < 0n && rounded !== 0n ? '−' : '';

    return sign + '$' + toDecimalString({ units: rounded, scale: decimals });
}

export type Estimate = {
    expected: string;
    maximum: string;
    tokens: {
        text: number;
        maxText: number;
        images: number;
        output: number;
        reasoning: { min: number; max: number };
    };
};

type TokenCounts = {
    text: number;
    images: number;
    output: number;
    reasoning: number;
};

function costOf(tokens: TokenCounts, pricing: Pricing): Decimal {
    const rate = (value: string): bigint =>
        rescale(parseDecimal(value), RATE_SCALE);
    const perRateUnit =
        BigInt(tokens.text) * rate(pricing.rates.input_text_tokens) +
        BigInt(tokens.images) * rate(pricing.rates.input_image_tokens) +
        BigInt(tokens.output) * rate(pricing.rates.output_tokens) +
        BigInt(tokens.reasoning) * rate(pricing.rates.reasoning_tokens);

    return {
        units: perRateUnit,
        scale: RATE_SCALE + TOKENS_PER_RATE_UNIT_DIGITS,
    };
}

function withMarkup(cost: Decimal, markup: string): string {
    const factor = parseDecimal(markup);

    return toDecimalString({
        units: cost.units * factor.units,
        scale: cost.scale + factor.scale,
    });
}

export function estimateCost(
    form: Pick<GenerationForm, 'prompt' | 'size' | 'quality'> & {
        referenceImages: number;
    },
    pricing: Pricing,
    markup: string,
): Estimate {
    const characters = Array.from(form.prompt).length;
    const text = Math.ceil(
        characters / pricing.tokens.characters_per_text_token,
    );
    const variance = parseDecimal(pricing.tokens.text_token_variance);
    const varianceBase = 10n ** BigInt(variance.scale);
    const maxText = Number(
        (BigInt(text) * (varianceBase + variance.units) + varianceBase - 1n) /
            varianceBase,
    );
    const images = form.referenceImages * pricing.tokens.per_reference_image;
    const output = pricing.tokens.output[form.quality][form.size];
    const reasoning = pricing.tokens.reasoning[form.quality];

    return {
        expected: withMarkup(
            costOf(
                {
                    text,
                    images,
                    output,
                    reasoning: Math.floor((reasoning.min + reasoning.max) / 2),
                },
                pricing,
            ),
            markup,
        ),
        maximum: withMarkup(
            costOf(
                { text: maxText, images, output, reasoning: reasoning.max },
                pricing,
            ),
            markup,
        ),
        tokens: { text, maxText, images, output, reasoning },
    };
}
