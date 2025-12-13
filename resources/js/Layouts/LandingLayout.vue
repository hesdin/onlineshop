<script setup>
import LandingFooter from '@/Components/Landing/LandingFooter.vue';
import LandingNavigation from '@/Components/Landing/LandingNavigation.vue';
</script>

<template>
  <div class="min-h-screen bg-slate-50 font-sans text-slate-900">
    <LandingNavigation />

    <main class="container mx-auto space-y-12 px-4 py-10 pt-44 lg:pt-44">
      <slot />
    </main>

    <LandingFooter />
  </div>
</template>
