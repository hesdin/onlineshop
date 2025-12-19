<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, reactive, onMounted, ref } from 'vue';
import LandingLayout from '@/Layouts/LandingLayout.vue';
import axios from 'axios';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { CheckCircle2, MessageSquare, PhoneOff } from 'lucide-vue-next';

// Track expanded state for each order
const expandedOrders = reactive({});
const isStartingChat = ref(false);
const showFallbackDialog = ref(false);
const pendingOrder = ref(null);

const toggleOrder = (orderId) => {
  if (expandedOrders[orderId] === undefined) {
    expandedOrders[orderId] = false; // First click collapses
  } else {
    expandedOrders[orderId] = !expandedOrders[orderId];
  }
};

const props = defineProps({
  appName: {
    type: String,
    default: 'TP-PKK Marketplace',
  },
  orders: {
    type: Array,
    default: () => [],
  },
});

const formatCurrency = (value) =>
  new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(
    Number.parseInt(value ?? 0, 10) || 0
  );

const totalAmount = computed(() =>
  props.orders.reduce((sum, order) => sum + (order.grand_total ?? 0), 0)
);

const getPaymentStatusClass = (status) => {
  const statusMap = {
    pending: 'bg-amber-100 text-amber-700',
    paid: 'bg-emerald-100 text-emerald-700',
    expired: 'bg-red-100 text-red-700',
    failed: 'bg-red-100 text-red-700',
  };
  return statusMap[status] || 'bg-slate-100 text-slate-700';
};

const getPaymentInstructions = (order) => {
  const paymentMethod = order.payment_method_code; // 'cod' or 'manual_transfer'
  const shippingMethod = order.shipping_method; // 'pickup' or 'delivery'

  if (paymentMethod === 'cod') {
    if (shippingMethod === 'pickup') {
      return 'Pembayaran dilakukan langsung di toko saat Anda mengambil pesanan.';
    }
    // COD + Delivery
    return 'Pembayaran dilakukan langsung kepada kurir saat menerima pesanan.';
  }

  if (paymentMethod === 'manual_transfer') {
    if (shippingMethod === 'pickup') {
      return 'Silakan transfer ke rekening toko. Pesanan dapat diambil di toko setelah pembayaran dikonfirmasi.';
    }
    // Transfer + Delivery
    return 'Silakan transfer ke rekening toko. Barang akan dikirim setelah pembayaran dikonfirmasi. Hubungi toko via WhatsApp untuk detail rekening.';
  }

  return order.payment_instructions || 'Silakan ikuti instruksi pembayaran dari toko.';
};

const getShippingMethodLabel = (shippingMethod) => {
  const labels = {
    'pickup': 'Ambil di Toko',
    'delivery': 'Diantar ke Alamat',
  };
  return labels[shippingMethod] || shippingMethod;
};

const getPaymentMethodLabel = (paymentMethodCode) => {
  const labels = {
    'cod': 'Cash on Delivery (COD)',
    'manual_transfer': 'Transfer Manual',
  };
  return labels[paymentMethodCode] || paymentMethodCode;
};

const generateWhatsAppLink = (order) => {
  // If store phone is not available, return null
  if (!order.store_phone) return order.whatsapp_link || null;

  // Format phone number
  let phone = order.store_phone.replace(/[^0-9]/g, '');
  if (!phone.startsWith('62')) {
    phone = '62' + phone.replace(/^0/, '');
  }

  // Build product list
  const itemsList = (order.items || []).map(item =>
    `• ${item.name} x${item.quantity} - ${formatCurrency(item.subtotal)}`
  ).join('\n');

  // Build message
  const message = `Halo ${order.store_name},

Saya ingin konfirmasi pesanan:

*No. Pesanan:* ${order.order_number}

*Detail Produk:*
${itemsList}

*Total:* ${formatCurrency(order.grand_total)}
*Metode:* ${getPaymentMethodLabel(order.payment_method_code)}
*Status Pembayaran:* ${order.payment_status === 'pending' ? 'Menunggu Pembayaran' : order.payment_status}
*Pengiriman:* ${getShippingMethodLabel(order.shipping_method)}

Mohon konfirmasi pesanan ini. Terima kasih!`;

  return `https://wa.me/${phone}?text=${encodeURIComponent(message)}`;
};

const handleWhatsAppClick = async (order) => {
  const link = generateWhatsAppLink(order);
  if (link) {
    window.open(link, '_blank');
  } else {
    pendingOrder.value = order;
    showFallbackDialog.value = true;
  }
};

const startInternalChat = async () => {
  const order = pendingOrder.value;
  if (!order) return;

  try {
    isStartingChat.value = true;
    showFallbackDialog.value = false;
    const response = await axios.post('/customer/chats/start', {
      store_id: order.store_id,
      message: `Halo ${order.store_name}, saya ingin konfirmasi pesanan: ${order.order_number}`
    });

    if (response.data.conversation_id) {
      router.visit(`/customer/dashboard/chat/${response.data.conversation_id}`);
    }
  } catch (error) {
    console.error('Failed to start chat:', error);
  } finally {
    isStartingChat.value = false;
    pendingOrder.value = null;
  }
};

const isCOD = computed(() => props.orders.some(order => order.payment_method_code === 'cod'));
</script>

<template>
  <LandingLayout>

    <Head :title="`Konfirmasi Pesanan - ${appName}`" />

    <section class="min-h-screen space-y-8 pb-20">
      <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 px-1">Konfirmasi Pesanan</h1>

      <!-- Success Message -->
      <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-6 shadow-sm ring-1 ring-emerald-100/50">
        <div class="flex items-start gap-5">
          <div
            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 shadow-sm ring-1 ring-emerald-200">
            <CheckCircle2 class="h-6 w-6" />
          </div>
          <div class="flex-1 space-y-1">
            <h2 class="text-xl font-bold text-emerald-900 leading-tight">Pesanan Berhasil Dibuat!</h2>
            <p class="text-emerald-700 leading-relaxed">
              Terima kasih atas pesanan Anda. Silakan cek detail pesanan di bawah ini dan lakukan konfirmasi ke penjual.
            </p>
          </div>
        </div>
      </div>

      <!-- AlertDialog Fallback -->
      <AlertDialog :open="showFallbackDialog" @update:open="showFallbackDialog = $event">
        <AlertDialogContent class="max-w-[400px]">
          <AlertDialogHeader>
            <div
              class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-600 mb-2">
              <PhoneOff class="h-8 w-8" />
            </div>
            <AlertDialogTitle class="text-center text-xl font-bold">WhatsApp Tidak Tersedia</AlertDialogTitle>
            <AlertDialogDescription class="text-center text-slate-600 pt-2">
              Toko <span class="font-semibold text-slate-900">{{ pendingOrder?.store_name }}</span> belum mengatur nomor
              WhatsApp. Apakah Anda ingin menghubungi mereka melalui chat internal?
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter class="sm:flex-col gap-2 mt-4">
            <AlertDialogAction @click="startInternalChat"
              class="w-full bg-sky-600 hover:bg-sky-700 font-bold py-6 rounded-xl">
              <MessageSquare class="h-4 w-4 mr-2" />
              Hubungi via Chat Internal
            </AlertDialogAction>
            <AlertDialogCancel class="w-full border-slate-200 font-semibold py-6 rounded-xl">Tutup</AlertDialogCancel>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>

      <!-- Orders List -->
      <div class="space-y-4">
        <div v-for="(order, index) in orders" :key="order.id"
          class="overflow-hidden rounded-md border border-slate-200 bg-white">
          <!-- Order Header (Clickable to toggle) -->
          <div
            class="border-b border-slate-100 bg-slate-50 px-6 py-4 cursor-pointer select-none hover:bg-slate-100 transition"
            @click="toggleOrder(order.id)">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <!-- Toggle Icon -->
                <svg class="h-5 w-5 text-slate-500 transition-transform duration-200"
                  :class="expandedOrders[order.id] === false ? '-rotate-90' : 'rotate-0'" viewBox="0 0 20 20"
                  fill="currentColor">
                  <path fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                    clip-rule="evenodd" />
                </svg>
                <div>
                  <p class="text-sm text-slate-600">Pesanan {{ index + 1 }}</p>
                  <p class="text-lg font-bold text-slate-900">{{ order.order_number }}</p>
                </div>
              </div>
              <span class="rounded-md px-4 py-1.5 text-xs font-semibold"
                :class="getPaymentStatusClass(order.payment_status)">
                {{ order.payment_status === 'pending' ? 'Menunggu Pembayaran' : order.payment_status }}
              </span>
            </div>
          </div>

          <!-- Order Content (Collapsible) -->
          <div v-show="expandedOrders[order.id] !== false" class="p-6 space-y-4">
            <!-- Order Summary Card (Similar to Payment.vue) -->
            <div class="rounded-md border border-slate-100 bg-slate-50 px-4 py-3 space-y-2">
              <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-semibold text-slate-900">{{ order.store_name }}</p>
                <div v-if="order.store_rating" class="flex items-center gap-1 text-xs text-slate-600">
                  <svg class="h-3.5 w-3.5 text-amber-500" viewBox="0 0 24 24" fill="currentColor">
                    <path
                      d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" />
                  </svg>
                  <span>{{ order.store_rating }}/5</span>
                </div>
              </div>

              <div class="text-sm text-slate-800 space-y-1">
                <div class="flex items-center justify-between">
                  <span class="text-slate-600">Metode Pembayaran</span>
                  <span class="font-semibold">{{ getPaymentMethodLabel(order.payment_method_code) }}</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-slate-600">Metode Pengiriman</span>
                  <span class="font-semibold">{{ getShippingMethodLabel(order.shipping_method) }}</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-slate-600">Tanggal Pesanan</span>
                  <span class="font-semibold">{{ order.ordered_at }}</span>
                </div>
              </div>

              <!-- Order Items (Collapsible) -->
              <div v-if="order.items && order.items.length" class="border-t border-slate-200 pt-2 space-y-2">
                <div v-for="(item, idx) in (expandedOrders[order.id] ? order.items : order.items.slice(0, 2))"
                  :key="idx" class="flex items-start justify-between gap-3 text-sm">
                  <span class="text-slate-700 break-words max-w-[65%]">{{ item.name }} <span class="text-slate-500">x{{
                    item.quantity }}</span></span>
                  <span class="font-semibold text-slate-900 shrink-0 text-right">{{ formatCurrency(item.subtotal)
                  }}</span>
                </div>
                <!-- Show expand/collapse button if more than 2 items -->
                <button v-if="order.items.length > 2" type="button"
                  @click="expandedOrders[order.id] = !expandedOrders[order.id]"
                  class="flex items-center justify-center gap-1.5 w-full py-2 px-3 rounded-md border border-slate-200 bg-white text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-800 transition">
                  <svg class="h-4 w-4 transition-transform" :class="{ 'rotate-180': expandedOrders[order.id] }"
                    viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                      d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                      clip-rule="evenodd" />
                  </svg>
                  {{ expandedOrders[order.id] ? 'Sembunyikan' : `Lihat ${order.items.length - 2} item lainnya` }}
                </button>
              </div>

              <!-- Total -->
              <div
                class="flex items-center justify-between border-t border-slate-200 pt-2 text-sm font-semibold text-slate-900">
                <span>Total Pesanan</span>
                <span class="text-base font-bold text-sky-600">{{ formatCurrency(order.grand_total) }}</span>
              </div>
            </div>

            <!-- Payment Instructions -->
            <div class="rounded-md border-1 border-sky-500 bg-sky-50 p-4">
              <p class="text-sm font-semibold text-sky-900">Instruksi Pembayaran:</p>
              <p class="mt-1 text-sm text-sky-700">{{ getPaymentInstructions(order) }}</p>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap items-center gap-3 border-t border-slate-100 pt-4">
              <a :href="`/orders/${order.id}/invoice`"
                class="inline-flex items-center gap-2 rounded-md border border-sky-600 px-4 py-2.5 text-sm font-semibold text-sky-600 transition hover:bg-sky-50">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                  <polyline points="7 10 12 15 17 10" />
                  <line x1="12" y1="15" x2="12" y2="3" />
                </svg>
                Download Invoice
              </a>

              <!-- WhatsApp Konfirmasi -->
              <button @click="handleWhatsAppClick(order)" :disabled="isStartingChat"
                class="inline-flex flex-1 items-center justify-center gap-2 rounded-md bg-emerald-500 px-6 py-3 text-sm font-semibold text-white transition hover:bg-emerald-600 sm:flex-none disabled:opacity-50">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                  <path
                    d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                </svg>
                <span class="font-bold">{{ isStartingChat ? 'Menghubungkan...' : 'Konfirmasi via WhatsApp' }}</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Summary -->
      <div class="rounded-md border border-slate-200 bg-white p-6">
        <h3 class="text-lg font-bold text-slate-900">Ringkasan Pesanan</h3>
        <div class="mt-4 space-y-3">
          <div class="flex items-center justify-between text-sm">
            <span class="text-slate-600">Total Pesanan</span>
            <span class="font-semibold text-slate-900">{{ orders.length }} pesanan</span>
          </div>
          <div class="flex items-center justify-between border-t border-slate-100 pt-3">
            <span class="font-semibold text-slate-900">Total Semua Pesanan</span>
            <span class="text-xl font-bold text-sky-600">{{ formatCurrency(totalAmount) }}</span>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-wrap items-center gap-3">
        <Link href="/customer/dashboard/transactions"
          class="inline-flex items-center gap-2 rounded-md bg-sky-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-sky-700">
          Lihat Pesanan Saya
        </Link>
        <Link href="/"
          class="inline-flex items-center gap-2 rounded-md border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
          Kembali ke Beranda
        </Link>
      </div>
    </section>
  </LandingLayout>
</template>
