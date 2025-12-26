<script setup>
import { computed } from 'vue';

const props = defineProps({
  type: {
    type: String,
    default: 'success',
    validator: (value) => ['success', 'error', 'warning', 'info'].includes(value),
  },
  message: {
    type: String,
    required: true,
  },
  show: {
    type: Boolean,
    default: true,
  },
  dismissible: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['close']);

const alertConfig = computed(() => {
  const configs = {
    success: {
      bg: 'bg-green-50',
      text: 'text-green-700',
      iconBg: 'bg-green-500',
      icon: 'check',
    },
    error: {
      bg: 'bg-red-50',
      text: 'text-red-700',
      iconBg: 'bg-red-500',
      icon: 'x',
    },
    warning: {
      bg: 'bg-amber-50',
      text: 'text-amber-700',
      iconBg: 'bg-amber-500',
      icon: 'warning',
    },
    info: {
      bg: 'bg-blue-50',
      text: 'text-blue-700',
      iconBg: 'bg-blue-500',
      icon: 'info',
    },
  };
  return configs[props.type];
});

const close = () => {
  emit('close');
};
</script>

<template>
  <Transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="opacity-0 -translate-y-2"
    enter-to-class="opacity-100 translate-y-0" leave-active-class="transition-all duration-200 ease-in"
    leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
    <div v-if="show" :class="[alertConfig.bg, 'flex items-center gap-3 rounded-lg px-4 py-3']">
      <!-- Icon -->
      <div :class="[alertConfig.iconBg, 'flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full']">
        <!-- Success Icon -->
        <svg v-if="alertConfig.icon === 'check'" class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
          <path d="M20 6L9 17l-5-5" />
        </svg>

        <!-- Error Icon -->
        <svg v-else-if="alertConfig.icon === 'x'" class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>

        <!-- Warning Icon -->
        <svg v-else-if="alertConfig.icon === 'warning'" class="h-4 w-4 text-white" viewBox="0 0 24 24"
          fill="currentColor">
          <path d="M12 2L1 21h22L12 2zm0 4l7.53 13H4.47L12 6zm-1 5v4h2v-4h-2zm0 6v2h2v-2h-2z" />
        </svg>

        <!-- Info Icon -->
        <svg v-else-if="alertConfig.icon === 'info'" class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z" />
        </svg>
      </div>

      <!-- Message -->
      <p :class="[alertConfig.text, 'flex-1 text-sm font-medium']">{{ message }}</p>

      <!-- Close Button (optional) -->
      <button v-if="dismissible" @click="close" type="button"
        :class="[alertConfig.text, 'flex-shrink-0 rounded p-0.5 opacity-60 transition hover:opacity-100']">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
    </div>
  </Transition>
</template>
