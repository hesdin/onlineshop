<script setup lang="ts">
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
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
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps<{
  order: any;
}>();

defineOptions({
  layout: AdminDashboardLayout,
});

const form = useForm({
  status: props.order.status,
  payment_status: props.order.payment_status,
});

const submit = () => {
  form.put(`/admin/orders/${props.order.id}`, {
    preserveScroll: true,
  });
};

const formatCurrency = (value: number) => {
  return `Rp ${value.toLocaleString('id-ID')}`;
};
</script>

<template>
  <div class="space-y-6">

    <Head :title="`Pesanan #${order.order_number}`" />

    <div class="flex items-center gap-3">
      <Button variant="ghost" size="icon" as-child>
        <Link href="/admin/orders">
        <ArrowLeft class="h-4 w-4" />
        </Link>
      </Button>
      <div>
        <h1 class="text-2xl font-semibold text-slate-900">Detail Pesanan</h1>
        <p class="text-sm text-slate-500">{{ order.order_number }}</p>
      </div>
    </div>

    <div class="grid gap-6 md:grid-cols-3">
      <Card class="md:col-span-2">
        <CardHeader>
          <CardTitle>Produk</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <div v-for="item in order.items" :key="item.id" class="flex justify-between">
              <div>
                <p class="font-semibold text-slate-900">{{ item.product_name }}</p>
                <p class="text-xs text-slate-500">{{ item.quantity }} x {{ formatCurrency(item.price) }}</p>
              </div>
              <p class="font-semibold text-slate-900">{{ formatCurrency(item.total) }}</p>
            </div>

            <Separator />

            <div class="space-y-2">
              <div class="flex justify-between text-sm">
                <span class="text-slate-600">Subtotal</span>
                <span class="font-semibold">{{ formatCurrency(order.total_amount - order.shipping_cost) }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-slate-600">Ongkir</span>
                <span class="font-semibold">{{ formatCurrency(order.shipping_cost) }}</span>
              </div>
              <Separator />
              <div class="flex justify-between">
                <span class="font-semibold text-slate-900">Total</span>
                <span class="text-lg font-bold text-slate-900">{{ formatCurrency(order.total_amount) }}</span>
              </div>
            </div>
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
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Update Status</CardTitle>
            <CardDescription>Ubah status pesanan</CardDescription>
          </CardHeader>
          <CardContent>
            <form @submit.prevent="submit" class="space-y-4">
              <div class="space-y-2">
                <Label>Status Pesanan</Label>
                <Select v-model="form.status">
                  <SelectTrigger>
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="pending_payment">Pending Payment</SelectItem>
                    <SelectItem value="processing">Processing</SelectItem>
                    <SelectItem value="shipped">Shipped</SelectItem>
                    <SelectItem value="delivered">Delivered</SelectItem>
                    <SelectItem value="completed">Completed</SelectItem>
                    <SelectItem value="cancelled">Cancelled</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div class="space-y-2">
                <Label>Status Pembayaran</Label>
                <Select v-model="form.payment_status">
                  <SelectTrigger>
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="pending">Pending</SelectItem>
                    <SelectItem value="paid">Paid</SelectItem>
                    <SelectItem value="expired">Expired</SelectItem>
                    <SelectItem value="failed">Failed</SelectItem>
                  </SelectContent>
                </Select>
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
