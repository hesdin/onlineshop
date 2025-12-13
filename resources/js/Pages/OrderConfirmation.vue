<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import LandingLayout from '@/Layouts/LandingLayout.vue';

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

const isCOD = computed(() => props.orders.some(order => order.payment_method_code === 'cod'));
</script>

<template>
  <LandingLayout>
    <Head :title="`Konfirmasi Pesanan - ${appName}`" />

    <section class="min-h-screen space-y-6 pb-20">
      <div class="flex items-center gap-2">
        <h1 class="text-3xl font-bold text-slate-900">Konfirmasi Pesanan</h1>
      </div>

      <!-- Success Message -->
      <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-6 shadow-sm">
        <div class="flex items-start gap-4">
          <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-emerald-500">
            <svg class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </div>
          <div class="flex-1">
            <h2 class="text-xl font-bold text-emerald-900">Pesanan Berhasil Dibuat!</h2>
            <p class="mt-2 text-sm text-emerald-700">
              Terima kasih atas pesanan Anda. Silakan cek detail pesanan di bawah ini.
            </p>
          </div>
        </div>
      </div>

      <!-- Orders List -->
      <div class="space-y-4">
        <div v-for="(order, index) in orders" :key="order.id"
          class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
          <div class="border-b border-slate-100 bg-slate-50 px-6 py-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-slate-600">Pesanan {{ index + 1 }}</p>
                <p class="text-lg font-bold text-slate-900">{{ order.order_number }}</p>
              </div>
              <span class="rounded-full px-4 py-1.5 text-xs font-semibold"
                :class="getPaymentStatusClass(order.payment_status)">
                {{ order.payment_status === 'pending' ? 'Menunggu Pembayaran' : order.payment_status }}
              </span>
            </div>
          </div>

          <div class="space-y-4 p-6">
            <div class="grid gap-4 sm:grid-cols-2">
              <div>
                <p class="text-sm text-slate-500">Nama Toko</p>
                <p class="font-semibold text-slate-900">{{ order.store_name }}</p>
              </div>
              <div>
                <p class="text-sm text-slate-500">Metode Pembayaran</p>
                <p class="font-semibold text-slate-900">{{ order.payment_method }}</p>
              </div>
              <div>
                <p class="text-sm text-slate-500">Total Pembayaran</p>
                <p class="text-lg font-bold text-sky-600">{{ formatCurrency(order.grand_total) }}</p>
              </div>
              <div>
                <p class="text-sm text-slate-500">Tanggal Pesanan</p>
                <p class="font-semibold text-slate-900">{{ order.ordered_at }}</p>
              </div>
            </div>

            <div v-if="order.payment_instructions"
              class="rounded-lg border-l-4 border-sky-500 bg-sky-50 p-4">
              <p class="text-sm font-semibold text-sky-900">Instruksi Pembayaran:</p>
              <p class="mt-1 text-sm text-sky-700">{{ order.payment_instructions }}</p>
            </div>

            <!-- Order Items -->
            <div v-if="order.items && order.items.length" class="mt-4 space-y-2">
              <p class="text-sm font-semibold text-slate-700">Detail Pesanan:</p>
              <div class="rounded-lg border border-slate-100 bg-slate-50 px-4 py-3">
                <div v-for="(item, idx) in order.items" :key="idx" class="flex items-center justify-between text-sm">
                  <span class="text-slate-700">{{ item.name }} x{{ item.quantity }}</span>
                  <span class="font-semibold text-slate-900">{{ formatCurrency(item.subtotal) }}</span>
                </div>
              </div>
            </div>

            <!-- WhatsApp CTA Buttons -->
            <div class="flex flex-wrap items-center gap-3 border-t border-slate-100 pt-4">
              <a :href="`/orders/${order.id}/invoice`"
                class="inline-flex items-center gap-2 rounded-lg border border-sky-600 px-4 py-2.5 text-sm font-semibold text-sky-600 transition hover:bg-sky-50">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                  <polyline points="7 10 12 15 17 10" />
                  <line x1="12" y1="15" x2="12" y2="3" />
                </svg>
                Download Invoice
              </a>

              <!-- COD WhatsApp Button -->
              <a v-if="order.whatsapp_link && order.payment_method_code === 'cod'"
                :href="order.whatsapp_link"
                target="_blank" rel="noopener noreferrer"
                class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-emerald-500 px-6 py-3 text-sm font-semibold text-white transition hover:bg-emerald-600 sm:flex-none">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                <span class="font-bold">Konfirmasi Pesanan via WhatsApp</span>
              </a>

              <!-- Manual Transfer WhatsApp Button -->
              <a v-else-if="order.whatsapp_link && order.payment_method_code === 'manual_transfer'"
                :href="order.whatsapp_link"
                target="_blank" rel="noopener noreferrer"
                class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-sky-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-sky-700 sm:flex-none">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                <span class="font-bold">Minta Nomor Rekening Transfer</span>
              </a>
            </div>

            <!-- Store Info Card -->
            <div v-if="order.store_rating || order.store_response_time"
              class="mt-4 flex items-center gap-4 rounded-lg border border-slate-100 bg-slate-50 px-4 py-3">
              <div class="flex items-center gap-2 text-sm text-slate-600">
                <svg class="h-4 w-4 text-amber-500" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                </svg>
                <span v-if="order.store_rating">{{ order.store_rating }}/5.0</span>
              </div>
              <div v-if="order.store_response_time" class="text-sm text-slate-600">
                <span class="font-semibold">Respon:</span> {{ order.store_response_time }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Summary -->
      <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-lg font-bold text-slate-900">Ringkasan Pesanan</h3>
        <div class="mt-4 space-y-3">
          <div class="flex items-center justify-between text-sm">
            <span class="text-slate-600">Total Pesanan</span>
            <span class="font-semibold text-slate-900">{{ orders.length }} pesanan</span>
          </div>
          <div class="flex items-center justify-between border-t border-slate-100 pt-3">
            <span class="font-semibold text-slate-900">Total Pembayaran</span>
            <span class="text-xl font-bold text-sky-600">{{ formatCurrency(totalAmount) }}</span>
          </div>
        </div>
      </div>

      <!-- Next Steps -->
      <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-lg font-bold text-slate-900">Langkah Selanjutnya</h3>
        <div class="mt-4 space-y-3">
          <div v-if="isCOD" class="flex items-start gap-3">
            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-sky-100 text-sky-600 font-semibold">
              1
            </div>
            <div>
              <p class="font-semibold text-slate-900">Tunggu Konfirmasi Toko</p>
              <p class="text-sm text-slate-600">Toko akan mengkonfirmasi pesanan Anda dan memproses pengiriman.</p>
            </div>
          </div>
          <div v-else class="flex items-start gap-3">
            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-sky-100 text-sky-600 font-semibold">
              1
            </div>
            <div>
              <p class="font-semibold text-slate-900">Lakukan Pembayaran</p>
              <p class="text-sm text-slate-600">Silakan lakukan transfer ke rekening toko. Hubungi toko melalui WhatsApp untuk detail rekening.</p>
            </div>
          </div>
          <div class="flex items-start gap-3">
            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-sky-100 text-sky-600 font-semibold">
              {{ isCOD ? '2' : '2' }}
            </div>
            <div>
              <p class="font-semibold text-slate-900">{{ isCOD ? 'Terima Pesanan' : 'Upload Bukti Transfer' }}</p>
              <p class="text-sm text-slate-600">
                {{ isCOD
                  ? 'Bayar kepada kurir saat pesanan tiba di lokasi Anda.'
                  : 'Upload bukti transfer melalui dashboard Anda untuk verifikasi pembayaran.'
                }}
              </p>
            </div>
          </div>
          <div class="flex items-start gap-3">
            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-sky-100 text-sky-600 font-semibold">
              3
            </div>
            <div>
              <p class="font-semibold text-slate-900">Lacak Pesanan</p>
              <p class="text-sm text-slate-600">Pantau status pesanan Anda melalui dashboard dan tunggu sampai pesanan dikirimkan.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-wrap items-center gap-3">
        <Link href="/customer/dashboard/transactions"
          class="inline-flex items-center gap-2 rounded-lg bg-sky-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-sky-700">
          Lihat Pesanan Saya
        </Link>
        <Link href="/"
          class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
          Kembali ke Beranda
        </Link>
      </div>
    </section>
  </LandingLayout>
</template>
