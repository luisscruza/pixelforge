<template>
    <div
        class="corner-marks flex flex-col items-center justify-center gap-4 rounded-lg border border-dashed bg-blueprint px-6 py-16 text-center"
    >
        <svg
            viewBox="0 0 160 100"
            class="w-40 text-primary"
            fill="none"
            stroke="currentColor"
            stroke-width="1.2"
            aria-hidden="true"
        >
            <path d="M10 90 H150" />
            <path d="M20 90 V30 L80 12 L140 30 V90" stroke-dasharray="3 3" />
            <rect x="34" y="56" width="34" height="20" rx="3" />
            <path d="M38 76 V82 M64 76 V82" />
            <circle cx="112" cy="50" r="9" stroke-dasharray="2 2" />
            <path d="M112 59 V90 M102 90 H122" />
        </svg>
        <div class="space-y-1">
            <h3 class="font-semibold">Your first room is one prompt away</h3>
            <p class="max-w-sm text-sm text-muted-foreground">
                Describe a space, pick a format, and InteriorIA will draft it.
                You'll see the estimate before you spend anything.
            </p>
        </div>
    </div>
</template>
