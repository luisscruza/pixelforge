<script setup lang="ts">
import { useFormContext } from '@inertiajs/vue3';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import PresetChips from '@/components/studio/design-form/PresetChips.vue';
import { appendToPrompt, ROOM_PRESETS, STYLE_PRESETS } from '@/lib/studio';
import { cn } from '@/lib/utils';
import type { GenerationForm } from '@/types/generation';

const MAX_LENGTH = 4000;

const prompt = defineModel<string>({ required: true });
const form = useFormContext<GenerationForm>();

const length = computed(() => Array.from(prompt.value).length);

function add(preset: string): void {
    prompt.value = appendToPrompt(prompt.value, preset.toLowerCase());
}
</script>

<template>
    <div class="space-y-2">
        <label for="prompt" class="sr-only">Describe the room</label>
        <div class="relative">
            <textarea
                id="prompt"
                v-model="prompt"
                name="prompt"
                rows="5"
                :maxlength="MAX_LENGTH"
                required
                placeholder="A sunlit Japandi living room with oak floors, a low linen sofa and a paper lantern…"
                :aria-invalid="form?.errors.prompt ? true : undefined"
                class="w-full resize-none rounded-md border bg-card px-3 py-2.5 pb-7 text-sm leading-relaxed shadow-xs transition outline-none placeholder:text-muted-foreground/70 focus:border-primary focus:ring-[3px] focus:ring-ring/25 aria-invalid:border-destructive"
            />
            <span
                :class="
                    cn(
                        'pointer-events-none absolute right-3 bottom-2.5 font-mono text-[10px] tabular-nums transition-colors',
                        length > MAX_LENGTH * 0.9
                            ? 'text-warning'
                            : 'text-muted-foreground',
                    )
                "
            >
                {{ length.toLocaleString() }} /
                {{ MAX_LENGTH.toLocaleString() }}
            </span>
        </div>
        <InputError :message="form?.errors.prompt" />
        <div class="space-y-1.5">
            <PresetChips label="Room" :presets="ROOM_PRESETS" @pick="add" />
            <PresetChips label="Style" :presets="STYLE_PRESETS" @pick="add" />
        </div>
    </div>
</template>
