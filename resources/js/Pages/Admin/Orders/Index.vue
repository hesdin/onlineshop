<script setup lang="ts">
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, h, ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { DataTable } from '@/components/ui/data-table';
import { Eye, CheckCircle2, Search } from 'lucide-vue-next';
import type { ColumnDef } from '@tanstack/vue-table';

type OrderRow = {
  id: number;
  order_number: string;
  customer_name: string;
  store_name: string | null;
  total_amount: number;
  status: string;
  payment_status: string;
  created_at: string;
};

const props = defineProps<{
  orders: {
    data: OrderRow[];
    total: number;
    links: { label: string; url: string | null; active: boolean }[];
    prev_page_url: string | null;
    next_page_url: string | null;
  };
  filters: {
    search?: string | null;
    status?: string | null;
  };
}>();

defineOptions({
  layout: AdminDashboardLayout,
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success ?? '');
const search = ref(props.filters.search ?? '');

const debouncedSearch = useDebounceFn((value: string) => {
  router.get('/admin/orders', { search: value || undefined }, {
    preserveState: true,
    replace: true,
    preserveScroll: true,
  });
}, 400);

watch(search, (value) => {
  debouncedSearch(value);
});

const numberedPaginationLinks = computed(() =>
  (props.orders.links ?? []).filter((link) => Number.isInteger(Number(link.label))),
);

const paginateTo = (url?: string | null) => {
  if (!url) return;
  router.visit(url, {
    preserveScroll: true,
    preserveState: true,
    replace: true,
  });
};

const formatCurrency = (value: number) => {
  return `Rp ${value.toLocaleString('id-ID')}`;
};

const getStatusBadge = (status: string) => {
  const badges: Record<string, string> = {
    pending_payment: 'bg-amber-100 text-amber-700',
    processing: 'bg-blue-100 text-blue-700',
    shipped: 'bg-purple-100 text-purple-700',
    delivered: 'bg-green-100 text-green-700',
    completed: 'bg-green-100 text-green-700',
    cancelled: 'bg-red-100 text-red-700',
  };
  return badges[status] ?? 'bg-slate-100 text-slate-600';
};

const columns = computed<ColumnDef<OrderRow>[]>(() => [
  {
    accessorKey: 'order_number',
    header: () => 'No. Pesanan',
    cell: ({ row }) =>
      h('span', { class: 'font-semibold text-slate-900 font-mono text-sm' }, row.original.order_number),
  },
  {
    accessorKey: 'customer_name',
    header: () => 'Pelanggan',
    cell: ({ row }) =>
      h('span', { class: 'text-sm text-slate-700' }, row.original.customer_name),
  },
  {
    accessorKey: 'store_name',
    header: () => 'Toko',
    cell: ({ row }) =>
      h('span', { class: 'text-sm text-slate-600' }, row.original.store_name ?? '-'),
  },
  {
    accessorKey: 'total_amount',
    header: () => 'Total',
    cell: ({ row }) =>
      h('span', { class: 'text-sm font-semibold text-slate-900' }, formatCurrency(row.original.total_amount)),
  },
  {
    accessorKey: 'status',
    header: () => 'Status',
    cell: ({ row }) =>
      h(
        'span',
        {
          class: `rounded-sm px-2 py-1 text-xs font-semibold ${getStatusBadge(row.original.status)}`,
        },
        row.original.status.replace(/_/g, ' ').toUpperCase(),
      ),
  },
  {
    accessorKey: 'payment_status',
    header: () => 'Pembayaran',
    cell: ({ row }) =>
      h(
        'span',
        {
          class: row.original.payment_status === 'paid'
            ? 'rounded-sm bg-green-100 px-2 py-1 text-xs font-semibold text-green-700'
            : 'rounded-sm bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-700',
        },
        row.original.payment_status === 'paid' ? 'LUNAS' : row.original.payment_status.toUpperCase(),
      ),
  },
  {
    id: 'actions',
    header: () => 'Aksi',
    cell: ({ row }) =>
      h('div', { class: 'flex justify-end gap-3' }, [
        h(
          'span',
          {
            class:
              'inline-flex h-8 w-8 items-center justify-center rounded-full ' +
              'cursor-pointer text-slate-500 hover:bg-slate-100 hover:text-slate-900',
            onClick: () => router.visit(`/admin/orders/${row.original.id}`),
          },
          [h(Eye, { class: 'h-[15px] w-[15px]' })],
        ),
      ]),
    meta: { class: 'text-right w-24' },
  },
]);
</script>

<template>
  <div class="space-y-6">

    <Head title="Pesanan" />

    <Alert v-if="flashSuccess" variant="default" class="flex items-start gap-3 border-green-200 bg-green-50">
      <CheckCircle2 class="h-5 w-5 text-green-600" />
      <div class="space-y-1">
        <AlertTitle class="text-green-800">Berhasil</AlertTitle>
        <AlertDescription class="text-green-700">{{ flashSuccess }}</AlertDescription>
      </div>
    </Alert>

    <div class="flex flex-wrap items-center justify-between gap-3">
      <h1 class="text-2xl font-semibold text-slate-900">Manajemen Pesanan</h1>
    </div>

    <section class="space-y-4">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="relative w-full md:w-64">
          <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
            <Search class="h-4 w-4" />
          </span>
          <Input v-model="search" placeholder="Cari nomor pesanan" class="w-full pl-9" />
        </div>
      </div>

      <div class="overflow-hidden rounded-sm border border-slate-200 bg-white">
        <DataTable :columns="columns" :data="orders.data" />
      </div>

      <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">
        <p>Menampilkan {{ orders.data.length }} dari {{ orders.total }} pesanan.</p>
        <div class="flex flex-wrap items-center gap-1">
          <Button variant="outline" size="sm" :disabled="!orders.prev_page_url"
            @click="paginateTo(orders.prev_page_url)">
            Sebelumnya
          </Button>
          <Button v-for="link in numberedPaginationLinks" :key="link.label" size="sm"
            :variant="link.active ? 'default' : 'outline'" :disabled="!link.url" @click="paginateTo(link.url)">
            {{ link.label }}
          </Button>
          <Button variant="outline" size="sm" :disabled="!orders.next_page_url"
            @click="paginateTo(orders.next_page_url)">
            Selanjutnya
          </Button>
        </div>
      </div>
    </section>
  </div>
</template>
