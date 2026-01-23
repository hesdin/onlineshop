<script setup lang="ts">
import SellerDashboardLayout from '@/Layouts/SellerDashboardLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
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

defineOptions({
  layout: SellerDashboardLayout,
});

const form = useForm({
  code: '',
  description: '',
  discount_type: 'percent',
  discount_value: 0,
  min_order_amount: 0,
  max_discount: null as number | null,
  starts_at: '',
  ends_at: '',
  quota: null as number | null,
  is_active: true,
});

const submit = () => {
  form.post('/seller/promo-codes', {
    preserveScroll: true,
  });
};
</script>

<template>
  <div class="space-y-6">

    <Head title="Tambah Promo Baru" />

    <div class="flex items-center justify-between gap-3">
      <div class="space-y-1">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Tambah Promo</h1>
        <p class="text-sm text-slate-500">Buat promo baru untuk menarik lebih banyak pembeli.</p>
      </div>
      <Button variant="outline" size="sm" @click="router.visit('/seller/promo-codes')" :disabled="form.processing">
        Kembali
      </Button>
    </div>

    <Card>
      <CardHeader>
        <CardTitle>Detail Promo</CardTitle>
        <CardDescription>Isi detail informasi promo Anda di bawah ini.</CardDescription>
      </CardHeader>
      <CardContent>
        <form @submit.prevent="submit" class="space-y-6">
          <div class="grid gap-6 md:grid-cols-2">
            <div class="space-y-2">
              <Label for="code">Kode Promo</Label>
              <Input id="code" v-model="form.code" placeholder="CONTOH: PROMOTOKO10" class="uppercase font-mono"
                :class="{ 'border-red-500': form.errors.code }" />
              <p class="text-[10px] text-slate-500 italic">Gunakan karakter alfanumerik (huruf Besar & angka).</p>
              <p v-if="form.errors.code" class="text-xs text-red-600">{{ form.errors.code }}</p>
            </div>

            <div class="space-y-2">
              <Label for="is_active">Status Promo</Label>
              <div class="flex items-center h-10 gap-3 px-3 border rounded-md">
                <Switch id="is_active" :checked="form.is_active" @update:checked="(val) => (form.is_active = val)" />
                <span class="text-sm font-medium text-slate-700">{{ form.is_active ? 'Aktif' : 'Non-aktif' }}</span>
              </div>
            </div>
          </div>

          <div class="space-y-2">
            <Label for="description">Deskripsi Promo (Opsional)</Label>
            <Textarea id="description" v-model="form.description" rows="2"
              placeholder="Jelaskan syart dan ketentuan promo ini..." />
          </div>

          <div class="grid gap-6 md:grid-cols-2">
            <div class="space-y-2">
              <Label for="discount_type">Tipe Diskon</Label>
              <Select v-model="form.discount_type">
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="percent">Persentase (%)</SelectItem>
                  <SelectItem value="fixed">Nominal (Rp)</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="space-y-2">
              <Label for="discount_value">Nilai Diskon</Label>
              <div class="relative">
                <span v-if="form.discount_type === 'fixed'"
                  class="absolute left-3 top-2.5 text-slate-400 text-sm">Rp</span>
                <Input id="discount_value" type="number" v-model.number="form.discount_value"
                  :class="{ 'border-red-500': form.errors.discount_value, 'pl-9': form.discount_type === 'fixed' }" />
                <span v-if="form.discount_type === 'percent'"
                  class="absolute right-3 top-2.5 text-slate-400 text-sm">%</span>
              </div>
              <p v-if="form.errors.discount_value" class="text-xs text-red-600">{{ form.errors.discount_value }}</p>
            </div>
          </div>

          <div class="grid gap-6 md:grid-cols-2">
            <div class="space-y-2">
              <Label for="min_order_amount">Minimum Pembelian (Rp)</Label>
              <div class="relative">
                <span class="absolute left-3 top-2.5 text-slate-400 text-sm">Rp</span>
                <Input id="min_order_amount" type="number" v-model.number="form.min_order_amount" class="pl-9" />
              </div>
              <p class="text-[10px] text-slate-500 italic">Promo hanya berlaku jika belanja mencapai nominal ini.</p>
            </div>

            <div class="space-y-2">
              <Label for="max_discount">Maksimum Diskon (Rp)</Label>
              <div class="relative">
                <span class="absolute left-3 top-2.5 text-slate-400 text-sm">Rp</span>
                <Input id="max_discount" type="number" v-model.number="form.max_discount" placeholder="Tanpa batas"
                  class="pl-9" />
              </div>
              <p class="text-[10px] text-slate-500 italic">Khusus tipe persentase, batasi nominal diskon maksimal.</p>
            </div>
          </div>

          <div class="grid gap-6 md:grid-cols-2">
            <div class="space-y-2">
              <Label for="starts_at">Tanggal Mulai</Label>
              <Input id="starts_at" type="datetime-local" v-model="form.starts_at"
                :class="{ 'border-red-500': form.errors.starts_at }" />
              <p v-if="form.errors.starts_at" class="text-xs text-red-600">{{ form.errors.starts_at }}</p>
            </div>

            <div class="space-y-2">
              <Label for="ends_at">Tanggal Berakhir</Label>
              <Input id="ends_at" type="datetime-local" v-model="form.ends_at"
                :class="{ 'border-red-500': form.errors.ends_at }" />
              <p v-if="form.errors.ends_at" class="text-xs text-red-600">{{ form.errors.ends_at }}</p>
            </div>
          </div>

          <div class="space-y-2">
            <Label for="quota">Kuota Promo</Label>
            <Input id="quota" type="number" v-model.number="form.quota"
              placeholder="Isi angka, atau kosongkan untuk tidak terbatas" />
            <p class="text-[10px] text-slate-500 italic">Jumlah maksimal pemakaian promo ini.</p>
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t">
            <Button variant="outline" type="button" as-child :disabled="form.processing">
              <Link href="/seller/promo-codes">Batal</Link>
            </Button>
            <Button type="submit" :disabled="form.processing">
              {{ form.processing ? 'Menyimpan...' : 'Simpan Promo' }}
            </Button>
          </div>
        </form>
      </CardContent>
    </Card>
  </div>
</template>
