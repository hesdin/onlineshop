<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

const isLoading = ref(false);

let startTimeout = null;

onMounted(() => {
  router.on('start', () => {
    // Add small delay to avoid flash for quick page loads
    startTimeout = setTimeout(() => {
      isLoading.value = true;
    }, 100);
  });

  router.on('finish', () => {
    clearTimeout(startTimeout);
    isLoading.value = false;
  });
});

onUnmounted(() => {
  clearTimeout(startTimeout);
});
</script>

<template>
  <Transition enter-active-class="transition-opacity duration-200" leave-active-class="transition-opacity duration-150"
    enter-from-class="opacity-0" leave-to-class="opacity-0">
    <div v-if="isLoading" class="fixed inset-0 z-[9999] flex items-center justify-center bg-white/80 backdrop-blur-sm">
      <div class="flex flex-col items-center gap-3">
        <!-- Spinner -->
        <div class="relative h-10 w-10">
          <div class="absolute inset-0 rounded-full border-4 border-slate-200"></div>
          <div class="absolute inset-0 rounded-full border-4 border-sky-500 border-t-transparent animate-spin"></div>
        </div>
        <!-- Text -->
        <p class="text-sm font-medium text-slate-500">Memuat...</p>
      </div>
    </div>
  </Transition>
</template>
