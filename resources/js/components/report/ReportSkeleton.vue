<template>
    <div class="space-y-6" aria-label="Loading the report">
        <div class="grid border-t border-l sm:grid-cols-2 xl:grid-cols-4">
            <div
                v-for="tile in 4"
                :key="tile"
                class="space-y-3 border-r border-b bg-white px-5 py-4"
            >
                <div class="h-3.5 w-20 skeleton-shimmer" />
                <div class="h-7 w-28 skeleton-shimmer" />
                <div class="h-3 w-24 skeleton-shimmer" />
            </div>
        </div>
        <div class="border bg-white p-5">
            <div class="flex h-[240px] items-end gap-2">
                <div
                    v-for="bar in 14"
                    :key="bar"
                    class="flex-1 skeleton-shimmer"
                    :style="{ height: `${20 + ((bar * 37) % 70)}%` }"
                />
            </div>
        </div>
        <div class="border bg-white">
            <div class="h-10 border-b bg-muted/60" />
            <div
                v-for="row in 6"
                :key="row"
                class="flex items-center gap-8 border-b px-5 py-3 last:border-b-0"
            >
                <div class="h-3.5 w-28 skeleton-shimmer" />
                <div class="ml-auto h-3.5 w-12 skeleton-shimmer" />
                <div class="h-3.5 w-20 skeleton-shimmer" />
                <div class="h-3.5 w-20 skeleton-shimmer" />
            </div>
        </div>
    </div>
</template>
