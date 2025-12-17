<script setup lang="ts">
import SellerDashboardLayout from '@/Layouts/SellerDashboardLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref, watch } from 'vue';

import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Alert } from '@/components/ui/alert';
import {
  ArrowLeft,
  CheckCircle2,
  Clock,
  CreditCard,
  Download,
  Mail,
  MapPin,
  MessageCircle,
  Package,
  Phone,
  ShoppingBag,
  Truck,
  User,
  XCircle,
  Wallet,
  Calendar,
  FileText,
  Upload,
  Image,
} from 'lucide-vue-next';

type StatusOption = {
  value: string;
  label: string;
};

type OrderItem = {
  id: number;
  product_name: string;
  product_image?: string | null;
  quantity: number;
  price: number;
  total: number;
};

type ShippingAddress = {
  recipient_name?: string | null;
  phone?: string | null;
  address?: string | null;
  district?: string | null;
  city?: string | null;
  province?: string | null;
  postal_code?: string | null;
  label?: string | null;
};

type PaymentMethod = {
  name?: string | null;
  code?: string | null;
};

type OrderPayload = {
  id: number;
  order_number: string;
  status: string;
  payment_status: string;
  subtotal: number;
  discount_total: number;
  shipping_cost: number;
  grand_total: number;
  shipping_service?: string | null;
  shipping_awb?: string | null;
  note: string | null;
  created_at: string | null;
  ordered_at?: string | null;
  customer: { name: string; email: string; phone?: string | null };
  items: OrderItem[];
  shipping_address?: ShippingAddress | null;
  payment_method?: PaymentMethod | null;
  invoice_url?: string | null;
  customer_whatsapp_link?: string | null;
  payment_proof_url?: string | null;
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
const flashSuccess = computed(() => (page.props.flash as any)?.success ?? '');
const successVisible = ref(false);
let successTimeout: ReturnType<typeof setTimeout> | null = null;

watch(
  flashSuccess,
  (value) => {
    if (successTimeout) {
      clearTimeout(successTimeout);
      successTimeout = null;
    }

    if (value) {
      successVisible.value = true;
      successTimeout = setTimeout(() => {
        successVisible.value = false;
      }, 3000);
    } else {
      successVisible.value = false;
    }
  },
  { immediate: true },
);

onBeforeUnmount(() => {
  if (successTimeout) {
    clearTimeout(successTimeout);
  }
});

const form = useForm({
  status: props.order.status,
  payment_status: props.order.payment_status,
  payment_proof: null as File | null,
});

const paymentProofPreview = ref<string | null>(null);
const paymentProofInputRef = ref<HTMLInputElement | null>(null);

const requiresPaymentProof = computed(() => {
  // Show upload when changing to paid, or when already paid but no proof uploaded
  const changingToPaid = form.payment_status === 'paid' && props.order.payment_status !== 'paid';
  const paidButNoProof = form.payment_status === 'paid' && !props.order.payment_proof_url;
  return changingToPaid || paidButNoProof;
});

const handlePaymentProofChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0];
  if (file) {
    form.payment_proof = file;
    paymentProofPreview.value = URL.createObjectURL(file);
  }
};

const triggerPaymentProofPicker = () => {
  paymentProofInputRef.value?.click();
};

const removePaymentProof = () => {
  form.payment_proof = null;
  if (paymentProofPreview.value) {
    URL.revokeObjectURL(paymentProofPreview.value);
    paymentProofPreview.value = null;
  }
  if (paymentProofInputRef.value) {
    paymentProofInputRef.value.value = '';
  }
};

const submit = () => {
  form
    .transform((data) => ({
      ...data,
      _method: 'PUT',
    }))
    .post(`/seller/orders/${props.order.id}`, {
      preserveScroll: true,
      forceFormData: true,
    });
};

const currencyFormatter = new Intl.NumberFormat('id-ID', {
  style: 'currency',
  currency: 'IDR',
  minimumFractionDigits: 0,
});

const formatCurrency = (value?: number | null) => currencyFormatter.format(value ?? 0);

const statusConfig = computed(() => {
  const configs: Record<string, { class: string; bgClass: string; label: string; icon: any }> = {
    pending_payment: {
      class: 'text-amber-700',
      bgClass: 'bg-amber-100',
      label: 'Menunggu Pembayaran',
      icon: Clock,
    },
    processing: {
      class: 'text-blue-700',
      bgClass: 'bg-blue-100',
      label: 'Sedang Diproses',
      icon: Package,
    },
    shipped: {
      class: 'text-indigo-700',
      bgClass: 'bg-indigo-100',
      label: 'Dalam Pengiriman',
      icon: Truck,
    },
    delivered: {
      class: 'text-emerald-700',
      bgClass: 'bg-emerald-100',
      label: 'Sudah Diterima',
      icon: CheckCircle2,
    },
    completed: {
      class: 'text-emerald-700',
      bgClass: 'bg-emerald-100',
      label: 'Selesai',
      icon: CheckCircle2,
    },
    cancelled: {
      class: 'text-slate-600',
      bgClass: 'bg-slate-100',
      label: 'Dibatalkan',
      icon: XCircle,
    },
  };
  return configs[props.order.status] || {
    class: 'text-slate-600',
    bgClass: 'bg-slate-100',
    label: props.order.status,
    icon: Clock,
  };
});

const paymentConfig = computed(() => {
  const configs: Record<string, { class: string; bgClass: string; label: string }> = {
    paid: { class: 'text-emerald-700', bgClass: 'bg-emerald-100', label: 'Lunas' },
    pending: { class: 'text-amber-700', bgClass: 'bg-amber-100', label: 'Menunggu Pembayaran' },
    expired: { class: 'text-slate-600', bgClass: 'bg-slate-100', label: 'Kedaluwarsa' },
    failed: { class: 'text-rose-700', bgClass: 'bg-rose-100', label: 'Gagal' },
  };
  return configs[props.order.payment_status] || {
    class: 'text-slate-600',
    bgClass: 'bg-slate-100',
    label: props.order.payment_status,
  };
});

const shippingServiceLabel = computed(() => {
  const service = props.order.shipping_service;
  if (!service) return 'Belum dipilih';
  if (service === 'pickup') return 'Ambil di Toko';
  if (service === 'delivery') return 'Diantar ke Alamat';
  return service;
});

const fullAddress = computed(() => {
  const addr = props.order.shipping_address;
  if (!addr) return null;
  const parts = [
    addr.address,
    addr.district,
    addr.city,
    addr.province,
    addr.postal_code,
  ].filter(Boolean);
  return parts.join(', ');
});
</script>

<template>
  <div class="space-y-6 w-full">

    <Head :title="`Pesanan #${order.order_number}`" />

    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-semibold text-slate-900">{{ order.order_number }}</h1>
        <p class="text-sm text-slate-500">Dibuat pada {{ order.created_at ?? '-' }}</p>
      </div>
      <div class="flex items-center gap-2">
        <Button v-if="order.customer_whatsapp_link" variant="outline" class="text-green-600 hover:text-green-700"
          as-child>
          <a :href="order.customer_whatsapp_link" target="_blank">
            <MessageCircle class="h-4 w-4" />
            Chat Customer
          </a>
        </Button>
        <Button v-if="order.invoice_url" variant="outline" as-child>
          <a :href="order.invoice_url" target="_blank">
            <Download class="h-4 w-4" />
            Invoice
          </a>
        </Button>
        <Button variant="outline" as-child>
          <Link href="/seller/orders">
            <ArrowLeft class="h-4 w-4" />
            Kembali
          </Link>
        </Button>
      </div>
    </div>

    <!-- Success Alert -->
    <Alert v-if="successVisible && flashSuccess" variant="default"
      class="flex items-center gap-2 border-green-200 bg-green-50 text-sm font-medium text-green-700">
      <CheckCircle2 class="h-5 w-5 text-green-600" />
      <span>{{ flashSuccess }}</span>
    </Alert>

    <div class="grid gap-6 lg:grid-cols-3">
      <!-- Main Content -->
      <div class="space-y-6 lg:col-span-2">
        <!-- Order Items -->
        <Card>
          <CardHeader class="pb-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
              <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100">
                  <ShoppingBag class="h-4 w-4 text-blue-600" />
                </div>
                <div>
                  <CardTitle class="text-base">Produk Dipesan</CardTitle>
                  <CardDescription>{{ order.items.length }} item</CardDescription>
                </div>
              </div>
              <div class="flex flex-wrap items-center gap-2">
                <span
                  :class="[statusConfig.bgClass, statusConfig.class, 'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold']">
                  <component :is="statusConfig.icon" class="h-3.5 w-3.5" />
                  {{ statusConfig.label }}
                </span>
                <span
                  :class="[paymentConfig.bgClass, paymentConfig.class, 'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold']">
                  <CreditCard class="h-3.5 w-3.5" />
                  {{ paymentConfig.label }}
                </span>
              </div>
            </div>
          </CardHeader>
          <CardContent class="space-y-4">
            <div v-for="item in order.items" :key="item.id"
              class="flex items-start gap-4 rounded-lg border border-slate-100 bg-slate-50/50 p-4">
              <!-- Product Image -->
              <div class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-md border border-slate-200 bg-white">
                <img v-if="item.product_image" :src="item.product_image" :alt="item.product_name"
                  class="h-full w-full object-cover object-center" />
                <div v-else class="flex h-full w-full items-center justify-center text-slate-300">
                  <Package class="h-6 w-6" />
                </div>
              </div>
              <!-- Product Info -->
              <div class="flex-1 min-w-0">
                <p class="font-semibold text-slate-900 line-clamp-2">{{ item.product_name }}</p>
                <p class="mt-1 text-sm text-slate-500">
                  {{ item.quantity }} × {{ formatCurrency(item.price) }}
                </p>
              </div>
              <!-- Price -->
              <p class="text-sm font-semibold text-slate-900 whitespace-nowrap">
                {{ formatCurrency(item.total) }}
              </p>
            </div>

            <Separator />

            <!-- Order Summary -->
            <div class="space-y-3 pt-2">
              <div class="flex justify-between text-sm">
                <span class="text-slate-600">Subtotal</span>
                <span class="font-medium text-slate-800">{{ formatCurrency(order.subtotal) }}</span>
              </div>
              <div v-if="order.discount_total" class="flex justify-between text-sm">
                <span class="text-slate-600">Diskon</span>
                <span class="font-medium text-emerald-600">-{{ formatCurrency(order.discount_total) }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-slate-600">Ongkos Kirim</span>
                <span class="font-medium text-slate-800">{{ formatCurrency(order.shipping_cost) }}</span>
              </div>
              <Separator />
              <div class="flex justify-between">
                <span class="text-base font-semibold text-slate-900">Total Pembayaran</span>
                <span class="text-lg font-bold text-blue-600">{{ formatCurrency(order.grand_total) }}</span>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Buyer Note -->
        <Card v-if="order.note">
          <CardHeader class="pb-3">
            <div class="flex items-center gap-2">
              <div class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-100">
                <FileText class="h-4 w-4 text-amber-600" />
              </div>
              <CardTitle class="text-base">Catatan Pembeli</CardTitle>
            </div>
          </CardHeader>
          <CardContent>
            <p class="text-sm text-slate-700 leading-relaxed">{{ order.note }}</p>
          </CardContent>
        </Card>

        <!-- Shipping Info -->
        <Card>
          <CardHeader class="pb-4">
            <div class="flex items-center gap-2">
              <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100">
                <Truck class="h-4 w-4 text-indigo-600" />
              </div>
              <CardTitle class="text-base">Informasi Pengiriman</CardTitle>
            </div>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2">
              <div class="space-y-1">
                <p class="text-xs font-semibold text-slate-500 uppercase">Metode Pengiriman</p>
                <p class="text-sm font-medium text-slate-800">{{ shippingServiceLabel }}</p>
              </div>
              <div v-if="order.shipping_awb" class="space-y-1">
                <p class="text-xs font-semibold text-slate-500 uppercase">No. Resi</p>
                <p class="text-sm font-medium text-slate-800 font-mono">{{ order.shipping_awb }}</p>
              </div>
            </div>

            <Separator v-if="order.shipping_address" />

            <div v-if="order.shipping_address" class="space-y-3">
              <p class="text-xs font-semibold text-slate-500 uppercase">Alamat Pengiriman</p>
              <div class="rounded-lg border border-slate-100 bg-slate-50/50 p-4 space-y-2">
                <div class="flex items-center gap-2">
                  <User class="h-4 w-4 text-slate-400" />
                  <span class="font-semibold text-slate-800">{{ order.shipping_address.recipient_name }}</span>
                  <span v-if="order.shipping_address.label"
                    class="rounded bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700">
                    {{ order.shipping_address.label }}
                  </span>
                </div>
                <div v-if="order.shipping_address.phone" class="flex items-center gap-2 text-sm text-slate-600">
                  <Phone class="h-4 w-4 text-slate-400" />
                  <span>{{ order.shipping_address.phone }}</span>
                </div>
                <div v-if="fullAddress" class="flex items-start gap-2 text-sm text-slate-600">
                  <MapPin class="h-4 w-4 text-slate-400 mt-0.5" />
                  <span>{{ fullAddress }}</span>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Payment Proof -->
        <Card v-if="order.payment_proof_url">
          <CardHeader class="pb-4">
            <div class="flex items-center gap-2">
              <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100">
                <Image class="h-4 w-4 text-emerald-600" />
              </div>
              <CardTitle class="text-base">Bukti Pembayaran</CardTitle>
            </div>
          </CardHeader>
          <CardContent>
            <a :href="order.payment_proof_url" target="_blank" class="block">
              <img :src="order.payment_proof_url" alt="Bukti Pembayaran"
                class="max-w-sm rounded-lg border border-slate-200 shadow-sm hover:shadow-md transition" />
            </a>
          </CardContent>
        </Card>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Customer Info -->
        <Card>
          <CardHeader class="pb-4">
            <div class="flex items-center gap-2">
              <div class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100">
                <User class="h-4 w-4 text-slate-600" />
              </div>
              <CardTitle class="text-base">Informasi Pelanggan</CardTitle>
            </div>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="flex items-start gap-3">
              <div
                class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-blue-600 font-semibold text-sm">
                {{ order.customer.name?.charAt(0)?.toUpperCase() || 'U' }}
              </div>
              <div class="min-w-0 flex-1 space-y-1">
                <p class="font-semibold text-slate-900">{{ order.customer.name }}</p>
                <div class="flex items-center gap-2 text-sm text-slate-500">
                  <Mail class="h-3.5 w-3.5" />
                  <span class="truncate">{{ order.customer.email }}</span>
                </div>
                <div v-if="order.customer.phone" class="flex items-center gap-2 text-sm text-slate-500">
                  <Phone class="h-3.5 w-3.5" />
                  <span>{{ order.customer.phone }}</span>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Payment Method -->
        <Card v-if="order.payment_method">
          <CardHeader class="pb-4">
            <div class="flex items-center gap-2">
              <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100">
                <Wallet class="h-4 w-4 text-emerald-600" />
              </div>
              <CardTitle class="text-base">Metode Pembayaran</CardTitle>
            </div>
          </CardHeader>
          <CardContent>
            <p class="font-medium text-slate-800">{{ order.payment_method.name }}</p>
          </CardContent>
        </Card>

        <!-- Order Timeline -->
        <Card>
          <CardHeader class="pb-4">
            <div class="flex items-center gap-2">
              <div class="flex h-8 w-8 items-center justify-center rounded-full bg-purple-100">
                <Calendar class="h-4 w-4 text-purple-600" />
              </div>
              <CardTitle class="text-base">Waktu Pesanan</CardTitle>
            </div>
          </CardHeader>
          <CardContent class="space-y-3">
            <div class="flex justify-between text-sm">
              <span class="text-slate-500">Dibuat</span>
              <span class="font-medium text-slate-800">{{ order.created_at ?? '-' }}</span>
            </div>
            <div v-if="order.ordered_at" class="flex justify-between text-sm">
              <span class="text-slate-500">Dipesan</span>
              <span class="font-medium text-slate-800">{{ order.ordered_at }}</span>
            </div>
          </CardContent>
        </Card>

        <!-- Update Status -->
        <Card>
          <CardHeader class="pb-4">
            <CardTitle class="text-base">Update Status</CardTitle>
            <CardDescription>Ubah status pesanan atau pembayaran.</CardDescription>
          </CardHeader>
          <CardContent>
            <form @submit.prevent="submit" class="space-y-4">
              <div class="space-y-2">
                <Label class="text-xs font-semibold text-slate-500">Status Pesanan</Label>
                <Select v-model="form.status">
                  <SelectTrigger class="w-full" :class="form.errors.status ? 'border-red-500' : ''">
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
                <Label class="text-xs font-semibold text-slate-500">Status Pembayaran</Label>
                <Select v-model="form.payment_status">
                  <SelectTrigger class="w-full" :class="form.errors.payment_status ? 'border-red-500' : ''">
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

              <!-- Payment Proof Upload -->
              <div v-if="requiresPaymentProof" class="space-y-2">
                <Label class="text-xs font-semibold text-slate-500">
                  Bukti Pembayaran
                  <span class="text-red-500">*</span>
                </Label>
                <input ref="paymentProofInputRef" type="file" accept="image/*" class="sr-only"
                  @change="handlePaymentProofChange" />
                <div v-if="paymentProofPreview" class="relative">
                  <img :src="paymentProofPreview" alt="Preview" class="w-full rounded-lg border border-slate-200" />
                  <button type="button"
                    class="absolute top-2 right-2 inline-flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-white shadow-sm hover:bg-red-600"
                    @click="removePaymentProof">
                    <XCircle class="h-4 w-4" />
                  </button>
                </div>
                <Button v-else type="button" variant="outline" class="w-full" @click="triggerPaymentProofPicker">
                  <Upload class="h-4 w-4" />
                  Upload Bukti Pembayaran
                </Button>
                <p v-if="form.errors.payment_proof" class="text-xs text-red-600">
                  {{ form.errors.payment_proof }}
                </p>
                <p class="text-xs text-slate-500">
                  Wajib upload bukti pembayaran saat mengubah status ke "Dibayar"
                </p>
              </div>

              <Button type="submit" class="w-full" :disabled="form.processing">
                {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
              </Button>
            </form>
          </CardContent>
        </Card>
      </div>
    </div>
  </div>
</template>
