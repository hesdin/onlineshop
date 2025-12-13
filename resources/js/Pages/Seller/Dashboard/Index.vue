<script setup lang="ts">
import SellerDashboardLayout from '@/Layouts/SellerDashboardLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Separator } from '@/components/ui/separator';

import {
  AlertTriangle,
  ArrowUpRight,
  CheckCircle2,
  MapPin,
  Package,
  ShieldCheck,
  ShoppingBag,
  Store as StoreIcon,
  TrendingUp,
} from 'lucide-vue-next';

type StoreSummary = {
  id: number;
  name: string;
  slug: string;
  city: string | null;
  province: string | null;
  tagline: string | null;
  type: string;
  tax_status: string;
  is_verified: boolean;
  response_time_label: string | null;
};

type Stat = {
  label: string;
  value: number;
  helper?: string;
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

type ProductHighlight = {
  product_id: number;
  name: string;
  total_sold: number;
  total_amount: number;
};

const props = defineProps<{
  store: StoreSummary | null;
  needsStoreSetup: boolean;
  stats: Stat[];
  recentOrders: OrderRow[];
  topProducts: ProductHighlight[];
}>();

defineOptions({
  layout: SellerDashboardLayout,
});

const currencyFormatter = new Intl.NumberFormat('id-ID', {
  style: 'currency',
  currency: 'IDR',
  minimumFractionDigits: 0,
});

const formatCurrency = (value?: number | null) => currencyFormatter.format(value ?? 0);

const storeLocation = computed(() => {
  if (!props.store) return '';
  if (props.store.city && props.store.province) return `${props.store.city}, ${props.store.province}`;
  return props.store.city ?? props.store.province ?? '';
});

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
</script>

<template>
  <div class="space-y-6">

    <Head title="Dashboard Seller" />

    <div class="flex flex-wrap items-center justify-between gap-3">
      <div class="space-y-1">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
          Panel Seller
        </p>
        <div class="flex items-center gap-2">
          <h1 class="text-2xl font-bold text-slate-900">
            {{ store?.name ?? 'Mulai setup toko' }}
          </h1>
          <Badge v-if="store?.is_verified" variant="secondary" class="gap-1 bg-emerald-50 text-emerald-700">
            <ShieldCheck class="h-3.5 w-3.5" />
            Terverifikasi
          </Badge>
        </div>
        <p class="text-sm text-slate-600">
          {{ store?.tagline ?? 'Lengkapi profil toko untuk mulai berjualan.' }}
        </p>
        <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
          <span class="inline-flex items-center gap-1">
            <MapPin class="h-3.5 w-3.5" />
            {{ storeLocation || 'Lokasi belum diisi' }}
          </span>
          <span v-if="store?.response_time_label" class="inline-flex items-center gap-1">
            <TrendingUp class="h-3.5 w-3.5" />
            Respon {{ store.response_time_label }}
          </span>
        </div>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <Button variant="outline" as-child>
          <Link href="/seller/store">
          Kelola Toko
          </Link>
        </Button>
        <Button as-child>
          <Link href="/seller/products/create">
          <Package class="mr-2 h-4 w-4" />
          Tambah Produk
          </Link>
        </Button>
      </div>
    </div>

    <Alert v-if="needsStoreSetup" variant="default" class="border-amber-200 bg-amber-50">
      <AlertTriangle class="h-4 w-4" />
      <AlertTitle>Profil toko belum selesai</AlertTitle>
      <AlertDescription>
        Buat atau lengkapi data toko terlebih dahulu sebelum menambahkan produk dan memproses pesanan.
        <Button as-child size="sm" class="ml-3">
          <Link href="/seller/store">Lengkapi Toko</Link>
        </Button>
      </AlertDescription>
    </Alert>

    <template v-else>
      <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <Card v-for="stat in stats" :key="stat.label" class="border-slate-200 bg-white">
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium text-slate-600">
              {{ stat.label }}
            </CardTitle>
            <ArrowUpRight class="h-4 w-4 text-slate-400" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-slate-900">
              {{ stat.label.toLowerCase().includes('pendapatan') ? formatCurrency(stat.value) : stat.value }}
            </div>
            <p class="text-xs text-slate-500">
              {{ stat.helper }}
            </p>
          </CardContent>
        </Card>
      </div>

      <div class="grid gap-6 lg:grid-cols-3">
        <Card class="lg:col-span-2">
          <CardHeader class="flex flex-wrap items-center justify-between gap-3">
            <div>
              <CardTitle>Ringkasan Pesanan</CardTitle>
              <CardDescription>Lacak pesanan terbaru yang perlu ditindaklanjuti.</CardDescription>
            </div>
            <Button variant="outline" size="sm" as-child>
              <Link href="/seller/orders">
              <ShoppingBag class="mr-2 h-4 w-4" />
              Lihat Semua
              </Link>
            </Button>
          </CardHeader>
          <CardContent>
            <div class="overflow-hidden rounded-md border border-slate-200">
              <table class="w-full text-left text-sm">
                <thead class="bg-slate-50">
                  <tr class="text-xs uppercase text-slate-500">
                    <th class="px-4 py-3">Pesanan</th>
                    <th class="px-4 py-3">Pelanggan</th>
                    <th class="px-4 py-3">Total</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Pembayaran</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="order in recentOrders" :key="order.id"
                    class="border-t border-slate-100 transition hover:bg-slate-50/60">
                    <td class="px-4 py-3 font-semibold text-slate-900">
                      {{ order.order_number }}
                    </td>
                    <td class="px-4 py-3">
                      <p class="font-medium text-slate-800">{{ order.customer.name ?? 'Tidak diketahui' }}</p>
                      <p class="text-xs text-slate-500">{{ order.created_at }}</p>
                    </td>
                    <td class="px-4 py-3 text-sm font-semibold text-slate-900">
                      {{ formatCurrency(order.grand_total) }}
                    </td>
                    <td class="px-4 py-3">
                      <span
                        :class="['inline-flex items-center rounded-full px-3 py-1 text-[11px] font-semibold', orderStatusBadge(order.status).class]">
                        {{ orderStatusBadge(order.status).label }}
                      </span>
                    </td>
                    <td class="px-4 py-3">
                      <span
                        :class="['inline-flex items-center rounded-full px-3 py-1 text-[11px] font-semibold', paymentStatusBadge(order.payment_status).class]">
                        {{ paymentStatusBadge(order.payment_status).label }}
                      </span>
                    </td>
                  </tr>
                  <tr v-if="recentOrders.length === 0">
                    <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">
                      Belum ada pesanan untuk toko ini.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <div class="flex items-center justify-between gap-3">
              <div>
                <CardTitle>Performa Produk</CardTitle>
                <CardDescription>Produk dengan penjualan terbaik.</CardDescription>
              </div>
              <Package class="h-5 w-5 text-slate-400" />
            </div>
          </CardHeader>
          <CardContent class="space-y-3">
            <div v-for="product in topProducts" :key="product.product_id"
              class="rounded-md border border-slate-100 bg-slate-50/60 p-3">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <p class="font-semibold text-slate-900">{{ product.name }}</p>
                  <p class="text-xs text-slate-500">Terjual {{ product.total_sold }} pcs</p>
                </div>
                <Badge variant="secondary" class="bg-emerald-50 text-emerald-700">
                  {{ formatCurrency(product.total_amount) }}
                </Badge>
              </div>
            </div>
            <div v-if="topProducts.length === 0" class="rounded-md border border-dashed border-slate-200 p-4 text-center">
              <p class="text-sm font-medium text-slate-700">Belum ada transaksi</p>
              <p class="text-xs text-slate-500">Mulai promosikan produk untuk melihat performanya.</p>
            </div>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader class="flex items-center justify-between gap-3">
          <div>
            <CardTitle>Checklist toko</CardTitle>
            <CardDescription>Pastikan toko selalu siap menerima pesanan.</CardDescription>
          </div>
          <StoreIcon class="h-5 w-5 text-slate-400" />
        </CardHeader>
        <CardContent class="grid gap-4 md:grid-cols-3">
          <div class="rounded-md border border-slate-200 p-4">
            <div class="flex items-center gap-2 text-slate-700">
              <CheckCircle2 class="h-4 w-4 text-emerald-600" />
              <p class="font-semibold">Katalog</p>
            </div>
            <p class="text-xs text-slate-500 mt-2">
              Perbarui stok dan status produk secara berkala.
            </p>
            <Separator class="my-3" />
            <Button as-child variant="ghost" size="sm" class="px-0 text-blue-600">
              <Link href="/seller/products">
              Kelola produk
              </Link>
            </Button>
          </div>

          <div class="rounded-md border border-slate-200 p-4">
            <div class="flex items-center gap-2 text-slate-700">
              <ShoppingBag class="h-4 w-4 text-blue-600" />
              <p class="font-semibold">Pesanan</p>
            </div>
            <p class="text-xs text-slate-500 mt-2">
              Respon pesanan baru dan update status pengiriman.
            </p>
            <Separator class="my-3" />
            <Button as-child variant="ghost" size="sm" class="px-0 text-blue-600">
              <Link href="/seller/orders">
              Pantau pesanan
              </Link>
            </Button>
          </div>

          <div class="rounded-md border border-slate-200 p-4">
            <div class="flex items-center gap-2 text-slate-700">
              <ShieldCheck class="h-4 w-4 text-indigo-600" />
              <p class="font-semibold">Profil toko</p>
            </div>
            <p class="text-xs text-slate-500 mt-2">
              Lengkapi informasi toko agar lebih dipercaya pembeli.
            </p>
            <Separator class="my-3" />
            <Button as-child variant="ghost" size="sm" class="px-0 text-blue-600">
              <Link href="/seller/store">
              Ubah profil toko
              </Link>
            </Button>
          </div>
        </CardContent>
      </Card>
    </template>
  </div>
</template>
