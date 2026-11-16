<script setup lang="ts">
import BrandLogo from '@/components/brand/BrandLogo.vue';
</script>

<template>
    <footer class="border-t">
        <div
            class="mx-auto flex max-w-7xl flex-col items-start justify-between gap-4 border-x px-4 py-10 text-sm text-muted-foreground md:flex-row md:items-center md:px-6"
        >
            <BrandLogo class="h-6" />
            <p>Prices in US dollars, exact to the fraction of a cent.</p>
            <p>© {{ new Date().getFullYear() }} InteriorIA</p>
        </div>
    </footer>
</template>
