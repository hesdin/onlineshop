<script setup lang="ts">
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, h, ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { DataTable } from '@/components/ui/data-table';
import { CheckCircle2, Eye, Search, ShieldAlert } from 'lucide-vue-next';
import type { ColumnDef } from '@tanstack/vue-table';

type DocumentRow = {
  id: number;
  store_name: string | null;
  store_type: string | null;
  store_is_verified: boolean;
  owner_name: string | null;
  owner_email: string | null;
  submission_status: string;
  submitted_at: string | null;
  ktp_status: string;
  npwp_status: string;
  nib_status: string;
  has_ktp: boolean;
  has_npwp: boolean;
  has_nib: boolean;
  missing_required: boolean;
};

const props = defineProps<{
  documents: {
    data: DocumentRow[];
    total: number;
    links: { label: string; url: string | null; active: boolean }[];
    prev_page_url: string | null;
    next_page_url: string | null;
  };
  filters: {
    search?: string | null;
    status?: string | null;
  };
  metrics: {
    draft: number;
    submitted: number;
    approved: number;
    rejected: number;
  };
}>();

defineOptions({
  layout: AdminDashboardLayout,
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success ?? '');

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? 'submitted');

const debouncedSearch = useDebounceFn((value: string) => {
  router.get('/admin/seller-documents', { search: value || undefined, status: status.value || undefined }, {
    preserveState: true,
    replace: true,
    preserveScroll: true,
  });
}, 400);

watch(search, (value) => {
  debouncedSearch(value);
});

watch(status, (value) => {
  router.get('/admin/seller-documents', { search: search.value || undefined, status: value || undefined }, {
    preserveState: true,
    replace: true,
    preserveScroll: true,
  });
});

const paginateTo = (url?: string | null) => {
  if (!url) return;
  router.visit(url, {
    preserveScroll: true,
    preserveState: true,
    replace: true,
  });
};

const numberedPaginationLinks = computed(() =>
  (props.documents.links ?? []).filter((link) => Number.isInteger(Number(link.label))),
);

const typeLabel = (type: string | null) => {
  const map: Record<string, string> = {
    umkm: 'UMKM',
    vendor: 'Vendor',
    koperasi: 'Koperasi',
    premium: 'Premium',
  };
  return type ? (map[type] ?? type) : '-';
};

const statusLabel = (value: string) => {
  const map: Record<string, string> = {
    draft: 'DRAFT',
    submitted: 'MENUNGGU',
    approved: 'DISETUJUI',
    rejected: 'DITOLAK',
  };
  return map[value] ?? value.toUpperCase();
};

const statusBadge = (value: string) => {
  const map: Record<string, string> = {
    draft: 'bg-slate-100 text-slate-700',
    submitted: 'bg-amber-100 text-amber-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
  };
  return map[value] ?? 'bg-slate-100 text-slate-700';
};

const columns = computed<ColumnDef<DocumentRow>[]>(() => [
  {
    accessorKey: 'store_name',
    header: () => 'Toko',
    cell: ({ row }) =>
      h('div', { class: 'space-y-0.5' }, [
        h('div', { class: 'font-semibold text-slate-900' }, row.original.store_name ?? '-'),
        h('div', { class: 'text-xs text-slate-500' }, typeLabel(row.original.store_type)),
      ]),
  },
  {
    accessorKey: 'owner_name',
    header: () => 'Pemilik',
    cell: ({ row }) =>
      h('div', { class: 'space-y-0.5' }, [
        h('div', { class: 'text-sm font-medium text-slate-800' }, row.original.owner_name ?? '-'),
        h('div', { class: 'text-xs text-slate-500' }, row.original.owner_email ?? '-'),
      ]),
  },
  {
    accessorKey: 'submission_status',
    header: () => 'Status',
    cell: ({ row }) =>
      h(
        'span',
        { class: `rounded-sm px-2 py-1 text-xs font-semibold ${statusBadge(row.original.submission_status)}` },
        statusLabel(row.original.submission_status),
      ),
  },
  {
    id: 'required',
    header: () => 'Dokumen Wajib',
    cell: ({ row }) => {
      const ok = !row.original.missing_required;
      return h(
        'span',
        { class: ok ? 'text-xs font-semibold text-green-700' : 'text-xs font-semibold text-red-700' },
        ok ? 'Lengkap' : 'Belum lengkap',
      );
    },
  },
  {
    id: 'actions',
    header: () => 'Aksi',
    meta: { class: 'text-right w-24' },
    cell: ({ row }) =>
      h('div', { class: 'flex justify-end' }, [
        h(
          'span',
          {
            class:
              'inline-flex h-8 w-8 items-center justify-center rounded-full ' +
              'cursor-pointer text-slate-500 hover:bg-slate-100 hover:text-slate-900',
            onClick: () => router.visit(`/admin/seller-documents/${row.original.id}`),
          },
          [h(Eye, { class: 'h-[15px] w-[15px]' })],
        ),
      ]),
  },
]);
</script>

<template>
  <div class="space-y-6">
    <Head title="Verifikasi Dokumen Penjual" />

    <Alert v-if="flashSuccess" variant="default" class="flex items-start gap-3 border-green-200 bg-green-50">
      <CheckCircle2 class="h-5 w-5 text-green-600" />
      <div class="space-y-1">
        <AlertTitle class="text-green-800">Berhasil</AlertTitle>
        <AlertDescription class="text-green-700">{{ flashSuccess }}</AlertDescription>
      </div>
    </Alert>

    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Vendor & Verifikasi</p>
        <h1 class="text-2xl font-bold text-slate-900">Verifikasi Dokumen Penjual</h1>
        <p class="text-sm text-slate-500">Tinjau dokumen KTP, NPWP, dan NIB untuk memverifikasi toko.</p>
      </div>
    </div>

    <div class="grid gap-4 md:grid-cols-4">
      <div class="rounded-sm border border-slate-200 bg-white p-4">
        <p class="text-xs text-slate-500">Menunggu</p>
        <p class="mt-1 text-2xl font-semibold text-amber-700">{{ metrics.submitted }}</p>
      </div>
      <div class="rounded-sm border border-slate-200 bg-white p-4">
        <p class="text-xs text-slate-500">Disetujui</p>
        <p class="mt-1 text-2xl font-semibold text-green-700">{{ metrics.approved }}</p>
      </div>
      <div class="rounded-sm border border-slate-200 bg-white p-4">
        <p class="text-xs text-slate-500">Ditolak</p>
        <p class="mt-1 text-2xl font-semibold text-red-700">{{ metrics.rejected }}</p>
      </div>
      <div class="rounded-sm border border-slate-200 bg-white p-4">
        <p class="text-xs text-slate-500">Draft</p>
        <p class="mt-1 text-2xl font-semibold text-slate-700">{{ metrics.draft }}</p>
      </div>
    </div>

    <section class="space-y-4">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="relative w-full md:w-72">
          <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
            <Search class="h-4 w-4" />
          </span>
          <Input v-model="search" placeholder="Cari toko / nama / email" class="w-full pl-9" />
        </div>

        <div class="flex items-center gap-2">
          <ShieldAlert class="h-4 w-4 text-slate-500" />
          <select v-model="status" class="rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
            <option value="">Semua</option>
            <option value="submitted">Menunggu</option>
            <option value="approved">Disetujui</option>
            <option value="rejected">Ditolak</option>
            <option value="draft">Draft</option>
          </select>
        </div>
      </div>

      <div class="overflow-hidden rounded-sm border border-slate-200 bg-white">
        <DataTable :columns="columns" :data="documents.data" />
      </div>

      <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">
        <p>Menampilkan {{ documents.data.length }} dari {{ documents.total }} pengajuan.</p>
        <div class="flex flex-wrap items-center gap-1">
          <Button variant="outline" size="sm" :disabled="!documents.prev_page_url" @click="paginateTo(documents.prev_page_url)">
            Sebelumnya
          </Button>
          <Button v-for="link in numberedPaginationLinks" :key="link.label" size="sm"
            :variant="link.active ? 'default' : 'outline'" :disabled="!link.url" @click="paginateTo(link.url)">
            {{ link.label }}
          </Button>
          <Button variant="outline" size="sm" :disabled="!documents.next_page_url" @click="paginateTo(documents.next_page_url)">
            Selanjutnya
          </Button>
        </div>
      </div>
    </section>
  </div>
</template>

