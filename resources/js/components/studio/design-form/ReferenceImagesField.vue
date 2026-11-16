<script setup lang="ts">
import { useFormContext } from '@inertiajs/vue3';
import { ImagePlus, X } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import type { GenerationForm } from '@/types/generation';

const props = defineProps<{
    max: number;
    tokensPerImage: number;
}>();

const urls = defineModel<string[]>({ required: true });
const form = useFormContext<GenerationForm>();

function add(): void {
    if (urls.value.length < props.max) {
        urls.value = [...urls.value, ''];
    }
}

function remove(index: number): void {
    urls.value = urls.value.filter((_, position) => position !== index);
}

function update(index: number, value: string): void {
    urls.value = urls.value.map((url, position) =>
        position === index ? value : url,
    );
}

function errorFor(index: number): string | undefined {
    return (form?.errors as Record<string, string | undefined> | undefined)?.[
        `reference_images.${index}`
    ];
}
</script>

<template>
    <div class="space-y-2">
        <div class="flex items-baseline justify-between">
            <span class="text-xs text-muted-foreground"
                >A mood board helps match a style</span
            >
            <span class="font-mono text-[10px] text-muted-foreground"
                >{{ tokensPerImage }} tokens each · up to {{ max }}</span
            >
        </div>

        <TransitionGroup
            tag="div"
            class="space-y-2"
            enter-from-class="opacity-0 -translate-y-1"
            leave-to-class="opacity-0 translate-x-2"
            enter-active-class="transition duration-200"
            leave-active-class="transition duration-150 absolute"
        >
            <div v-for="(url, index) in urls" :key="index" class="space-y-1">
                <div class="flex gap-2">
                    <input
                        :value="url"
                        type="url"
                        inputmode="url"
                        :name="url.trim() ? 'reference_images[]' : undefined"
                        placeholder="https://…/moodboard.jpg"
                        class="h-9 min-w-0 flex-1 rounded-md border bg-card px-3 font-mono text-xs shadow-xs transition outline-none focus:border-primary focus:ring-[3px] focus:ring-ring/25"
                        @input="
                            update(
                                index,
                                ($event.target as HTMLInputElement).value,
                            )
                        "
                    />
                    <button
                        type="button"
                        class="grid size-9 shrink-0 place-items-center rounded-md border text-muted-foreground transition hover:border-destructive/50 hover:text-destructive active:scale-95"
                        :aria-label="`Remove inspiration photo ${index + 1}`"
                        @click="remove(index)"
                    >
                        <X class="size-3.5" />
                    </button>
                </div>
                <InputError :message="errorFor(index)" />
            </div>
        </TransitionGroup>

        <button
            v-if="urls.length < max"
            type="button"
            class="flex w-full items-center justify-center gap-1.5 rounded-md border border-dashed py-2 text-xs text-muted-foreground transition hover:border-primary/60 hover:bg-accent/40 hover:text-primary"
            @click="add"
        >
            <ImagePlus class="size-3.5" /> Add an inspiration photo URL
        </button>
        <InputError :message="form?.errors.reference_images" />
    </div>
</template>
