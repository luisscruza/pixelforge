<script setup lang="ts">
import { cn } from '@/lib/utils';

withDefaults(
    defineProps<{
        eyebrow: string;
        title: string;
        centered?: boolean;
        inverted?: boolean;
    }>(),
    { centered: false, inverted: false },
);
</script>

<template>
    <div :class="cn('max-w-2xl space-y-4', centered && 'mx-auto text-center')">
        <p
            :class="
                cn(
                    'text-sm font-medium',
                    inverted ? 'text-mint' : 'text-primary',
                )
            "
        >
            {{ eyebrow }}
        </p>
        <h2
            :class="
                cn(
                    'text-4xl leading-[1.05] font-medium tracking-tight text-balance md:text-5xl',
                    inverted ? 'text-white' : 'text-ink',
                )
            "
        >
            {{ title }}
        </h2>
        <p
            v-if="$slots.default"
            :class="
                cn(
                    'text-base leading-relaxed font-light text-pretty md:text-lg',
                    inverted ? 'text-white/80' : 'text-muted-foreground',
                )
            "
        >
            <slot />
        </p>
    </div>
</template>
