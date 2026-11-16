<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { CircleCheck } from '@lucide/vue';
import BrandLogo from '@/components/brand/BrandLogo.vue';
import CrossMarks from '@/components/marketing/CrossMarks.vue';
import RoomIllustration from '@/components/marketing/RoomIllustration.vue';
import { Toaster } from '@/components/ui/sonner';

defineProps<{
    title?: string;
    description?: string;
}>();

const HIGHLIGHTS = [
    'See the price before you design',
    'Only pay for renders that succeed',
    'Credits in small packs, no subscription',
];
</script>

<template>
    <div class="grid min-h-svh bg-white lg:grid-cols-2">
        <div class="flex flex-col px-6 py-8 md:px-12">
            <Link href="/" aria-label="InteriorIA home" class="w-fit">
                <BrandLogo class="h-8" />
            </Link>

            <div class="flex flex-1 items-center">
                <div class="mx-auto w-full max-w-sm space-y-8 py-12">
                    <div class="space-y-2">
                        <h1
                            class="text-3xl font-medium tracking-tight text-ink"
                        >
                            {{ title }}
                        </h1>
                        <p class="font-light text-muted-foreground">
                            {{ description }}
                        </p>
                    </div>
                    <slot />
                </div>
            </div>

            <p class="text-xs text-muted-foreground">
                © {{ new Date().getFullYear() }} InteriorIA
            </p>
        </div>

        <aside
            class="relative hidden overflow-hidden bg-primary bg-grid-on-brand lg:flex lg:flex-col lg:justify-center lg:px-16"
            aria-label="Why InteriorIA"
        >
            <CrossMarks />
            <div class="relative max-w-lg space-y-10">
                <h2
                    class="text-4xl leading-[1.05] font-medium tracking-tight text-white"
                >
                    Design any room in
                    <span class="text-mint">one sentence.</span>
                </h2>
                <div
                    class="overflow-hidden border border-white/25 bg-white shadow-2xl shadow-ink/30"
                >
                    <div class="aspect-[3/2]">
                        <RoomIllustration />
                    </div>
                    <p class="border-t px-4 py-3 text-sm text-ink">
                        "A sunlit Japandi living room with a low linen sofa"
                        <span class="float-right font-mono text-primary">
                            $0.0706
                        </span>
                    </p>
                </div>
                <ul class="space-y-3">
                    <li
                        v-for="highlight in HIGHLIGHTS"
                        :key="highlight"
                        class="flex items-center gap-2.5 text-white/90"
                    >
                        <CircleCheck class="size-4 text-mint" />
                        {{ highlight }}
                    </li>
                </ul>
            </div>
        </aside>
        <Toaster position="bottom-right" />
    </div>
</template>
