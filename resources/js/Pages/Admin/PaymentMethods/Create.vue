<script setup lang="ts">
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

defineOptions({
  layout: AdminDashboardLayout,
});

const form = useForm({
  name: '',
  code: '',
  channel: 'transfer',
  is_active: true,
});

const submit = () => {
  form.post('/admin/payment-methods', {
    preserveScroll: true,
  });
};
</script>

<template>
  <div class="space-y-6">

    <Head title="Tambah Metode Pembayaran" />

    <div class="flex items-center justify-between gap-3">
      <h1 class="text-xl font-bold tracking-tight text-slate-900">Tambah Metode Pembayaran</h1>
      <Button variant="outline" size="sm" @click="router.visit('/admin/payment-methods')" :disabled="form.processing">
        Kembali
      </Button>
    </div>

    <Card>
      <CardHeader>
        <CardTitle>Informasi Metode Pembayaran</CardTitle>
        <CardDescription>Tambahkan metode pembayaran baru</CardDescription>
      </CardHeader>
      <CardContent>
        <form @submit.prevent="submit" class="space-y-4">
          <div class="space-y-2">
            <Label for="name">Nama</Label>
            <Input id="name" v-model="form.name" :class="{ 'border-red-500': form.errors.name }" />
            <p v-if="form.errors.name" class="text-xs text-red-600">{{ form.errors.name }}</p>
          </div>

          <div class="space-y-2">
            <Label for="code">Kode</Label>
            <Input id="code" v-model="form.code" :class="{ 'border-red-500': form.errors.code }" />
            <p v-if="form.errors.code" class="text-xs text-red-600">{{ form.errors.code }}</p>
          </div>

          <div class="space-y-2">
            <Label for="channel">Channel</Label>
            <Input id="channel" v-model="form.channel" :class="{ 'border-red-500': form.errors.channel }" />
            <p v-if="form.errors.channel" class="text-xs text-red-600">{{ form.errors.channel }}</p>
          </div>

          <div class="flex items-center gap-2">
            <Switch id="is_active" :checked="form.is_active" @update:checked="(val) => (form.is_active = val)" />
            <Label for="is_active" class="font-normal cursor-pointer">Aktif</Label>
          </div>

          <div class="flex gap-3">
            <Button type="submit" :disabled="form.processing">Simpan</Button>
            <Button variant="outline" type="button" as-child>
              <Link href="/admin/payment-methods">Batal</Link>
            </Button>
          </div>
        </form>
      </CardContent>
    </Card>
  </div>
</template>
