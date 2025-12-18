<script setup lang="ts">
import { computed } from 'vue';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';

interface Props {
  disabled: boolean;
  reason?: string;
  showTooltip?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  reason: 'Lengkapi verifikasi dokumen toko untuk mengakses fitur ini',
  showTooltip: true,
});
</script>

<template>
  <TooltipProvider v-if="disabled && showTooltip">
    <Tooltip>
      <TooltipTrigger as-child>
        <div class="inline-block">
          <slot :disabled="true" />
        </div>
      </TooltipTrigger>
      <TooltipContent class="max-w-xs">
        <p class="text-sm">{{ reason }}</p>
      </TooltipContent>
    </Tooltip>
  </TooltipProvider>

  <slot v-else :disabled="disabled" />
</template>
