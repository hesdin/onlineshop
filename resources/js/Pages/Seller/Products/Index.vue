<script setup lang="ts">
import SellerDashboardLayout from '@/Layouts/SellerDashboardLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, h, onBeforeUnmount, ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Alert } from '@/components/ui/alert';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { DataTable } from '@/components/ui/data-table';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';

import { AlertTriangle, Edit2, Filter, Plus, Search, Trash2 } from 'lucide-vue-next';
import RestrictedActionTooltip from '@/components/RestrictedActionTooltip.vue';
import AlertBanner from '@/components/AlertBanner.vue';
import type { ColumnDef } from '@tanstack/vue-table';

type Option = {
  id: number;
  name: string;
};

type SelectOption = {
  value: string;
  label: string;
};

type ProductRow = {
  id: number;
  name: string;
  slug: string | null;
  brand: string | null;
  price: number;
  sale_price: number | null;
  min_order: number;
  stock: number;
  item_type: string;
  status: string;
  is_pdn: boolean;
  is_pkp: boolean;
  is_tkdn: boolean;
  category: Option | null;
  image_url: string | null;
};

const props = defineProps<{
  products: {
    data: ProductRow[];
    total: number;
    links: { label: string; url: string | null; active: boolean }[];
    prev_page_url: string | null;
    next_page_url: string | null;
  };
  categoryOptions: Option[];
  statuses: SelectOption[];
  itemTypes: SelectOption[];
  filters: {
    search?: string | null;
    status?: string | null;
    category?: string | null;
  };
}>();

defineOptions({
  layout: SellerDashboardLayout,
});

const page = usePage();
const flashSuccess = computed(() => (page.props as any).flash?.success ?? '');
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
      }, 5000);
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
const appliedCategoryFilter = ref(normalizeFilterValue(props.filters.category));
const statusFilterInput = ref(appliedStatusFilter.value);
const categoryFilterInput = ref(appliedCategoryFilter.value);
const filterPopoverOpen = ref(false);

watch(
  () => props.filters.status,
  (value) => {
    const normalized = normalizeFilterValue(value);
    appliedStatusFilter.value = normalized;
    statusFilterInput.value = normalized;
  },
);

watch(
  () => props.filters.category,
  (value) => {
    const normalized = normalizeFilterValue(value);
    appliedCategoryFilter.value = normalized;
    categoryFilterInput.value = normalized;
  },
);

const deleteDialogOpen = ref(false);
const deletingProduct = ref<ProductRow | null>(null);

const currencyFormatter = new Intl.NumberFormat('id-ID', {
  style: 'currency',
  currency: 'IDR',
  minimumFractionDigits: 0,
});

const formatCurrency = (value?: number | null) => currencyFormatter.format(value ?? 0);

const getStatusLabel = (value: string) =>
  props.statuses.find((status) => status.value === value)?.label ?? value;

const getItemTypeLabel = (value: string) =>
  props.itemTypes.find((option) => option.value === value)?.label ?? value;

const buildQuery = (override: Record<string, unknown> = {}) => ({
  search: search.value || undefined,
  status:
    appliedStatusFilter.value === 'all' ? undefined : appliedStatusFilter.value || undefined,
  category:
    appliedCategoryFilter.value === 'all'
      ? undefined
      : appliedCategoryFilter.value || undefined,
  ...override,
});

const debouncedSearch = useDebounceFn((value: string) => {
  router.get('/seller/products', buildQuery({ search: value || undefined }), {
    preserveState: true,
    replace: true,
    preserveScroll: true,
  });
}, 400);

watch(search, (value) => debouncedSearch(value));

const requestDelete = (product: ProductRow) => {
  deletingProduct.value = product;
  deleteDialogOpen.value = true;
};

const applyFilters = () => {
  appliedCategoryFilter.value = categoryFilterInput.value || 'all';
  appliedStatusFilter.value = statusFilterInput.value || 'all';
  router.get('/seller/products', buildQuery(), {
    preserveState: true,
    replace: true,
    preserveScroll: true,
  });
  filterPopoverOpen.value = false;
};

const deleteProduct = () => {
  if (!deletingProduct.value) return;

  router.delete(`/seller/products/${deletingProduct.value.id}`, {
    preserveScroll: true,
    onFinish: () => {
      deleteDialogOpen.value = false;
      deletingProduct.value = null;
    },
  });
};

const numberedPaginationLinks = computed(() =>
  (props.products.links ?? []).filter((link) => Number.isInteger(Number(link.label))),
);

const paginateTo = (url?: string | null) => {
  if (!url) return;
  router.visit(url, {
    preserveScroll: true,
    preserveState: true,
    replace: true,
  });
};

const complianceBadges = (product: ProductRow) => {
  const badges: { label: string; color: string }[] = [];
  if (product.is_pdn) badges.push({ label: 'PDN', color: 'bg-emerald-100 text-emerald-700' });
  if (product.is_pkp) badges.push({ label: 'PKP', color: 'bg-blue-100 text-blue-700' });
  if (product.is_tkdn) badges.push({ label: 'TKDN', color: 'bg-amber-100 text-amber-700' });
  return badges;
};

const columns = computed<ColumnDef<ProductRow>[]>(() => [
  {
    id: 'name',
    accessorKey: 'name',
    header: () => 'Produk',
    cell: ({ row }) =>
      h('div', { class: 'flex items-center gap-3' }, [
        // Product Image
        h(
          'div',
          {
            class: 'h-12 w-12 flex-shrink-0 overflow-hidden rounded-md border border-slate-200 bg-slate-100',
          },
          row.original.image_url
            ? h('img', {
              src: row.original.image_url,
              alt: row.original.name,
              class: 'h-full w-full object-cover object-center',
            })
            : h('div', { class: 'flex h-full w-full items-center justify-center text-slate-400' }, [
              // Placeholder icon
              h(
                'svg',
                {
                  xmlns: 'http://www.w3.org/2000/svg',
                  viewBox: '0 0 24 24',
                  fill: 'none',
                  stroke: 'currentColor',
                  'stroke-width': '2',
                  'stroke-linecap': 'round',
                  'stroke-linejoin': 'round',
                  class: 'h-6 w-6',
                },
                [
                  h('rect', { width: '18', height: '18', x: '3', y: '3', rx: '2', ry: '2' }),
                  h('circle', { cx: '9', cy: '9', r: '2' }),
                  h('path', { d: 'm21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21' }),
                ],
              ),
            ]),
        ),
        // Product Name
        h('div', { class: 'space-y-0.5' }, [
          h(
            'p',
            { class: 'font-semibold text-slate-900 leading-snug line-clamp-2' },
            row.original.name,
          ),
          row.original.brand
            ? h('p', { class: 'text-xs text-slate-500' }, row.original.brand)
            : null,
        ]),
      ]),
    meta: { class: 'min-w-[220px]' },
  },
  {
    id: 'category',
    header: () => 'Kategori',
    cell: ({ row }) =>
      h('span', { class: 'text-sm text-slate-600' }, row.original.category?.name ?? '-'),
  },
  {
    id: 'price',
    header: () => 'Harga',
    cell: ({ row }) =>
      h('div', { class: 'space-y-0.5 text-sm' }, [
        h(
          'p',
          { class: 'font-semibold text-slate-900' },
          formatCurrency(row.original.sale_price ?? row.original.price),
        ),
        row.original.sale_price
          ? h(
            'p',
            { class: 'text-xs text-slate-500 line-through' },
            formatCurrency(row.original.price),
          )
          : null,
      ]),
    meta: { class: 'min-w-[120px]' },
  },
  {
    id: 'stock',
    header: () => 'Kuantitas',
    cell: ({ row }) =>
      h('div', { class: 'text-sm text-slate-700' }, [
        h('p', { class: 'font-semibold' }, `${row.original.stock} stok`),
        h('p', { class: 'text-xs text-slate-500' }, `Min order ${row.original.min_order}`),
      ]),
    meta: { class: 'w-40' },
  },
  {
    id: 'status',
    header: () => 'Status',
    cell: ({ row }) =>
      h(
        'span',
        {
          class:
            'inline-flex items-center rounded-sm px-3 py-1 text-xs font-semibold ' +
            (row.original.status === 'ready'
              ? 'bg-emerald-100 text-emerald-700'
              : row.original.status === 'pre_order'
                ? 'bg-amber-100 text-amber-700'
                : 'bg-slate-100 text-slate-600'),
        },
        getStatusLabel(row.original.status),
      ),
    meta: { class: 'w-32' },
  },
  {
    id: 'item_type',
    header: () => 'Jenis',
    cell: ({ row }) =>
      h('span', { class: 'text-xs text-slate-500' }, getItemTypeLabel(row.original.item_type)),
    meta: { class: 'w-24' },
  },
  {
    id: 'compliance',
    header: () => 'Label',
    cell: ({ row }) =>
      h(
        'div',
        { class: 'flex flex-wrap gap-1' },
        complianceBadges(row.original).map((badge) =>
          h(
            'span',
            { class: `rounded-sm px-2 py-0.5 text-[11px] font-semibold ${badge.color}` },
            badge.label,
          ),
        ),
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
            onClick: () => router.visit(`/seller/products/${row.original.id}/edit`),
          },
          [h(Edit2, { class: 'h-[15px] w-[15px]' })],
        ),
        h(
          'span',
          {
            class:
              'inline-flex h-8 w-8 items-center justify-center rounded-full ' +
              'cursor-pointer text-red-500 hover:bg-red-50 hover:text-red-600',
            onClick: () => requestDelete(row.original),
          },
          [h(Trash2, { class: 'h-[15px] w-[15px]' })],
        ),
      ]),
    meta: { class: 'text-right w-28' },
    enableHiding: false,
  },
]);

const clearFilterInputs = () => {
  categoryFilterInput.value = 'all';
  statusFilterInput.value = 'all';
};

const resetFilters = () => {
  search.value = '';
  appliedCategoryFilter.value = 'all';
  appliedStatusFilter.value = 'all';
  clearFilterInputs();
  router.get('/seller/products', buildQuery(), {
    preserveState: true,
    replace: true,
    preserveScroll: true,
  });
};

const hasActiveFilters = computed(
  () =>
    !!search.value ||
    appliedStatusFilter.value !== 'all' ||
    appliedCategoryFilter.value !== 'all',
);
</script>

<template>
  <div class="space-y-6 w-full">

    <Head title="Produk Saya" />

    <!-- Floating Success Alert -->
    <Teleport to="body">
      <div v-if="successVisible && flashSuccess"
        class="fixed top-20 left-1/2 -translate-x-1/2 z-[9999] min-w-[600px] max-w-2xl shadow-lg rounded-lg overflow-hidden">
        <AlertBanner type="success" :message="flashSuccess" :show="successVisible" :dismissible="true"
          @close="successVisible = false" />
      </div>
    </Teleport>


    <div class="flex flex-wrap items-start justify-between gap-3">
      <div>
        <h1 class="text-2xl font-semibold text-slate-900">Produk Toko</h1>
        <p class="text-sm text-slate-500">Kelola katalog dan stok yang tampil di etalase.</p>
      </div>
      <RestrictedActionTooltip :disabled="!($page.props.auth as any).seller_document?.is_approved" :reason="($page.props.auth as any).seller_document?.submission_status === 'submitted'
        ? 'Dokumen sedang dalam proses verifikasi. Fitur ini akan aktif setelah disetujui.'
        : 'Lengkapi dan verifikasi dokumen toko untuk menambah produk.'">
        <Button as-child :disabled="!($page.props.auth as any).seller_document?.is_approved">
          <Link href="/seller/products/create"
            :class="{ 'pointer-events-none opacity-50': !($page.props.auth as any).seller_document?.is_approved }">
            <Plus class="h-4 w-4" />
            Produk
          </Link>
        </Button>
      </RestrictedActionTooltip>
    </div>

    <section class="space-y-4">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="relative w-full md:w-64">
          <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
            <Search class="h-4 w-4" />
          </span>
          <Input v-model="search" placeholder="Cari nama atau brand"
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
                  <p class="text-sm font-semibold text-slate-900">Filter Produk</p>
                </div>
                <div class="space-y-4 text-left">
                  <div class="space-y-2">
                    <label class="text-xs font-semibold text-slate-500">Kategori</label>
                    <Select v-model="categoryFilterInput">
                      <SelectTrigger class="w-full bg-white">
                        <SelectValue placeholder="Semua kategori" />
                      </SelectTrigger>
                      <SelectContent class="max-h-[250px] z-[100]">
                        <SelectItem value="all">Semua kategori</SelectItem>
                        <SelectItem v-for="option in categoryOptions" :key="option.id" :value="option.id.toString()">
                          {{ option.name }}
                        </SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                  <div class="space-y-2">
                    <label class="text-xs font-semibold text-slate-500">Status</label>
                    <Select v-model="statusFilterInput">
                      <SelectTrigger class="w-full bg-white">
                        <SelectValue placeholder="Semua status" />
                      </SelectTrigger>
                      <SelectContent class="z-[100]">
                        <SelectItem value="all">Semua status</SelectItem>
                        <SelectItem v-for="option in statuses" :key="option.value" :value="option.value">
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
        <DataTable :columns="columns" :data="products.data" />
      </div>

      <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">
        <p>
          Menampilkan {{ products.data.length }} dari
          {{ products.total }} produk.
        </p>
        <div class="flex flex-wrap items-center gap-1">
          <Button variant="outline" size="sm" :disabled="!products.prev_page_url"
            @click="paginateTo(products.prev_page_url)">
            Sebelumnya
          </Button>
          <Button v-for="link in numberedPaginationLinks" :key="link.label" size="sm"
            :variant="link.active ? 'default' : 'outline'" :aria-current="link.active ? 'page' : undefined"
            :disabled="!link.url" @click="paginateTo(link.url)">
            {{ link.label }}
          </Button>
          <Button variant="outline" size="sm" :disabled="!products.next_page_url"
            @click="paginateTo(products.next_page_url)">
            Selanjutnya
          </Button>
        </div>
      </div>
    </section>

    <AlertDialog :open="deleteDialogOpen" @update:open="(value) => (deleteDialogOpen = value)">
      <AlertDialogContent class="text-center">
        <AlertDialogHeader class="space-y-4 text-center">
          <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-50 text-red-600">
            <AlertTriangle class="h-8 w-8" />
          </div>
          <AlertDialogTitle class="text-lg font-semibold text-slate-900 text-center">Hapus Produk?
          </AlertDialogTitle>
          <AlertDialogDescription class="text-sm text-slate-600 text-center">
            Produk <strong>{{ deletingProduct?.name }}</strong> akan dihapus secara permanen dari katalog.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-center">
          <AlertDialogCancel class="sm:min-w-[120px]" @click="deleteDialogOpen = false">
            Batal
          </AlertDialogCancel>
          <AlertDialogAction class="bg-red-600 hover:bg-red-700 sm:min-w-[120px]" @click="deleteProduct">
            Hapus
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </div>
</template>
