import { router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import CreditPurchaseController from '@/actions/App/Http/Controllers/CreditPurchaseController';
import { addDecimals, compareDecimals } from '@/lib/money';

export const CREDIT_PACKS = [5, 10, 25] as const;

export function useCredits() {
    const page = usePage();
    const purchasing = ref<number | null>(null);

    const balance = computed(() => page.props.credits ?? '0');

    const canAfford = (amount: string): boolean =>
        compareDecimals(balance.value, amount) >= 0;

    function purchase(amount: number, onSuccess?: () => void): void {
        purchasing.value = amount;

        router
            .optimistic(() => ({
                credits: addDecimals(balance.value, String(amount)),
            }))
            .post(
                CreditPurchaseController.store(),
                { amount },
                {
                    preserveScroll: true,
                    preserveState: true,
                    onSuccess,
                    onFinish: () => {
                        purchasing.value = null;
                    },
                },
            );
    }

    return { balance, canAfford, purchase, purchasing };
}
