<script setup lang="ts">
import SectionIntro from '@/components/marketing/SectionIntro.vue';
import { ROOM_PRESETS, STYLE_PRESETS } from '@/lib/studio';
</script>

<template>
    <section id="styles" class="mx-auto max-w-7xl scroll-mt-16 border-x">
        <div class="px-4 py-20 text-center md:px-12 md:py-28">
            <SectionIntro
                eyebrow="Any room, any style"
                title="Start from a preset or from scratch."
                centered
            >
                Presets are just a head start. Mix them, or write something
                nobody has asked for before.
            </SectionIntro>

            <div
                class="mx-auto mt-12 grid max-w-4xl border-t border-l sm:grid-cols-2"
            >
                <div class="border-r border-b p-6 text-left">
                    <p class="mb-4 text-sm font-medium text-primary">Rooms</p>
                    <div class="flex flex-wrap gap-2">
                        <span
                            v-for="room in ROOM_PRESETS"
                            :key="room"
                            class="border px-3 py-1 text-sm text-ink transition hover:border-primary hover:text-primary"
                        >
                            {{ room }}
                        </span>
                    </div>
                </div>
                <div class="border-r border-b p-6 text-left">
                    <p class="mb-4 text-sm font-medium text-primary">Styles</p>
                    <div class="flex flex-wrap gap-2">
                        <span
                            v-for="style in STYLE_PRESETS"
                            :key="style"
                            class="border px-3 py-1 text-sm text-ink transition hover:border-primary hover:text-primary"
                        >
                            {{ style }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
