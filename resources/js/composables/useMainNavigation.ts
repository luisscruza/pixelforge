import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { ComputedRef } from 'vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { index as spendReport } from '@/routes/admin/spend-report';
import { index as studio } from '@/routes/generations';
import type { NavItem } from '@/types';

export type MainNavItem = NavItem & { active: boolean };

export function useMainNavigation(): ComputedRef<MainNavItem[]> {
    const page = usePage();
    const { isCurrentOrParentUrl } = useCurrentUrl();

    return computed(() => {
        const items: NavItem[] = [
            { title: 'Studio', href: studio(), component: 'generations/Index' },
        ];

        if (page.props.auth.canViewSpendReport) {
            items.push({
                title: 'Spend report',
                href: spendReport(),
                component: 'admin/SpendReport',
            });
        }

        return items.map((item) => ({
            ...item,
            active: isCurrentOrParentUrl(item.href),
        }));
    });
}
