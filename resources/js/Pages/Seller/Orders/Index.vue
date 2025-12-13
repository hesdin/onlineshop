<script setup lang="ts">
import SellerDashboardLayout from '@/Layouts/SellerDashboardLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';

import { CheckCircle2, Search, ShoppingBag } from 'lucide-vue-next';

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

const search = ref(props.filters.search ?? '');
const statusFilter = ref(props.filters.status ?? 'all');

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
  status: statusFilter.value === 'all' ? undefined : statusFilter.value || undefined,
  ...override,
});

const debouncedSearch = useDebounceFn((value: string) => {
  router.get('/seller/orders', buildQuery({ search: value || undefined }), {
    preserveScroll: true,
    replace: true,
    preserveState: true,
  });
}, 350);

watch(search, (value) => debouncedSearch(value));

watch(statusFilter, () => {
  router.get('/seller/orders', buildQuery(), {
    preserveScroll: true,
    replace: true,
    preserveState: true,
  });
});

const resetFilters = () => {
  search.value = '';
  statusFilter.value = 'all';
};

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
</script>

<template>
  <div class="space-y-6">

    <Head title="Pesanan" />

    <Alert v-if="flashSuccess" variant="default" class="flex items-start gap-3 border-green-200 bg-green-50">
      <CheckCircle2 class="h-5 w-5 text-green-600" />
      <div class="space-y-1">
        <AlertTitle class="text-green-800">Berhasil</AlertTitle>
        <AlertDescription class="text-green-700">
          {{ flashSuccess }}
        </AlertDescription>
      </div>
    </Alert>

    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <h1 class="text-2xl font-semibold text-slate-900">Pesanan</h1>
        <p class="text-sm text-slate-500">Pantau dan update status pesanan toko.</p>
      </div>
      <Button variant="outline" size="sm" as-child>
        <Link href="/seller/dashboard">
        <ShoppingBag class="mr-2 h-4 w-4" />
        Ringkasan
        </Link>
      </Button>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3">
      <div class="relative w-full md:w-72">
        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
          <Search class="h-4 w-4" />
        </span>
        <Input v-model="search" placeholder="Cari nomor pesanan" class="w-full pl-9" />
      </div>

      <div class="flex items-center gap-2">
        <Select v-model="statusFilter">
          <SelectTrigger class="w-44">
            <SelectValue placeholder="Status pesanan" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">Semua status</SelectItem>
            <SelectItem v-for="option in statusOptions" :key="option.value" :value="option.value">
              {{ option.label }}
            </SelectItem>
          </SelectContent>
        </Select>

        <Button v-if="search || statusFilter !== 'all'" variant="outline" size="sm" @click="resetFilters">
          Reset
        </Button>
      </div>
    </div>

    <div class="w-full overflow-x-auto rounded-md border border-slate-200 bg-white">
      <table class="w-full text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase text-slate-500">
          <tr>
            <th class="px-4 py-3">Pesanan</th>
            <th class="px-4 py-3">Pelanggan</th>
            <th class="px-4 py-3">Total</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">Pembayaran</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in orders.data" :key="order.id"
            class="border-t border-slate-100 transition hover:bg-slate-50/60">
            <td class="px-4 py-3">
              <p class="font-semibold text-slate-900">{{ order.order_number }}</p>
              <p class="text-xs text-slate-500">{{ order.created_at }}</p>
            </td>
            <td class="px-4 py-3">
              <p class="font-medium text-slate-800">{{ order.customer.name ?? 'Tidak diketahui' }}</p>
              <p class="text-xs text-slate-500">{{ order.customer.email }}</p>
            </td>
            <td class="px-4 py-3 font-semibold text-slate-900">
              {{ formatCurrency(order.grand_total) }}
            </td>
            <td class="px-4 py-3">
              <Badge :class="orderStatusBadge(order.status).class" variant="secondary">
                {{ orderStatusBadge(order.status).label }}
              </Badge>
            </td>
            <td class="px-4 py-3">
              <Badge :class="paymentStatusBadge(order.payment_status).class" variant="secondary">
                {{ paymentStatusBadge(order.payment_status).label }}
              </Badge>
            </td>
            <td class="px-4 py-3 text-right">
              <Button size="sm" variant="outline" as-child>
                <Link :href="`/seller/orders/${order.id}`">Detail</Link>
              </Button>
            </td>
          </tr>
          <tr v-if="orders.data.length === 0">
            <td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500">
              Belum ada pesanan.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">
      <p>
        Menampilkan {{ orders.data.length }} dari
        {{ orders.total }} pesanan.
      </p>
      <div class="flex flex-wrap items-center gap-1">
        <Button variant="outline" size="sm" :disabled="!orders.prev_page_url" @click="paginateTo(orders.prev_page_url)">
          Sebelumnya
        </Button>
        <Button v-for="link in numberedPaginationLinks" :key="link.label" size="sm"
          :variant="link.active ? 'default' : 'outline'" :aria-current="link.active ? 'page' : undefined"
          :disabled="!link.url" @click="paginateTo(link.url)">
          {{ link.label }}
        </Button>
        <Button variant="outline" size="sm" :disabled="!orders.next_page_url" @click="paginateTo(orders.next_page_url)">
          Selanjutnya
        </Button>
      </div>
    </div>
  </div>
</template>
