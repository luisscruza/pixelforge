<script setup lang="ts">
import MoneyValue from '@/components/studio/MoneyValue.vue';
import type { SpendReportUser } from '@/types/generation';

defineProps<{
    users: SpendReportUser[];
}>();
</script>

<template>
    <div class="overflow-x-auto rounded-lg border bg-card">
        <table class="w-full min-w-[560px] text-sm">
            <thead
                class="bg-muted/60 font-mono text-[10px] tracking-wider text-muted-foreground uppercase"
            >
                <tr>
                    <th class="py-2.5 pl-4 text-left font-medium">User</th>
                    <th class="text-right font-medium">Generations</th>
                    <th class="text-right font-medium">Charged</th>
                    <th class="text-right font-medium">Provider cost</th>
                    <th class="pr-4 text-right font-medium">Profit</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="users.length === 0">
                    <td
                        colspan="5"
                        class="py-8 text-center text-sm text-muted-foreground"
                    >
                        No completed generations in this range yet.
                    </td>
                </tr>
                <tr
                    v-for="(user, rank) in users"
                    :key="user.user_id"
                    class="border-t transition-colors hover:bg-accent/30"
                >
                    <td class="py-2.5 pl-4">
                        <span
                            class="mr-2 font-mono text-[10px] text-muted-foreground"
                            >#{{ rank + 1 }}</span
                        >
                        <span class="font-medium">{{ user.name }}</span>
                        <span class="ml-1.5 text-xs text-muted-foreground">{{
                            user.email
                        }}</span>
                    </td>
                    <td class="text-right font-mono text-xs tabular-nums">
                        {{ user.generations }}
                    </td>
                    <td class="text-right text-xs">
                        <MoneyValue :value="user.charged" :decimals="6" />
                    </td>
                    <td class="text-right text-xs">
                        <MoneyValue :value="user.provider_cost" :decimals="6" />
                    </td>
                    <td class="pr-4 text-right text-xs">
                        <MoneyValue
                            :value="user.profit"
                            :decimals="6"
                            class="text-success"
                        />
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
