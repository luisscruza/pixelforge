<script setup lang="ts" generic="T extends string">
import type { Option } from '@/lib/studio';
import { cn } from '@/lib/utils';

defineProps<{
    name: string;
    legend: string;
    options: Option<T>[];
}>();

const model = defineModel<T>({ required: true });
</script>

<template>
    <fieldset class="space-y-2">
        <legend class="mb-2 text-sm font-medium">{{ legend }}</legend>
        <div class="grid grid-cols-3 gap-2">
            <label
                v-for="option in options"
                :key="option.value"
                :class="
                    cn(
                        'relative cursor-pointer rounded-md border bg-card px-3 py-2 transition duration-200 select-none',
                        'hover:border-primary/50 has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-ring/40',
                        model === option.value
                            ? 'border-primary bg-accent shadow-xs'
                            : 'border-border',
                    )
                "
            >
                <input
                    v-model="model"
                    type="radio"
                    :name="name"
                    :value="option.value"
                    class="sr-only"
                />
                <span class="block text-sm font-medium">{{
                    option.label
                }}</span>
                <span class="block text-[11px] text-muted-foreground">{{
                    option.hint
                }}</span>
                <span
                    :class="
                        cn(
                            'absolute top-2 right-2 size-1.5 rounded-full bg-primary transition duration-200',
                            model === option.value
                                ? 'scale-100 opacity-100'
                                : 'scale-0 opacity-0',
                        )
                    "
                />
            </label>
        </div>
    </fieldset>
</template>
