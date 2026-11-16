<script setup lang="ts">
import { Plus, Wallet } from '@lucide/vue';
import { ref } from 'vue';
import BuyCreditsDialog from '@/components/credits/BuyCreditsDialog.vue';
import { useCredits } from '@/composables/useCredits';
import { useFlashOnChange } from '@/composables/useFlashOnChange';
import { formatBalance } from '@/lib/money';
import { cn } from '@/lib/utils';

const { balance } = useCredits();
const changed = useFlashOnChange(balance);
const open = ref(false);
</script>

<template>
    <div class="flex items-center gap-1 rounded border bg-white p-1 pl-3">
        <Wallet class="size-3.5 text-muted-foreground" />
        <span
            :class="
                cn(
                    'rounded px-1 font-mono text-sm tabular-nums transition-colors duration-500',
                    changed && 'bg-primary/15 text-primary',
                )
            "
            :title="`$${balance} available`"
        >
            {{ formatBalance(balance) }}
        </span>
        <button
            type="button"
            class="inline-flex items-center gap-1 rounded-sm bg-primary px-2.5 py-1 text-xs font-medium text-primary-foreground transition hover:bg-primary/90 active:scale-95"
            @click="open = true"
        >
            <Plus class="size-3" /> Buy credits
        </button>
        <BuyCreditsDialog v-model:open="open" />
    </div>
</template>
