<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ClosingCta from '@/components/marketing/ClosingCta.vue';
import HatchedDivider from '@/components/marketing/HatchedDivider.vue';
import HeroSection from '@/components/marketing/HeroSection.vue';
import HowItWorksSection from '@/components/marketing/HowItWorksSection.vue';
import MarketingFooter from '@/components/marketing/MarketingFooter.vue';
import MarketingHeader from '@/components/marketing/MarketingHeader.vue';
import PricingSection from '@/components/marketing/PricingSection.vue';
import StylesSection from '@/components/marketing/StylesSection.vue';
</script>

<template>
    <Head title="Design any room in one sentence" />

    <div class="min-h-svh bg-white text-ink">
        <MarketingHeader />
        <main>
            <HeroSection />
            <HowItWorksSection />
            <HatchedDivider />
            <PricingSection />
            <HatchedDivider />
            <StylesSection />
            <ClosingCta />
        </main>
        <MarketingFooter />
    </div>
</template>
