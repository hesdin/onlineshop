<script setup>
import LandingLayout from '@/Layouts/LandingLayout.vue';
import CustomerSidebarMenu from '@/components/Customer/SidebarMenu.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineOptions({
  layout: LandingLayout,
});

const props = defineProps({
  orders: {
    type: Array,
    default: () => [],
  },
  statusOptions: {
    type: Array,
    default: () => [],
  },
  paymentStatusOptions: {
    type: Array,
    default: () => [],
  },
});

const statusLabel = (value) => props.statusOptions.find((item) => item.value === value)?.label ?? value ?? '-';
const paymentLabel = (value) => props.paymentStatusOptions.find((item) => item.value === value)?.label ?? value ?? '-';

const statusBadgeClass = (value) => {
  switch (value) {
    case 'pending_payment':
      return 'bg-amber-100 text-amber-700';
    case 'processing':
    case 'shipped':
      return 'bg-sky-100 text-sky-700';
    case 'delivered':
    case 'completed':
      return 'bg-sky-100 text-sky-700';
    case 'cancelled':
      return 'bg-rose-100 text-rose-700';
    default:
      return 'bg-slate-100 text-slate-700';
  }
};

const formatPrice = (value) =>
  new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(
    Number(value ?? 0),
  );

const formatDate = (value) => {
  if (!value) return '-';
  const date = new Date(value);
  return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

// Modal Logic
import { ref, computed } from 'vue';
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
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from '@/components/ui/tooltip';
import { MessageSquare, PhoneOff, Copy, Check, ExternalLink, Info } from 'lucide-vue-next';

// ... existing refs ...

const getPaymentValidation = (order) => {
  if (!order) return { valid: false, message: '' };

  // Important: Check phone for ALL payment methods if we rely on it for confirmation
  // But based on previous logic, strictly:
  const isCOD = order.payment_info?.method?.toLowerCase().includes('cod');

  if (!order.store.phone) {
    return { valid: false, message: 'Toko belum mengatur nomor WhatsApp.' };
  }

  if (!isCOD && (!order.store.bank_details?.bank_name || !order.store.bank_details?.account_number)) {
    return { valid: false, message: 'Toko belum mengatur informasi rekening bank.' };
  }

  return { valid: true };
};

const getChatValidation = (order) => {
  if (!order?.store?.phone) {
    return { valid: false, message: 'Toko belum mengatur nomor WhatsApp.' };
  }
  return { valid: true };
};

const showDetailModal = ref(false);
const showTransferModal = ref(false);
const showCODModal = ref(false);
const showFallbackDialog = ref(false);
const isStartingChat = ref(false);
const selectedOrder = ref(null);
const copiedField = ref(null);

const openDetailModal = (order) => {
  selectedOrder.value = order;
  showDetailModal.value = true;
  document.body.style.overflow = 'hidden';
};

const closeDetailModal = () => {
  showDetailModal.value = false;
  setTimeout(() => {
    selectedOrder.value = null;
  }, 300);
  document.body.style.overflow = '';
};

const closeTransferModal = () => {
  showTransferModal.value = false;
  document.body.style.overflow = 'hidden'; // Restore to detail modal state
};

const handlePayment = (order) => {
  const isCOD = order.payment_info?.method?.toLowerCase().includes('cod');

  // Check if store has necessary contact/payment info immediately
  const hasPhone = !!order.store.phone;
  const hasBankInfo = isCOD || (order.store.bank_details?.bank_name && order.store.bank_details?.account_number);

  if (!hasPhone || !hasBankInfo) {
    pendingOrder.value = order;
    showFallbackDialog.value = true;
    return;
  }

  selectedOrder.value = order;

  if (isCOD) {
    showCODModal.value = true;
    document.body.style.overflow = 'hidden';
  } else {
    // Transfer Manual
    showTransferModal.value = true;
    document.body.style.overflow = 'hidden';
  }
};

const handleWhatsAppClick = (order, customText = null) => {
  const phone = order.store.phone;
  if (!phone) {
    pendingOrder.value = order;
    showFallbackDialog.value = true;
    return;
  }

  const text = customText || `Halo ${order.store.name}, saya ingin konfirmasi pesanan: ${order.order_number}`;
  const link = chatStore(phone, text);
  window.open(link, '_blank');
};

const startInternalChat = async () => {
  const order = selectedOrder.value || pendingOrder.value;
  if (!order) return;

  try {
    isStartingChat.value = true;
    showFallbackDialog.value = false;
    showTransferModal.value = false;
    showCODModal.value = false;

    const response = await axios.post('/customer/chats/start', {
      store_id: order.store.id,
      message: `Halo ${order.store.name}, saya ingin konfirmasi pesanan: ${order.order_number}`
    });

    if (response.data.conversation_id) {
      router.visit(`/customer/dashboard/chat/${response.data.conversation_id}`);
    }
  } catch (error) {
    console.error('Failed to start chat:', error);
  } finally {
    isStartingChat.value = false;
  }
};

const copyToClipboard = (text, field) => {
  navigator.clipboard.writeText(text);
  copiedField.value = field;
  setTimeout(() => {
    copiedField.value = null;
  }, 2000);
};

const closeCODModal = () => {
  showCODModal.value = false;
  document.body.style.overflow = '';
};

const chatStore = (phone, text) => {
  if (!phone) return '#';
  const formattedPhone = phone.replace(/\D/g, ''); // Basic formatting
  // Ensure 62 prefix
  const finalPhone = formattedPhone.startsWith('62') ? formattedPhone : '62' + formattedPhone.replace(/^0/, '');
  return `https://wa.me/${finalPhone}?text=${encodeURIComponent(text)}`;
};

const generateTransferProofLink = (order) => {
  const text = `Halo, saya ingin konfirmasi pembayaran untuk pesanan ${order.order_number}.

Berikut bukti transfer saya untuk produk:
${order.first_item?.name} ${order.items_count > 1 ? '(+' + (order.items_count - 1) + ' lainnya)' : ''}

Total Transfer: ${formatPrice(order.grand_total)}`;

  return text; // Return text to be used in handleWhatsAppClick
};

const pendingOrder = ref(null);

const fallbackReason = computed(() => {
  const order = selectedOrder.value || pendingOrder.value;
  if (!order) return "belum melengkapi informasi kontak";

  const isCOD = order.payment_info?.method?.toLowerCase().includes('cod');
  if (!order.store.phone) return "belum mengatur nomor WhatsApp";
  if (!isCOD && (!order.store.bank_details?.bank_name || !order.store.bank_details?.account_number)) {
    return "belum melengkapi informasi rekening bank";
  }
  return "belum melengkapi informasi kontak";
});

// Buy Again Logic
const isBuyingAgain = ref(false);

const buyAgain = async (order) => {
  if (isBuyingAgain.value) return;

  // Check if order has items
  if (!order.items || order.items.length === 0) {
    alert('Tidak ada item untuk dibeli ulang.');
    return;
  }

  isBuyingAgain.value = true;

  try {
    // Add each item to cart sequentially
    for (let i = 0; i < order.items.length; i++) {
      const item = order.items[i];

      await new Promise((resolve, reject) => {
        router.post('/cart', {
          product_id: item.product_id,
          quantity: item.quantity,
          note: item.note || '',
        }, {
          preserveScroll: true,
          preserveState: true,
          onSuccess: () => resolve(),
          onError: (errors) => {
            console.warn(`Failed to add item ${item.name}:`, errors);
            resolve(); // Continue even if one item fails
          },
        });
      });
    }

    // Redirect to cart page after all items added
    router.visit('/cart');
  } catch (error) {
    console.error('Error buying again:', error);
    alert('Terjadi kesalahan saat menambahkan produk ke keranjang.');
  } finally {
    isBuyingAgain.value = false;
  }
};
</script>

<template>
  <div class="bg-slate-50 min-h-screen">

    <Head title="Daftar Transaksi" />

    <div class="mx-auto flex max-w-full flex-col gap-6 px-4 py-8">
      <!-- Breadcrumb -->
      <nav class="flex items-center gap-2 text-sm font-medium text-slate-500">
        <a href="/" class="transition hover:text-slate-800">Beranda</a>
        <svg class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd"
            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
            clip-rule="evenodd" />
        </svg>
        <span class="text-slate-900">Daftar Transaksi</span>
      </nav>

      <div class="grid gap-8 lg:grid-cols-[280px_1fr]">
        <CustomerSidebarMenu active-key="transaksi" />

        <main class="space-y-6">
          <section class="max-w-4xl space-y-6">
            <!-- Header Section -->
            <div class="flex flex-wrap items-end justify-between gap-4">
              <div>
                <h1 class="text-2xl font-bold text-slate-900">Daftar Transaksi</h1>
              </div>
              <div class="flex gap-2">
                <!-- Placeholder for future filters -->
              </div>
            </div>

            <!-- Transactions List -->
            <div v-if="props.orders.length" class="space-y-4">
              <div v-for="order in props.orders" :key="order.id"
                class="group overflow-hidden rounded-md border border-slate-200 bg-white shadow-sm transition-shadow hover:shadow-md">

                <!-- Card Header -->
                <div class="flex items-center gap-3 border-b border-slate-50 bg-white px-5 py-3 text-sm">
                  <div class="flex items-center gap-2 text-slate-900 font-semibold">
                    <svg class="h-5 w-5 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    Belanja
                  </div>
                  <span class="text-slate-400">{{ formatDate(order.created_at) }}</span>
                  <span class="rounded px-1.5 py-0.5 text-xs font-semibold" :class="statusBadgeClass(order.status)">
                    {{ statusLabel(order.status) }}
                  </span>
                  <span class="text-slate-400 text-xs ml-auto">{{ order.order_number }}</span>
                </div>

                <!-- Card Body -->
                <div class="px-5 py-4">
                  <!-- Store Name -->
                  <div class="mb-3 flex items-center gap-2 text-sm font-semibold text-slate-900">
                    <!-- <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                      class="bi bi-shop-window" viewBox="0 0 16 16">
                      <path
                        d="M2.97 1.35A1 1 0 0 1 3.73 1h8.54a1 1 0 0 1 .76.35l2.609 3.044A1.5 1.5 0 0 1 16 5.37v.255a2.375 2.375 0 0 1-4.25 1.458A2.37 2.37 0 0 1 9.875 8 2.37 2.37 0 0 1 8 7.083 2.37 2.37 0 0 1 6.125 8a2.37 2.37 0 0 1-1.875-.917A2.375 2.375 0 0 1 0 5.625V5.37a1.5 1.5 0 0 1 .361-.976zm1.78 4.275a1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0 1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0 1.375 1.375 0 1 0 2.75 0V5.37a.5.5 0 0 0-.12-.325L12.27 2H3.73L1.12 5.045A.5.5 0 0 0 1 5.37v.255a1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0M1.5 8.5A.5.5 0 0 1 2 9v6h12V9a.5.5 0 0 1 1 0v6h.5a.5.5 0 0 1 0 1H.5a.5.5 0 0 1 0-1H1V9a.5.5 0 0 1 .5-.5m2 .5a.5.5 0 0 1 .5.5V13h8V9.5a.5.5 0 0 1 1 0V13a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5a.5.5 0 0 1 .5-.5" />
                    </svg> -->
                    {{ order.store.name }}
                  </div>

                  <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex gap-4">
                      <!-- Product Image -->
                      <div class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-md border border-slate-200">
                        <img v-if="order.first_item?.image_url" :src="order.first_item.image_url"
                          :alt="order.first_item.name" class="h-full w-full object-cover">
                        <div v-else class="flex h-full w-full items-center justify-center bg-slate-50 text-slate-300">
                          <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                          </svg>
                        </div>
                      </div>

                      <div class="space-y-1">
                        <div v-if="order.first_item" class="font-bold text-slate-900 line-clamp-1">
                          {{ order.first_item.name }}
                        </div>
                        <div class="text-sm text-slate-500">
                          {{ order.items_count }} barang x {{ formatPrice(order.grand_total / order.items_count) }}
                          <span v-if="order.items_count > 1" class="ml-1 text-slate-400 text-xs">
                            +{{ order.items_count - 1 }} produk lainnya
                          </span>
                        </div>
                      </div>
                    </div>

                    <div
                      class="flex flex-col items-end gap-1 mt-2 sm:mt-0 pl-4 border-l-0 sm:border-l border-slate-100 min-w-[120px]">
                      <span class="text-xs text-slate-500">Total Belanja</span>
                      <span class="font-bold text-slate-900">{{ formatPrice(order.grand_total) }}</span>
                    </div>
                  </div>

                  <!-- Actions -->
                  <div class="mt-4 flex items-center justify-end gap-2 text-sm">
                    <button @click="openDetailModal(order)"
                      class="font-semibold text-sky-600 hover:text-sky-700 mr-auto hover:cursor-pointer">
                      Lihat Detail Transaksi
                    </button>

                    <button v-if="order.status === 'pending_payment'" @click="handlePayment(order)"
                      :disabled="isStartingChat"
                      class="rounded-md bg-sky-600 px-5 py-2 font-bold text-white shadow hover:bg-sky-700 transition-colors hover:brightness-110 disabled:opacity-50 disabled:cursor-not-allowed">
                      Bayar
                    </button>
                    <button v-else @click="buyAgain(order)" :disabled="isBuyingAgain"
                      class="rounded-md bg-sky-600 px-5 py-2 font-bold text-white shadow hover:bg-sky-700 disabled:opacity-60 disabled:cursor-not-allowed flex items-center gap-2">
                      <svg v-if="isBuyingAgain" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <circle class="opacity-25" cx="12" cy="12" r="10"></circle>
                        <path class="opacity-75" d="M4 12a8 8 0 0 1 8-8" stroke-linecap="round"></path>
                      </svg>
                      {{ isBuyingAgain ? 'Memproses...' : 'Beli Lagi' }}
                    </button>
                    <!-- More menu placeholder -->
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
                    <AlertDialogTitle class="text-center text-xl font-bold">Informasi Belum Lengkap</AlertDialogTitle>
                    <AlertDialogDescription class="text-center text-slate-600 pt-2">
                      Toko <span class="font-semibold text-slate-900">{{ (selectedOrder || pendingOrder)?.store.name
                        }}</span> {{ fallbackReason }}. Apakah Anda ingin menghubungi mereka melalui chat
                      internal?
                    </AlertDialogDescription>
                  </AlertDialogHeader>
                  <AlertDialogFooter class="sm:flex-col gap-2 mt-4">
                    <AlertDialogAction @click="startInternalChat" :disabled="isStartingChat"
                      class="w-full bg-sky-600 hover:bg-sky-700 font-bold py-6 rounded-xl disabled:opacity-50 disabled:cursor-not-allowed transition-opacity">
                      <MessageSquare class="h-4 w-4 mr-2" />
                      Hubungi via Chat Internal
                    </AlertDialogAction>
                    <AlertDialogCancel class="w-full border-slate-200 font-semibold py-6 rounded-xl">Tutup
                    </AlertDialogCancel>
                  </AlertDialogFooter>
                </AlertDialogContent>
              </AlertDialog>
            </div>

            <!-- Empty State -->
            <div v-else
              class="flex min-h-[400px] flex-col items-center justify-center rounded-md border border-dashed border-slate-300 bg-slate-50 p-8 text-center">
              <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
              </div>
              <h3 class="text-lg font-semibold text-slate-900">Belum ada transaksi</h3>
              <p class="mt-2 max-w-sm text-slate-500">Anda belum pernah melakukan transaksi. Mulai belanja sekarang
                untuk melihat riwayat pesanan Anda di sini.</p>
              <a href="/"
                class="mt-6 inline-flex items-center gap-2 rounded-md bg-sky-600 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700">
                Mulai Belanja
              </a>
            </div>
          </section>
        </main>
      </div>
    </div>

    <!-- Detail Transaction Modal -->
    <Teleport to="body">
      <div v-if="showDetailModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
        role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="closeDetailModal"></div>

        <!-- Modal Panel -->
        <div
          class="relative w-full max-w-4xl transform overflow-hidden rounded-md bg-white shadow-2xl transition-all max-h-[90vh] flex flex-col">

          <!-- Modal Header -->
          <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <h3 class="text-lg font-bold text-slate-900">Detail Transaksi</h3>
            <button @click="closeDetailModal"
              class="rounded-full p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
              <span class="sr-only">Close</span>
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Modal Body -->
          <div class="flex-1 overflow-y-auto bg-white p-6" v-if="selectedOrder">
            <div class="grid gap-8 lg:grid-cols-[1fr_300px]">

              <!-- Left Column: Details -->
              <div class="space-y-8">
                <!-- Status Section -->
                <div class="border-b border-slate-100 pb-6">
                  <div class="font-bold text-lg text-slate-900 mb-3">{{ statusLabel(selectedOrder.status) }}</div>
                  <div class="space-y-1 text-sm">
                    <div class="flex">
                      <span class="text-slate-500 w-36">No. Pesanan</span>
                      <span class="font-semibold text-sky-600">{{ selectedOrder.order_number }}</span>
                    </div>
                    <div class="flex">
                      <span class="text-slate-500 w-36">Tanggal Pembelian</span>
                      <span class="text-slate-900">{{ formatDate(selectedOrder.created_at) }}</span>
                    </div>
                  </div>
                </div>

                <!-- Product List -->
                <div class="space-y-4">
                  <div class="flex items-center justify-between">
                    <h4 class="font-bold text-slate-900">Detail Produk</h4>
                    <div class="flex items-center gap-1.5 text-sm font-semibold text-slate-700">
                      {{ selectedOrder.store.name }}
                    </div>
                  </div>

                  <div class="space-y-4 rounded-md border border-slate-100 p-4">
                    <div v-for="item in selectedOrder.items" :key="item.id" class="flex gap-4">
                      <div
                        class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-md border border-slate-200 bg-slate-50">
                        <img v-if="item.image_url" :src="item.image_url" :alt="item.name"
                          class="h-full w-full object-cover">
                        <div v-else class="flex h-full w-full items-center justify-center text-slate-300">
                          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                          </svg>
                        </div>
                      </div>
                      <div class="flex-1">
                        <div class="font-bold text-slate-900 line-clamp-2">{{ item.name }}</div>
                        <div class="mt-1 text-sm text-slate-500">{{ item.quantity }} x {{ formatPrice(item.unit_price)
                        }}</div>
                        <div v-if="item.note" class="mt-1 text-xs text-slate-500 italic">"{{ item.note }}"</div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Shipping Info -->
                <div class="space-y-3">
                  <div class="flex items-center justify-between">
                    <h4 class="font-bold text-slate-900">Info Pengiriman</h4>
                  </div>
                  <div class="grid grid-cols-[120px_1fr] gap-x-4 gap-y-2 text-sm">
                    <div class="text-slate-500">Dikirim dari</div>
                    <div class="text-slate-900 font-medium">
                      {{ selectedOrder.store.city }} <span class="text-slate-400 font-normal">({{
                        selectedOrder.store.name }})</span>
                    </div>

                    <div class="text-slate-500">Metode Pengiriman</div>
                    <div class="text-slate-900 font-medium">
                      {{ selectedOrder.shipping_info?.courier === 'pickup' ? 'Ambil di Toko' : 'Diantar ke Tempat' }}
                      <span
                        v-if="selectedOrder.shipping_info?.service && selectedOrder.shipping_info?.courier !== 'pickup'"
                        class="text-slate-500">- {{ selectedOrder.shipping_info.service }}</span>
                    </div>

                    <template v-if="selectedOrder.shipping_info?.tracking_number">
                      <div class="text-slate-500">No. Resi</div>
                      <div class="text-slate-900 font-medium flex items-center gap-2">
                        {{ selectedOrder.shipping_info.tracking_number }}
                        <button class="text-sky-600 hover:text-sky-700" title="Salin">
                          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                          </svg>
                        </button>
                      </div>
                    </template>

                    <div class="text-slate-500">Alamat Tujuan</div>
                    <div class="text-slate-900">
                      <div class="font-medium">{{ selectedOrder.shipping_address?.recipient_name }}</div>
                      <div class="text-slate-600">{{ selectedOrder.shipping_address?.phone }}</div>
                      <div class="text-slate-600 mt-1">{{ selectedOrder.shipping_address?.full_address }}</div>
                    </div>
                  </div>
                </div>

                <!-- Payment Info -->
                <div class="space-y-3">
                  <h4 class="font-bold text-slate-900">Rincian Pembayaran</h4>
                  <div class="rounded-md bg-slate-50 p-4 space-y-2 text-sm">
                    <div class="flex justify-between items-center">
                      <span class="text-slate-600">Status Pembayaran</span>
                      <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold" :class="selectedOrder.payment_status === 'paid'
                        ? 'bg-emerald-100 text-emerald-700 border border-emerald-300'
                        : 'bg-amber-100 text-amber-700 border border-amber-300'">
                        <svg v-if="selectedOrder.payment_status === 'paid'" class="h-3 w-3" viewBox="0 0 20 20"
                          fill="currentColor">
                          <path fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                        </svg>
                        <svg v-else class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                            clip-rule="evenodd" />
                        </svg>
                        {{ selectedOrder.payment_status === 'paid' ? 'LUNAS' : 'BELUM LUNAS' }}
                      </span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-slate-600">Metode Pembayaran</span>
                      <span class="font-medium text-slate-900">{{ selectedOrder.payment_info?.method ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-slate-600">Total Harga ({{ selectedOrder.items_count }} barang)</span>
                      <span class="text-slate-900">{{ formatPrice(selectedOrder.grand_total -
                        (selectedOrder.shipping_info?.cost || 0))
                        }}</span>
                    </div>
                    <!-- Shipping Cost removed as requested -->

                    <div
                      class="border-t border-slate-200 mt-2 pt-2 flex justify-between text-base font-bold text-slate-900">
                      <span>Total Belanja</span>
                      <span>{{ formatPrice(selectedOrder.grand_total) }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Right Column: Actions (Sticky on desktop) -->
              <div class="lg:sticky lg:top-0 space-y-3">
                <TooltipProvider>
                  <Tooltip :delay-duration="0">
                    <TooltipTrigger as-child>
                      <div class="w-full"
                        :class="{ 'cursor-not-allowed opacity-50': !getPaymentValidation(selectedOrder).valid }">
                        <button v-if="selectedOrder.status === 'pending_payment'" @click="handlePayment(selectedOrder)"
                          :disabled="isStartingChat || !getPaymentValidation(selectedOrder).valid"
                          class="w-full rounded-md bg-sky-600 px-4 py-2.5 font-bold text-white shadow hover:bg-sky-700 flex items-center justify-center gap-2 disabled:pointer-events-none transition-opacity">
                          <ExternalLink class="h-4 w-4" />
                          Bayar Sekarang
                        </button>
                      </div>
                    </TooltipTrigger>
                    <TooltipContent v-if="!getPaymentValidation(selectedOrder).valid">
                      <p>{{ getPaymentValidation(selectedOrder).message }}</p>
                    </TooltipContent>
                  </Tooltip>
                </TooltipProvider>

                <TooltipProvider>
                  <Tooltip :delay-duration="0">
                    <TooltipTrigger as-child>
                      <div class="w-full"
                        :class="{ 'cursor-not-allowed opacity-50': !getChatValidation(selectedOrder).valid }">
                        <button @click="handleWhatsAppClick(selectedOrder)"
                          :disabled="isStartingChat || !getChatValidation(selectedOrder).valid"
                          class="flex items-center justify-center gap-2 w-full rounded-md border border-emerald-600 px-4 py-2.5 font-bold text-emerald-600 hover:bg-emerald-50 transition-colors disabled:pointer-events-none transition-opacity">
                          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path
                              d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                          </svg>
                          Chat Penjual
                        </button>
                      </div>
                    </TooltipTrigger>
                    <TooltipContent v-if="!getChatValidation(selectedOrder).valid">
                      <p>{{ getChatValidation(selectedOrder).message }}</p>
                    </TooltipContent>
                  </Tooltip>
                </TooltipProvider>

                <button v-if="selectedOrder.status === 'shipped' || selectedOrder.status === 'delivered'"
                  class="w-full rounded-md bg-sky-600 px-4 py-2 font-bold text-white shadow hover:bg-sky-700">
                  Lacak Pengiriman
                </button>

                <!-- Invoice Link -->
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                  <span class="text-sm font-bold text-slate-900">Invoice</span>
                  <a :href="selectedOrder.invoice_url" class="text-sm font-semibold text-sky-600 hover:text-sky-700">
                    Lihat Invoice
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Transfer Information Modal -->
    <Teleport to="body">
      <div v-if="showTransferModal" class="fixed inset-0 z-[110] flex items-center justify-center p-4 sm:p-6"
        role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="closeTransferModal">
        </div>

        <div
          class="relative w-full max-w-md transform overflow-hidden rounded-md bg-white shadow-xl transition-all p-6">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-slate-900">Info Transfer Manual</h3>
            <button @click="closeTransferModal" class="rounded-full p-1 text-slate-400 hover:bg-slate-100">
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="space-y-4">
            <div class="border-l-4 border-sky-500 bg-sky-50 p-4 text-sm text-sky-700">
              Silakan transfer sesuai total tagihan ke rekening di bawah ini.
            </div>

            <div class="rounded-xl border border-slate-200 p-5 space-y-5 bg-slate-50/50">
              <div>
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Bank Tujuan</div>
                <div class="font-bold text-slate-900 text-xl">{{ selectedOrder?.store.bank_details?.bank_name || '-' }}
                </div>
              </div>

              <div class="pt-4 border-t border-slate-200/60">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">No. Rekening</div>
                <div class="flex items-center justify-between group">
                  <span class="font-mono text-2xl font-bold text-slate-900 tracking-tight">{{
                    selectedOrder?.store.bank_details?.account_number || '-' }}</span>
                  <button @click="copyToClipboard(selectedOrder?.store.bank_details?.account_number, 'account')"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-sky-600 text-xs font-bold transition-all hover:border-sky-300 hover:bg-sky-50 active:scale-95 shadow-sm">
                    <component :is="copiedField === 'account' ? Check : Copy" class="h-3.5 w-3.5" />
                    {{ copiedField === 'account' ? 'Berhasil' : 'Salin' }}
                  </button>
                </div>
                <div class="text-sm font-medium text-slate-600 mt-2 flex items-center gap-1.5">
                  <span class="text-slate-400">a.n</span>
                  {{ selectedOrder?.store.bank_details?.account_name || '-' }}
                </div>
              </div>

              <div class="pt-5 border-t border-slate-200/60">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Total Tagihan</div>
                <div class="flex items-center justify-between">
                  <span class="font-bold text-sky-600 text-2xl tracking-tight">{{
                    formatPrice(selectedOrder?.grand_total)
                    }}</span>
                  <button @click="copyToClipboard(selectedOrder?.grand_total, 'amount')"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-sky-600 text-xs font-bold transition-all hover:border-sky-300 hover:bg-sky-50 active:scale-95 shadow-sm">
                    <component :is="copiedField === 'amount' ? Check : Copy" class="h-3.5 w-3.5" />
                    {{ copiedField === 'amount' ? 'Berhasil' : 'Salin' }}
                  </button>
                </div>
              </div>
            </div>

            <button v-if="selectedOrder"
              @click="handleWhatsAppClick(selectedOrder, generateTransferProofLink(selectedOrder))"
              class="flex items-center justify-center gap-2 w-full rounded-xl bg-emerald-500 px-4 py-4 text-center font-bold text-white shadow-lg shadow-emerald-200 hover:bg-emerald-600 transition-all hover:-translate-y-0.5 active:translate-y-0">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                <path
                  d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
              </svg>
              Kirim Bukti Pembayaran
            </button>

            <p class="text-[11px] text-center text-slate-400 mt-2">
              Pesanan otomatis dibatalkan jika pembayaran tidak diterima dalam 24 jam.
            </p>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- COD Info Modal -->
    <Teleport to="body">
      <div v-if="showCODModal" class="fixed inset-0 z-[110] flex items-center justify-center p-4 sm:p-6" role="dialog"
        aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="closeCODModal"></div>

        <div
          class="relative w-full max-w-sm transform overflow-hidden rounded-2xl bg-white shadow-xl transition-all p-8 text-center">
          <div
            class="mb-6 mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-amber-50 text-amber-500 shadow-inner">
            <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor font-bold">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>

          <h3 class="text-xl font-bold text-slate-900 mb-2">Pembayaran COD</h3>
          <p class="text-slate-500 mb-8 leading-relaxed">Metode <strong>Cash on Delivery</strong> dipilih. Silakan
            siapkan
            uang tunai dan lakukan konfirmasi ke penjual.</p>

          <div class="space-y-3">
            <button
              @click="handleWhatsAppClick(selectedOrder, 'Halo, saya ingin konfirmasi pesanan COD ' + selectedOrder.order_number)"
              class="flex items-center justify-center gap-2 w-full rounded-xl bg-emerald-500 px-4 py-3.5 font-bold text-white hover:bg-emerald-600 transition-all shadow-lg shadow-emerald-100">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                <path
                  d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
              </svg>
              Konfirmasi via WhatsApp
            </button>
            <button @click="closeCODModal"
              class="w-full rounded-xl border border-slate-200 px-4 py-3.5 font-bold text-slate-500 hover:bg-slate-50 transition-colors">
              Tutup
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>
