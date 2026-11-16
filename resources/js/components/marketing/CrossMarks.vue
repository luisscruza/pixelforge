<script setup lang="ts">
const MARKS = [
    { top: '18%', left: '6%' },
    { top: '62%', left: '3%' },
    { top: '30%', left: '92%' },
    { top: '74%', left: '88%' },
];
</script>

<template>
    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
        <svg
            v-for="(mark, index) in MARKS"
            :key="index"
            class="absolute size-4 -translate-1/2 text-white/40"
            :style="mark"
            viewBox="0 0 16 16"
            fill="none"
            stroke="currentColor"
        >
            <path d="M8 0v16M0 8h16" />
        </svg>
        <div
            class="absolute top-[22%] left-[78%] hidden grid-cols-3 gap-1.5 md:grid"
        >
            <span
                v-for="square in 9"
                :key="square"
                :class="[
                    'size-2.5',
                    square % 3 === 0 ? 'bg-mint/70' : 'bg-white/15',
                ]"
            />
        </div>
    </div>
</template>
