<script setup lang="ts">
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
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
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
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
import { computed, ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import {
  Building2,
  CheckCircle2,
  MapPin,
  Plus,
  Search,
  Shield,
  Store as StoreIcon,
  Trash2,
  Users,
  Filter,
  ChevronsUpDown,
  Check,
  X,
  Star,
  TrendingUp,
  ShoppingBag,
  Clock,
  ArrowRight,
  ExternalLink,
} from 'lucide-vue-next';

type SelectOption = {
  value: string;
  label: string;
};

const props = defineProps({
  stores: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  typeOptions: {
    type: Array as () => SelectOption[],
    default: () => [],
  },
  taxStatusOptions: {
    type: Array as () => SelectOption[],
    default: () => [],
  },
  metrics: {
    type: Object,
    default: () => ({
      total: 0,
      verified: 0,
      umkm: 0,
    }),
  },
});

const page = usePage();

const search = ref(props.filters.search ?? '');
const typeFilter = ref(props.filters.type ?? '');
const statusFilter = ref(props.filters.status ?? '');
const taxStatusFilter = ref(props.filters.tax_status ?? '');

// Filter popover states
const filterPopoverOpen = ref(false);
const typeFilterPopoverOpen = ref(false);
const statusFilterPopoverOpen = ref(false);
const taxStatusFilterPopoverOpen = ref(false);

const typeFilterSearch = ref('');
const statusFilterSearch = ref('');
const taxStatusFilterSearch = ref('');

const deleteDialogOpen = ref(false);
const deletingStore = ref<any | null>(null);
const deletingProcessing = ref(false);
const successMessage = ref<string | null>((page.props.flash as any)?.success ?? null);

watch(
  () => (page.props.flash as any)?.success,
  (value) => {
    successMessage.value = value ?? null;
  },
);

const typeLabelMap = computed<Record<string, string>>(() =>
  Object.fromEntries(props.typeOptions.map((option) => [option.value, option.label])),
);
const taxLabelMap = computed<Record<string, string>>(() =>
  Object.fromEntries(props.taxStatusOptions.map((option) => [option.value, option.label])),
);

// Verification Status Options
const statusOptions = [
  { value: 'verified', label: 'Terverifikasi' },
  { value: 'unverified', label: 'Belum Verifikasi' },
];

const selectedTypeFilter = computed(() =>
  props.typeOptions.find((option) => option.value === typeFilter.value)
);

const selectedStatusFilter = computed(() =>
  statusOptions.find((option) => option.value === statusFilter.value)
);

const selectedTaxStatusFilter = computed(() =>
  props.taxStatusOptions.find((option) => option.value === taxStatusFilter.value)
);

const filteredTypeOptions = computed(() => {
  if (!typeFilterSearch.value) return props.typeOptions;
  const term = typeFilterSearch.value.toLowerCase();
  return props.typeOptions.filter((option) =>
    option.label.toLowerCase().includes(term)
  );
});

const filteredStatusOptions = computed(() => {
  if (!statusFilterSearch.value) return statusOptions;
  const term = statusFilterSearch.value.toLowerCase();
  return statusOptions.filter((option) =>
    option.label.toLowerCase().includes(term)
  );
});

const filteredTaxStatusOptions = computed(() => {
  if (!taxStatusFilterSearch.value) return props.taxStatusOptions;
  const term = taxStatusFilterSearch.value.toLowerCase();
  return props.taxStatusOptions.filter((option) =>
    option.label.toLowerCase().includes(term)
  );
});

const resetFilters = () => {
  search.value = '';
  typeFilter.value = '';
  statusFilter.value = '';
  taxStatusFilter.value = '';

  typeFilterSearch.value = '';
  statusFilterSearch.value = '';
  taxStatusFilterSearch.value = '';
};

const hasActiveFilters = computed(() => {
  return !!search.value || !!typeFilter.value || !!statusFilter.value || !!taxStatusFilter.value;
});

const buildQuery = (override: Record<string, unknown> = {}) => ({
  search: search.value || undefined,
  type: typeFilter.value || undefined,
  status: statusFilter.value || undefined,
  tax_status: taxStatusFilter.value || undefined,
  ...override,
});

const debouncedSearch = useDebounceFn((value: string) => {
  router.get('/admin/stores', buildQuery({ search: value || undefined }), {
    preserveScroll: true,
    preserveState: true,
    replace: true,
  });
}, 400);

watch(search, (value) => {
  debouncedSearch(value);
});

watch([typeFilter, statusFilter, taxStatusFilter], () => {
  router.get('/admin/stores', buildQuery(), {
    preserveScroll: true,
    preserveState: true,
    replace: true,
  });
});

const requestDelete = (store: any) => {
  deletingStore.value = store;
  deleteDialogOpen.value = true;
};

const deleteStore = () => {
  if (!deletingStore.value) {
    return;
  }
  deletingProcessing.value = true;
  router.delete(`/admin/stores/${deletingStore.value.id}`, {
    preserveScroll: true,
    onFinish: () => {
      deleteDialogOpen.value = false;
      deletingStore.value = null;
      deletingProcessing.value = false;
    },
  });
};

const paginateTo = (url?: string | null) => {
  if (!url) return;
  router.visit(url, {
    preserveScroll: true,
    preserveState: true,
    replace: true,
  });
};

const numberedPaginationLinks = computed(() =>
  (props.stores.links ?? []).filter((link: any) => Number.isInteger(Number(link.label))),
);

defineOptions({
  layout: AdminDashboardLayout,
});
</script>

<template>
  <div class="space-y-6">

    <Head title="Manajemen Toko" />

    <!-- Header Section -->
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div>
        <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Vendor & Kemitraan</p>
        <h1 class="text-2xl font-bold text-foreground">Manajemen Toko</h1>
        <p class="text-sm text-muted-foreground mt-1">Kelola status verifikasi, profil toko, dan performa penjualan.
        </p>
      </div>
      <Button as-child class="gap-2 shadow-md shadow-primary/25">
        <Link href="/admin/stores/create">
          <Plus class="h-4 w-4" />
          Tambah Toko
        </Link>
      </Button>
    </div>

    <!-- Stats Cards -->
    <div class="grid gap-4 md:grid-cols-3">
      <Card class="border-0 shadow-sm bg-gradient-to-br from-blue-500 to-indigo-600 text-white">
        <CardContent class="pt-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-blue-100 text-sm font-medium">Total Toko Terdaftar</p>
              <p class="text-3xl font-bold mt-1">{{ metrics.total }}</p>
              <p class="text-blue-100 text-xs mt-2">Semua vendor aktif di platform</p>
            </div>
            <div class="h-12 w-12 rounded-xl bg-white/20 flex items-center justify-center">
              <StoreIcon class="h-6 w-6 text-white" />
            </div>
          </div>
        </CardContent>
      </Card>
      <Card class="border-0 shadow-sm bg-gradient-to-br from-emerald-500 to-teal-600 text-white">
        <CardContent class="pt-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-emerald-100 text-sm font-medium">Terverifikasi</p>
              <p class="text-3xl font-bold mt-1">{{ metrics.verified }}</p>
              <p class="text-emerald-100 text-xs mt-2">Sudah melalui proses verifikasi</p>
            </div>
            <div class="h-12 w-12 rounded-xl bg-white/20 flex items-center justify-center">
              <Shield class="h-6 w-6 text-white" />
            </div>
          </div>
        </CardContent>
      </Card>
      <Card class="border-0 shadow-sm bg-gradient-to-br from-amber-500 to-orange-600 text-white">
        <CardContent class="pt-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-amber-100 text-sm font-medium">Toko UMKM</p>
              <p class="text-3xl font-bold mt-1">{{ metrics.umkm }}</p>
              <p class="text-amber-100 text-xs mt-2">Mendapat prioritas dukungan</p>
            </div>
            <div class="h-12 w-12 rounded-xl bg-white/20 flex items-center justify-center">
              <Users class="h-6 w-6 text-white" />
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Filter Bar -->
    <Card class="border-0 shadow-sm bg-card">
      <CardContent class="pt-4 pb-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <!-- Left side: Search -->
          <div class="relative w-full md:w-96">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-muted-foreground">
              <Search class="h-4 w-4" />
            </span>
            <Input v-model="search" placeholder="Nama, kota, atau provinsi"
              class="w-full pl-9 bg-muted/30 border border-border focus-visible:ring-primary" />
          </div>

          <!-- Right side: Filter & Reset -->
          <div class="flex flex-wrap items-center gap-2">
            <Button v-if="hasActiveFilters" variant="ghost" size="sm" class="gap-2 text-muted-foreground"
              @click="resetFilters">
              <X class="h-4 w-4" />
              Reset
            </Button>

            <Popover v-model:open="filterPopoverOpen">
              <PopoverTrigger as-child>
                <Button variant="outline" size="sm" class="gap-2">
                  <Filter class="h-4 w-4" />
                  Filter
                  <Badge v-if="hasActiveFilters" class="ml-1 h-5 w-5 rounded-full p-0 text-[10px]">
                    {{ [typeFilter, statusFilter, taxStatusFilter].filter(Boolean).length }}
                  </Badge>
                </Button>
              </PopoverTrigger>
              <PopoverContent align="end" class="w-80">
                <div class="space-y-4">
                  <div class="space-y-1">
                    <h4 class="font-semibold text-sm">Filter Toko</h4>
                    <p class="text-xs text-muted-foreground">Pilih filter yang ingin diterapkan</p>
                  </div>

                  <!-- Filter Jenis Toko -->
                  <div class="space-y-2">
                    <Label class="text-xs font-medium">Jenis Toko</Label>
                    <Popover v-model:open="typeFilterPopoverOpen">
                      <PopoverTrigger as-child>
                        <Button variant="outline" role="combobox" class="w-full justify-between h-9 text-xs">
                          <span class="truncate">
                            {{ selectedTypeFilter?.label ?? 'Semua jenis' }}
                          </span>
                          <ChevronsUpDown class="ml-2 h-3 w-3 shrink-0 opacity-50" />
                        </Button>
                      </PopoverTrigger>
                      <PopoverContent align="start" class="w-[300px] p-0">
                        <Command>
                          <CommandInput v-model="typeFilterSearch" placeholder="Cari jenis..." />
                          <CommandEmpty>Jenis tidak ditemukan.</CommandEmpty>
                          <CommandGroup>
                            <CommandItem value="Semua jenis"
                              @select="() => { typeFilter = ''; typeFilterPopoverOpen = false; }">
                              <Check class="mr-2 h-4 w-4" :class="!typeFilter ? 'opacity-100' : 'opacity-0'" />
                              <span>Semua jenis</span>
                            </CommandItem>
                            <CommandItem v-for="option in filteredTypeOptions" :key="option.value" :value="option.label"
                              @select="() => { typeFilter = option.value; typeFilterPopoverOpen = false; }">
                              <Check class="mr-2 h-4 w-4"
                                :class="typeFilter === option.value ? 'opacity-100' : 'opacity-0'" />
                              <span>{{ option.label }}</span>
                            </CommandItem>
                          </CommandGroup>
                        </Command>
                      </PopoverContent>
                    </Popover>
                  </div>

                  <!-- Filter Status Verifikasi -->
                  <div class="space-y-2">
                    <Label class="text-xs font-medium">Status Verifikasi</Label>
                    <Popover v-model:open="statusFilterPopoverOpen">
                      <PopoverTrigger as-child>
                        <Button variant="outline" role="combobox" class="w-full justify-between h-9 text-xs">
                          <span class="truncate">
                            {{ selectedStatusFilter?.label ?? 'Semua status' }}
                          </span>
                          <ChevronsUpDown class="ml-2 h-3 w-3 shrink-0 opacity-50" />
                        </Button>
                      </PopoverTrigger>
                      <PopoverContent align="start" class="w-[300px] p-0">
                        <Command>
                          <CommandInput v-model="statusFilterSearch" placeholder="Cari status..." />
                          <CommandEmpty>Status tidak ditemukan.</CommandEmpty>
                          <CommandGroup>
                            <CommandItem value="Semua status"
                              @select="() => { statusFilter = ''; statusFilterPopoverOpen = false; }">
                              <Check class="mr-2 h-4 w-4" :class="!statusFilter ? 'opacity-100' : 'opacity-0'" />
                              <span>Semua status</span>
                            </CommandItem>
                            <CommandItem v-for="option in filteredStatusOptions" :key="option.value"
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

                  <!-- Filter Status Pajak -->
                  <div class="space-y-2">
                    <Label class="text-xs font-medium">Status Pajak</Label>
                    <Popover v-model:open="taxStatusFilterPopoverOpen">
                      <PopoverTrigger as-child>
                        <Button variant="outline" role="combobox" class="w-full justify-between h-9 text-xs">
                          <span class="truncate">
                            {{ selectedTaxStatusFilter?.label ?? 'Semua status pajak' }}
                          </span>
                          <ChevronsUpDown class="ml-2 h-3 w-3 shrink-0 opacity-50" />
                        </Button>
                      </PopoverTrigger>
                      <PopoverContent align="start" class="w-[300px] p-0">
                        <Command>
                          <CommandInput v-model="taxStatusFilterSearch" placeholder="Cari status pajak..." />
                          <CommandEmpty>Status pajak tidak ditemukan.</CommandEmpty>
                          <CommandGroup>
                            <CommandItem value="Semua status pajak"
                              @select="() => { taxStatusFilter = ''; taxStatusFilterPopoverOpen = false; }">
                              <Check class="mr-2 h-4 w-4" :class="!taxStatusFilter ? 'opacity-100' : 'opacity-0'" />
                              <span>Semua status pajak</span>
                            </CommandItem>
                            <CommandItem v-for="option in filteredTaxStatusOptions" :key="option.value"
                              :value="option.label"
                              @select="() => { taxStatusFilter = option.value; taxStatusFilterPopoverOpen = false; }">
                              <Check class="mr-2 h-4 w-4"
                                :class="taxStatusFilter === option.value ? 'opacity-100' : 'opacity-0'" />
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
          </div>
        </div>
      </CardContent>
    </Card>

    <Alert v-if="successMessage" variant="default"
      class="flex items-start gap-3 border-emerald-200 bg-emerald-50 dark:border-emerald-900 dark:bg-emerald-950/50">
      <CheckCircle2 class="h-5 w-5 text-emerald-600" />
      <div>
        <AlertTitle class="text-emerald-800 dark:text-emerald-200">Berhasil</AlertTitle>
        <AlertDescription class="text-emerald-700 dark:text-emerald-300">
          {{ successMessage }}
        </AlertDescription>
      </div>
    </Alert>

    <!-- Store List -->
    <Card class="border-0 shadow-sm bg-card">
      <CardHeader class="pb-2">
        <div class="flex items-center justify-between">
          <div>
            <CardTitle class="text-lg">Daftar Toko</CardTitle>
            <CardDescription>Menampilkan {{ stores.data.length }} dari {{ stores.total }} toko</CardDescription>
          </div>
        </div>
      </CardHeader>
      <CardContent class="p-0">
        <div v-if="stores.data.length === 0" class="flex flex-col items-center justify-center py-16 text-center">
          <div class="h-14 w-14 rounded-full bg-muted flex items-center justify-center">
            <StoreIcon class="h-7 w-7 text-muted-foreground" />
          </div>
          <h3 class="mt-4 text-sm font-semibold text-foreground">Tidak ada toko ditemukan</h3>
          <p class="mt-1 text-sm text-muted-foreground">Coba ubah filter atau kata kunci pencarian Anda.</p>
        </div>
        <div v-else class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-border bg-muted/30">
                <th class="text-left py-3 px-4 text-xs font-semibold text-muted-foreground uppercase tracking-wide">Toko
                </th>
                <th class="text-left py-3 px-4 text-xs font-semibold text-muted-foreground uppercase tracking-wide">
                  Lokasi</th>
                <th class="text-center py-3 px-4 text-xs font-semibold text-muted-foreground uppercase tracking-wide">
                  Status</th>
                <th class="text-center py-3 px-4 text-xs font-semibold text-muted-foreground uppercase tracking-wide">
                  Rating</th>
                <th class="text-center py-3 px-4 text-xs font-semibold text-muted-foreground uppercase tracking-wide">
                  Transaksi</th>
                <th class="text-center py-3 px-4 text-xs font-semibold text-muted-foreground uppercase tracking-wide">
                  Produk</th>
                <th class="text-right py-3 px-4 text-xs font-semibold text-muted-foreground uppercase tracking-wide">
                  Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="store in stores.data" :key="store.id"
                class="border-b border-border last:border-0 hover:bg-muted/30 transition-colors">
                <td class="py-3 px-4">
                  <div class="flex items-center gap-3">
                    <div class="relative shrink-0 overflow-hidden rounded-lg border border-border bg-muted/50">
                      <img v-if="store.logo_url" :src="store.logo_url" :alt="store.name"
                        class="h-10 w-10 object-cover" />
                      <div v-else class="grid h-10 w-10 place-items-center text-muted-foreground">
                        <StoreIcon class="h-5 w-5" />
                      </div>
                    </div>
                    <div class="min-w-0">
                      <p class="text-sm font-semibold text-foreground truncate max-w-[200px]">{{ store.name }}</p>
                      <p class="text-xs text-muted-foreground">{{ typeLabelMap[store.type] ?? store.type }}</p>
                    </div>
                  </div>
                </td>
                <td class="py-3 px-4">
                  <div class="flex items-center gap-1.5 text-sm text-muted-foreground">
                    <MapPin class="h-3.5 w-3.5 flex-shrink-0" />
                    <span class="truncate max-w-[150px]">{{ store.city ?? '-' }}{{ store.city && store.province ? ', ' :
                      '' }}{{ store.province ?? '' }}</span>
                  </div>
                </td>
                <td class="py-3 px-4">
                  <div class="flex items-center justify-center gap-1.5 flex-wrap">
                    <Badge v-if="store.is_verified"
                      class="bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400 border-0 gap-1 text-[10px]">
                      <Shield class="h-3 w-3" /> Verified
                    </Badge>
                    <Badge v-if="store.is_umkm"
                      class="bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-400 border-0 gap-1 text-[10px]">
                      <Users class="h-3 w-3" /> UMKM
                    </Badge>
                    <span v-if="!store.is_verified && !store.is_umkm" class="text-xs text-muted-foreground">-</span>
                  </div>
                </td>
                <td class="py-3 px-4 text-center">
                  <div class="flex items-center justify-center gap-1">
                    <Star class="h-3.5 w-3.5 text-amber-500 fill-amber-500" /><span
                      class="text-sm font-medium text-foreground">{{ store.rating ? Number(store.rating).toFixed(1) :
                        '-' }}</span>
                  </div>
                </td>
                <td class="py-3 px-4 text-center"><span class="text-sm font-medium text-foreground">{{
                  store.transactions_count?.toLocaleString() ?? 0 }}</span></td>
                <td class="py-3 px-4 text-center"><span class="text-sm font-medium text-foreground">{{
                  store.products_count ?? 0 }}</span></td>
                <td class="py-3 px-4">
                  <div class="flex items-center justify-end gap-1">
                    <Button variant="ghost" size="sm" as-child
                      class="h-8 px-3 text-primary hover:text-primary hover:bg-primary/10">
                      <Link :href="`/admin/stores/${store.id}/edit`">Kelola</Link>
                    </Button>
                    <Button variant="ghost" size="icon"
                      class="h-8 w-8 text-muted-foreground hover:bg-destructive/10 hover:text-destructive"
                      @click="requestDelete(store)">
                      <Trash2 class="h-4 w-4" />
                    </Button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </CardContent>
      <CardFooter class="flex flex-wrap items-center justify-between gap-3 border-t border-border pt-4">
        <p class="text-sm text-muted-foreground">Menampilkan {{ stores.data.length }} dari {{ stores.total }} toko.</p>
        <div class="flex flex-wrap items-center gap-1">
          <Button variant="outline" size="sm" :disabled="!stores.prev_page_url"
            @click="paginateTo(stores.prev_page_url)">Sebelumnya</Button>
          <Button v-for="link in numberedPaginationLinks" :key="link.label" size="sm"
            :variant="link.active ? 'default' : 'outline'" :aria-current="link.active ? 'page' : undefined"
            :disabled="!link.url" @click="paginateTo(link.url)">{{ link.label }}</Button>
          <Button variant="outline" size="sm" :disabled="!stores.next_page_url"
            @click="paginateTo(stores.next_page_url)">Selanjutnya</Button>
        </div>
      </CardFooter>
    </Card>

    <AlertDialog :open="deleteDialogOpen" @update:open="(value) => (deleteDialogOpen = value)">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Hapus Toko?</AlertDialogTitle>
          <AlertDialogDescription>
            Tindakan ini akan menghapus data toko {{ deletingStore?.name }} secara permanen.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel :disabled="deletingProcessing">Batal</AlertDialogCancel>
          <AlertDialogAction class="bg-destructive hover:bg-destructive/90" :disabled="deletingProcessing"
            @click="deleteStore">
            Hapus
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </div>
</template>
