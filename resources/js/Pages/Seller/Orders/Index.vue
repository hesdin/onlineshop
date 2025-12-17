<script setup lang="ts">
import SellerDashboardLayout from '@/Layouts/SellerDashboardLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, h, onBeforeUnmount, ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Alert } from '@/components/ui/alert';
import { DataTable } from '@/components/ui/data-table';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';

import { CheckCircle2, Eye, Filter, Search } from 'lucide-vue-next';
import type { ColumnDef } from '@tanstack/vue-table';

type StatusOption = {
  value: string;
  label: string;
};

type OrderRow = {
  id: number;
  order_number: string;
  status: string;
  payment_status: string;
  grand_total: number;
  customer: { name: string | null; email: string | null };
  created_at: string | null;
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
  statusOptions: StatusOption[];
  paymentStatusOptions: StatusOption[];
}>();

defineOptions({
  layout: SellerDashboardLayout,
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success ?? '');
const successVisible = ref(false);
let successTimeout: ReturnType<typeof setTimeout> | null = null;

watch(
  flashSuccess,
  (value) => {
    if (successTimeout) {
      clearTimeout(successTimeout);
      successTimeout = null;
    }

    if (value) {
      successVisible.value = true;
      successTimeout = setTimeout(() => {
        successVisible.value = false;
      }, 3000);
    } else {
      successVisible.value = false;
    }
  },
  { immediate: true },
);

onBeforeUnmount(() => {
  if (successTimeout) {
    clearTimeout(successTimeout);
  }
});

const normalizeFilterValue = (value?: string | null) => (value && value.length ? value : 'all');

const search = ref(props.filters.search ?? '');
const appliedStatusFilter = ref(normalizeFilterValue(props.filters.status));
const statusFilterInput = ref(appliedStatusFilter.value);
const filterPopoverOpen = ref(false);

watch(
  () => props.filters.status,
  (value) => {
    const normalized = normalizeFilterValue(value);
    appliedStatusFilter.value = normalized;
    statusFilterInput.value = normalized;
  },
);

const currencyFormatter = new Intl.NumberFormat('id-ID', {
  style: 'currency',
  currency: 'IDR',
  minimumFractionDigits: 0,
});

const formatCurrency = (value?: number | null) => currencyFormatter.format(value ?? 0);

const orderStatusBadge = (status: string) => {
  switch (status) {
    case 'pending_payment':
      return { label: 'Menunggu Bayar', class: 'bg-amber-100 text-amber-700' };
    case 'processing':
      return { label: 'Diproses', class: 'bg-blue-100 text-blue-700' };
    case 'shipped':
      return { label: 'Dikirim', class: 'bg-indigo-100 text-indigo-700' };
    case 'delivered':
      return { label: 'Diterima', class: 'bg-emerald-100 text-emerald-700' };
    case 'completed':
      return { label: 'Selesai', class: 'bg-emerald-100 text-emerald-700' };
    case 'cancelled':
      return { label: 'Dibatalkan', class: 'bg-slate-100 text-slate-600' };
    default:
      return { label: status, class: 'bg-slate-100 text-slate-600' };
  }
};

const paymentStatusBadge = (status: string) => {
  switch (status) {
    case 'paid':
      return { label: 'Dibayar', class: 'bg-emerald-100 text-emerald-700' };
    case 'pending':
      return { label: 'Menunggu', class: 'bg-amber-100 text-amber-700' };
    case 'expired':
      return { label: 'Kedaluwarsa', class: 'bg-slate-100 text-slate-600' };
    case 'failed':
      return { label: 'Gagal', class: 'bg-rose-100 text-rose-700' };
    default:
      return { label: status, class: 'bg-slate-100 text-slate-600' };
  }
};

const buildQuery = (override: Record<string, unknown> = {}) => ({
  search: search.value || undefined,
  status: appliedStatusFilter.value === 'all' ? undefined : appliedStatusFilter.value || undefined,
  ...override,
});

const debouncedSearch = useDebounceFn((value: string) => {
  router.get('/seller/orders', buildQuery({ search: value || undefined }), {
    preserveState: true,
    replace: true,
    preserveScroll: true,
  });
}, 400);

watch(search, (value) => debouncedSearch(value));

const applyFilters = () => {
  appliedStatusFilter.value = statusFilterInput.value || 'all';
  router.get('/seller/orders', buildQuery(), {
    preserveState: true,
    replace: true,
    preserveScroll: true,
  });
  filterPopoverOpen.value = false;
};

const clearFilterInputs = () => {
  statusFilterInput.value = 'all';
};

const resetFilters = () => {
  search.value = '';
  appliedStatusFilter.value = 'all';
  clearFilterInputs();
  router.get('/seller/orders', buildQuery(), {
    preserveState: true,
    replace: true,
    preserveScroll: true,
  });
};

const hasActiveFilters = computed(
  () => !!search.value || appliedStatusFilter.value !== 'all',
);

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

const columns = computed<ColumnDef<OrderRow>[]>(() => [
  {
    id: 'order',
    accessorKey: 'order_number',
    header: () => 'Pesanan',
    cell: ({ row }) =>
      h('div', { class: 'space-y-0.5' }, [
        h('p', { class: 'font-semibold text-slate-900' }, row.original.order_number),
        h('p', { class: 'text-xs text-slate-500' }, row.original.created_at ?? '-'),
      ]),
    meta: { class: 'min-w-[160px]' },
  },
  {
    id: 'customer',
    header: () => 'Pelanggan',
    cell: ({ row }) =>
      h('div', { class: 'space-y-0.5' }, [
        h('p', { class: 'font-medium text-slate-800' }, row.original.customer.name ?? 'Tidak diketahui'),
        row.original.customer.email
          ? h('p', { class: 'text-xs text-slate-500' }, row.original.customer.email)
          : null,
      ]),
    meta: { class: 'min-w-[180px]' },
  },
  {
    id: 'total',
    header: () => 'Total',
    cell: ({ row }) =>
      h('p', { class: 'font-semibold text-slate-900' }, formatCurrency(row.original.grand_total)),
    meta: { class: 'min-w-[120px]' },
  },
  {
    id: 'status',
    header: () => 'Status',
    cell: ({ row }) => {
      const badge = orderStatusBadge(row.original.status);
      return h(
        'span',
        {
          class: `inline-flex items-center rounded-sm px-3 py-1 text-xs font-semibold ${badge.class}`,
        },
        badge.label,
      );
    },
    meta: { class: 'w-36' },
  },
  {
    id: 'payment',
    header: () => 'Pembayaran',
    cell: ({ row }) => {
      const badge = paymentStatusBadge(row.original.payment_status);
      return h(
        'span',
        {
          class: `inline-flex items-center rounded-sm px-3 py-1 text-xs font-semibold ${badge.class}`,
        },
        badge.label,
      );
    },
    meta: { class: 'w-32' },
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
            onClick: () => router.visit(`/seller/orders/${row.original.id}`),
          },
          [h(Eye, { class: 'h-[15px] w-[15px]' })],
        ),
      ]),
    meta: { class: 'text-right w-20' },
    enableHiding: false,
  },
]);
</script>

<template>
  <div class="space-y-6 w-full">

    <Head title="Pesanan" />

    <Alert v-if="successVisible && flashSuccess" variant="default"
      class="flex items-center gap-2 border-green-200 bg-green-50 text-sm font-medium text-green-700">
      <CheckCircle2 class="h-5 w-5 text-green-600" />
      <span>{{ flashSuccess }}</span>
    </Alert>

    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <h1 class="text-2xl font-semibold text-slate-900">Pesanan Masuk</h1>
        <p class="text-sm text-slate-500">Pantau dan update status pesanan toko.</p>
      </div>
    </div>

    <section class="space-y-4">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="relative w-full md:w-64">
          <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
            <Search class="h-4 w-4" />
          </span>
          <Input v-model="search" placeholder="Cari nomor pesanan"
            class="w-full border border-slate-200 bg-white pl-9" />
        </div>

        <div class="flex flex-wrap items-center gap-3">
          <Button v-if="hasActiveFilters" variant="outline" class="gap-2" @click="resetFilters">
            Reset
          </Button>

          <div class="flex items-center gap-2">
            <Popover v-model:open="filterPopoverOpen">
              <PopoverTrigger as-child>
                <button type="button"
                  class="inline-flex items-center gap-2 rounded-md border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 transition hover:text-sky-600">
                  <Filter class="h-4 w-4" />
                  Filter
                </button>
              </PopoverTrigger>
              <PopoverContent align="end" class="w-72 space-y-4 p-4">
                <div>
                  <p class="text-sm font-semibold text-slate-900">Filter Pesanan</p>
                </div>
                <div class="space-y-4 text-left">
                  <div class="space-y-2">
                    <label class="text-xs font-semibold text-slate-500">Status Pesanan</label>
                    <Select v-model="statusFilterInput">
                      <SelectTrigger class="w-full bg-white">
                        <SelectValue placeholder="Semua status" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="all">Semua status</SelectItem>
                        <SelectItem v-for="option in statusOptions" :key="option.value" :value="option.value">
                          {{ option.label }}
                        </SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                </div>
                <div class="flex items-center justify-between pt-2">
                  <button type="button" class="text-sm font-medium text-slate-500 hover:text-slate-700"
                    @click="clearFilterInputs">
                    Reset
                  </button>
                  <Button type="button" class="px-4" @click="applyFilters">
                    Apply Filter
                  </Button>
                </div>
              </PopoverContent>
            </Popover>
          </div>
        </div>
      </div>

      <div class="overflow-hidden rounded-sm border border-slate-200 bg-white">
        <DataTable :columns="columns" :data="orders.data" />
      </div>

      <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">
        <p>
          Menampilkan {{ orders.data.length }} dari
          {{ orders.total }} pesanan.
        </p>
        <div class="flex flex-wrap items-center gap-1">
          <Button variant="outline" size="sm" :disabled="!orders.prev_page_url"
            @click="paginateTo(orders.prev_page_url)">
            Sebelumnya
          </Button>
          <Button v-for="link in numberedPaginationLinks" :key="link.label" size="sm"
            :variant="link.active ? 'default' : 'outline'" :aria-current="link.active ? 'page' : undefined"
            :disabled="!link.url" @click="paginateTo(link.url)">
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
