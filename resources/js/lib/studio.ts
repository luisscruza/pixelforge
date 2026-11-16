import type { Estimate } from '@/lib/money';
import type {
    Generation,
    GenerationForm,
    GenerationQuality,
    GenerationSize,
} from '@/types/generation';

export type Option<T extends string> = {
    value: T;
    label: string;
    hint: string;
};

export const SIZE_OPTIONS: Option<GenerationSize>[] = [
    { value: '1024x1024', label: 'Square', hint: 'Detail & corners' },
    { value: '1536x1024', label: 'Wide', hint: 'Whole room' },
    { value: '1024x1536', label: 'Tall', hint: 'Walls & stairs' },
];

export const QUALITY_OPTIONS: Option<GenerationQuality>[] = [
    { value: 'low', label: 'Sketch', hint: 'Fast concept' },
    { value: 'medium', label: 'Render', hint: 'Balanced' },
    { value: 'high', label: 'Showroom', hint: 'Finest detail' },
];

export const ROOM_PRESETS = [
    'Living room',
    'Bedroom',
    'Kitchen',
    'Home office',
    'Bathroom',
    'Dining room',
];

export const STYLE_PRESETS = [
    'Japandi',
    'Mid-century modern',
    'Scandinavian',
    'Industrial loft',
    'Coastal',
    'Art deco',
];

export const PROVIDER_SIMULATIONS = [
    { keyword: '[succeed]', label: 'Never fail' },
    { keyword: '[fail]', label: 'Fail after accepting' },
    { keyword: '[slow]', label: 'Slow render (60–90s)' },
    { keyword: '[duplicate]', label: 'Duplicate webhooks' },
    { keyword: '[overloaded]', label: 'Provider overloaded' },
    { keyword: '[blocked]', label: 'Policy rejection' },
] as const;

export const ASPECT_CLASSES: Record<GenerationSize, string> = {
    '1024x1024': 'aspect-square',
    '1536x1024': 'aspect-[3/2]',
    '1024x1536': 'aspect-[2/3]',
};

export function appendToPrompt(prompt: string, addition: string): string {
    const trimmed = prompt.trimEnd();

    if (trimmed === '') {
        return addition;
    }

    return /[.,;:]$/.test(trimmed) || addition.startsWith('[')
        ? `${trimmed} ${addition}`
        : `${trimmed}, ${addition}`;
}

export function optimisticGeneration(
    form: GenerationForm,
    estimate: Estimate,
): Generation {
    return {
        id: `optimistic-${Date.now()}`,
        prompt: form.prompt,
        size: form.size,
        quality: form.quality,
        reference_images: form.reference_images,
        status: 'queued',
        is_active: true,
        is_confirming: false,
        estimated_price: estimate.expected,
        maximum_price: estimate.maximum,
        price: null,
        tokens: null,
        image_url: null,
        image_expired: false,
        error_message: null,
        created_at: new Date().toISOString(),
        completed_at: null,
        optimistic: true,
    };
}
