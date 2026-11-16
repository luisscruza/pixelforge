<script setup lang="ts">
import { ArrowLeft } from '@lucide/vue';
import RoomIllustration from '@/components/marketing/RoomIllustration.vue';

const HINTS = [
    'Name the room and a style, like “a Japandi bedroom”',
    'Mention materials and light: oak, linen, morning sun',
    'Check the estimate, then press Design this room',
];
</script>

<template>
    <div class="overflow-hidden border bg-white">
        <div class="aspect-[3/1] border-b opacity-90">
            <RoomIllustration cover />
        </div>
        <div class="space-y-5 p-6 md:p-8">
            <div class="space-y-2">
                <h3 class="text-2xl font-medium tracking-tight text-ink">
                    Your first room is one prompt away
                </h3>
                <p class="max-w-md leading-relaxed text-muted-foreground">
                    Describe a space in the form, pick a format and a finish,
                    and your design will appear here. You'll see the price
                    before you spend anything.
                </p>
            </div>
            <ol class="space-y-2.5">
                <li
                    v-for="(hint, index) in HINTS"
                    :key="hint"
                    class="flex items-start gap-3 text-sm text-ink"
                >
                    <span
                        class="grid size-5 shrink-0 place-items-center bg-accent text-xs font-medium text-accent-foreground"
                    >
                        {{ index + 1 }}
                    </span>
                    {{ hint }}
                </li>
            </ol>
            <p
                class="hidden items-center gap-1.5 text-sm font-medium text-primary lg:flex"
            >
                <ArrowLeft class="size-4" /> Start with the form on the left
            </p>
        </div>
    </div>
</template>
