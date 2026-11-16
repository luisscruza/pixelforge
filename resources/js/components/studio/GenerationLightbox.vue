<script setup lang="ts">
import { ExternalLink } from '@lucide/vue';
import MoneyValue from '@/components/studio/MoneyValue.vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogTitle,
} from '@/components/ui/dialog';
import type { Generation } from '@/types/generation';

defineProps<{
    generation: Generation | null;
}>();

const open = defineModel<boolean>('open', { required: true });
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent
            v-if="generation"
            class="max-w-4xl gap-0 overflow-hidden p-0 sm:max-w-4xl"
        >
            <img
                v-if="generation.image_url"
                :src="generation.image_url"
                :alt="generation.prompt"
                class="max-h-[70vh] w-full bg-muted object-contain"
            />
            <div class="grid gap-4 p-5 sm:grid-cols-[1fr_auto]">
                <div class="min-w-0 space-y-1">
                    <DialogTitle class="text-base leading-snug">{{
                        generation.prompt
                    }}</DialogTitle>
                    <DialogDescription class="font-mono text-xs">
                        {{ generation.size }} · {{ generation.quality }} ·
                        {{ generation.tokens?.toLocaleString() ?? '—' }} tokens
                    </DialogDescription>
                </div>
                <div class="flex items-start gap-4 text-right text-xs">
                    <div>
                        <p class="text-muted-foreground">Estimated</p>
                        <MoneyValue :value="generation.estimated_price" />
                    </div>
                    <div>
                        <p class="text-muted-foreground">Charged</p>
                        <MoneyValue
                            :value="generation.price"
                            class="font-semibold"
                        />
                    </div>
                    <a
                        v-if="generation.image_url"
                        :href="generation.image_url"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex items-center gap-1 self-center rounded-md border px-2 py-1.5 transition hover:border-primary hover:text-primary"
                    >
                        Full size <ExternalLink class="size-3" />
                    </a>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
