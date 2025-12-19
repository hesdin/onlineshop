<script setup lang="ts">
import SellerDashboardLayout from '@/Layouts/SellerDashboardLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler,
} from 'chart.js';
import { Line } from 'vue-chartjs';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import {
  AlertTriangle,
  ArrowRight,
  DollarSign,
  MapPin,
  Package,
  ShoppingBag,
  Store as StoreIcon,
  TrendingUp,
  Clock,
} from 'lucide-vue-next';

// Register ChartJS components
ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
);

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
  image_url?: string;
};

const props = defineProps<{
  store: StoreSummary | null;
  needsStoreSetup: boolean;
  stats: Stat[];
  recentOrders: OrderRow[];
  topProducts: ProductHighlight[];
  revenueChart?: {
    labels: string[];
    data: number[];
  };
}>();

defineOptions({
  layout: SellerDashboardLayout,
});

const currencyFormatter = new Intl.NumberFormat('id-ID', {
  style: 'currency',
  currency: 'IDR',
  minimumFractionDigits: 0,
  maximumFractionDigits: 0,
});

const formatCurrency = (value?: number | null) => currencyFormatter.format(value ?? 0);

const storeLocation = computed(() => {
  if (!props.store) return '';
  if (props.store.city && props.store.province) return `${props.store.city}, ${props.store.province}`;
  return props.store.city ?? props.store.province ?? '';
});

// Chart Configuration
const chartData = computed(() => ({
  labels: props.revenueChart?.labels ?? [],
  datasets: [
    {
      label: 'Pendapatan',
      backgroundColor: (ctx: any) => {
        const canvas = ctx.chart.ctx;
        const gradient = canvas.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(16, 185, 129, 0.2)');
        gradient.addColorStop(1, 'rgba(16, 185, 129, 0)');
        return gradient;
      },
      borderColor: '#10b981', // emerald-500
      borderWidth: 2,
      pointBackgroundColor: '#ffffff',
      pointBorderColor: '#10b981',
      pointBorderWidth: 2,
      pointRadius: 4,
      pointHoverRadius: 6,
      fill: true,
      tension: 0.4,
      data: props.revenueChart?.data ?? [],
    },
  ],
}));

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false,
    },
    tooltip: {
      backgroundColor: '#1e293b',
      padding: 12,
      titleFont: { size: 13 },
      bodyFont: { size: 13 },
      displayColors: false,
      callbacks: {
        label: (context: any) => currencyFormatter.format(context.raw),
      },
    },
  },
  scales: {
    y: {
      grid: {
        color: '#f1f5f9',
      },
      ticks: {
        callback: (value: any) => 'Rp ' + (value / 1000000).toFixed(1) + 'jt',
        font: { size: 11 },
        color: '#64748b',
      },
      border: { display: false },
    },
    x: {
      grid: { display: false },
      ticks: {
        font: { size: 11 },
        color: '#64748b',
      },
      border: { display: false },
    },
  },
};

const getStatIcon = (label: string) => {
  const l = label.toLowerCase();
  if (l.includes('pendapatan')) return DollarSign;
  if (l.includes('pesanan')) return ShoppingBag;
  if (l.includes('produk')) return Package;
  return TrendingUp;
};

const getStatColor = (label: string) => {
  const l = label.toLowerCase();
  if (l.includes('pendapatan')) return 'text-emerald-600 bg-emerald-50';
  if (l.includes('pesanan')) return 'text-blue-600 bg-blue-50';
  if (l.includes('produk')) return 'text-orange-600 bg-orange-50';
  return 'text-indigo-600 bg-indigo-50';
};

const orderStatusBadge = (status: string) => {
  const styles: Record<string, string> = {
    pending_payment: 'bg-amber-50 text-amber-700 border-amber-200',
    processing: 'bg-blue-50 text-blue-700 border-blue-200',
    shipped: 'bg-indigo-50 text-indigo-700 border-indigo-200',
    delivered: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    completed: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    cancelled: 'bg-slate-50 text-slate-600 border-slate-200',
  };

  const labels: Record<string, string> = {
    pending_payment: 'Menunggu Bayar',
    processing: 'Diproses',
    shipped: 'Dikirim',
    delivered: 'Diterima',
    completed: 'Selesai',
    cancelled: 'Dibatalkan',
  };

  return {
    class: styles[status] || 'bg-slate-50 text-slate-600 border-slate-200',
    label: labels[status] || status,
  };
};
</script>

<template>
  <div class="space-y-8 animate-in fade-in duration-500">

    <Head title="Dashboard Seller" />

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div>
        <h1 class="text-3xl font-bold tracking-tight text-slate-900">
          Dashboard
        </h1>
        <p class="text-slate-500 mt-1">
          Selamat datang kami di <span class="font-semibold text-slate-700">{{ store?.name ?? 'Toko Anda' }}</span>.
        </p>
      </div>
      <div class="flex items-center gap-2">
        <Button variant="outline" class="hidden sm:flex" as-child>
          <Link href="/seller/settings">
            <StoreIcon class="mr-2 h-4 w-4" />
            Kelola Toko
          </Link>
        </Button>
        <Button class="active:scale-95 transition-transform" as-child>
          <Link href="/seller/products/create">
            <Package class="mr-2 h-4 w-4" />
            Tambah Produk
          </Link>
        </Button>
      </div>
    </div>

    <!-- Alert Setup -->
    <Alert v-if="needsStoreSetup" variant="default" class="border-amber-200 bg-amber-50 text-amber-800">
      <AlertTriangle class="h-4 w-4 text-amber-600" />
      <AlertTitle class="text-amber-800">Lengkapi Profil Toko</AlertTitle>
      <AlertDescription class="text-amber-700/90 mt-1">
        Agar toko terlihat profesional dan dipercaya pembeli, segera lengkapi informasi toko Anda.
        <Link href="/seller/settings" class="font-medium underline underline-offset-4 hover:text-amber-900 ml-1">
          Setup sekarang &rarr;
        </Link>
      </AlertDescription>
    </Alert>

    <template v-else>
      <!-- Stats Grid -->
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <Card v-for="(stat, i) in stats" :key="stat.label"
          class="overflow-hidden border-slate-200 shadow-sm hover:shadow-md transition-shadow group relative">
          <CardContent class="p-6">
            <div class="flex items-center justify-between">
              <div :class="['p-2 rounded-lg', getStatColor(stat.label)]">
                <component :is="getStatIcon(stat.label)" class="h-5 w-5" />
              </div>
              <span
                class="text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full flex items-center gap-1">
                <TrendingUp class="h-3 w-3" />
                +12%
              </span>
            </div>
            <div class="mt-4">
              <p class="text-sm font-medium text-slate-500">{{ stat.label }}</p>
              <h3 class="text-2xl font-bold text-slate-900 mt-1">
                {{ stat.label.toLowerCase().includes('pendapatan') ? formatCurrency(stat.value) : stat.value }}
              </h3>
              <p class="text-xs text-slate-400 mt-1">{{ stat.helper }}</p>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Main Content Grid -->
      <div class="grid gap-6 lg:grid-cols-7">

        <!-- Chart Section -->
        <Card class="lg:col-span-4 border-slate-200 shadow-sm order-2 lg:order-1">
          <CardHeader>
            <CardTitle>Analisis Pendapatan</CardTitle>
            <CardDescription>Grafik pendapatan toko dalam 7 hari terakhir.</CardDescription>
          </CardHeader>
          <CardContent class="pl-2">
            <div class="h-[300px] w-full">
              <Line :data="chartData" :options="chartOptions" />
            </div>
          </CardContent>
        </Card>

        <!-- Top Products -->
        <Card class="lg:col-span-3 border-slate-200 shadow-sm order-1 lg:order-2">
          <CardHeader>
            <CardTitle>Produk Terlaris</CardTitle>
            <CardDescription>Produk dengan performa penjualan terbaik.</CardDescription>
          </CardHeader>
          <CardContent>
            <div class="space-y-6">
              <div v-for="(product, index) in topProducts.slice(0, 4)" :key="product.product_id"
                class="flex items-center gap-4">
                <div
                  class="relative h-12 w-12 rounded-lg bg-slate-100 flex items-center justify-center overflow-hidden border border-slate-100">
                  <Package v-if="!product.image_url" class="h-5 w-5 text-slate-400" />
                  <img v-else :src="product.image_url" :alt="product.name" class="h-full w-full object-cover" />
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-slate-900 truncate">{{ product.name }}</p>
                  <p class="text-xs text-slate-500 mt-0.5">
                    {{ product.total_sold }} terjual
                  </p>
                </div>
                <div class="text-right">
                  <p class="text-sm font-semibold text-slate-900">{{ formatCurrency(product.total_amount) }}</p>
                  <Badge variant="secondary"
                    class="mt-1 text-[10px] h-5 px-1.5 bg-slate-100 text-slate-600 border-none">
                    #{{ index + 1 }}
                  </Badge>
                </div>
              </div>

              <div v-if="topProducts.length === 0" class="flex flex-col items-center justify-center py-8 text-center">
                <div class="h-12 w-12 rounded-full bg-slate-50 flex items-center justify-center mb-3">
                  <Package class="h-6 w-6 text-slate-300" />
                </div>
                <p class="text-sm font-medium text-slate-500">Belum ada data produk</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Recent Orders & Actions -->
      <div class="grid gap-6 lg:grid-cols-3">
        <!-- Recent Orders Table -->
        <Card class="lg:col-span-2 border-slate-200 shadow-sm">
          <CardHeader class="flex flex-row items-center justify-between space-y-0">
            <div>
              <CardTitle>Pesanan Terbaru</CardTitle>
              <CardDescription>Pesanan yang perlu diproses segera.</CardDescription>
            </div>
            <Button variant="outline" size="sm" as-child>
              <Link href="/seller/orders" class="text-xs">
                Lihat Semua
                <ArrowRight class="ml-2 h-3 w-3" />
              </Link>
            </Button>
          </CardHeader>
          <CardContent>
            <div class="relative overflow-x-auto rounded-md border border-slate-100">
              <table class="w-full text-left text-sm">
                <thead class="bg-slate-50/80 text-xs uppercase text-slate-500">
                  <tr>
                    <th class="px-4 py-3 font-medium">Order ID</th>
                    <th class="px-4 py-3 font-medium">Pelanggan</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium text-right">Total</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr v-for="order in recentOrders" :key="order.id" class="hover:bg-slate-50/50 transition-colors">
                    <td
                      class="px-4 py-3 font-medium text-slate-900 border-l-2 border-transparent hover:border-emerald-500">
                      {{ order.order_number }}
                    </td>
                    <td class="px-4 py-3">
                      <div class="flex items-center gap-2">
                        <div
                          class="h-6 w-6 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-500">
                          {{ order.customer.name?.charAt(0) || '?' }}
                        </div>
                        <span class="text-slate-700 truncate max-w-[120px]">{{ order.customer.name ?? 'Guest' }}</span>
                      </div>
                    </td>
                    <td class="px-4 py-3">
                      <span
                        :class="['inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-medium capitalize', orderStatusBadge(order.status).class]">
                        {{ orderStatusBadge(order.status).label }}
                      </span>
                    </td>
                    <td class="px-4 py-3 text-right font-medium text-slate-900">
                      {{ formatCurrency(order.grand_total) }}
                    </td>
                  </tr>
                  <tr v-if="recentOrders.length === 0">
                    <td colspan="4" class="py-8 text-center text-slate-500">
                      Belum ada pesanan terbaru.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </CardContent>
        </Card>

        <!-- Quick Actions / Status -->
        <Card class="border-slate-200 shadow-sm">
          <CardHeader>
            <CardTitle>Status Toko</CardTitle>
            <CardDescription>Informasi penting toko Anda.</CardDescription>
          </CardHeader>
          <CardContent class="grid gap-4">
            <div class="flex items-center justify-between rounded-lg border border-slate-100 p-3 shadow-sm bg-white">
              <div class="flex items-center gap-3">
                <div class="rounded-md bg-blue-50 p-2 text-blue-600">
                  <Clock class="h-4 w-4" />
                </div>
                <div>
                  <p class="text-sm font-medium text-slate-900">Respon Pesanan</p>
                  <p class="text-xs text-slate-500">{{ store?.response_time_label || '-' }}</p>
                </div>
              </div>
            </div>

            <div class="flex items-center justify-between rounded-lg border border-slate-100 p-3 shadow-sm bg-white">
              <div class="flex items-center gap-3">
                <div class="rounded-md bg-purple-50 p-2 text-purple-600">
                  <MapPin class="h-4 w-4" />
                </div>
                <div>
                  <p class="text-sm font-medium text-slate-900">Lokasi Pengiriman</p>
                  <p class="text-xs text-slate-500 truncate max-w-[150px]">{{ storeLocation || 'Belum diatur' }}</p>
                </div>
              </div>
            </div>

            <StoreIcon class="hidden" /> <!-- ensure import usage -->

            <div class="mt-2 rounded-lg bg-slate-50 p-3 text-xs text-slate-500">
              <p class="font-medium text-slate-700 mb-1">Tips:</p>
              Rutin update stok dan proses pesanan cepat untuk meningkatkan rating toko.
            </div>
          </CardContent>
        </Card>
      </div>
    </template>
  </div>
</template>
