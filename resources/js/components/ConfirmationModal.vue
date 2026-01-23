<script setup>
import { AlertTriangle } from 'lucide-vue-next';

defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: 'Konfirmasi',
  },
  message: {
    type: String,
    default: 'Apakah Anda yakin?',
  },
  confirmText: {
    type: String,
    default: 'Ya',
  },
  cancelText: {
    type: String,
    default: 'Batal',
  },
  variant: {
    type: String,
    default: 'danger', // 'danger' | 'warning'
  },
  position: {
    type: String,
    default: 'center', // 'center' | 'top'
  },
});

const emit = defineEmits(['confirm', 'cancel']);
</script>

<template>
  <Teleport to="body">
    <div v-if="show"
      class="fixed inset-0 z-[9999] flex min-h-screen bg-black/60 px-4 backdrop-blur-sm"
      :class="position === 'top' ? 'items-start justify-center pt-20' : 'items-center justify-center'"
      @click.self="emit('cancel')">
      <div class="relative w-full max-w-md transform rounded-md bg-white p-8 shadow-2xl transition-all">
        <div class="flex flex-col items-center text-center">
          <div class="grid h-16 w-16 place-items-center rounded-full ring-8"
            :class="variant === 'warning' ? 'bg-amber-100 ring-amber-50' : 'bg-red-100 ring-red-50'">
            <AlertTriangle class="h-8 w-8" :class="variant === 'warning' ? 'text-amber-600' : 'text-red-600'" />
          </div>
          <h3 class="mt-5 text-lg font-bold text-slate-900">{{ title }}</h3>
          <p class="mt-2 text-sm text-slate-500">{{ message }}</p>
        </div>

        <div class="mt-8 flex gap-3">
          <button type="button" @click="emit('cancel')"
            class="flex-1 rounded-sm border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 hover:border-slate-300">
            {{ cancelText }}
          </button>
          <button type="button" @click="emit('confirm')"
            class="flex-1 rounded-sm px-4 py-3 text-sm font-semibold text-white shadow-md transition"
            :class="variant === 'warning' ? 'bg-amber-600 shadow-amber-600/30 hover:bg-amber-700' : 'bg-red-600 shadow-red-600/30 hover:bg-red-700'">
            {{ confirmText }}
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>
