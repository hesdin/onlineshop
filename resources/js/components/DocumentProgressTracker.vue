<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { FileCheck, Clock, AlertCircle, XCircle } from 'lucide-vue-next';

interface Props {
  documentsUploaded: number;
  submissionStatus: 'draft' | 'submitted' | 'approved' | 'rejected';
}

const props = defineProps<Props>();

const totalRequired = 3; // KTP, NPWP, NIB

const percentage = computed(() => {
  if (props.submissionStatus === 'approved') {
    return 100;
  }
  return Math.round((props.documentsUploaded / totalRequired) * 100);
});

const statusConfig = computed(() => {
  switch (props.submissionStatus) {
    case 'approved':
      return {
        icon: FileCheck,
        color: 'text-green-600',
        bgColor: 'bg-green-100',
        label: 'Terverifikasi',
        progressColor: 'bg-green-500',
      };
    case 'submitted':
      return {
        icon: Clock,
        color: 'text-amber-600',
        bgColor: 'bg-amber-100',
        label: 'Direview',
        progressColor: 'bg-amber-500',
      };
    case 'rejected':
      return {
        icon: XCircle,
        color: 'text-red-600',
        bgColor: 'bg-red-100',
        label: 'Ditolak',
        progressColor: 'bg-red-500',
      };
    default:
      return {
        icon: AlertCircle,
        color: 'text-slate-600',
        bgColor: 'bg-slate-100',
        label: 'Belum Lengkap',
        progressColor: 'bg-slate-400',
      };
  }
});
</script>

<template>
  <Link href="/seller/documents"
    class="block rounded-lg border border-slate-200 bg-white p-4 transition hover:bg-slate-50">
    <div class="flex items-center justify-between mb-3">
      <div class="flex items-center gap-2">
        <component :is="statusConfig.icon" :class="['h-4 w-4', statusConfig.color]" />
        <span class="text-sm font-medium text-slate-900">Verifikasi Toko</span>
      </div>
      <span :class="['rounded-full px-2 py-0.5 text-xs font-medium', statusConfig.bgColor, statusConfig.color]">
        {{ statusConfig.label }}
      </span>
    </div>

    <!-- Progress Bar -->
    <div class="mb-2">
      <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100">
        <div :class="['h-full transition-all duration-300', statusConfig.progressColor]"
          :style="{ width: `${percentage}%` }" />
      </div>
    </div>

    <!-- Progress Text -->
    <p class="text-xs text-slate-600">
      <template v-if="submissionStatus === 'approved'">
        Dokumen terverifikasi
      </template>
      <template v-else-if="submissionStatus === 'submitted'">
        Menunggu verifikasi admin
      </template>
      <template v-else-if="submissionStatus === 'rejected'">
        Perbaiki dokumen yang ditolak
      </template>
      <template v-else>
        {{ documentsUploaded }}/{{ totalRequired }} dokumen terupload
      </template>
    </p>
  </Link>
</template>
