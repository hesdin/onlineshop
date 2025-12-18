<script setup lang="ts">
import SellerDashboardLayout from '@/Layouts/SellerDashboardLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import ProductForm from './ProductForm.vue';
import { onMounted } from 'vue';

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
  shippingMethods: SelectOption[];
  storeLocation: StoreLocation | null;
}>();

defineOptions({
  layout: SellerDashboardLayout,
});

const page = usePage();

onMounted(() => {
  const docStatus = page.props.auth?.seller_document;
  if (!docStatus?.is_approved) {
    // If not approved, redirect to documents
    // Note: If we wanted to allow drafts for "submitted", we would check !docStatus.is_submitted
    // But for strictly preventing creation until verified (simplest soft restriction), we redirect.
    // However, plan said "Submitted: Allow create product draft".
    // So let's check: if (!is_approved && !is_submitted) -> redirect

    // But actually, if they are submitted, they should be able to create but only save as draft (controlled in form).
    // So strictly redirect only if NO doc or REJECTED.
    if (!docStatus?.submission_status || docStatus?.submission_status === 'draft' || docStatus?.submission_status === 'rejected') {
      // Optional: Let them see page but maybe specific errors?
      // Plan says: "Redirect ke /seller/documents jika !isApproved".
      // I will follow plan but slightly relaxed for 'submitted' if possible?
      // Let's stick to the simpler rule: if not approved, you can't access CREATE page?
      // Wait, if I block access to create page, how can they create draft?
      // So I must NOT redirect if submitted.

      if (docStatus?.submission_status !== 'submitted') {
        router.get('/seller/documents');
      }
    }
  }
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
      :shipping-methods="props.shippingMethods" :store-location="props.storeLocation"
      :can-publish="$page.props.auth.seller_document?.is_approved">
      <template #cancel>
        <Link href="/seller/products">Batal</Link>
      </template>
    </ProductForm>
  </div>
</template>
