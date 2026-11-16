<script setup lang="ts">
import RoomIllustration from '@/components/marketing/RoomIllustration.vue';
import StatusPill from '@/components/studio/StatusPill.vue';

const RECEIPT = [
    { label: 'Estimate shown', value: '$0.0722' },
    { label: 'Held while rendering', value: '$0.0863' },
    { label: 'Charged', value: '$0.0706' },
];
</script>

<template>
    <div
        class="overflow-hidden rounded-md border border-white/20 bg-white shadow-2xl shadow-ink/30"
    >
        <div
            class="flex items-center gap-1.5 border-b bg-muted/60 px-3 py-2"
            aria-hidden="true"
        >
            <span class="size-2.5 rounded-full bg-[#ED6A5E]" />
            <span class="size-2.5 rounded-full bg-[#F4BF4F]" />
            <span class="size-2.5 rounded-full bg-[#61C554]" />
            <span class="mx-auto font-mono text-[11px] text-muted-foreground">
                interioria · studio
            </span>
        </div>

        <div class="grid md:grid-cols-[1.6fr_1fr]">
            <div
                class="relative aspect-[3/2] border-b md:border-r md:border-b-0"
            >
                <RoomIllustration />
                <StatusPill status="succeeded" class="absolute top-3 left-3" />
            </div>

            <div class="flex flex-col justify-between gap-6 p-5 text-left">
                <div class="space-y-2">
                    <p
                        class="font-mono text-[10px] tracking-wider text-muted-foreground uppercase"
                    >
                        Prompt
                    </p>
                    <p class="text-sm leading-snug text-ink">
                        A sunlit Japandi living room, oak floor, low linen sofa,
                        paper lantern and a fiddle-leaf fig.
                    </p>
                </div>
                <dl class="divide-y border-y text-sm">
                    <div
                        v-for="line in RECEIPT"
                        :key="line.label"
                        class="flex justify-between py-2"
                    >
                        <dt class="text-muted-foreground">{{ line.label }}</dt>
                        <dd class="font-mono tabular-nums">{{ line.value }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</template>
