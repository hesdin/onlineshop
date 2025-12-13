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
import { DataTable } from '@/components/ui/data-table';
import { Trash2, Edit2, Plus, CheckCircle2, Search } from 'lucide-vue-next';
import type { ColumnDef } from '@tanstack/vue-table';

type CollectionRow = {
  id: number;
  title: string;
  slug: string;
  is_active: boolean;
  products_count: number;
  cover_image_url: string | null;
  created_at: string;
};

const props = defineProps<{
  collections: {
    data: CollectionRow[];
    total: number;
    links: { label: string; url: string | null; active: boolean }[];
    prev_page_url: string | null;
    next_page_url: string | null;
  };
  filters: {
    search?: string | null;
  };
}>();

defineOptions({
  layout: AdminDashboardLayout,
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success ?? '');
const search = ref(props.filters.search ?? '');
const deleteDialogOpen = ref(false);
const deletingCollection = ref<CollectionRow | null>(null);

const debouncedSearch = useDebounceFn((value: string) => {
  router.get('/admin/collections', { search: value || undefined }, {
    preserveState: true,
    replace: true,
    preserveScroll: true,
  });
}, 400);

watch(search, (value) => {
  debouncedSearch(value);
});

const requestDelete = (collection: CollectionRow) => {
  deletingCollection.value = collection;
  deleteDialogOpen.value = true;
};

const deleteCollection = () => {
  if (!deletingCollection.value) return;
  router.delete(`/admin/collections/${deletingCollection.value.id}`, {
    preserveScroll: true,
    onFinish: () => {
      deleteDialogOpen.value = false;
      deletingCollection.value = null;
    },
  });
};

const numberedPaginationLinks = computed(() =>
  (props.collections.links ?? []).filter((link) => Number.isInteger(Number(link.label))),
);

const paginateTo = (url?: string | null) => {
  if (!url) return;
  router.visit(url, {
    preserveScroll: true,
    preserveState: true,
    replace: true,
  });
};

const columns = computed<ColumnDef<CollectionRow>[]>(() => [
  {
    accessorKey: 'title',
    header: () => 'Judul',
    cell: ({ row }) =>
      h('span', { class: 'font-semibold text-slate-900' }, row.original.title),
  },
  {
    accessorKey: 'products_count',
    header: () => 'Jumlah Produk',
    cell: ({ row }) =>
      h('span', { class: 'text-sm text-slate-600' }, `${row.original.products_count} produk`),
  },
  {
    accessorKey: 'is_active',
    header: () => 'Status',
    cell: ({ row }) =>
      h(
        'span',
        {
          class: row.original.is_active
            ? 'rounded-sm bg-green-100 px-2 py-1 text-xs font-semibold text-green-700'
            : 'rounded-sm bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-600',
        },
        row.original.is_active ? 'Aktif' : 'Tidak Aktif',
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
            onClick: () => router.visit(`/admin/collections/${row.original.id}/edit`),
          },
          [h(Edit2, { class: 'h-[15px] w-[15px]' })],
        ),
        h(
          'span',
          {
            class:
              'inline-flex h-8 w-8 items-center justify-center rounded-full ' +
              'cursor-pointer text-slate-500 hover:bg-red-50 hover:text-red-600',
            onClick: () => requestDelete(row.original),
          },
          [h(Trash2, { class: 'h-[15px] w-[15px]' })],
        ),
      ]),
    meta: { class: 'text-right w-32' },
  },
]);
</script>

<template>
  <div class="space-y-6">

    <Head title="Koleksi" />

    <Alert v-if="flashSuccess" variant="default" class="flex items-start gap-3 border-green-200 bg-green-50">
      <CheckCircle2 class="h-5 w-5 text-green-600" />
      <div class="space-y-1">
        <AlertTitle class="text-green-800">Berhasil</AlertTitle>
        <AlertDescription class="text-green-700">{{ flashSuccess }}</AlertDescription>
      </div>
    </Alert>

    <div class="flex flex-wrap items-center justify-between gap-3">
      <h1 class="text-2xl font-semibold text-slate-900">Manajemen Koleksi</h1>
      <Button as-child>
        <Link href="/admin/collections/create">
        <Plus class="h-4 w-4" />
        Tambah Koleksi
        </Link>
      </Button>
    </div>

    <section class="space-y-4">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="relative w-full md:w-64">
          <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
            <Search class="h-4 w-4" />
          </span>
          <Input v-model="search" placeholder="Cari koleksi" class="w-full pl-9" />
        </div>
      </div>

      <div class="overflow-hidden rounded-sm border border-slate-200 bg-white">
        <DataTable :columns="columns" :data="collections.data" />
      </div>

      <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">
        <p>Menampilkan {{ collections.data.length }} dari {{ collections.total }} koleksi.</p>
        <div class="flex flex-wrap items-center gap-1">
          <Button variant="outline" size="sm" :disabled="!collections.prev_page_url"
            @click="paginateTo(collections.prev_page_url)">
            Sebelumnya
          </Button>
          <Button v-for="link in numberedPaginationLinks" :key="link.label" size="sm"
            :variant="link.active ? 'default' : 'outline'" :disabled="!link.url" @click="paginateTo(link.url)">
            {{ link.label }}
          </Button>
          <Button variant="outline" size="sm" :disabled="!collections.next_page_url"
            @click="paginateTo(collections.next_page_url)">
            Selanjutnya
          </Button>
        </div>
      </div>
    </section>

    <AlertDialog :open="deleteDialogOpen" @update:open="(value) => (deleteDialogOpen = value)">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Hapus koleksi?</AlertDialogTitle>
          <AlertDialogDescription>
            Koleksi <strong>{{ deletingCollection?.title }}</strong> akan dihapus permanen.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel @click="deleteDialogOpen = false">Batal</AlertDialogCancel>
          <AlertDialogAction class="bg-red-600 hover:bg-red-700" @click="deleteCollection">
            Hapus
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </div>
</template>
