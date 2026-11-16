<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ArrowRight } from '@lucide/vue';
import { computed } from 'vue';
import BrandLogo from '@/components/brand/BrandLogo.vue';
import { Button } from '@/components/ui/button';
import { login, register } from '@/routes';
import { index as studio } from '@/routes/generations';

const page = usePage();
const signedIn = computed(() => Boolean(page.props.auth.user));

const LINKS = [
    { label: 'How it works', href: '#how-it-works' },
    { label: 'Pricing', href: '#pricing' },
    { label: 'Styles', href: '#styles' },
];
</script>

<template>
    <header
        class="sticky top-0 z-40 border-b bg-white/90 backdrop-blur supports-backdrop-filter:bg-white/75"
    >
        <div
            class="mx-auto flex h-16 max-w-7xl items-center gap-8 border-x px-4 md:px-6"
        >
            <Link href="/" aria-label="InteriorIA home" class="shrink-0">
                <BrandLogo class="h-7" />
            </Link>

            <nav
                class="hidden items-center gap-6 md:flex"
                aria-label="Sections"
            >
                <a
                    v-for="link in LINKS"
                    :key="link.href"
                    :href="link.href"
                    class="text-sm text-muted-foreground transition-colors hover:text-ink"
                >
                    {{ link.label }}
                </a>
            </nav>

            <div class="ml-auto flex items-center gap-2">
                <template v-if="signedIn">
                    <Button as-child>
                        <Link :href="studio()" component="generations/Index">
                            Open the studio <ArrowRight />
                        </Link>
                    </Button>
                </template>
                <template v-else>
                    <Button variant="ghost" as-child>
                        <Link :href="login()">Log in</Link>
                    </Button>
                    <Button as-child>
                        <Link :href="register()">Start designing</Link>
                    </Button>
                </template>
            </div>
        </div>
    </header>
</template>
