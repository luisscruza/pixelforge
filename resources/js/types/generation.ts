export type GenerationSize = '1024x1024' | '1536x1024' | '1024x1536';
export type GenerationQuality = 'low' | 'medium' | 'high';
export type GenerationStatus = 'queued' | 'processing' | 'succeeded' | 'failed';

export type Generation = {
    id: string;
    prompt: string;
    size: GenerationSize;
    quality: GenerationQuality;
    reference_images: string[];
    status: GenerationStatus;
    is_active: boolean;
    is_confirming: boolean;
    estimated_price: string;
    maximum_price: string;
    price: string | null;
    tokens: number | null;
    image_url: string | null;
    image_expired: boolean;
    error_message: string | null;
    created_at: string | null;
    completed_at: string | null;
    optimistic?: boolean;
};

export type ResourceCollection<T> = {
    data: T[];
};

export type GenerationStats = {
    total: number;
    succeeded: number;
    in_flight: number;
    spent: string;
};

export type Pricing = {
    rates: {
        input_text_tokens: string;
        input_image_tokens: string;
        output_tokens: string;
        reasoning_tokens: string;
    };
    tokens: {
        characters_per_text_token: number;
        text_token_variance: string;
        per_reference_image: number;
        output: Record<GenerationQuality, Record<GenerationSize, number>>;
        reasoning: Record<GenerationQuality, { min: number; max: number }>;
    };
};

export type GenerationForm = {
    prompt: string;
    size: GenerationSize;
    quality: GenerationQuality;
    reference_images: string[];
};

export type SpendReportUser = {
    user_id: number;
    name: string;
    email: string;
    generations: number;
    charged: string;
    provider_cost: string;
    profit: string;
};

export type SpendReportDay = {
    date: string;
    generations: number;
    charged: string;
    provider_cost: string;
    profit: string;
    pixelforge_cost: string | null;
    difference: string | null;
    users: SpendReportUser[];
};

export type SpendReport = {
    start_date: string;
    end_date: string;
    usage_available: boolean;
    unresolved_submissions: number;
    totals: {
        charged: string;
        provider_cost: string;
        profit: string;
        pixelforge_cost: string | null;
        difference: string | null;
    };
    days: SpendReportDay[];
    user_totals: SpendReportUser[];
};
