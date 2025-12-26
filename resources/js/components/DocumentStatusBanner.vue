<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { AlertCircle, Clock, XCircle, ChevronRight } from 'lucide-vue-next';

interface Props {
  status: 'draft' | 'submitted' | 'approved' | 'rejected';
  adminNotes?: string | null;
}

const props = withDefaults(defineProps<Props>(), {
  adminNotes: null,
});

const config = computed(() => {
  switch (props.status) {
    case 'rejected':
      return {
        icon: XCircle,
        bg: 'bg-red-600',
        title: 'Dokumen Ditolak',
        message: props.adminNotes ? `Catatan: ${props.adminNotes}` : 'Silakan perbaiki dan submit ulang.',
        ctaText: 'Perbaiki',
        showCta: true,
      };
    case 'submitted':
      return {
        icon: Clock,
        bg: 'bg-amber-500',
        title: 'Menunggu Verifikasi',
        message: 'Dokumen sedang ditinjau (1-3 hari kerja)',
        ctaText: '',
        showCta: false,
      };
    case 'draft':
    default:
      return {
        icon: AlertCircle,
        bg: 'bg-indigo-600',
        title: 'Lengkapi Data Toko',
        message: 'Verifikasi toko untuk mulai berjualan',
        ctaText: 'Lengkapi',
        showCta: true,
      };
  }
});
</script>

<template>
  <div :class="['z-[9999]', config.bg]">
    <div class="px-4 py-2.5">
      <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3 min-w-0">
          <component :is="config.icon" class="h-4 w-4 flex-shrink-0 text-white/90" />
          <div class="flex items-center gap-2 min-w-0 text-sm">
            <span class="font-semibold text-white truncate">{{ config.title }}</span>
            <span class="hidden sm:inline text-white/80">·</span>
            <span class="hidden sm:inline text-white/80 truncate">{{ config.message }}</span>
          </div>
        </div>

        <Link v-if="config.showCta" href="/seller/settings"
          class="flex items-center gap-1 rounded-sm bg-white px-4 py-1.5 text-xs font-semibold text-indigo-700 shadow-sm transition hover:bg-white/90 flex-shrink-0">
          {{ config.ctaText }}
          <ChevronRight class="h-3 w-3" />
        </Link>
      </div>
    </div>
  </div>
</template>
