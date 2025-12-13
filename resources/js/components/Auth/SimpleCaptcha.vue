<script setup lang="ts">
import { computed } from 'vue';
import busImage from '@/../images/captcha/bus.jpg';

const props = defineProps<{
  open: boolean;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'solved'): void;
}>();

const visibility = computed(() => (props.open ? 'opacity-100 pointer-events-auto' : 'opacity-0 pointer-events-none'));

const solve = () => {
  emit('solved');
  emit('close');
};
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 transition duration-200" :class="visibility">
    <div class="w-full max-w-xl rounded-lg border border-slate-200 bg-white shadow-2xl overflow-hidden">
      <div class="flex flex-col bg-white">
        <div class="bg-[#0b7dda] px-6 py-4 text-white">
          <p class="text-lg font-semibold leading-tight">
            Select all squares with <span class="font-bold text-xl">buses</span>
          </p>
          <p class="text-xs text-white/80">If there are none, click skip</p>
        </div>
        <div class="relative bg-slate-100">
          <img :src="busImage" alt="Captcha challenge" class="h-80 w-full object-cover" />
          <div class="absolute inset-0 grid grid-cols-4 grid-rows-3">
            <span v-for="i in 12" :key="i" class="border border-white/60"></span>
          </div>
        </div>
        <div class="flex items-center justify-end gap-3 bg-white px-6 py-4">
          <button type="button" @click="emit('close')" class="rounded-md px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">
            Skip
          </button>
          <button
            type="button"
            @click="solve"
            class="rounded-md bg-[#0b7dda] px-4 py-2 text-sm font-semibold text-white hover:bg-[#0a72c5]"
          >
            Verify
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
