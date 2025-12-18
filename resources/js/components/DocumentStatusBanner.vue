<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { AlertCircle, Clock, XCircle, FileCheck } from 'lucide-vue-next';

interface Props {
  status: 'draft' | 'submitted' | 'approved' | 'rejected';
  adminNotes?: string | null;
  compact?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  adminNotes: null,
  compact: false,
});

const config = computed(() => {
  switch (props.status) {
    case 'rejected':
      return {
        icon: XCircle,
        bgColor: 'bg-red-50',
        borderColor: 'border-red-200',
        iconColor: 'text-red-600',
        textColor: 'text-red-800',
        title: 'Dokumen Ditolak',
        message: 'Dokumen Anda ditolak. Silakan perbaiki dan submit ulang untuk mulai berjualan.',
        ctaText: 'Perbaiki Dokumen',
        showCta: true,
      };
    case 'submitted':
      return {
        icon: Clock,
        bgColor: 'bg-amber-50',
        borderColor: 'border-amber-200',
        iconColor: 'text-amber-600',
        textColor: 'text-amber-800',
        title: 'Dokumen sedang dalam proses verifikasi',
        message: 'Dokumen sedang dalam proses verifikasi. Anda dapat membuat draft produk tapi belum bisa publish atau terima pesanan.',
        ctaText: 'Lihat Status',
        showCta: false,
      };
    case 'draft':
    default:
      return {
        icon: AlertCircle,
        bgColor: 'bg-red-50',
        borderColor: 'border-red-200',
        iconColor: 'text-red-600',
        textColor: 'text-red-800',
        title: 'Lengkapi Dokumen Toko',
        message: 'Lengkapi dan verifikasi dokumen toko Anda untuk mulai berjualan dan menggunakan semua fitur.',
        ctaText: 'Lengkapi Sekarang',
        showCta: true,
      };
  }
});
</script>

<template>
  <div :class="[
    'border-b',
    config.bgColor,
    config.borderColor,
  ]">
    <div :class="compact ? 'px-4 py-3' : 'px-6 py-4'">
      <div class="flex items-start gap-3">
        <component :is="config.icon" :class="['h-5 w-5 flex-shrink-0 mt-0.5', config.iconColor]" />

        <div class="flex-1 min-w-0">
          <p :class="['font-semibold', compact ? 'text-sm' : 'text-base', config.textColor]">
            {{ config.title }}
          </p>
          <p :class="['mt-1', compact ? 'text-xs' : 'text-sm', config.textColor, 'opacity-90']">
            {{ config.message }}
          </p>
          <p v-if="adminNotes" :class="['mt-2 text-sm', config.textColor, 'font-medium']">
            Catatan Admin: {{ adminNotes }}
          </p>
        </div>

        <Link v-if="config.showCta" href="/seller/documents" :class="[
          'inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-medium transition flex-shrink-0',
          status === 'rejected'
            ? 'bg-red-600 text-white hover:bg-red-700'
            : 'bg-red-600 text-white hover:bg-red-700'
        ]">
          {{ config.ctaText }}
        </Link>
      </div>
    </div>
  </div>
</template>
