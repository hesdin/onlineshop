<script setup lang="ts">
import SellerDashboardLayout from '@/Layouts/SellerDashboardLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Search, Package, ShoppingCart, ArrowRight, SearchX } from 'lucide-vue-next';
import { ref } from 'vue';

type Product = {
  id: number;
  name: string;
  slug: string;
  price: number;
  stock: number;
  status: string;
  category: string | null;
  image_url: string | null;
};

type Order = {
  id: number;
  order_number: string;
  customer_name: string;
  total: number;
  status: string;
  created_at: string;
};

const props = defineProps<{
  query: string;
  products: Product[];
  orders: Order[];
}>();

defineOptions({
  layout: SellerDashboardLayout,
});

const searchQuery = ref(props.query);

const handleSearch = () => {
  if (searchQuery.value.trim()) {
    router.visit(`/seller/search?q=${encodeURIComponent(searchQuery.value.trim())}`);
  }
};

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(price);
};

const getStatusBadge = (status: string) => {
  const statusMap: Record<string, { label: string; variant: 'default' | 'secondary' | 'destructive' | 'outline' }> = {
    ready: { label: 'Aktif', variant: 'default' },
    draft: { label: 'Draft', variant: 'secondary' },
    out_of_stock: { label: 'Stok Habis', variant: 'destructive' },
    pending: { label: 'Menunggu', variant: 'outline' },
    processing: { label: 'Diproses', variant: 'secondary' },
    shipped: { label: 'Dikirim', variant: 'default' },
    delivered: { label: 'Diterima', variant: 'default' },
    completed: { label: 'Selesai', variant: 'default' },
    cancelled: { label: 'Dibatalkan', variant: 'destructive' },
  };
  return statusMap[status] || { label: status, variant: 'outline' as const };
};
</script>

<template>
  <div class="mx-auto space-y-6">

    <Head :title="`Hasil Pencarian: ${query}`" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-xl font-bold tracking-tight text-slate-900">Hasil Pencarian</h1>
        <p v-if="query" class="text-sm text-slate-500">
          Menampilkan hasil untuk "<span class="font-medium text-slate-700">{{ query }}</span>"
        </p>
      </div>
      <div class="relative w-full sm:w-80">
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
        <Input v-model="searchQuery" @keydown.enter="handleSearch" type="text" placeholder="Cari lagi..."
          class="pl-9" />
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="!query" class="text-center py-16">
      <Search class="h-16 w-16 mx-auto text-slate-300 mb-4" />
      <h2 class="text-lg font-semibold text-slate-700 mb-2">Masukkan kata kunci pencarian</h2>
      <p class="text-sm text-slate-500">Ketik di kolom pencarian untuk mencari produk atau pesanan.</p>
    </div>

    <div v-else-if="products.length === 0 && orders.length === 0" class="text-center py-16">
      <SearchX class="h-16 w-16 mx-auto text-slate-300 mb-4" />
      <h2 class="text-lg font-semibold text-slate-700 mb-2">Tidak ada hasil ditemukan</h2>
      <p class="text-sm text-slate-500">Coba kata kunci lain atau periksa ejaan.</p>
    </div>

    <div v-else class="space-y-6">
      <!-- Products Section -->
      <Card v-if="products.length > 0">
        <CardHeader class="flex-row items-center justify-between space-y-0 pb-4">
          <div class="flex items-center gap-2">
            <Package class="h-5 w-5 text-emerald-600" />
            <CardTitle class="text-base">Produk ({{ products.length }})</CardTitle>
          </div>
          <Button variant="ghost" size="sm" as-child>
            <Link href="/seller/products" class="flex items-center gap-1 text-emerald-600">
              Lihat Semua
              <ArrowRight class="h-4 w-4" />
            </Link>
          </Button>
        </CardHeader>
        <CardContent>
          <div class="divide-y divide-slate-100">
            <div v-for="product in products" :key="product.id"
              class="flex items-center gap-4 py-3 first:pt-0 last:pb-0">
              <div class="h-12 w-12 rounded-lg bg-slate-100 overflow-hidden flex-shrink-0">
                <img v-if="product.image_url" :src="product.image_url" :alt="product.name"
                  class="h-full w-full object-cover" />
                <div v-else class="h-full w-full flex items-center justify-center">
                  <Package class="h-5 w-5 text-slate-400" />
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <Link :href="`/seller/products/${product.id}/edit`"
                  class="text-sm font-medium text-slate-900 hover:text-emerald-600 truncate block">
                  {{ product.name }}
                </Link>
                <p class="text-xs text-slate-500">
                  {{ product.category || 'Tanpa kategori' }} • Stok: {{ product.stock }}
                </p>
              </div>
              <div class="text-right">
                <p class="text-sm font-medium text-slate-900">{{ formatPrice(product.price) }}</p>
                <Badge :variant="getStatusBadge(product.status).variant" class="text-[10px]">
                  {{ getStatusBadge(product.status).label }}
                </Badge>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Orders Section -->
      <Card v-if="orders.length > 0">
        <CardHeader class="flex-row items-center justify-between space-y-0 pb-4">
          <div class="flex items-center gap-2">
            <ShoppingCart class="h-5 w-5 text-blue-600" />
            <CardTitle class="text-base">Pesanan ({{ orders.length }})</CardTitle>
          </div>
          <Button variant="ghost" size="sm" as-child>
            <Link href="/seller/orders" class="flex items-center gap-1 text-blue-600">
              Lihat Semua
              <ArrowRight class="h-4 w-4" />
            </Link>
          </Button>
        </CardHeader>
        <CardContent>
          <div class="divide-y divide-slate-100">
            <div v-for="order in orders" :key="order.id" class="flex items-center gap-4 py-3 first:pt-0 last:pb-0">
              <div class="h-12 w-12 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                <ShoppingCart class="h-5 w-5 text-blue-600" />
              </div>
              <div class="flex-1 min-w-0">
                <Link :href="`/seller/orders/${order.id}`"
                  class="text-sm font-medium text-slate-900 hover:text-blue-600 truncate block">
                  {{ order.order_number }}
                </Link>
                <p class="text-xs text-slate-500">
                  {{ order.customer_name }} • {{ order.created_at }}
                </p>
              </div>
              <div class="text-right">
                <p class="text-sm font-medium text-slate-900">{{ formatPrice(order.total) }}</p>
                <Badge :variant="getStatusBadge(order.status).variant" class="text-[10px]">
                  {{ getStatusBadge(order.status).label }}
                </Badge>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
</template>
