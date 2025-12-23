<script setup lang="ts">
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, h, ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
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
import {
  DropdownMenu,
  DropdownMenuCheckboxItem,
  DropdownMenuContent,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { DataTable } from '@/components/ui/data-table';

import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover';
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
} from '@/components/ui/command';

import {
  Trash2,
  Edit2,
  Plus,
  SlidersHorizontal,
  CheckCircle2,
  Search,
  ChevronsUpDown,
  Check,
  Filter,
  X,
  AlertTriangle,
} from 'lucide-vue-next';

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
  slug: string;
  brand: string | null;
  description: string | null;
  price: number;
  sale_price: number | null;
  min_order: number;
  stock: number;
  weight: number | null;
  length: number | null;
  width: number | null;
  height: number | null;
  item_type: string;
  status: string;
  location_city: string | null;
  location_province: string | null;
  is_pdn: boolean;
  is_pkp: boolean;
  is_tkdn: boolean;
  store: Option | null;
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
  storeOptions: Option[];
  categoryOptions: Option[];
  statuses: SelectOption[];
  itemTypes: SelectOption[];
  filters: {
    search?: string | null;
    status?: string | null;
    store?: string | null;
    category?: string | null;
  };
}>();

defineOptions({
  layout: AdminDashboardLayout,
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success ?? '');

// filters
const search = ref(props.filters.search ?? '');
const storeFilter = ref(props.filters.store ?? '');
const categoryFilter = ref(props.filters.category ?? '');
const statusFilter = ref(props.filters.status ?? '');

// filter popover state
const filterPopoverOpen = ref(false);

// popover open state (inside filter dialog)
const storeFilterPopoverOpen = ref(false);
const categoryFilterPopoverOpen = ref(false);
const statusFilterPopoverOpen = ref(false);

// search text di combobox
const storeFilterSearch = ref('');
const categoryFilterSearch = ref('');
const statusFilterSearch = ref('');

// delete dialog
const deleteDialogOpen = ref(false);
const deletingProduct = ref<ProductRow | null>(null);

// currency helper
const currencyFormatter = new Intl.NumberFormat('id-ID', {
  style: 'currency',
  currency: 'IDR',
  minimumFractionDigits: 0,
});

const formatCurrency = (value?: number | null) => {
  if (value === null || value === undefined) {
    return '-';
  }
  return currencyFormatter.format(value);
};

const getStatusLabel = (value: string) =>
  props.statuses.find((status) => status.value === value)?.label ?? value;

const getItemTypeLabel = (value: string) =>
  props.itemTypes.find((option) => option.value === value)?.label ?? value;

// label terpilih untuk combobox (current applied filters)
const selectedStoreFilter = computed(() =>
  props.storeOptions.find((s) => s.id.toString() === storeFilter.value) ?? null,
);

const selectedCategoryFilter = computed(() =>
  props.categoryOptions.find((c) => c.id.toString() === categoryFilter.value) ?? null,
);

const selectedStatusFilter = computed(() =>
  props.statuses.find((s) => s.value === statusFilter.value) ?? null,
);

// list yang sudah di-filter oleh search di combobox
const filteredStoreFilterOptions = computed(() => {
  if (!storeFilterSearch.value) return props.storeOptions;
  const term = storeFilterSearch.value.toLowerCase();
  return props.storeOptions.filter((option) =>
    option.name.toLowerCase().includes(term),
  );
});

const filteredCategoryFilterOptions = computed(() => {
  if (!categoryFilterSearch.value) return props.categoryOptions;
  const term = categoryFilterSearch.value.toLowerCase();
  return props.categoryOptions.filter((option) =>
    option.name.toLowerCase().includes(term),
  );
});

const filteredStatusFilterOptions = computed(() => {
  if (!statusFilterSearch.value) return props.statuses;
  const term = statusFilterSearch.value.toLowerCase();
  return props.statuses.filter((option) =>
    option.label.toLowerCase().includes(term),
  );
});

// build query untuk router.get
const buildQuery = (override: Record<string, unknown> = {}) => ({
  search: search.value || undefined,
  store: storeFilter.value || undefined,
  category: categoryFilter.value || undefined,
  status: statusFilter.value || undefined,
  ...override,
});

// debounced search
const debouncedSearch = useDebounceFn((value: string) => {
  router.get('/admin/products', buildQuery({ search: value || undefined }), {
    preserveState: true,
    replace: true,
    preserveScroll: true,
  });
}, 400);

watch(search, (value) => {
  debouncedSearch(value);
});

// watch filter lainnya
watch([storeFilter, categoryFilter, statusFilter], () => {
  router.get('/admin/products', buildQuery(), {
    preserveState: true,
    replace: true,
    preserveScroll: true,
  });
});

const requestDelete = (product: ProductRow) => {
  deletingProduct.value = product;
  deleteDialogOpen.value = true;
};

const deleteProduct = () => {
  if (!deletingProduct.value) return;

  router.delete(`/admin/products/${deletingProduct.value.id}`, {
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

// column visibility
const allColumnIds = ['name', 'store', 'category', 'price', 'stock', 'status', 'item_type', 'compliance', 'actions'];
const visibleColumns = ref<string[]>([...allColumnIds]);

const baseColumns = computed<ColumnDef<ProductRow>[]>(() => [
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
            class: 'h-12 w-12 flex-shrink-0 overflow-hidden rounded-md border border-border bg-muted',
          },
          row.original.image_url
            ? h('img', {
              src: row.original.image_url,
              alt: row.original.name,
              class: 'h-full w-full object-cover object-center',
            })
            : h('div', { class: 'flex h-full w-full items-center justify-center text-muted-foreground' }, [
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
          h('p', { class: 'font-semibold text-foreground leading-snug line-clamp-2' }, row.original.name),
          row.original.brand
            ? h('p', { class: 'text-xs text-muted-foreground' }, row.original.brand)
            : null,
        ]),
      ]),
    meta: { class: 'min-w-[220px]' },
  },
  {
    id: 'store',
    header: () => 'Toko',
    cell: ({ row }) =>
      h(
        'span',
        { class: 'text-sm font-medium text-foreground' },
        row.original.store?.name ?? '-',
      ),
  },
  {
    id: 'category',
    header: () => 'Kategori',
    cell: ({ row }) =>
      h('span', { class: 'text-sm text-muted-foreground' }, row.original.category?.name ?? '-'),
  },
  {
    id: 'price',
    header: () => 'Harga',
    cell: ({ row }) =>
      h('div', { class: 'space-y-0.5 text-sm' }, [
        h(
          'p',
          { class: 'font-semibold text-foreground' },
          formatCurrency(row.original.sale_price ?? row.original.price),
        ),
        row.original.sale_price
          ? h(
            'p',
            { class: 'text-xs text-muted-foreground line-through' },
            formatCurrency(row.original.price),
          )
          : null,
      ]),
    meta: { class: 'min-w-[140px]' },
  },
  {
    id: 'stock',
    header: () => 'Kuantitas',
    cell: ({ row }) =>
      h('div', { class: 'text-sm text-foreground' }, [
        h('p', { class: 'font-semibold' }, `${row.original.stock} stok`),
        h('p', { class: 'text-xs text-muted-foreground' }, `Min order ${row.original.min_order}`),
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
            'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ' +
            (row.original.status === 'ready'
              ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400'
              : row.original.status === 'pre_order'
                ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-400'
                : 'bg-muted text-muted-foreground'),
        },
        getStatusLabel(row.original.status),
      ),
    meta: { class: 'w-36' },
  },
  {
    id: 'item_type',
    header: () => 'Jenis',
    cell: ({ row }) =>
      h('span', { class: 'text-xs text-muted-foreground' }, getItemTypeLabel(row.original.item_type)),
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
        // ikon edit
        h(
          'span',
          {
            class:
              'inline-flex h-8 w-8 items-center justify-center rounded-full ' +
              'cursor-pointer text-muted-foreground hover:bg-muted hover:text-foreground',
            onClick: () => router.visit(`/admin/products/${row.original.id}/edit`),
          },
          [h(Edit2, { class: 'h-[15px] w-[15px]' })],
        ),
        // ikon delete
        h(
          'span',
          {
            class:
              'inline-flex h-8 w-8 items-center justify-center rounded-full ' +
              'cursor-pointer text-muted-foreground hover:bg-destructive/10 hover:text-destructive',
            onClick: () => requestDelete(row.original),
          },
          [h(Trash2, { class: 'h-[15px] w-[15px]' })],
        ),
      ]),
    meta: { class: 'text-right w-32' },
    enableHiding: false,
  },
]);

const tableColumns = computed(() => {
  return baseColumns.value.filter((column) => {
    const columnId = column.id ?? (column as any).accessorKey;
    if (!columnId) return true;
    return visibleColumns.value.includes(columnId);
  });
});

const toggleColumn = (columnId: string) => {
  if (columnId === 'actions') return;
  if (visibleColumns.value.includes(columnId)) {
    visibleColumns.value = visibleColumns.value.filter((id) => id !== columnId);
  } else {
    visibleColumns.value = [...visibleColumns.value, columnId];
  }
};

const columnOptions = [
  { id: 'name', label: 'Produk' },
  { id: 'store', label: 'Toko' },
  { id: 'category', label: 'Kategori' },
  { id: 'price', label: 'Harga' },
  { id: 'stock', label: 'Kuantitas' },
  { id: 'status', label: 'Status' },
  { id: 'item_type', label: 'Jenis' },
  { id: 'compliance', label: 'Label' },
  { id: 'actions', label: 'Aksi', disabled: true },
];

// RESET FILTER
const resetFilters = () => {
  search.value = '';
  storeFilter.value = '';
  categoryFilter.value = '';
  statusFilter.value = '';

  storeFilterSearch.value = '';
  categoryFilterSearch.value = '';
  statusFilterSearch.value = '';
};

// Check if any filter or search is active
const hasActiveFilters = computed(() => {
  return !!search.value || !!storeFilter.value || !!categoryFilter.value || !!statusFilter.value;
});
</script>

<template>
  <div class="space-y-6">

    <Head title="Produk" />

    <!-- Alert success -->
    <Alert v-if="flashSuccess" variant="default" class="flex items-start gap-3 border-green-200 bg-green-50">
      <CheckCircle2 class="h-5 w-5 text-green-600" />
      <div class="space-y-1">
        <AlertTitle class="text-green-800">Berhasil</AlertTitle>
        <AlertDescription class="text-green-700">
          {{ flashSuccess }}
        </AlertDescription>
      </div>
    </Alert>

    <!-- Header atas -->
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <h1 class="text-2xl font-semibold text-slate-900">Produk Marketplace</h1>
      </div>
      <Button as-child>
        <Link href="/admin/products/create">
          <Plus class="h-4 w-4" />
          Produk
        </Link>
      </Button>
    </div>

    <section class="space-y-4">
      <!-- Header + filter -->
      <div class="flex flex-wrap items-center justify-between gap-3">
        <!-- Left side: Search -->
        <div class="relative w-full md:w-64">
          <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
            <Search class="h-4 w-4" />
          </span>
          <Input v-model="search" placeholder="Cari nama atau brand" class="w-full pl-9" />
        </div>

        <!-- Right side: Reset, Filter, Kolom (left to right = Kolom, Filter, Reset from right to left) -->
        <div class="flex flex-wrap items-center gap-3">
          <!-- Tombol Reset Filter (only shown when filters are active) -->
          <Button v-if="hasActiveFilters" variant="outline" class="gap-2" @click="resetFilters">
            <X class="h-4 w-4" />
            Reset
          </Button>

          <!-- Filter Popover -->
          <Popover v-model:open="filterPopoverOpen">
            <PopoverTrigger as-child>
              <Button variant="outline" class="gap-2">
                <Filter class="h-4 w-4" />
                Filter
              </Button>
            </PopoverTrigger>
            <PopoverContent align="end" class="w-80">
              <div class="space-y-4">
                <div class="space-y-2">
                  <h4 class="font-medium text-sm">Filter Produk</h4>
                  <p class="text-xs text-slate-500">Pilih filter yang ingin diterapkan</p>
                </div>

                <!-- Filter Toko -->
                <div class="space-y-2">
                  <label class="text-xs font-medium text-slate-700">Toko</label>
                  <Popover v-model:open="storeFilterPopoverOpen">
                    <PopoverTrigger as-child>
                      <Button variant="outline" role="combobox" class="w-full justify-between h-9 text-xs">
                        <span class="truncate">
                          {{ selectedStoreFilter?.name ?? 'Semua toko' }}
                        </span>
                        <ChevronsUpDown class="ml-2 h-3 w-3 shrink-0 opacity-50" />
                      </Button>
                    </PopoverTrigger>
                    <PopoverContent align="start" class="w-[320px] p-0">
                      <Command>
                        <CommandInput v-model="storeFilterSearch" placeholder="Cari toko..." />
                        <CommandEmpty>Toko tidak ditemukan.</CommandEmpty>
                        <CommandGroup>
                          <CommandItem value="Semua toko"
                            @select="() => { storeFilter = ''; storeFilterPopoverOpen = false; }">
                            <Check class="mr-2 h-4 w-4" :class="!storeFilter ? 'opacity-100' : 'opacity-0'" />
                            <span>Semua toko</span>
                          </CommandItem>

                          <CommandItem v-for="option in filteredStoreFilterOptions" :key="option.id"
                            :value="option.name"
                            @select="() => { storeFilter = option.id.toString(); storeFilterPopoverOpen = false; }">
                            <Check class="mr-2 h-4 w-4"
                              :class="storeFilter === option.id.toString() ? 'opacity-100' : 'opacity-0'" />
                            <span>{{ option.name }}</span>
                          </CommandItem>
                        </CommandGroup>
                      </Command>
                    </PopoverContent>
                  </Popover>
                </div>

                <!-- Filter Kategori -->
                <div class="space-y-2">
                  <label class="text-xs font-medium text-slate-700">Kategori</label>
                  <Popover v-model:open="categoryFilterPopoverOpen">
                    <PopoverTrigger as-child>
                      <Button variant="outline" role="combobox" class="w-full justify-between h-9 text-xs">
                        <span class="truncate">
                          {{ selectedCategoryFilter?.name ?? 'Semua kategori' }}
                        </span>
                        <ChevronsUpDown class="ml-2 h-3 w-3 shrink-0 opacity-50" />
                      </Button>
                    </PopoverTrigger>
                    <PopoverContent align="start" class="w-[320px] p-0">
                      <Command>
                        <CommandInput v-model="categoryFilterSearch" placeholder="Cari kategori..." />
                        <CommandEmpty>Kategori tidak ditemukan.</CommandEmpty>
                        <CommandGroup>
                          <CommandItem value="Semua kategori"
                            @select="() => { categoryFilter = ''; categoryFilterPopoverOpen = false; }">
                            <Check class="mr-2 h-4 w-4" :class="!categoryFilter ? 'opacity-100' : 'opacity-0'" />
                            <span>Semua kategori</span>
                          </CommandItem>

                          <CommandItem v-for="option in filteredCategoryFilterOptions" :key="option.id"
                            :value="option.name"
                            @select="() => { categoryFilter = option.id.toString(); categoryFilterPopoverOpen = false; }">
                            <Check class="mr-2 h-4 w-4"
                              :class="categoryFilter === option.id.toString() ? 'opacity-100' : 'opacity-0'" />
                            <span>{{ option.name }}</span>
                          </CommandItem>
                        </CommandGroup>
                      </Command>
                    </PopoverContent>
                  </Popover>
                </div>

                <!-- Filter Status -->
                <div class="space-y-2">
                  <label class="text-xs font-medium text-slate-700">Status</label>
                  <Popover v-model:open="statusFilterPopoverOpen">
                    <PopoverTrigger as-child>
                      <Button variant="outline" role="combobox" class="w-full justify-between h-9 text-xs">
                        <span class="truncate">
                          {{ selectedStatusFilter?.label ?? 'Semua status' }}
                        </span>
                        <ChevronsUpDown class="ml-2 h-3 w-3 shrink-0 opacity-50" />
                      </Button>
                    </PopoverTrigger>
                    <PopoverContent align="start" class="w-[320px] p-0">
                      <Command>
                        <CommandInput v-model="statusFilterSearch" placeholder="Cari status..." />
                        <CommandEmpty>Status tidak ditemukan.</CommandEmpty>
                        <CommandGroup>
                          <CommandItem value="Semua status"
                            @select="() => { statusFilter = ''; statusFilterPopoverOpen = false; }">
                            <Check class="mr-2 h-4 w-4" :class="!statusFilter ? 'opacity-100' : 'opacity-0'" />
                            <span>Semua status</span>
                          </CommandItem>

                          <CommandItem v-for="option in filteredStatusFilterOptions" :key="option.value"
                            :value="option.label"
                            @select="() => { statusFilter = option.value; statusFilterPopoverOpen = false; }">
                            <Check class="mr-2 h-4 w-4"
                              :class="statusFilter === option.value ? 'opacity-100' : 'opacity-0'" />
                            <span>{{ option.label }}</span>
                          </CommandItem>
                        </CommandGroup>
                      </Command>
                    </PopoverContent>
                  </Popover>
                </div>
              </div>
            </PopoverContent>
          </Popover>

          <!-- Dropdown Kolom (icon only) -->
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button variant="outline" size="icon">
                <SlidersHorizontal class="h-4 w-4" />
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" class="w-48">
              <DropdownMenuCheckboxItem v-for="option in columnOptions" :key="option.id"
                :model-value="visibleColumns.includes(option.id)" :disabled="option.disabled"
                @update:modelValue="() => toggleColumn(option.id)">
                {{ option.label }}
              </DropdownMenuCheckboxItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
      </div>

      <!-- Table wrapper -->
      <div class="overflow-hidden rounded-sm border border-slate-200 bg-white">
        <DataTable :columns="tableColumns" :data="products.data" />
      </div>

      <!-- Footer / pagination -->
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



    <!-- Dialog hapus -->
    <AlertDialog :open="deleteDialogOpen" @update:open="(value) => (deleteDialogOpen = value)">
      <AlertDialogContent class="text-center">
        <AlertDialogHeader class="space-y-4 text-center">
          <div
            class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-50 dark:bg-red-950/50 text-red-600">
            <AlertTriangle class="h-8 w-8" />
          </div>
          <AlertDialogTitle class="text-lg font-semibold text-foreground text-center">Hapus Produk?</AlertDialogTitle>
          <AlertDialogDescription class="text-sm text-muted-foreground text-center">
            Produk <strong>{{ deletingProduct?.name }}</strong> akan dihapus secara permanen dari katalog.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-center">
          <AlertDialogCancel class="sm:min-w-[120px]" @click="deleteDialogOpen = false">
            Batal
          </AlertDialogCancel>
          <AlertDialogAction class="bg-destructive hover:bg-destructive/90 sm:min-w-[120px]" @click="deleteProduct">
            Hapus
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </div>
</template>
