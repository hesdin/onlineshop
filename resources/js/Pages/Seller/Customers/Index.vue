<script setup lang="ts">
import SellerDashboardLayout from '@/Layouts/SellerDashboardLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { ChevronLeft, ChevronRight, Search, Users, X } from 'lucide-vue-next';

interface Customer {
  id: number;
  name: string;
  email: string;
  phone: string | null;
  total_orders: number;
  total_spent: number;
  last_order_at: string;
  member_since: string;
}

const props = defineProps<{
  customers: {
    data: Customer[];
    total: number;
    links: { label: string; url: string | null; active: boolean }[];
    prev_page_url: string | null;
    next_page_url: string | null;
  };
  filters: {
    search?: string;
  };
}>();

const search = ref(props.filters.search ?? '');

const currencyFormatter = new Intl.NumberFormat('id-ID', {
  style: 'currency',
  currency: 'IDR',
  minimumFractionDigits: 0,
});

function formatCurrency(value?: number | null): string {
  return currencyFormatter.format(value ?? 0);
}

function formatDate(dateString?: string | null): string {
  if (!dateString) return '-';
  return new Date(dateString).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
}

function buildQuery(override: Record<string, unknown> = {}) {
  return { search: search.value || undefined, ...override };
}

const debouncedSearch = useDebounceFn((value: string) => {
  router.get('/seller/customers', buildQuery({ search: value || undefined }), {
    preserveState: true,
    replace: true,
    preserveScroll: true,
  });
}, 400);

watch(search, (value) => debouncedSearch(value));

function clearSearch() {
  search.value = '';
  router.get('/seller/customers', {}, {
    preserveState: true,
    replace: true,
    preserveScroll: true,
  });
}

const numberedPaginationLinks = computed(() =>
  (props.customers.links ?? []).filter((link) => Number.isInteger(Number(link.label))),
);

function paginateTo(url?: string | null) {
  if (!url) return;
  router.get(url, {}, { preserveState: true, preserveScroll: true });
}
</script>

<template>

  <Head title="Customer" />
  <SellerDashboardLayout>
    <div class="space-y-6 w-full">
      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-slate-900">Customer</h1>
          <p class="text-sm text-slate-500">Daftar customer yang pernah berbelanja di toko Anda</p>
        </div>
      </div>

      <!-- Search & Filters -->
      <Card class="bg-white">
        <CardContent class="pt-6">
          <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
            <div class="relative flex-1">
              <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
              <Input v-model="search" placeholder="Cari nama atau email customer..." class="pl-10 pr-10" />
              <button v-if="search" @click="clearSearch"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                <X class="h-4 w-4" />
              </button>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Customer Table -->
      <Card class="bg-white">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Users class="h-5 w-5 text-slate-500" />
            Daftar Customer
          </CardTitle>
          <CardDescription>
            Total {{ customers.total }} customer
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div v-if="customers.data.length === 0" class="py-12 text-center">
            <Users class="mx-auto h-12 w-12 text-slate-300" />
            <h3 class="mt-4 text-lg font-medium text-slate-900">Belum ada customer</h3>
            <p class="mt-2 text-sm text-slate-500">Belum ada customer yang berbelanja di toko Anda.</p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-slate-200">
                  <th class="min-w-[180px] px-4 py-3 text-left font-medium text-slate-600">Customer</th>
                  <th class="min-w-[120px] px-4 py-3 text-left font-medium text-slate-600">No. Telepon</th>
                  <th class="min-w-[100px] px-4 py-3 text-center font-medium text-slate-600">Total Pesanan</th>
                  <th class="min-w-[120px] px-4 py-3 text-right font-medium text-slate-600">Total Belanja</th>
                  <th class="min-w-[120px] px-4 py-3 text-left font-medium text-slate-600">Pesanan Terakhir</th>
                  <th class="min-w-[120px] px-4 py-3 text-left font-medium text-slate-600">Member Sejak</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="customer in customers.data" :key="customer.id"
                  class="border-b border-slate-100 hover:bg-slate-50">
                  <td class="px-4 py-3">
                    <div class="flex flex-col">
                      <span class="font-medium text-slate-900">{{ customer.name }}</span>
                      <span class="text-sm text-slate-500">{{ customer.email }}</span>
                    </div>
                  </td>
                  <td class="px-4 py-3 text-slate-600">{{ customer.phone || '-' }}</td>
                  <td class="px-4 py-3 text-center">
                    <span
                      class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-sm font-medium text-blue-700">
                      {{ customer.total_orders }}x
                    </span>
                  </td>
                  <td class="px-4 py-3 text-right font-medium text-slate-900">
                    {{ formatCurrency(customer.total_spent) }}
                  </td>
                  <td class="px-4 py-3 text-slate-600">{{ formatDate(customer.last_order_at) }}</td>
                  <td class="px-4 py-3 text-slate-600">{{ formatDate(customer.member_since) }}</td>
                </tr>
              </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="numberedPaginationLinks.length > 1"
              class="flex items-center justify-between border-t border-slate-200 px-4 py-3 sm:px-6 mt-4">
              <div class="flex flex-1 justify-between sm:hidden">
                <Button variant="outline" size="sm" :disabled="!customers.prev_page_url"
                  @click="paginateTo(customers.prev_page_url)">
                  Sebelumnya
                </Button>
                <Button variant="outline" size="sm" :disabled="!customers.next_page_url"
                  @click="paginateTo(customers.next_page_url)">
                  Selanjutnya
                </Button>
              </div>
              <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <p class="text-sm text-slate-700">
                  Menampilkan <span class="font-medium">{{ customers.data.length }}</span> dari
                  <span class="font-medium">{{ customers.total }}</span> customer
                </p>
                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                  <Button variant="outline" size="sm" class="rounded-r-none" :disabled="!customers.prev_page_url"
                    @click="paginateTo(customers.prev_page_url)">
                    <ChevronLeft class="h-4 w-4" />
                  </Button>
                  <Button v-for="link in numberedPaginationLinks" :key="link.label"
                    :variant="link.active ? 'default' : 'outline'" size="sm" class="rounded-none" :disabled="!link.url"
                    @click="paginateTo(link.url)">
                    {{ link.label }}
                  </Button>
                  <Button variant="outline" size="sm" class="rounded-l-none" :disabled="!customers.next_page_url"
                    @click="paginateTo(customers.next_page_url)">
                    <ChevronRight class="h-4 w-4" />
                  </Button>
                </nav>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </SellerDashboardLayout>
</template>
