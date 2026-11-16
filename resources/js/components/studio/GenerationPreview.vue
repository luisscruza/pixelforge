<script setup lang="ts">
import { ImageOff, TriangleAlert } from '@lucide/vue';
import { ref } from 'vue';
import RenderingCanvas from '@/components/studio/RenderingCanvas.vue';
import { ASPECT_CLASSES } from '@/lib/studio';
import { cn } from '@/lib/utils';
import type { Generation } from '@/types/generation';

defineProps<{
    generation: Generation;
}>();

defineEmits<{
    open: [];
}>();

const loaded = ref(false);
</script>

<template>
    <div
        :class="
            cn(
                'relative w-full overflow-hidden bg-muted',
                ASPECT_CLASSES[generation.size],
            )
        "
    >
        <RenderingCanvas v-if="generation.is_active" :generation="generation" />

        <button
            v-else-if="generation.image_url"
            type="button"
            class="group/image absolute inset-0 cursor-zoom-in focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-primary"
            :aria-label="`Open ${generation.prompt}`"
            @click="$emit('open')"
        >
            <div v-if="!loaded" class="absolute inset-0 skeleton-shimmer" />
            <img
                :src="generation.image_url"
                :alt="generation.prompt"
                loading="lazy"
                :class="
                    cn(
                        'size-full object-cover transition duration-700 ease-out group-hover/image:scale-[1.03]',
                        loaded ? 'blur-0 opacity-100' : 'opacity-0 blur-md',
                    )
                "
                @load="loaded = true"
            />
        </button>

        <div
            v-else-if="generation.status === 'failed'"
            class="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-destructive/5 p-6 text-center"
        >
            <TriangleAlert class="size-5 text-destructive" />
            <p class="max-w-56 text-xs text-muted-foreground">
                {{
                    generation.error_message ??
                    'This design could not be rendered.'
                }}
            </p>
        </div>

        <div
            v-else
            class="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-blueprint p-6 text-center"
        >
            <ImageOff class="size-5 text-muted-foreground" />
            <p class="text-xs text-muted-foreground">
                The render link expired after 24 hours.
            </p>
        </div>
    </div>
</template>
