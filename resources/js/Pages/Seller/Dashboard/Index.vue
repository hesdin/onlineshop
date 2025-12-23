<script setup lang="ts">
import SellerDashboardLayout from '@/Layouts/SellerDashboardLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
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
import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';
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
  Calendar,
  ChevronRight,
  MoreHorizontal,
  CreditCard,
  Download,
  Filter,
  Users,
  AlertCircle,
  PackageX,
  Zap
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
  growth?: number;
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

type LowStockProduct = {
  id: number;
  name: string;
  stock: number;
  image_url?: string | null;
};

type OrderNeedingAction = {
  id: number;
  order_number: string;
  status: string;
  grand_total: number;
  customer_name: string | null;
  created_at: string | null;
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
  // New props
  lowStockProducts?: LowStockProduct[];
  outOfStockCount?: number;
  ordersNeedingAction?: OrderNeedingAction[];
  todayStats?: {
    orders: number;
    revenue: number;
  };
  totalCustomers?: number;
  avgOrderValue?: number;
  growthData?: {
    revenue: number;
    orders: number;
    products: number;
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

const timeGreeting = computed(() => {
  const hour = new Date().getHours();
  if (hour < 11) return 'Selamat Pagi';
  if (hour < 15) return 'Selamat Siang';
  if (hour < 18) return 'Selamat Sore';
  return 'Selamat Malam';
});

// Calculate total revenue from chart data for display
const totalChartRevenue = computed(() => {
  if (!props.revenueChart?.data) return 0;
  return props.revenueChart.data.reduce((a, b) => Number(a) + Number(b), 0);
});

// Chart Configuration - Using CSS theme variables
const chartData = computed(() => ({
  labels: props.revenueChart?.labels ?? [],
  datasets: [
    {
      label: 'Pendapatan',
      backgroundColor: (ctx: any) => {
        const canvas = ctx.chart.ctx;
        const gradient = canvas.createLinearGradient(0, 0, 0, 300);
        // Use rgba colors that Canvas API can understand
        gradient.addColorStop(0, 'rgba(59, 130, 246, 0.25)'); // Primary blue
        gradient.addColorStop(0.5, 'rgba(59, 130, 246, 0.1)');
        gradient.addColorStop(1, 'rgba(59, 130, 246, 0)');
        return gradient;
      },
      borderColor: 'rgb(59, 130, 246)', // Primary blue
      borderWidth: 3,
      pointBackgroundColor: '#ffffff',
      pointBorderColor: 'rgb(59, 130, 246)',
      pointBorderWidth: 3,
      pointRadius: 5,
      pointHoverRadius: 8,
      pointHoverBackgroundColor: 'rgb(59, 130, 246)',
      pointHoverBorderColor: '#ffffff',
      pointHoverBorderWidth: 2,
      fill: true,
      tension: 0.4,
      data: (props.revenueChart?.data ?? []).map(v => Number(v)),
    },
  ],
}));

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: 'hsl(var(--popover))',
      titleColor: 'hsl(var(--popover-foreground))',
      bodyColor: 'hsl(var(--popover-foreground))',
      borderColor: 'hsl(var(--border))',
      borderWidth: 1,
      padding: 12,
      titleFont: { size: 13, family: 'Inter' },
      bodyFont: { size: 14, family: 'Inter', weight: 'bold' },
      displayColors: false,
      callbacks: {
        label: (context: any) => 'Pendapatan: ' + currencyFormatter.format(context.raw),
        title: (context: any) => context[0].label,
      },
      cornerRadius: 8,
    },
  },
  scales: {
    y: {
      beginAtZero: true,
      grid: {
        display: false, // Hide horizontal grid lines
      },
      ticks: {
        callback: (value: any) => {
          if (value >= 1000000) return (value / 1000000).toFixed(0) + 'jt';
          if (value >= 1000) return (value / 1000).toFixed(0) + 'rb';
          return value;
        },
        font: { size: 11, family: 'Inter' },
        color: 'hsl(var(--muted-foreground))',
        padding: 10,
      },
      border: { display: false },
    },
    x: {
      grid: { display: false },
      ticks: {
        font: { size: 11, family: 'Inter' },
        color: 'hsl(var(--muted-foreground))',
        autoSkip: true,
        maxTicksLimit: 7,
      },
      border: { display: false },
    },
  },
  interaction: {
    intersect: false,
    mode: 'index',
  },
};

const getStatIcon = (label: string) => {
  const l = label.toLowerCase();
  if (l.includes('pendapatan')) return DollarSign;
  if (l.includes('pesanan')) return ShoppingBag;
  if (l.includes('produk')) return Package;
  return TrendingUp;
};

const getStatStyles = (label: string) => {
  const l = label.toLowerCase();
  if (l.includes('pendapatan')) return {
    iconBg: 'bg-emerald-100/50 dark:bg-emerald-900/20',
    iconColor: 'text-emerald-600 dark:text-emerald-400',
    trendColor: 'text-emerald-600 dark:text-emerald-400',
  };
  if (l.includes('pesanan')) return {
    iconBg: 'bg-blue-100/50 dark:bg-blue-900/20',
    iconColor: 'text-blue-600 dark:text-blue-400',
    trendColor: 'text-blue-600 dark:text-blue-400',
  };
  if (l.includes('produk')) return {
    iconBg: 'bg-orange-100/50 dark:bg-orange-900/20',
    iconColor: 'text-orange-600 dark:text-orange-400',
    trendColor: 'text-orange-600 dark:text-orange-400',
  };
  return {
    iconBg: 'bg-primary/10',
    iconColor: 'text-primary',
    trendColor: 'text-primary',
  };
};

const orderStatusBadge = (status: string) => {
  const styles: Record<string, string> = {
    pending_payment: 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-900/20 dark:text-amber-400 dark:ring-amber-500/30',
    processing: 'bg-blue-50 text-blue-700 ring-blue-700/10 dark:bg-blue-900/20 dark:text-blue-400 dark:ring-blue-500/30',
    shipped: 'bg-indigo-50 text-indigo-700 ring-indigo-700/10 dark:bg-indigo-900/20 dark:text-indigo-400 dark:ring-indigo-500/30',
    delivered: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-900/20 dark:text-emerald-400 dark:ring-emerald-500/30',
    completed: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-900/20 dark:text-emerald-400 dark:ring-emerald-500/30',
    cancelled: 'bg-muted text-muted-foreground ring-muted-foreground/10',
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
    class: styles[status] || 'bg-muted text-muted-foreground ring-muted-foreground/10',
    label: labels[status] || status,
  };
};

// Tabs state for Revenue Card
const periodOptions = [
  { label: '7 Hari', value: '7d' },
  { label: '30 Hari', value: '30d' },
  { label: '90 Hari', value: '90d' },
];
const selectedPeriod = ref('7d');
</script>

<template>
  <div class="space-y-8 animate-in fade-in duration-500 pb-10">

    <Head title="Dashboard Seller" />

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-5 border-b border-border pb-6">
      <div class="space-y-1">
        <h1 class="text-3xl font-bold tracking-tight text-foreground leading-tight">
          {{ timeGreeting }}, <span class="text-primary">{{ store?.name ?? 'Partner' }}</span>
        </h1>
        <p class="text-muted-foreground text-sm max-w-xl">
          Berikut adalah ringkasan performa tokomu hari ini.
        </p>
      </div>

      <div class="flex items-center gap-3">
        <!-- Date Display Mockup -->
        <div
          class="hidden md:flex items-center gap-2 px-3 py-1.5 bg-background border border-border rounded-md shadow-sm text-sm text-muted-foreground">
          <Calendar class="h-4 w-4" />
          <span>{{ new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}</span>
        </div>

        <Button variant="outline" class="hidden sm:flex" as-child>
          <Link href="/seller/settings">
            <StoreIcon class="mr-2 h-4 w-4" />
            Kelola Toko
          </Link>
        </Button>
        <Button class="shadow-sm active:scale-95 transition-transform" as-child>
          <Link href="/seller/products/create">
            <Package class="mr-2 h-4 w-4" />
            Tambah Produk
          </Link>
        </Button>
      </div>
    </div>

    <!-- Alert Setup -->
    <div v-if="needsStoreSetup"
      class="relative overflow-hidden rounded-xl border border-amber-200 bg-amber-50/50 p-4 dark:bg-amber-900/10 dark:border-amber-900/50">
      <div class="flex gap-4">
        <div
          class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-500">
          <AlertTriangle class="h-5 w-5" />
        </div>
        <div>
          <h3 class="font-semibold text-amber-900 dark:text-amber-400">Lengkapi Profil Toko</h3>
          <p class="mt-1 text-sm text-amber-800/80 dark:text-amber-500/90">
            Agar toko terlihat profesional dan dipercaya pembeli, segera lengkapi informasi toko Anda.
          </p>
          <div class="mt-3">
            <Link href="/seller/settings"
              class="inline-flex items-center text-sm font-medium text-amber-700 hover:text-amber-800 hover:underline dark:text-amber-400 dark:hover:text-amber-300">
              Setup sekarang
              <ArrowRight class="ml-1 h-3 w-3" />
            </Link>
          </div>
        </div>
      </div>
    </div>

    <template v-else>
      <!-- Stats Grid with Colorful Gradients -->
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Pendapatan Card -->
        <Card v-for="(stat, index) in stats" :key="stat.label" :class="[
          'group relative overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 border-0',
          index === 0 ? 'bg-gradient-to-br from-emerald-500 to-teal-600 text-white' :
            index === 1 ? 'bg-gradient-to-br from-blue-500 to-indigo-600 text-white' :
              index === 2 ? 'bg-gradient-to-br from-orange-500 to-amber-600 text-white' :
                'bg-gradient-to-br from-violet-500 to-purple-600 text-white'
        ]">
          <CardContent class="p-5 relative">
            <!-- Background decoration -->
            <div class="absolute top-0 right-0 w-24 h-24 transform translate-x-8 -translate-y-8">
              <div class="w-full h-full rounded-full bg-white/10"></div>
            </div>
            <div class="absolute bottom-0 left-0 w-16 h-16 transform -translate-x-6 translate-y-6">
              <div class="w-full h-full rounded-full bg-white/10"></div>
            </div>

            <div class="flex items-center justify-between relative z-10">
              <div>
                <p class="text-sm font-medium opacity-90">{{ stat.label }}</p>
                <div class="mt-2 flex items-baseline gap-2">
                  <span class="text-2xl font-bold tracking-tight">
                    {{ stat.label.toLowerCase().includes('pendapatan') ? formatCurrency(stat.value) :
                      stat.value.toLocaleString() }}
                  </span>
                </div>
                <div class="mt-3 flex items-center gap-2 text-xs opacity-80">
                  <TrendingUp v-if="(stat.growth ?? 0) >= 0" class="h-3 w-3" />
                  <TrendingUp v-else class="h-3 w-3 rotate-180" />
                  <span>{{ (stat.growth ?? 0) >= 0 ? '+' : '' }}{{ stat.growth ?? 0 }}% dari periode lalu</span>
                </div>
              </div>
              <div class="h-12 w-12 rounded-xl bg-white/20 flex items-center justify-center">
                <component :is="getStatIcon(stat.label)" class="h-6 w-6 text-white" />
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Today's Quick Stats -->
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <Card class="shadow-sm border-0 bg-card">
          <CardContent class="p-4">
            <div class="flex items-center gap-3">
              <div class="h-10 w-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                <ShoppingBag class="h-5 w-5 text-blue-600 dark:text-blue-400" />
              </div>
              <div>
                <p class="text-xs text-muted-foreground">Pesanan Hari Ini</p>
                <p class="text-xl font-bold text-foreground">{{ todayStats?.orders ?? 0 }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card class="shadow-sm border-0 bg-card">
          <CardContent class="p-4">
            <div class="flex items-center gap-3">
              <div class="h-10 w-10 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                <DollarSign class="h-5 w-5 text-green-600 dark:text-green-400" />
              </div>
              <div>
                <p class="text-xs text-muted-foreground">Pendapatan Hari Ini</p>
                <p class="text-xl font-bold text-foreground">{{ formatCurrency(todayStats?.revenue ?? 0) }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card class="shadow-sm border-0 bg-card">
          <CardContent class="p-4">
            <div class="flex items-center gap-3">
              <div class="h-10 w-10 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                <Users class="h-5 w-5 text-purple-600 dark:text-purple-400" />
              </div>
              <div>
                <p class="text-xs text-muted-foreground">Total Pelanggan</p>
                <p class="text-xl font-bold text-foreground">{{ totalCustomers ?? 0 }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card class="shadow-sm border-0 bg-card">
          <CardContent class="p-4">
            <div class="flex items-center gap-3">
              <div class="h-10 w-10 rounded-xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                <Zap class="h-5 w-5 text-orange-600 dark:text-orange-400" />
              </div>
              <div>
                <p class="text-xs text-muted-foreground">Rata-rata Order</p>
                <p class="text-xl font-bold text-foreground">{{ formatCurrency(avgOrderValue ?? 0) }}</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Alerts Section -->
      <div
        v-if="(lowStockProducts?.length ?? 0) > 0 || (outOfStockCount ?? 0) > 0 || (ordersNeedingAction?.length ?? 0) > 0"
        class="grid gap-4 md:grid-cols-2">
        <!-- Low Stock Alert -->
        <Card v-if="(lowStockProducts?.length ?? 0) > 0 || (outOfStockCount ?? 0) > 0"
          class="shadow-sm border-orange-200 dark:border-orange-900/50 bg-orange-50/50 dark:bg-orange-950/20">
          <CardHeader class="pb-3">
            <div class="flex items-center justify-between">
              <CardTitle class="text-sm font-semibold text-orange-800 dark:text-orange-300 flex items-center gap-2">
                <AlertCircle class="h-4 w-4" />
                Stok Perlu Perhatian
              </CardTitle>
              <Badge v-if="outOfStockCount"
                class="bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300 border-0">
                {{ outOfStockCount }} habis
              </Badge>
            </div>
          </CardHeader>
          <CardContent class="pt-0">
            <div class="space-y-2">
              <div v-for="product in lowStockProducts?.slice(0, 3)" :key="product.id"
                class="flex items-center justify-between py-2 px-3 bg-white dark:bg-card rounded-lg border border-orange-200/50 dark:border-orange-900/30">
                <div class="flex items-center gap-2">
                  <div class="h-8 w-8 rounded bg-secondary flex items-center justify-center">
                    <Package v-if="!product.image_url" class="h-4 w-4 text-muted-foreground" />
                    <img v-else :src="product.image_url" :alt="product.name"
                      class="h-full w-full object-cover rounded" />
                  </div>
                  <span class="text-sm font-medium text-foreground truncate max-w-[150px]">{{ product.name }}</span>
                </div>
                <Badge class="bg-orange-100 text-orange-700 dark:bg-orange-900/50 dark:text-orange-400 border-0">
                  {{ product.stock }} tersisa
                </Badge>
              </div>
            </div>
            <Link href="/seller/products"
              class="inline-flex items-center text-xs font-medium text-orange-700 dark:text-orange-400 hover:underline mt-3">
              Kelola stok
              <ChevronRight class="h-3 w-3 ml-1" />
            </Link>
          </CardContent>
        </Card>

        <!-- Orders Needing Action -->
        <Card v-if="(ordersNeedingAction?.length ?? 0) > 0"
          class="shadow-sm border-blue-200 dark:border-blue-900/50 bg-blue-50/50 dark:bg-blue-950/20">
          <CardHeader class="pb-3">
            <div class="flex items-center justify-between">
              <CardTitle class="text-sm font-semibold text-blue-800 dark:text-blue-300 flex items-center gap-2">
                <Clock class="h-4 w-4" />
                Pesanan Perlu Diproses
              </CardTitle>
              <Badge class="bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300 border-0">
                {{ ordersNeedingAction?.length ?? 0 }} pesanan
              </Badge>
            </div>
          </CardHeader>
          <CardContent class="pt-0">
            <div class="space-y-2">
              <div v-for="order in ordersNeedingAction?.slice(0, 3)" :key="order.id"
                class="flex items-center justify-between py-2 px-3 bg-white dark:bg-card rounded-lg border border-blue-200/50 dark:border-blue-900/30">
                <div>
                  <span class="text-sm font-medium text-foreground">{{ order.order_number }}</span>
                  <p class="text-xs text-muted-foreground">{{ order.customer_name ?? 'Pelanggan' }} • {{
                    order.created_at }}</p>
                </div>
                <Badge
                  :class="order.status === 'processing' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-400'"
                  class="border-0 text-[10px]">
                  {{ order.status === 'processing' ? 'Diproses' : 'Menunggu' }}
                </Badge>
              </div>
            </div>
            <Link href="/seller/orders"
              class="inline-flex items-center text-xs font-medium text-blue-700 dark:text-blue-400 hover:underline mt-3">
              Lihat semua pesanan
              <ChevronRight class="h-3 w-3 ml-1" />
            </Link>
          </CardContent>
        </Card>
      </div>

      <!-- Main Content Grid -->
      <div class="grid gap-6 lg:grid-cols-7">

        <!-- Chart Section -->
        <Card class="lg:col-span-4 shadow-sm order-2 lg:order-1 flex flex-col border-0 overflow-hidden">
          <CardHeader class="bg-secondary/50 border-b border-border p-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
              <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-primary flex items-center justify-center shadow-md">
                  <DollarSign class="h-5 w-5 text-primary-foreground" />
                </div>
                <div>
                  <CardTitle class="text-lg font-semibold text-foreground">
                    Analisis Pendapatan
                  </CardTitle>
                  <CardDescription class="mt-0.5">
                    Total pendapatan kotor periode ini
                  </CardDescription>
                </div>
              </div>
              <!-- Period Filters -->
              <div class="flex items-center gap-1 bg-background p-1 rounded-xl shadow-sm border border-border">
                <button v-for="period in periodOptions" :key="period.value" @click="selectedPeriod = period.value"
                  :class="['px-3 py-1.5 text-xs font-semibold rounded-lg transition-all', selectedPeriod === period.value ? 'bg-primary text-primary-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground hover:bg-muted/50']">
                  {{ period.label }}
                </button>
              </div>
            </div>

            <!-- Big Summary Number inside Header -->
            <div class="mt-5 flex flex-wrap items-baseline gap-3">
              <span class="text-4xl font-bold text-foreground tracking-tight">
                {{ formatCurrency(totalChartRevenue) }}
              </span>
              <span :class="[
                'text-sm font-semibold px-2.5 py-1 rounded-full flex items-center shadow-sm',
                (growthData?.revenue ?? 0) >= 0
                  ? 'text-emerald-600 dark:text-emerald-400 bg-emerald-100 dark:bg-emerald-900/50'
                  : 'text-red-600 dark:text-red-400 bg-red-100 dark:bg-red-900/50'
              ]">
                <TrendingUp v-if="(growthData?.revenue ?? 0) >= 0" class="h-3.5 w-3.5 mr-1" />
                <TrendingUp v-else class="h-3.5 w-3.5 mr-1 rotate-180" />
                {{ (growthData?.revenue ?? 0) >= 0 ? '+' : '' }}{{ growthData?.revenue ?? 0 }}%
              </span>
              <span class="text-xs text-muted-foreground ml-auto">
                vs bulan sebelumnya
              </span>
            </div>
          </CardHeader>
          <CardContent class="flex-1 p-5 bg-card">
            <div class="h-[300px] w-full">
              <Line :data="chartData" :options="chartOptions" />
            </div>
          </CardContent>
        </Card>

        <!-- Top Products -->
        <Card class="lg:col-span-3 shadow-sm order-1 lg:order-2 flex flex-col">
          <CardHeader class="border-b border-border pb-4 p-5">
            <div class="flex items-center justify-between">
              <div>
                <CardTitle class="text-base font-semibold">Produk Terlaris</CardTitle>
                <CardDescription class="mt-1">Performa penjualan terbaik.</CardDescription>
              </div>
              <Link href="/seller/products" class="text-xs font-medium text-primary hover:underline">Lihat Semua</Link>
            </div>
          </CardHeader>
          <CardContent class="p-0 flex-1 overflow-auto">
            <div v-if="topProducts.length === 0"
              class="flex flex-col items-center justify-center h-full min-h-[200px] text-center p-4">
              <div class="h-10 w-10 rounded-full bg-muted flex items-center justify-center mb-2">
                <Package class="h-5 w-5 text-muted-foreground" />
              </div>
              <p class="text-sm font-medium text-muted-foreground">Belum ada data produk</p>
            </div>

            <div v-else class="divide-y divide-border">
              <div v-for="(product, index) in topProducts.slice(0, 5)" :key="product.product_id"
                class="group flex items-center gap-4 p-4 hover:bg-muted/50 transition-colors">

                <div
                  class="relative h-12 w-12 flex-shrink-0 rounded-lg bg-secondary flex items-center justify-center overflow-hidden border border-border">
                  <Package v-if="!product.image_url" class="h-5 w-5 text-muted-foreground" />
                  <img v-else :src="product.image_url" :alt="product.name" class="h-full w-full object-cover" />
                  <!-- Rank Badge -->
                  <div
                    class="absolute top-0 left-0 bg-primary text-primary-foreground text-[10px] font-bold px-1.5 py-0.5 rounded-br-md">
                    #{{ index + 1 }}
                  </div>
                </div>

                <div class="flex-1 min-w-0 space-y-1">
                  <div class="flex items-center justify-between">
                    <p
                      class="text-sm font-medium text-foreground truncate pr-2 group-hover:text-primary transition-colors">
                      {{ product.name }}</p>
                    <span class="text-sm font-bold text-foreground">{{ formatCurrency(product.total_amount) }}</span>
                  </div>
                  <div class="flex items-center justify-between text-xs text-muted-foreground">
                    <span>{{ product.total_sold }} terjual</span>
                    <!-- Simple progress bar mock -->
                    <div class="w-16 h-1.5 bg-secondary rounded-full overflow-hidden">
                      <div class="h-full bg-primary rounded-full"
                        :style="{ width: Math.min(100, (product.total_sold / (topProducts[0]?.total_sold || 1)) * 100) + '%' }">
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Recent Orders & Actions -->
      <div class="grid gap-6 lg:grid-cols-3">
        <!-- Recent Orders Table -->
        <Card class="lg:col-span-2 shadow-sm overflow-hidden">
          <CardHeader class="bg-muted/40 border-b border-border py-4 px-5">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <ShoppingBag class="h-4 w-4 text-muted-foreground" />
                <CardTitle class="text-sm font-semibold text-foreground">Pesanan Terbaru</CardTitle>
              </div>
              <Button variant="ghost" size="sm" class="h-8 text-xs bg-background border border-border hover:bg-muted"
                as-child>
                <Link href="/seller/orders">
                  Lihat Semua
                  <ChevronRight class="ml-1 h-3 w-3" />
                </Link>
              </Button>
            </div>
          </CardHeader>
          <CardContent class="p-0">
            <div class="overflow-x-auto">
              <table class="w-full text-left text-sm">
                <thead>
                  <tr class="border-b border-border text-xs text-muted-foreground bg-muted/20">
                    <th class="px-5 py-3 font-medium">Order ID</th>
                    <th class="px-5 py-3 font-medium">Pelanggan</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 font-medium text-right">Total</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-border">
                  <tr v-for="order in recentOrders" :key="order.id" class="group hover:bg-muted/50 transition-colors">
                    <td class="px-5 py-3.5">
                      <span class="font-medium text-foreground group-hover:text-primary transition-colors">{{
                        order.order_number }}</span>
                    </td>
                    <td class="px-5 py-3.5">
                      <div class="flex items-center gap-2.5">
                        <div
                          class="h-7 w-7 rounded-full bg-secondary flex items-center justify-center text-[10px] font-bold text-muted-foreground border border-background shadow-sm ring-1 ring-border">
                          {{ order.customer.name?.charAt(0) || '?' }}
                        </div>
                        <span class="text-foreground truncate max-w-[120px]">{{ order.customer.name ?? 'Guest' }}</span>
                      </div>
                    </td>
                    <td class="px-5 py-3.5">
                      <span
                        :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-medium capitalize shadow-sm ring-1 ring-inset', orderStatusBadge(order.status).class]">
                        {{ orderStatusBadge(order.status).label }}
                      </span>
                    </td>
                    <td class="px-5 py-3.5 text-right font-medium text-foreground">
                      {{ formatCurrency(order.grand_total) }}
                    </td>
                  </tr>
                  <tr v-if="recentOrders.length === 0">
                    <td colspan="4" class="py-12 text-center text-muted-foreground">
                      <div class="flex flex-col items-center justify-center">
                        <ShoppingBag class="h-8 w-8 text-muted-foreground/50 mb-2" />
                        <p>Belum ada pesanan terbaru.</p>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </CardContent>
        </Card>

        <!-- Quick Actions / Status -->
        <div class="space-y-6">
          <Card class="shadow-sm border-0 bg-card overflow-hidden">
            <CardHeader
              class="bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-900/50 dark:to-slate-800/50 border-b border-border py-4 px-5">
              <CardTitle class="text-sm font-semibold text-foreground flex items-center gap-2">
                <div class="h-6 w-6 rounded-lg bg-primary/10 flex items-center justify-center">
                  <StoreIcon class="h-3.5 w-3.5 text-primary" />
                </div>
                Status Toko
              </CardTitle>
            </CardHeader>
            <CardContent class="p-5 space-y-5">
              <div>
                <div class="flex items-center justify-between mb-2">
                  <span class="text-xs text-muted-foreground">Respon Pesanan</span>
                  <Badge
                    class="bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400 border-0 text-[10px]">
                    {{ store?.response_time_label || 'Standar' }}
                  </Badge>
                </div>
                <div class="h-2.5 w-full bg-secondary rounded-full overflow-hidden">
                  <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-500 w-3/4 rounded-full"></div>
                </div>
              </div>

              <div class="pt-4 border-t border-border">
                <div class="flex items-start gap-3">
                  <div
                    class="h-9 w-9 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center shrink-0">
                    <MapPin class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                  </div>
                  <div>
                    <p class="text-xs font-semibold text-foreground">Lokasi Pengiriman</p>
                    <p class="text-xs text-muted-foreground leading-relaxed mt-0.5">{{ storeLocation || 'Belum diatur'
                    }}</p>
                  </div>
                </div>
              </div>

              <div class="pt-4 border-t border-border">
                <div class="flex items-start gap-3">
                  <div
                    class="h-9 w-9 rounded-xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center shrink-0">
                    <CreditCard class="h-4 w-4 text-orange-600 dark:text-orange-400" />
                  </div>
                  <div>
                    <p class="text-xs font-semibold text-foreground">Status Pajak</p>
                    <p class="text-xs text-muted-foreground mt-0.5">{{ store?.tax_status ? 'Terdaftar PKP' : 'Non-PKP'
                    }}</p>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card
            class="bg-gradient-to-br from-violet-500 via-purple-500 to-fuchsia-600 text-white border-0 shadow-lg relative overflow-hidden group hover:shadow-xl transition-all duration-300">
            <!-- Decorative elements -->
            <div class="absolute top-0 right-0 w-32 h-32 transform translate-x-12 -translate-y-12">
              <div class="w-full h-full rounded-full bg-white/10"></div>
            </div>
            <div class="absolute bottom-0 left-0 w-24 h-24 transform -translate-x-10 translate-y-10">
              <div class="w-full h-full rounded-full bg-white/10"></div>
            </div>
            <div class="absolute top-1/2 right-6 opacity-10 group-hover:opacity-20 transition-opacity">
              <TrendingUp class="h-20 w-20 transform rotate-12" />
            </div>
            <CardContent class="p-5 relative z-10">
              <div class="flex items-center gap-2 mb-2">
                <div class="h-8 w-8 rounded-lg bg-white/20 flex items-center justify-center">
                  <TrendingUp class="h-4 w-4" />
                </div>
                <h3 class="font-bold text-lg">Tingkatkan Penjualan!</h3>
              </div>
              <p class="text-white/80 text-xs mb-4 leading-relaxed max-w-[85%]">
                Bergabung dengan program promosi untuk menjangkau lebih banyak pembeli potensial.
              </p>
              <Button size="sm" variant="secondary"
                class="w-full border-0 font-semibold shadow-md hover:shadow-lg transition-shadow">
                🚀 Lihat Program
              </Button>
            </CardContent>
          </Card>
        </div>
      </div>
    </template>
  </div>
</template>
