<template>
    <div class="space-y-4" aria-label="Loading the report">
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <div
                v-for="tile in 4"
                :key="tile"
                class="h-[76px] skeleton-shimmer rounded-lg"
            />
        </div>
        <div class="overflow-hidden rounded-lg border">
            <div class="h-9 bg-muted/60" />
            <div
                v-for="row in 7"
                :key="row"
                class="flex items-center gap-6 border-t px-4 py-3"
            >
                <div class="h-3 w-24 skeleton-shimmer rounded" />
                <div class="ml-auto h-3 w-10 skeleton-shimmer rounded" />
                <div class="h-3 w-20 skeleton-shimmer rounded" />
                <div class="h-3 w-20 skeleton-shimmer rounded" />
                <div class="h-3 w-20 skeleton-shimmer rounded" />
            </div>
        </div>
    </div>
</template>
