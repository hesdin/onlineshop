<script setup lang="ts">
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, h, ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { DataTable } from '@/components/ui/data-table';
import {
  CheckCircle2, Eye, Search, ShieldAlert, Clock, CheckCheck, XCircle, FileText,
  Store as StoreIcon, User, Mail, FileCheck, FileWarning
} from 'lucide-vue-next';
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
    draft: 'bg-muted text-muted-foreground',
    submitted: 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-400',
    approved: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400',
    rejected: 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-400',
  };
  return map[value] ?? 'bg-muted text-muted-foreground';
};

const columns = computed<ColumnDef<DocumentRow>[]>(() => [
  {
    accessorKey: 'store_name',
    header: () => 'Toko',
    cell: ({ row }) =>
      h('div', { class: 'flex items-center gap-3' }, [
        h('div', { class: 'h-10 w-10 rounded-lg bg-primary/10 flex items-center justify-center' }, [
          h(StoreIcon, { class: 'h-5 w-5 text-primary' })
        ]),
        h('div', { class: 'space-y-0.5' }, [
          h('div', { class: 'font-semibold text-foreground' }, row.original.store_name ?? '-'),
          h('div', { class: 'text-xs text-muted-foreground' }, typeLabel(row.original.store_type)),
        ]),
      ]),
  },
  {
    accessorKey: 'owner_name',
    header: () => 'Pemilik',
    cell: ({ row }) =>
      h('div', { class: 'space-y-1' }, [
        h('div', { class: 'flex items-center gap-1.5 text-sm font-medium text-foreground' }, [
          h(User, { class: 'h-3.5 w-3.5 text-muted-foreground' }),
          row.original.owner_name ?? '-'
        ]),
        h('div', { class: 'flex items-center gap-1.5 text-xs text-muted-foreground' }, [
          h(Mail, { class: 'h-3 w-3' }),
          row.original.owner_email ?? '-'
        ]),
      ]),
  },
  {
    accessorKey: 'submission_status',
    header: () => 'Status',
    cell: ({ row }) =>
      h(
        'span',
        { class: `inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ${statusBadge(row.original.submission_status)}` },
        [
          row.original.submission_status === 'approved' ? h(CheckCheck, { class: 'h-3.5 w-3.5' }) :
            row.original.submission_status === 'rejected' ? h(XCircle, { class: 'h-3.5 w-3.5' }) :
              row.original.submission_status === 'submitted' ? h(Clock, { class: 'h-3.5 w-3.5' }) :
                h(FileText, { class: 'h-3.5 w-3.5' }),
          statusLabel(row.original.submission_status),
        ]
      ),
  },
  {
    id: 'required',
    header: () => 'Dokumen',
    cell: ({ row }) => {
      const ok = !row.original.missing_required;
      return h('div', { class: 'flex items-center gap-1.5' }, [
        ok
          ? h('div', { class: 'h-6 w-6 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center' }, [
            h(FileCheck, { class: 'h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400' })
          ])
          : h('div', { class: 'h-6 w-6 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center' }, [
            h(FileWarning, { class: 'h-3.5 w-3.5 text-red-600 dark:text-red-400' })
          ]),
        h('span', { class: ok ? 'text-xs font-medium text-emerald-700 dark:text-emerald-400' : 'text-xs font-medium text-red-700 dark:text-red-400' },
          ok ? 'Lengkap' : 'Belum Lengkap'
        ),
      ]);
    },
  },
  {
    id: 'actions',
    header: () => 'Aksi',
    meta: { class: 'text-right w-24' },
    cell: ({ row }) =>
      h('div', { class: 'flex justify-end' }, [
        h(
          Button,
          {
            variant: 'ghost',
            size: 'sm',
            class: 'h-8 px-3 text-primary hover:text-primary hover:bg-primary/10',
            onClick: () => router.visit(`/admin/seller-documents/${row.original.id}`),
          },
          () => [h(Eye, { class: 'h-4 w-4 mr-1.5' }), 'Lihat']
        ),
      ]),
  },
]);

const totalDocs = computed(() => props.metrics.draft + props.metrics.submitted + props.metrics.approved + props.metrics.rejected);
</script>

<template>
  <div class="space-y-6">

    <Head title="Verifikasi Dokumen Penjual" />

    <!-- Success Alert -->
    <Alert v-if="flashSuccess" variant="default"
      class="flex items-start gap-3 border-emerald-200 bg-emerald-50 dark:border-emerald-900 dark:bg-emerald-950/50">
      <CheckCircle2 class="h-5 w-5 text-emerald-600" />
      <div class="space-y-1">
        <AlertTitle class="text-emerald-800 dark:text-emerald-200">Berhasil</AlertTitle>
        <AlertDescription class="text-emerald-700 dark:text-emerald-300">{{ flashSuccess }}</AlertDescription>
      </div>
    </Alert>

    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div>
        <!-- <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Vendor & Verifikasi</p> -->
        <h1 class="text-2xl font-bold text-foreground">Verifikasi Dokumen Penjual</h1>
        <p class="text-sm text-muted-foreground mt-1">Tinjau dokumen KTP, NPWP, dan NIB untuk memverifikasi toko.</p>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid gap-4 md:grid-cols-4">
      <!-- Menunggu -->
      <Card class="border-0 shadow-sm bg-gradient-to-br from-amber-500 to-orange-600 text-white overflow-hidden">
        <CardContent class="pt-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-amber-100 text-sm font-medium">Menunggu</p>
              <p class="text-3xl font-bold mt-1">{{ metrics.submitted }}</p>
              <p class="text-amber-100 text-xs mt-2">Perlu ditinjau</p>
            </div>
            <div class="h-12 w-12 rounded-xl bg-white/20 flex items-center justify-center">
              <Clock class="h-6 w-6 text-white" />
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Disetujui -->
      <Card class="border-0 shadow-sm bg-gradient-to-br from-emerald-500 to-teal-600 text-white overflow-hidden">
        <CardContent class="pt-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-emerald-100 text-sm font-medium">Disetujui</p>
              <p class="text-3xl font-bold mt-1">{{ metrics.approved }}</p>
              <p class="text-emerald-100 text-xs mt-2">Toko terverifikasi</p>
            </div>
            <div class="h-12 w-12 rounded-xl bg-white/20 flex items-center justify-center">
              <CheckCheck class="h-6 w-6 text-white" />
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Ditolak -->
      <Card class="border-0 shadow-sm bg-gradient-to-br from-red-500 to-rose-600 text-white overflow-hidden">
        <CardContent class="pt-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-red-100 text-sm font-medium">Ditolak</p>
              <p class="text-3xl font-bold mt-1">{{ metrics.rejected }}</p>
              <p class="text-red-100 text-xs mt-2">Perlu revisi</p>
            </div>
            <div class="h-12 w-12 rounded-xl bg-white/20 flex items-center justify-center">
              <XCircle class="h-6 w-6 text-white" />
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Draft -->
      <Card class="border-0 shadow-sm bg-gradient-to-br from-slate-500 to-slate-600 text-white overflow-hidden">
        <CardContent class="pt-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-slate-200 text-sm font-medium">Draft</p>
              <p class="text-3xl font-bold mt-1">{{ metrics.draft }}</p>
              <p class="text-slate-200 text-xs mt-2">Belum diajukan</p>
            </div>
            <div class="h-12 w-12 rounded-xl bg-white/20 flex items-center justify-center">
              <FileText class="h-6 w-6 text-white" />
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Table Section -->
    <Card class="border-0 shadow-sm bg-card">
      <CardHeader class="pb-4">
        <div class="flex flex-wrap items-center justify-between gap-4">
          <div>
            <CardTitle class="text-lg">Daftar Pengajuan</CardTitle>
            <CardDescription>{{ documents.total }} pengajuan dokumen dari penjual</CardDescription>
          </div>

          <!-- Filters -->
          <div class="flex flex-wrap items-center gap-3">
            <div class="relative w-full md:w-80">
              <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-muted-foreground">
                <Search class="h-4 w-4" />
              </span>
              <Input v-model="search" placeholder="Cari toko / nama / email"
                class="w-full pl-9 bg-muted/30 border border-border focus-visible:ring-primary" />
            </div>

            <div class="flex items-center gap-2">
              <div class="flex items-center gap-1.5 px-3 py-2 rounded-lg border border-border bg-card">
                <ShieldAlert class="h-4 w-4 text-muted-foreground" />
                <select v-model="status"
                  class="bg-transparent text-sm text-foreground focus:outline-none cursor-pointer">
                  <option value="">Semua Status</option>
                  <option value="submitted">Menunggu</option>
                  <option value="approved">Disetujui</option>
                  <option value="rejected">Ditolak</option>
                  <option value="draft">Draft</option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </CardHeader>

      <CardContent class="p-0">
        <div class="overflow-hidden">
          <DataTable :columns="columns" :data="documents.data" />
        </div>
      </CardContent>

      <CardFooter class="flex flex-wrap items-center justify-between gap-3 border-t border-border pt-4">
        <p class="text-sm text-muted-foreground">
          Menampilkan {{ documents.data.length }} dari {{ documents.total }} pengajuan.
        </p>
        <div class="flex flex-wrap items-center gap-1">
          <Button variant="outline" size="sm" :disabled="!documents.prev_page_url"
            @click="paginateTo(documents.prev_page_url)">
            Sebelumnya
          </Button>
          <Button v-for="link in numberedPaginationLinks" :key="link.label" size="sm"
            :variant="link.active ? 'default' : 'outline'" :disabled="!link.url" @click="paginateTo(link.url)">
            {{ link.label }}
          </Button>
          <Button variant="outline" size="sm" :disabled="!documents.next_page_url"
            @click="paginateTo(documents.next_page_url)">
            Selanjutnya
          </Button>
        </div>
      </CardFooter>
    </Card>
  </div>
</template>
