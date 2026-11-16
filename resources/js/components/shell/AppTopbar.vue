<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import BrandLogo from '@/components/brand/BrandLogo.vue';
import CreditsBadge from '@/components/credits/CreditsBadge.vue';
import UserMenu from '@/components/shell/UserMenu.vue';
import { useMainNavigation } from '@/composables/useMainNavigation';
import { cn } from '@/lib/utils';
import { index as studio } from '@/routes/generations';

const navigation = useMainNavigation();
</script>

<template>
    <header
        class="sticky top-0 z-40 border-b bg-white/90 backdrop-blur supports-backdrop-filter:bg-white/75"
    >
        <div
            class="mx-auto flex h-16 max-w-7xl items-center gap-6 border-x px-4 md:px-6"
        >
            <Link
                :href="studio()"
                component="generations/Index"
                class="shrink-0"
                aria-label="InteriorIA studio"
            >
                <BrandLogo class="h-7" />
            </Link>

            <nav
                v-if="navigation.length > 1"
                class="hidden items-center gap-1 sm:flex"
                aria-label="Main"
            >
                <Link
                    v-for="item in navigation"
                    :key="item.title"
                    :href="item.href"
                    :component="item.component"
                    prefetch
                    :class="
                        cn(
                            'rounded px-3 py-1.5 text-sm transition-colors',
                            item.active
                                ? 'bg-accent font-medium text-accent-foreground'
                                : 'text-muted-foreground hover:text-foreground',
                        )
                    "
                >
                    {{ item.title }}
                </Link>
            </nav>

            <div class="ml-auto flex items-center gap-3">
                <CreditsBadge />
                <UserMenu />
            </div>
        </div>
    </header>
</template>
