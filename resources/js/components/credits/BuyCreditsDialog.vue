<script setup lang="ts">
import { Check, LoaderCircle } from '@lucide/vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { CREDIT_PACKS, useCredits } from '@/composables/useCredits';
import { formatBalance } from '@/lib/money';
import { cn } from '@/lib/utils';

const open = defineModel<boolean>('open', { required: true });

const { balance, purchase, purchasing } = useCredits();

function buy(amount: number): void {
    purchase(amount, () => {
        open.value = false;
    });
}
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Top up credits</DialogTitle>
                <DialogDescription>
                    Credits are US dollars. You have
                    <span class="font-mono text-foreground">{{
                        formatBalance(balance)
                    }}</span
                    >. This is a demo purchase, so no card is needed.
                </DialogDescription>
            </DialogHeader>

            <div class="grid grid-cols-3 gap-3">
                <button
                    v-for="pack in CREDIT_PACKS"
                    :key="pack"
                    type="button"
                    :disabled="purchasing !== null"
                    :class="
                        cn(
                            'group corner-marks flex flex-col items-center gap-1 rounded-lg border bg-card px-3 py-5 transition duration-200',
                            'hover:-translate-y-0.5 hover:border-primary hover:shadow-md active:translate-y-0 active:scale-[0.98] disabled:opacity-60',
                            purchasing === pack && 'border-primary',
                        )
                    "
                    @click="buy(pack)"
                >
                    <span class="font-mono text-2xl font-semibold tabular-nums"
                        >${{ pack }}</span
                    >
                    <span
                        class="flex h-4 items-center text-xs text-muted-foreground"
                    >
                        <LoaderCircle
                            v-if="purchasing === pack"
                            class="size-3.5 animate-spin text-primary"
                        />
                        <span
                            v-else
                            class="inline-flex items-center gap-1 transition group-hover:text-primary"
                        >
                            <Check
                                class="size-3 opacity-0 transition group-hover:opacity-100"
                            />
                            Add
                        </span>
                    </span>
                </button>
            </div>
        </DialogContent>
    </Dialog>
</template>
