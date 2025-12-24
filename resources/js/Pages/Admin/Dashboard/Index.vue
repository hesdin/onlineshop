<script setup lang="ts">
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Head, Link } from '@inertiajs/vue3';
import {
  Users,
  TrendingUp,
  TrendingDown,
  Package,
  Store,
  ShoppingCart,
  FileCheck,
  AlertTriangle,
  CheckCircle,
  ArrowRight,
  BarChart3,
  Wallet,
  Eye,
  Star,
  CreditCard,
  Truck,
  XCircle,
  RefreshCcw,
  DollarSign,
} from 'lucide-vue-next';

// Define types
interface Stat {
  title: string;
  value: string | number;
  trend: string;
  trendUp?: boolean;
}

interface Shortcut {
  title: string;
  description: string;
  href: string;
}

interface RevenueSummary {
  todayRevenue: number;
  todayRevenueFormatted: string;
  revenueTrend: string;
  todayOrders: number;
  ordersTrend: string;
  todayVisitors: number;
  visitorsTrend: string;
  conversionRate: string;
}

interface RecentOrder {
  id: string;
  customer: string;
  store: string;
  amount: string;
  status: string;
  time: string;
}

interface PendingTasks {
  storeVerification: number;
  pendingPayments: number;
  lowStockProducts: number;
  disputes: number;
}

interface TopStore {
  name: string;
  sales: string;
  orders: number;
  rating: string;
}

interface TopProduct {
  name: string;
  store: string;
  sold: number;
  revenue: string;
}

interface OrderStats {
  shipping: number;
  completed: number;
  cancelled: number;
  refund: number;
  averageRating: string;
}

const props = withDefaults(defineProps<{
  stats?: Stat[];
  shortcuts?: Shortcut[];
  revenueSummary?: RevenueSummary;
  recentOrders?: RecentOrder[];
  pendingTasks?: PendingTasks;
  topStores?: TopStore[];
  topProducts?: TopProduct[];
  orderStats?: OrderStats;
}>(), {
  stats: () => [],
  shortcuts: () => [],
  recentOrders: () => [],
  topStores: () => [],
  topProducts: () => [],
});

// Icon mapping for stats
const statConfigs = [
  { icon: Store, gradient: 'from-blue-500 to-indigo-600', bg: 'bg-blue-50 dark:bg-blue-950/30' },
  { icon: ShoppingCart, gradient: 'from-emerald-500 to-teal-600', bg: 'bg-emerald-50 dark:bg-emerald-950/30' },
  { icon: Users, gradient: 'from-violet-500 to-purple-600', bg: 'bg-violet-50 dark:bg-violet-950/30' },
  { icon: Wallet, gradient: 'from-amber-500 to-orange-600', bg: 'bg-amber-50 dark:bg-amber-950/30' },
];

const getStatConfig = (index: number) => statConfigs[index % statConfigs.length];

const getStatusBadge = (status: string) => {
  const configs: Record<string, { class: string; label: string }> = {
    pending_payment: { class: 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-400', label: 'Menunggu' },
    pending: { class: 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-400', label: 'Menunggu' },
    processing: { class: 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-400', label: 'Diproses' },
    shipped: { class: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-400', label: 'Dikirim' },
    delivered: { class: 'bg-teal-100 text-teal-700 dark:bg-teal-900/50 dark:text-teal-400', label: 'Diterima' },
    completed: { class: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400', label: 'Selesai' },
    cancelled: { class: 'bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-400', label: 'Dibatalkan' },
  };
  return configs[status] || configs.pending;
};

const formatNumber = (num: number) => {
  if (num >= 1000) {
    return (num / 1000).toFixed(1) + 'K';
  }
  return num.toString();
};
</script>

<template>
  <AdminDashboardLayout>

    <Head title="Dashboard Admin" />

    <!-- Welcome Section -->
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-foreground">Dashboard Admin</h1>
        <p class="text-muted-foreground mt-1">Ringkasan aktivitas marketplace hari ini, {{
          new Date().toLocaleDateString('id-ID', {
            weekday: 'long', year: 'numeric', month: 'long', day:
              'numeric'
          }) }}</p>
      </div>
      <Button variant="outline" size="sm" class="gap-2" @click="() => $inertia.reload()">
        <RefreshCcw class="h-4 w-4" />
        Refresh Data
      </Button>
    </div>

    <!-- Stats Grid -->
    <section class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
      <Card v-for="(stat, index) in props.stats" :key="stat.title"
        :class="['border-0 shadow-sm hover:shadow-lg transition-all duration-300', getStatConfig(index).bg]">
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardDescription class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
            {{ stat.title }}
          </CardDescription>
          <div
            :class="['h-11 w-11 rounded-xl bg-gradient-to-br flex items-center justify-center shadow-lg', getStatConfig(index).gradient]">
            <component :is="getStatConfig(index).icon" class="h-5 w-5 text-white" />
          </div>
        </CardHeader>
        <CardContent>
          <div class="text-3xl font-bold text-foreground">{{ stat.value }}</div>
          <p
            :class="['text-xs flex items-center gap-1 mt-2 font-medium', stat.trendUp !== false ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400']">
            <TrendingUp v-if="stat.trendUp !== false" class="h-3 w-3" />
            <TrendingDown v-else class="h-3 w-3" />
            {{ stat.trend }}
          </p>
        </CardContent>
      </Card>
    </section>

    <!-- Revenue Summary -->
    <section class="mt-6 grid gap-4 md:grid-cols-4">
      <Card class="border-0 shadow-sm bg-gradient-to-br from-emerald-500 to-teal-600 text-white">
        <CardContent class="pt-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-emerald-100 text-sm font-medium">Pendapatan Hari Ini</p>
              <p class="text-2xl font-bold mt-1">{{ props.revenueSummary?.todayRevenueFormatted || 'Rp 0'
                }}</p>
              <p class="text-emerald-100 text-xs mt-2 flex items-center gap-1">
                <TrendingUp class="h-3 w-3" /> {{ props.revenueSummary?.revenueTrend || '0%' }} dari
                kemarin
              </p>
            </div>
            <DollarSign class="h-10 w-10 text-emerald-200" />
          </div>
        </CardContent>
      </Card>
      <Card class="border-0 shadow-sm bg-gradient-to-br from-blue-500 to-indigo-600 text-white">
        <CardContent class="pt-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-blue-100 text-sm font-medium">Pesanan Hari Ini</p>
              <p class="text-2xl font-bold mt-1">{{ props.revenueSummary?.todayOrders || 0 }}</p>
              <p class="text-blue-100 text-xs mt-2 flex items-center gap-1">
                <TrendingUp class="h-3 w-3" /> {{ props.revenueSummary?.ordersTrend || '0%' }} dari
                kemarin
              </p>
            </div>
            <ShoppingCart class="h-10 w-10 text-blue-200" />
          </div>
        </CardContent>
      </Card>
      <Card class="border-0 shadow-sm bg-gradient-to-br from-violet-500 to-purple-600 text-white">
        <CardContent class="pt-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-violet-100 text-sm font-medium">Pengunjung Hari Ini</p>
              <p class="text-2xl font-bold mt-1">{{ formatNumber(props.revenueSummary?.todayVisitors || 0)
                }}</p>
              <p class="text-violet-100 text-xs mt-2 flex items-center gap-1">
                <TrendingUp class="h-3 w-3" /> {{ props.revenueSummary?.visitorsTrend || '0%' }} dari
                kemarin
              </p>
            </div>
            <Eye class="h-10 w-10 text-violet-200" />
          </div>
        </CardContent>
      </Card>
      <Card class="border-0 shadow-sm bg-gradient-to-br from-amber-500 to-orange-600 text-white">
        <CardContent class="pt-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-amber-100 text-sm font-medium">Konversi Rate</p>
              <p class="text-2xl font-bold mt-1">{{ props.revenueSummary?.conversionRate || '0' }}%</p>
              <p class="text-amber-100 text-xs mt-2 flex items-center gap-1">
                <TrendingUp class="h-3 w-3" /> +0.5% dari kemarin
              </p>
            </div>
            <BarChart3 class="h-10 w-10 text-amber-200" />
          </div>
        </CardContent>
      </Card>
    </section>

    <!-- Main Content Grid -->
    <section class="mt-6 grid gap-6 lg:grid-cols-3">
      <!-- Recent Orders -->
      <Card class="lg:col-span-2 border-0 shadow-sm bg-card">
        <CardHeader>
          <div class="flex items-center justify-between">
            <div>
              <CardTitle class="flex items-center gap-2 text-lg">
                <div
                  class="h-8 w-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
                  <ShoppingCart class="h-4 w-4 text-white" />
                </div>
                Pesanan Terbaru
              </CardTitle>
              <CardDescription class="mt-1">5 pesanan terakhir yang masuk</CardDescription>
            </div>
            <Button variant="ghost" size="sm" class="text-primary hover:text-primary/80" as-child>
              <Link href="/admin/orders">
                Lihat Semua
                <ArrowRight class="h-4 w-4 ml-1" />
              </Link>
            </Button>
          </div>
        </CardHeader>
        <CardContent>
          <div v-if="props.recentOrders.length === 0" class="py-8 text-center text-sm text-muted-foreground">
            Belum ada pesanan
          </div>
          <div v-else class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="border-b border-border">
                  <th class="text-left py-3 px-2 text-xs font-semibold text-muted-foreground uppercase">
                    Order ID</th>
                  <th class="text-left py-3 px-2 text-xs font-semibold text-muted-foreground uppercase">
                    Customer</th>
                  <th
                    class="text-left py-3 px-2 text-xs font-semibold text-muted-foreground uppercase hidden md:table-cell">
                    Toko</th>
                  <th class="text-right py-3 px-2 text-xs font-semibold text-muted-foreground uppercase">
                    Total</th>
                  <th class="text-center py-3 px-2 text-xs font-semibold text-muted-foreground uppercase">
                    Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="order in props.recentOrders" :key="order.id"
                  class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                  <td class="py-3 px-2">
                    <span class="text-sm font-medium text-primary">{{ order.id }}</span>
                    <p class="text-xs text-muted-foreground">{{ order.time }}</p>
                  </td>
                  <td class="py-3 px-2 text-sm text-foreground">{{ order.customer }}</td>
                  <td class="py-3 px-2 text-sm text-muted-foreground hidden md:table-cell">{{
                    order.store }}</td>
                  <td class="py-3 px-2 text-sm font-semibold text-foreground text-right">{{
                    order.amount }}</td>
                  <td class="py-3 px-2 text-center">
                    <span :class="['text-xs font-medium px-2.5 py-1 rounded-full', getStatusBadge(order.status).class]">
                      {{ getStatusBadge(order.status).label }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>

      <!-- Pending Tasks -->
      <Card class="border-0 shadow-sm bg-card">
        <CardHeader class="pb-3">
          <CardTitle class="flex items-center gap-2 text-lg">
            <div
              class="h-8 w-8 rounded-lg bg-gradient-to-br from-rose-500 to-pink-600 flex items-center justify-center">
              <AlertTriangle class="h-4 w-4 text-white" />
            </div>
            Perlu Tindakan
          </CardTitle>
        </CardHeader>
        <CardContent class="space-y-3">
          <Link href="/admin/stores?status=pending"
            class="group flex items-center gap-3 rounded-lg border border-amber-200 dark:border-amber-900/50 bg-amber-50 dark:bg-amber-950/30 px-3 py-2.5 transition-all hover:shadow-md">
            <div
              class="h-9 w-9 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center">
              <FileCheck class="h-4 w-4 text-white" />
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-foreground truncate">Verifikasi Toko</p>
              <p class="text-xs text-muted-foreground">{{ props.pendingTasks?.storeVerification || 0 }}
                menunggu review</p>
            </div>
            <Badge class="bg-amber-500 text-white">{{ props.pendingTasks?.storeVerification || 0 }}</Badge>
          </Link>

          <Link href="/admin/orders?status=pending"
            class="group flex items-center gap-3 rounded-lg border border-blue-200 dark:border-blue-900/50 bg-blue-50 dark:bg-blue-950/30 px-3 py-2.5 transition-all hover:shadow-md">
            <div
              class="h-9 w-9 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
              <CreditCard class="h-4 w-4 text-white" />
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-foreground truncate">Konfirmasi Pembayaran</p>
              <p class="text-xs text-muted-foreground">{{ props.pendingTasks?.pendingPayments || 0 }}
                menunggu</p>
            </div>
            <Badge class="bg-blue-500 text-white">{{ props.pendingTasks?.pendingPayments || 0 }}</Badge>
          </Link>

          <Link href="/admin/products?stock=low"
            class="group flex items-center gap-3 rounded-lg border border-rose-200 dark:border-rose-900/50 bg-rose-50 dark:bg-rose-950/30 px-3 py-2.5 transition-all hover:shadow-md">
            <div
              class="h-9 w-9 rounded-lg bg-gradient-to-br from-rose-500 to-pink-600 flex items-center justify-center">
              <Package class="h-4 w-4 text-white" />
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-foreground truncate">Stok Rendah</p>
              <p class="text-xs text-muted-foreground">{{ props.pendingTasks?.lowStockProducts || 0 }}
                produk</p>
            </div>
            <Badge class="bg-rose-500 text-white">{{ props.pendingTasks?.lowStockProducts || 0 }}</Badge>
          </Link>

          <Link href="/admin/reports"
            class="group flex items-center gap-3 rounded-lg border border-violet-200 dark:border-violet-900/50 bg-violet-50 dark:bg-violet-950/30 px-3 py-2.5 transition-all hover:shadow-md">
            <div
              class="h-9 w-9 rounded-lg bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center">
              <XCircle class="h-4 w-4 text-white" />
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-foreground truncate">Komplain & Dispute</p>
              <p class="text-xs text-muted-foreground">{{ props.pendingTasks?.disputes || 0 }} perlu
                ditangani</p>
            </div>
            <Badge class="bg-violet-500 text-white">{{ props.pendingTasks?.disputes || 0 }}</Badge>
          </Link>
        </CardContent>
      </Card>
    </section>

    <!-- Bottom Grid -->
    <section class="mt-6 grid gap-6 lg:grid-cols-2">
      <!-- Top Stores -->
      <Card class="border-0 shadow-sm bg-card">
        <CardHeader>
          <div class="flex items-center justify-between">
            <CardTitle class="flex items-center gap-2 text-lg">
              <div
                class="h-8 w-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center">
                <Store class="h-4 w-4 text-white" />
              </div>
              Toko Terbaik Bulan Ini
            </CardTitle>
            <Button variant="ghost" size="sm" class="text-primary" as-child>
              <Link href="/admin/stores">Lihat Semua</Link>
            </Button>
          </div>
        </CardHeader>
        <CardContent class="space-y-3">
          <div v-if="props.topStores.length === 0" class="py-8 text-center text-sm text-muted-foreground">
            Belum ada data toko
          </div>
          <div v-for="(store, index) in props.topStores" :key="store.name"
            class="flex items-center gap-3 p-3 rounded-lg border border-border hover:bg-muted/50 transition-colors">
            <div
              class="h-10 w-10 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-bold">
              {{ index + 1 }}
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-foreground truncate">{{ store.name }}</p>
              <p class="text-xs text-muted-foreground">{{ store.orders }} pesanan</p>
            </div>
            <div class="text-right">
              <p class="text-sm font-bold text-foreground">{{ store.sales }}</p>
              <p class="text-xs text-amber-500 flex items-center gap-0.5 justify-end">
                <Star class="h-3 w-3 fill-current" /> {{ store.rating }}
              </p>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Top Products -->
      <Card class="border-0 shadow-sm bg-card">
        <CardHeader>
          <div class="flex items-center justify-between">
            <CardTitle class="flex items-center gap-2 text-lg">
              <div
                class="h-8 w-8 rounded-lg bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center">
                <Package class="h-4 w-4 text-white" />
              </div>
              Produk Terlaris
            </CardTitle>
            <Button variant="ghost" size="sm" class="text-primary" as-child>
              <Link href="/admin/products">Lihat Semua</Link>
            </Button>
          </div>
        </CardHeader>
        <CardContent class="space-y-3">
          <div v-if="props.topProducts.length === 0" class="py-8 text-center text-sm text-muted-foreground">
            Belum ada data produk
          </div>
          <div v-for="(product, index) in props.topProducts" :key="product.name"
            class="flex items-center gap-3 p-3 rounded-lg border border-border hover:bg-muted/50 transition-colors">
            <div
              class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center text-white font-bold">
              {{ index + 1 }}
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-foreground truncate">{{ product.name }}</p>
              <p class="text-xs text-muted-foreground">{{ product.store }}</p>
            </div>
            <div class="text-right">
              <p class="text-sm font-bold text-foreground">{{ product.sold }} terjual</p>
              <p class="text-xs text-emerald-600 dark:text-emerald-400">{{ product.revenue }}</p>
            </div>
          </div>
        </CardContent>
      </Card>
    </section>

    <!-- Quick Stats Row -->
    <section class="mt-6 grid gap-4 md:grid-cols-5">
      <Card class="border-0 shadow-sm bg-card text-center">
        <CardContent class="pt-6">
          <Truck class="h-8 w-8 mx-auto text-blue-500 mb-2" />
          <p class="text-2xl font-bold text-foreground">{{ props.orderStats?.shipping || 0 }}</p>
          <p class="text-xs text-muted-foreground">Dalam Pengiriman</p>
        </CardContent>
      </Card>
      <Card class="border-0 shadow-sm bg-card text-center">
        <CardContent class="pt-6">
          <CheckCircle class="h-8 w-8 mx-auto text-emerald-500 mb-2" />
          <p class="text-2xl font-bold text-foreground">{{ props.orderStats?.completed || 0 }}</p>
          <p class="text-xs text-muted-foreground">Selesai Bulan Ini</p>
        </CardContent>
      </Card>
      <Card class="border-0 shadow-sm bg-card text-center">
        <CardContent class="pt-6">
          <XCircle class="h-8 w-8 mx-auto text-rose-500 mb-2" />
          <p class="text-2xl font-bold text-foreground">{{ props.orderStats?.cancelled || 0 }}</p>
          <p class="text-xs text-muted-foreground">Dibatalkan</p>
        </CardContent>
      </Card>
      <Card class="border-0 shadow-sm bg-card text-center">
        <CardContent class="pt-6">
          <RefreshCcw class="h-8 w-8 mx-auto text-amber-500 mb-2" />
          <p class="text-2xl font-bold text-foreground">{{ props.orderStats?.refund || 0 }}</p>
          <p class="text-xs text-muted-foreground">Return/Refund</p>
        </CardContent>
      </Card>
      <Card class="border-0 shadow-sm bg-card text-center">
        <CardContent class="pt-6">
          <Star class="h-8 w-8 mx-auto text-yellow-500 mb-2" />
          <p class="text-2xl font-bold text-foreground">{{ props.orderStats?.averageRating || '0.0' }}</p>
          <p class="text-xs text-muted-foreground">Rating Rata-rata</p>
        </CardContent>
      </Card>
    </section>
  </AdminDashboardLayout>
</template>
