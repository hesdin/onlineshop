<script setup>
import LandingFooter from '@/components/Landing/LandingFooter.vue';
import LandingNavigation from '@/components/Landing/LandingNavigation.vue';
</script>

<template>
  <div class="min-h-screen bg-slate-50 font-sans text-slate-900">
    <LandingNavigation />

    <main
      class="container mx-auto space-y-12 py-10 pt-[var(--landing-header-offset,11rem)] md:pt-[calc(var(--landing-header-offset,11rem)+1rem)] lg:pt-[calc(var(--landing-header-offset,11rem)+1.5rem)]">
      <slot />
    </main>

    <LandingFooter />
  </div>
</template>
