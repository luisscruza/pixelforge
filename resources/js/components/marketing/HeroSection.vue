<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Check } from '@lucide/vue';
import CrossMarks from '@/components/marketing/CrossMarks.vue';
import HeroPreview from '@/components/marketing/HeroPreview.vue';
import { register } from '@/routes';
</script>

<template>
    <section class="relative overflow-hidden bg-primary bg-grid-on-brand">
        <CrossMarks />
        <div
            class="relative mx-auto flex max-w-7xl flex-col items-center gap-10 border-x border-white/15 px-4 pt-20 pb-0 text-center md:px-6 md:pt-28"
        >
            <span
                class="inline-flex items-center gap-2 border border-white/30 bg-white/10 px-2.5 py-1 text-sm text-white"
            >
                <span
                    class="grid size-4 place-items-center bg-white text-primary"
                >
                    <Check class="size-3" />
                </span>
                An interior designer that shows the price first
            </span>

            <h1
                class="max-w-4xl text-5xl leading-[1.04] font-medium tracking-tight text-balance text-white md:text-7xl"
            >
                Design any room in
                <span class="text-mint">one sentence</span>
            </h1>

            <p
                class="max-w-xl text-base leading-relaxed font-light text-white/85 md:text-lg"
            >
                Describe the space you imagine, pick a format and a finish, and
                InteriorIA drafts it in seconds. You see the cost before you
                spend a cent, and you only pay for renders that succeed.
            </p>

            <div class="flex flex-col items-center gap-3">
                <Link
                    :href="register()"
                    class="group inline-flex items-center gap-2 border border-white bg-white px-5 py-2.5 text-sm font-medium text-primary shadow-[4px_4px_0_0_rgb(36_49_54/0.35)] transition hover:translate-x-px hover:translate-y-px hover:shadow-[3px_3px_0_0_rgb(36_49_54/0.35)]"
                >
                    Start designing
                    <ArrowRight
                        class="size-4 transition group-hover:translate-x-0.5"
                    />
                </Link>
                <p class="text-sm text-white/70">
                    Pay as you go · Most rooms cost a few cents
                </p>
            </div>

            <div class="w-full max-w-5xl translate-y-px">
                <HeroPreview />
            </div>
        </div>
    </section>
</template>
