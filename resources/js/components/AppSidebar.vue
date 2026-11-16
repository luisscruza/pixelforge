<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, ChartNoAxesCombined, Sparkles } from '@lucide/vue';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { index as spendReport } from '@/routes/admin/spend-report';
import { index as studio } from '@/routes/generations';
import type { NavItem } from '@/types';

const page = usePage();

const mainNavItems = computed<NavItem[]>(() => [
    {
        title: 'Studio',
        href: studio(),
        icon: Sparkles,
        component: 'generations/Index',
    },
    ...(page.props.auth.canViewSpendReport
        ? [
              {
                  title: 'Spend report',
                  href: spendReport(),
                  icon: ChartNoAxesCombined,
                  component: 'admin/SpendReport',
              },
          ]
        : []),
]);

const footerNavItems: NavItem[] = [
    {
        title: 'Provider API docs',
        href: 'https://pixelforge.promptedit.dev/docs',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="studio()" :component="'generations/Index'">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
