<script setup lang="ts">
import AppTopbar from '@/components/shell/AppTopbar.vue';
import { Toaster } from '@/components/ui/sonner';
</script>

<template>
    <div class="flex min-h-svh flex-col bg-white">
        <AppTopbar />
        <main class="mx-auto flex w-full max-w-7xl flex-1 flex-col border-x">
            <slot />
        </main>
        <Toaster position="bottom-right" />
    </div>
</template>
