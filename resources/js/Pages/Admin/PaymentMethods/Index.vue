<script setup lang="ts">
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, h, ref } from 'vue';
import { Button } from '@/components/ui/button';
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
import { Trash2, Edit2, Plus, CheckCircle2 } from 'lucide-vue-next';
import type { ColumnDef } from '@tanstack/vue-table';

type PaymentMethodRow = {
  id: number;
  name: string;
  code: string;
  is_active: boolean;
  logo_url: string | null;
};

const props = defineProps<{
  paymentMethods: PaymentMethodRow[];
}>();

defineOptions({
  layout: AdminDashboardLayout,
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success ?? '');
const deleteDialogOpen = ref(false);
const deletingMethod = ref<PaymentMethodRow | null>(null);

const requestDelete = (method: PaymentMethodRow) => {
  deletingMethod.value = method;
  deleteDialogOpen.value = true;
};

const deleteMethod = () => {
  if (!deletingMethod.value) return;
  router.delete(`/admin/payment-methods/${deletingMethod.value.id}`, {
    preserveScroll: true,
    onFinish: () => {
      deleteDialogOpen.value = false;
      deletingMethod.value = null;
    },
  });
};

const columns = computed<ColumnDef<PaymentMethodRow>[]>(() => [
  {
    accessorKey: 'name',
    header: () => 'Nama',
    cell: ({ row }) =>
      h('span', { class: 'font-semibold text-slate-900' }, row.original.name),
  },
  {
    accessorKey: 'code',
    header: () => 'Kode',
    cell: ({ row }) =>
      h('span', { class: 'text-sm text-slate-600 font-mono' }, row.original.code),
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
            onClick: () => router.visit(`/admin/payment-methods/${row.original.id}/edit`),
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

    <Head title="Metode Pembayaran" />

    <Alert v-if="flashSuccess" variant="default" class="flex items-start gap-3 border-green-200 bg-green-50">
      <CheckCircle2 class="h-5 w-5 text-green-600" />
      <div class="space-y-1">
        <AlertTitle class="text-green-800">Berhasil</AlertTitle>
        <AlertDescription class="text-green-700">{{ flashSuccess }}</AlertDescription>
      </div>
    </Alert>

    <div class="flex flex-wrap items-center justify-between gap-3">
      <h1 class="text-2xl font-semibold text-slate-900">Metode Pembayaran</h1>
      <Button as-child>
        <Link href="/admin/payment-methods/create">
        <Plus class="h-4 w-4" />
        Tambah Metode
        </Link>
      </Button>
    </div>

    <div class="overflow-hidden rounded-sm border border-slate-200 bg-white">
      <DataTable :columns="columns" :data="paymentMethods" />
    </div>

    <AlertDialog :open="deleteDialogOpen" @update:open="(value) => (deleteDialogOpen = value)">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Hapus metode pembayaran?</AlertDialogTitle>
          <AlertDialogDescription>
            Metode <strong>{{ deletingMethod?.name }}</strong> akan dihapus permanen.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel @click="deleteDialogOpen = false">Batal</AlertDialogCancel>
          <AlertDialogAction class="bg-red-600 hover:bg-red-700" @click="deleteMethod">
            Hapus
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </div>
</template>
