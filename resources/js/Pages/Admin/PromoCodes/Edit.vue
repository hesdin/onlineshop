<script setup lang="ts">
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps<{
  promoCode: any;
}>();

defineOptions({
  layout: AdminDashboardLayout,
});

const form = useForm({
  code: props.promoCode.code,
  description: props.promoCode.description ?? '',
  discount_type: props.promoCode.discount_type,
  discount_value: props.promoCode.discount_value,
  min_order_amount: props.promoCode.min_order_amount ?? 0,
  max_discount: props.promoCode.max_discount,
  starts_at: props.promoCode.starts_at ?? '',
  ends_at: props.promoCode.ends_at ?? '',
  quota: props.promoCode.quota,
  is_active: props.promoCode.is_active,
});

const submit = () => {
  form.put(`/admin/promo-codes/${props.promoCode.id}`, {
    preserveScroll: true,
  });
};
</script>

<template>
  <div class="space-y-6">

    <Head title="Edit Kode Promo" />

    <div class="flex items-center gap-3">
      <Button variant="ghost" size="icon" as-child>
        <Link href="/admin/promo-codes">
        <ArrowLeft class="h-4 w-4" />
        </Link>
      </Button>
      <h1 class="text-2xl font-semibold text-slate-900">Edit Kode Promo</h1>
    </div>

    <Card>
      <CardHeader>
        <CardTitle>Informasi Kode Promo</CardTitle>
        <CardDescription>Edit kode promo {{ promoCode.code }}</CardDescription>
      </CardHeader>
      <CardContent>
        <form @submit.prevent="submit" class="space-y-4">
          <div class="space-y-2">
            <Label for="code">Kode</Label>
            <Input id="code" v-model="form.code" class="uppercase" :class="{ 'border-red-500': form.errors.code }" />
            <p v-if="form.errors.code" class="text-xs text-red-600">{{ form.errors.code }}</p>
          </div>

          <div class="space-y-2">
            <Label for="description">Deskripsi</Label>
            <Textarea id="description" v-model="form.description" rows="2" />
          </div>

          <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-2">
              <Label for="discount_type">Tipe Diskon</Label>
              <Select v-model="form.discount_type">
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="percent">Persentase</SelectItem>
                  <SelectItem value="fixed">Nominal</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="space-y-2">
              <Label for="discount_value">Nilai Diskon</Label>
              <Input id="discount_value" type="number" v-model.number="form.discount_value"
                :class="{ 'border-red-500': form.errors.discount_value }" />
              <p v-if="form.errors.discount_value" class="text-xs text-red-600">{{ form.errors.discount_value }}</p>
            </div>
          </div>

          <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-2">
              <Label for="min_order_amount">Min. Pembelian</Label>
              <Input id="min_order_amount" type="number" v-model.number="form.min_order_amount" />
            </div>

            <div class="space-y-2">
              <Label for="max_discount">Max. Diskon</Label>
              <Input id="max_discount" type="number" v-model.number="form.max_discount" />
            </div>
          </div>

          <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-2">
              <Label for="starts_at">Tanggal Mulai</Label>
              <Input id="starts_at" type="datetime-local" v-model="form.starts_at" />
            </div>

            <div class="space-y-2">
              <Label for="ends_at">Tanggal Berakhir</Label>
              <Input id="ends_at" type="datetime-local" v-model="form.ends_at" />
            </div>
          </div>

          <div class="space-y-2">
            <Label for="quota">Kuota</Label>
            <Input id="quota" type="number" v-model.number="form.quota" />
          </div>

          <div class="flex items-center gap-2">
            <Switch id="is_active" :checked="form.is_active" @update:checked="(val) => (form.is_active = val)" />
            <Label for="is_active" class="font-normal cursor-pointer">Aktif</Label>
          </div>

          <div class="flex gap-3">
            <Button type="submit" :disabled="form.processing">Simpan</Button>
            <Button variant="outline" type="button" as-child>
              <Link href="/admin/promo-codes">Batal</Link>
            </Button>
          </div>
        </form>
      </CardContent>
    </Card>
  </div>
</template>
