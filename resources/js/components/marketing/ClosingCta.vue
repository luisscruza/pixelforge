<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from '@lucide/vue';
import CrossMarks from '@/components/marketing/CrossMarks.vue';
import { register } from '@/routes';
</script>

<template>
    <section class="relative overflow-hidden bg-primary bg-grid-on-brand">
        <CrossMarks />
        <div
            class="relative mx-auto flex max-w-7xl flex-col items-center gap-8 border-x border-white/15 px-4 py-24 text-center md:px-6"
        >
            <h2
                class="max-w-2xl text-4xl leading-[1.05] font-medium tracking-tight text-balance text-white md:text-5xl"
            >
                Your next room is
                <span class="text-mint">one prompt</span> away.
            </h2>
            <Link
                :href="register()"
                class="group inline-flex items-center gap-2 border border-white bg-white px-5 py-2.5 text-sm font-medium text-primary shadow-[4px_4px_0_0_rgb(36_49_54/0.35)] transition hover:translate-x-px hover:translate-y-px hover:shadow-[3px_3px_0_0_rgb(36_49_54/0.35)]"
            >
                Create a free account
                <ArrowRight
                    class="size-4 transition group-hover:translate-x-0.5"
                />
            </Link>
        </div>
    </section>
</template>
