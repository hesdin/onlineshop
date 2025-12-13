<script setup lang="ts">
import SellerDashboardLayout from '@/Layouts/SellerDashboardLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, CheckCircle2 } from 'lucide-vue-next';

type StatusOption = {
  value: string;
  label: string;
};

type OrderItem = {
  id: number;
  product_name: string;
  quantity: number;
  price: number;
  total: number;
};

type OrderPayload = {
  id: number;
  order_number: string;
  status: string;
  payment_status: string;
  grand_total: number;
  shipping_cost: number;
  note: string | null;
  created_at: string | null;
  customer: { name: string; email: string };
  items: OrderItem[];
};

const props = defineProps<{
  order: OrderPayload;
  statusOptions: StatusOption[];
  paymentStatusOptions: StatusOption[];
}>();

defineOptions({
  layout: SellerDashboardLayout,
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success ?? '');

const form = useForm({
  status: props.order.status,
  payment_status: props.order.payment_status,
});

const submit = () => {
  form.put(`/seller/orders/${props.order.id}`, {
    preserveScroll: true,
  });
};

const currencyFormatter = new Intl.NumberFormat('id-ID', {
  style: 'currency',
  currency: 'IDR',
  minimumFractionDigits: 0,
});

const formatCurrency = (value: number) => currencyFormatter.format(value ?? 0);

const statusBadge = (status: string) => {
  switch (status) {
    case 'pending_payment':
      return { class: 'bg-amber-100 text-amber-700', label: 'Menunggu Bayar' };
    case 'processing':
      return { class: 'bg-blue-100 text-blue-700', label: 'Diproses' };
    case 'shipped':
      return { class: 'bg-indigo-100 text-indigo-700', label: 'Dikirim' };
    case 'delivered':
      return { class: 'bg-emerald-100 text-emerald-700', label: 'Diterima' };
    case 'completed':
      return { class: 'bg-emerald-100 text-emerald-700', label: 'Selesai' };
    case 'cancelled':
      return { class: 'bg-slate-100 text-slate-600', label: 'Dibatalkan' };
    default:
      return { class: 'bg-slate-100 text-slate-600', label: status };
  }
};

const paymentBadge = (status: string) => {
  switch (status) {
    case 'paid':
      return { class: 'bg-emerald-100 text-emerald-700', label: 'Dibayar' };
    case 'pending':
      return { class: 'bg-amber-100 text-amber-700', label: 'Menunggu' };
    case 'expired':
      return { class: 'bg-slate-100 text-slate-600', label: 'Kedaluwarsa' };
    case 'failed':
      return { class: 'bg-rose-100 text-rose-700', label: 'Gagal' };
    default:
      return { class: 'bg-slate-100 text-slate-600', label: status };
  }
};
</script>

<template>
  <div class="space-y-6">

    <Head :title="`Pesanan #${order.order_number}`" />

    <div class="flex items-center gap-3">
      <Button variant="ghost" size="icon" as-child>
        <Link href="/seller/orders">
        <ArrowLeft class="h-4 w-4" />
        </Link>
      </Button>
      <div>
        <h1 class="text-2xl font-semibold text-slate-900">Detail Pesanan</h1>
        <p class="text-sm text-slate-500">{{ order.order_number }}</p>
      </div>
      <div class="ml-auto flex items-center gap-2">
        <Badge :class="statusBadge(order.status).class" variant="secondary">
          {{ statusBadge(order.status).label }}
        </Badge>
        <Badge :class="paymentBadge(order.payment_status).class" variant="secondary">
          {{ paymentBadge(order.payment_status).label }}
        </Badge>
      </div>
    </div>

    <Alert v-if="flashSuccess" variant="default" class="flex items-start gap-3 border-green-200 bg-green-50">
      <CheckCircle2 class="h-5 w-5 text-green-600" />
      <div class="space-y-1">
        <AlertTitle class="text-green-800">Berhasil</AlertTitle>
        <AlertDescription class="text-green-700">
          {{ flashSuccess }}
        </AlertDescription>
      </div>
    </Alert>

    <div class="grid gap-6 md:grid-cols-3">
      <Card class="md:col-span-2">
        <CardHeader>
          <div class="flex items-center justify-between">
            <div>
              <CardTitle>Produk</CardTitle>
              <CardDescription>Detail item dalam pesanan.</CardDescription>
            </div>
          </div>
        </CardHeader>
        <CardContent class="space-y-4">
          <div v-for="item in order.items" :key="item.id"
            class="flex items-start justify-between rounded-md border border-slate-100 bg-slate-50/70 px-4 py-3">
            <div>
              <p class="font-semibold text-slate-900">{{ item.product_name }}</p>
              <p class="text-xs text-slate-500">{{ item.quantity }} x {{ formatCurrency(item.price) }}</p>
            </div>
            <p class="text-sm font-semibold text-slate-900">{{ formatCurrency(item.total) }}</p>
          </div>

          <Separator />

          <div class="space-y-2">
            <div class="flex justify-between text-sm">
              <span class="text-slate-600">Subtotal</span>
              <span class="font-semibold">{{ formatCurrency(order.grand_total - order.shipping_cost) }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-slate-600">Ongkir</span>
              <span class="font-semibold">{{ formatCurrency(order.shipping_cost) }}</span>
            </div>
            <Separator />
            <div class="flex justify-between">
              <span class="font-semibold text-slate-900">Total</span>
              <span class="text-lg font-bold text-slate-900">{{ formatCurrency(order.grand_total) }}</span>
            </div>
          </div>

          <div v-if="order.note" class="rounded-md border border-slate-100 bg-slate-50/70 px-4 py-3 text-sm">
            <p class="text-xs uppercase text-slate-500">Catatan Pembeli</p>
            <p class="text-slate-700">{{ order.note }}</p>
          </div>
        </CardContent>
      </Card>

      <div class="space-y-6">
        <Card>
          <CardHeader>
            <CardTitle>Informasi Pelanggan</CardTitle>
          </CardHeader>
          <CardContent class="space-y-2">
            <div>
              <p class="text-xs text-slate-500">Nama</p>
              <p class="font-semibold text-slate-900">{{ order.customer.name }}</p>
            </div>
            <div>
              <p class="text-xs text-slate-500">Email</p>
              <p class="text-sm text-slate-700">{{ order.customer.email }}</p>
            </div>
            <div>
              <p class="text-xs text-slate-500">Tanggal</p>
              <p class="text-sm text-slate-700">{{ order.created_at }}</p>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Update Status</CardTitle>
            <CardDescription>Ubah status pesanan dan pembayaran.</CardDescription>
          </CardHeader>
          <CardContent>
            <form @submit.prevent="submit" class="space-y-4">
              <div class="space-y-2">
                <Label>Status Pesanan</Label>
                <Select v-model="form.status">
                  <SelectTrigger :class="form.errors.status ? 'border-red-500' : ''">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="option in statusOptions" :key="option.value" :value="option.value">
                      {{ option.label }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <p v-if="form.errors.status" class="text-xs text-red-600">
                  {{ form.errors.status }}
                </p>
              </div>

              <div class="space-y-2">
                <Label>Status Pembayaran</Label>
                <Select v-model="form.payment_status">
                  <SelectTrigger :class="form.errors.payment_status ? 'border-red-500' : ''">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="option in paymentStatusOptions" :key="option.value" :value="option.value">
                      {{ option.label }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <p v-if="form.errors.payment_status" class="text-xs text-red-600">
                  {{ form.errors.payment_status }}
                </p>
              </div>

              <Button type="submit" class="w-full" :disabled="form.processing">
                Update Status
              </Button>
            </form>
          </CardContent>
        </Card>
      </div>
    </div>
  </div>
</template>
