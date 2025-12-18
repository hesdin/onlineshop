<script setup lang="ts">
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

defineOptions({
  layout: AdminDashboardLayout,
});

const form = useForm({
  title: '',
  description: '',
  is_active: true,
});

const submit = () => {
  form.post('/admin/collections', {
    preserveScroll: true,
  });
};
</script>

<template>
  <div class="space-y-6">

    <Head title="Tambah Koleksi" />

    <div class="flex items-center justify-between gap-3">
      <h1 class="text-xl font-bold tracking-tight text-slate-900">Tambah Koleksi Baru</h1>
      <Button variant="outline" size="sm" @click="router.visit('/admin/collections')" :disabled="form.processing">
        Kembali
      </Button>
    </div>

    <Card>
      <CardHeader>
        <CardTitle>Informasi Koleksi</CardTitle>
        <CardDescription>Tambahkan koleksi produk baru</CardDescription>
      </CardHeader>
      <CardContent>
        <form @submit.prevent="submit" class="space-y-4">
          <div class="space-y-2">
            <Label for="title">Judul</Label>
            <Input id="title" v-model="form.title" :class="{ 'border-red-500': form.errors.title }" />
            <p v-if="form.errors.title" class="text-xs text-red-600">{{ form.errors.title }}</p>
          </div>

          <div class="space-y-2">
            <Label for="description">Deskripsi</Label>
            <Textarea id="description" v-model="form.description" rows="3"
              :class="{ 'border-red-500': form.errors.description }" />
            <p v-if="form.errors.description" class="text-xs text-red-600">{{ form.errors.description }}</p>
          </div>

          <div class="flex items-center gap-2">
            <Switch id="is_active" :checked="form.is_active" @update:checked="(val) => (form.is_active = val)" />
            <Label for="is_active" class="font-normal cursor-pointer">Aktif</Label>
          </div>

          <div class="flex gap-3">
            <Button type="submit" :disabled="form.processing">Simpan</Button>
            <Button variant="outline" type="button" as-child>
              <Link href="/admin/collections">Batal</Link>
            </Button>
          </div>
        </form>
      </CardContent>
    </Card>
  </div>
</template>
