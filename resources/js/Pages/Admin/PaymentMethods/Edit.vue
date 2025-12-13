<script setup lang="ts">
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps<{
  paymentMethod: {
    id: number;
    name: string;
    code: string;
    channel: string | null;
    is_active: boolean;
    logo_url: string | null;
  };
}>();

defineOptions({
  layout: AdminDashboardLayout,
});

const form = useForm({
  name: props.paymentMethod.name,
  code: props.paymentMethod.code,
  channel: props.paymentMethod.channel ?? '',
  is_active: props.paymentMethod.is_active,
});

const submit = () => {
  form.put(`/admin/payment-methods/${props.paymentMethod.id}`, {
    preserveScroll: true,
  });
};
</script>

<template>
  <div class="space-y-6">

    <Head title="Edit Metode Pembayaran" />

    <div class="flex items-center gap-3">
      <Button variant="ghost" size="icon" as-child>
        <Link href="/admin/payment-methods">
        <ArrowLeft class="h-4 w-4" />
        </Link>
      </Button>
      <h1 class="text-2xl font-semibold text-slate-900">Edit Metode Pembayaran</h1>
    </div>

    <Card>
      <CardHeader>
        <CardTitle>Informasi Metode Pembayaran</CardTitle>
        <CardDescription>Edit informasi metode {{ paymentMethod.name }}</CardDescription>
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
