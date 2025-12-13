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

type ProductPayload = {
  id: number;
  category_id: number | null;
  name: string;
  slug: string | null;
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
  visibility_scope: string;
  location_city: string | null;
  location_province: string | null;
  location_province_id: number | null;
  location_city_id: number | null;
  location_district_id: number | null;
  is_pdn: boolean;
  is_pkp: boolean;
  is_tkdn: boolean;
  images: { id: number; url: string }[];
};

const props = defineProps<{
  product: ProductPayload;
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

    <Head :title="`Edit ${product.name}`" />

    <div class="flex items-center justify-between gap-2">
      <div>
        <p class="text-xs uppercase tracking-wide text-slate-500">Ubah Produk</p>
        <h1 class="text-xl font-bold tracking-tight text-slate-900">
          {{ product.name }}
        </h1>
      </div>
      <Button variant="outline" size="sm" as-child>
        <Link href="/seller/products">
        Kembali
        </Link>
      </Button>
    </div>

    <ProductForm mode="edit" :product="props.product" :category-options="props.categoryOptions"
      :statuses="props.statuses" :item-types="props.itemTypes" :visibility-options="props.visibilityOptions"
      :store-location="props.storeLocation" :submit-url="`/seller/products/${product.id}`">
      <template #cancel>
        <Link href="/seller/products">Batal</Link>
      </template>
    </ProductForm>
  </div>
</template>
