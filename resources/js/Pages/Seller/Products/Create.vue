<script setup lang="ts">
import SellerDashboardLayout from '@/Layouts/SellerDashboardLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import ProductForm from './ProductForm.vue';

type Option = {
  id: number;
  name: string;
};

type SelectOption = {
  value: string;
  label: string;
};

type StoreLocation = {
  province_id: number | null;
  city_id: number | null;
  district_id: number | null;
  postal_code: string | null;
  province_name?: string | null;
  city_name?: string | null;
  district_name?: string | null;
};

const props = defineProps<{
  categoryOptions: Option[];
  statuses: SelectOption[];
  itemTypes: SelectOption[];
  visibilityOptions: SelectOption[];
  storeLocation: StoreLocation | null;
}>();

defineOptions({
  layout: SellerDashboardLayout,
});
</script>

<template>
  <div class="mx-auto space-y-6">

    <Head title="Tambah Produk" />

    <div class="flex items-center justify-between gap-2">
      <div class="space-y-1">
        <!-- <p class="text-xs uppercase tracking-wide text-slate-500">Produk Baru</p> -->
        <h1 class="text-2xl font-semibold text-slate-900">Tambah Produk Toko</h1>
        <p class="text-sm text-slate-500">
          Lengkapi data produk agar mudah ditemukan pembeli.
        </p>
      </div>
      <Button variant="outline" size="sm" as-child>
        <Link href="/seller/products">Kembali</Link>
      </Button>
    </div>

    <ProductForm mode="create" submit-url="/seller/products" :category-options="props.categoryOptions"
      :statuses="props.statuses" :item-types="props.itemTypes" :visibility-options="props.visibilityOptions"
      :store-location="props.storeLocation">
      <template #cancel>
        <Link href="/seller/products">Batal</Link>
      </template>
    </ProductForm>
  </div>
</template>
