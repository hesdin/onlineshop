<script setup>
import { Head, router } from '@inertiajs/vue3';
import { computed, reactive, onBeforeUnmount } from 'vue';
import LandingLayout from '@/Layouts/LandingLayout.vue';

const goBack = () => window.history.back();

const props = defineProps({
  appName: {
    type: String,
    default: 'TP-PKK Marketplace',
  },
  paymentMethods: {
    type: Array,
    default: () => [],
  },
  orders: {
    type: Array,
    default: () => [],
  },
  selectedItems: {
    type: Array,
    default: () => [],
  },
  addressId: {
    type: Number,
    default: null,
  },
  shippingSelections: {
    type: Object,
    default: () => ({}),
  },
  isBuyNow: {
    type: Boolean,
    default: false,
  },
});

const state = reactive({
  selectedMethod: props.paymentMethods?.[0]?.methods?.[0]?.id ?? null,
  showPromo: false,
  promoCode: '',
  submitting: false,
});
const PAYMENT_SUBMIT_LOADING_MS = 2000;
let submitRequestTimer = null;

const formatCurrency = (value) =>
  new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(
    Number.parseInt(value ?? 0, 10) || 0
  );

const selectMethod = (id) => {
  state.selectedMethod = id;
};

const applyPromo = () => {
  if (!state.promoCode.trim()) return;
  state.showPromo = false;
};

const submitPayment = () => {
  if (state.submitting) {
    return;
  }
  if (!state.selectedMethod) {
    alert('Silakan pilih metode pembayaran terlebih dahulu');
    return;
  }

  if (!props.addressId) {
    alert('Alamat pengiriman belum dipilih');
    return;
  }

  state.submitting = true;

  if (submitRequestTimer) {
    clearTimeout(submitRequestTimer);
  }

  submitRequestTimer = setTimeout(() => {
    router.post('/cart/payment/process', {
      payment_method_code: state.selectedMethod,
      items: props.selectedItems,
      address_id: props.addressId,
      shipping_selections: props.shippingSelections,
      notes: {},
    }, {
      preserveScroll: true,
      onFinish: () => {
        state.submitting = false;
      },
    });
    submitRequestTimer = null;
  }, PAYMENT_SUBMIT_LOADING_MS);
};

const totals = computed(() => {
  const items = (props.orders ?? []).reduce((sum, order) => sum + (order.total ?? 0), 0);
  const shipping = (props.orders ?? []).reduce((sum, order) => sum + (order.shipping ?? 0), 0);
  const fee = 0;
  return { items, shipping, fee };
});

onBeforeUnmount(() => {
  if (submitRequestTimer) {
    clearTimeout(submitRequestTimer);
  }
});
</script>

<template>
  <!-- Buy Now Mode: Minimal layout without header/footer -->
  <div v-if="isBuyNow" class="min-h-screen bg-slate-50 font-sans text-slate-900">

    <Head :title="`Pembayaran - ${appName}`" />

    <!-- Minimal Header for Buy Now -->
    <header class="fixed top-0 left-0 right-0 z-40 border-b border-slate-200 bg-white shadow-sm">
      <div class="container mx-auto flex h-16 items-center justify-between px-4">
        <div class="flex items-center gap-3">
          <button type="button" @click="goBack"
            class="flex h-10 w-10 items-center justify-center rounded-full text-slate-600 transition hover:bg-slate-100">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd"
                d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                clip-rule="evenodd" />
            </svg>
          </button>
          <h1 class="text-lg font-semibold text-slate-900">Pembayaran</h1>
        </div>
      </div>
    </header>

    <main class="container mx-auto space-y-12 px-4 py-10 pt-24">
      <section class="space-y-6" @keydown.window.escape="state.showPromo = false">
        <div class="flex items-center gap-3">
          <h1 class="text-3xl font-bold text-slate-900">Pembayaran</h1>
        </div>

        <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1.6fr)_minmax(360px,1fr)]">
          <div class="rounded-lg bg-white p-6 shadow-sm">
            <p class="text-lg font-bold text-slate-900">Metode Pembayaran</p>

            <div class="mt-4 space-y-6">
              <div v-for="(group, gIndex) in paymentMethods" :key="`pm-group-${gIndex}`" class="space-y-3">

                <div class="space-y-3">
                  <label v-for="method in group.methods" :key="method.id"
                    class="flex cursor-pointer items-center gap-4 rounded-lg border px-5 py-4 transition"
                    :class="state.selectedMethod === method.id ? 'border-sky-500 bg-sky-50' : 'border-slate-200 bg-white hover:border-sky-300'"
                    @click="selectMethod(method.id)">

                    <!-- Radio Button -->
                    <span class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full border-2"
                      :class="state.selectedMethod === method.id ? 'border-sky-500' : 'border-slate-300'">
                      <span class="h-2.5 w-2.5 rounded-full bg-sky-500"
                        v-show="state.selectedMethod === method.id"></span>
                    </span>

                    <!-- Icon -->
                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg"
                      :class="state.selectedMethod === method.id ? 'bg-sky-500' : 'bg-slate-100'">
                      <!-- COD Icon -->
                      <svg v-if="group.label === 'Cash on Delivery'" class="h-5 w-5"
                        :class="state.selectedMethod === method.id ? 'text-white' : 'text-slate-600'"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke-linecap="round"
                          stroke-linejoin="round" />
                      </svg>
                      <!-- Transfer Icon -->
                      <svg v-else-if="group.label === 'Transfer Manual'" class="h-5 w-5"
                        :class="state.selectedMethod === method.id ? 'text-white' : 'text-slate-600'"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="5" width="20" height="14" rx="2" stroke-linecap="round"
                          stroke-linejoin="round" />
                        <path d="M2 10h20" stroke-linecap="round" stroke-linejoin="round" />
                      </svg>
                    </div>

                    <!-- Text -->
                    <div class="flex-1">
                      <p class="text-sm font-semibold text-slate-900">{{ method.name }}</p>
                      <p class="text-xs text-slate-500">{{ group.note }}</p>
                    </div>
                  </label>
                </div>
              </div>
            </div>
          </div>

          <aside class="space-y-4">
            <div class="rounded-lg bg-white shadow-sm ring-1 ring-slate-100">
              <div class="p-5">
                <button type="button" @click="state.showPromo = true"
                  class="flex w-full items-center justify-between gap-3 rounded-md bg-amber-100 px-5 py-4 text-left text-base font-semibold text-slate-800 transition hover:bg-amber-200">
                  <span class="inline-flex items-center gap-2">
                    <svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path opacity="0.4"
                        d="M21.8 10.8401C22.19 10.8401 22.5 10.5301 22.5 10.1401V9.21011C22.5 5.11011 21.25 3.86011 17.15 3.86011H7.85C3.75 3.86011 2.5 5.11011 2.5 9.21011V9.68011C2.5 10.0701 2.81 10.3801 3.2 10.3801C4.1 10.3801 4.83 11.1101 4.83 12.0101C4.83 12.9101 4.1 13.6301 3.2 13.6301C2.81 13.6301 2.5 13.9401 2.5 14.3301V14.8001C2.5 18.9001 3.75 20.1501 7.85 20.1501H17.15C21.25 20.1501 22.5 18.9001 22.5 14.8001C22.5 14.4101 22.19 14.1001 21.8 14.1001C20.9 14.1001 20.17 13.3701 20.17 12.4701C20.17 11.5701 20.9 10.8401 21.8 10.8401Z"
                        fill="#F7931E" />
                      <path
                        d="M15.5 15.8799C14.94 15.8799 14.49 15.4299 14.49 14.8799C14.49 14.3299 14.94 13.8799 15.49 13.8799C16.04 13.8799 16.49 14.3299 16.49 14.8799C16.49 15.4299 16.06 15.8799 15.5 15.8799Z"
                        fill="#F7931E" />
                      <path
                        d="M9.49999 10.8799C8.93999 10.8799 8.48999 10.4299 8.48999 9.87988C8.48999 9.32988 8.93999 8.87988 9.48999 8.87988C10.04 8.87988 10.49 9.32988 10.49 9.87988C10.49 10.4299 10.06 10.8799 9.49999 10.8799Z"
                        fill="#F7931E" />
                      <path
                        d="M9.13007 16.4299C8.94007 16.4299 8.75007 16.3599 8.60007 16.2099C8.31007 15.9199 8.31007 15.4399 8.60007 15.1499L15.3301 8.41989C15.6201 8.12989 16.1001 8.12989 16.3901 8.41989C16.6801 8.70989 16.6801 9.18989 16.3901 9.47989L9.66007 16.2099C9.52007 16.3599 9.32007 16.4299 9.13007 16.4299Z"
                        fill="#F7931E" />
                    </svg>

                    Gunakan Promo?
                  </span>
                  <svg class="h-4 w-4 text-amber-500" viewBox="0 0 20 20" fill="none" stroke="currentColor"
                    stroke-width="1.6">
                    <path d="m8 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                </button>
              </div>

              <div class="space-y-4 px-5 py-4">
                <p class="text-xs text-slate-500">*Voucher hanya bisa digunakan untuk 1 Penjual per transaksi.</p>

                <div class="space-y-4 border-t border-slate-100 pt-3">
                  <p class="text-base font-bold text-slate-900">Ringkasan Belanja</p>

                  <div v-for="(order, index) in orders" :key="`order-${index}`"
                    class="space-y-2 rounded-lg border border-slate-100 bg-slate-50 px-4 py-3">
                    <div class="flex items-center justify-between">
                      <p class="text-sm font-semibold text-slate-900">{{ order.title }}</p>
                    </div>

                    <div class="text-sm text-slate-800">
                      <div class="flex items-center justify-between">
                        <span class="text-slate-600">Nama Toko</span>
                        <span class="max-w-[60%] text-right font-semibold">{{ order.vendor }}</span>
                      </div>
                      <div class="flex items-center justify-between">
                        <span class="text-slate-600">{{ order.typeLabel }}</span>
                        <span class="font-semibold">{{ formatCurrency(order.total) }}</span>
                      </div>
                    </div>

                    <!-- WhatsApp Tanya Toko Button -->
                    <a v-if="order.whatsapp_link" :href="order.whatsapp_link" target="_blank" rel="noopener noreferrer"
                      class="mt-2 flex w-full items-center justify-center gap-2 rounded-sm border border-emerald-600 bg-white px-4 py-2 text-sm font-semibold text-emerald-600 transition hover:bg-emerald-50">
                      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                        <path
                          d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                      </svg>
                      Tanya Toko (Transfer Manual)
                    </a>

                    <div
                      class="flex items-center justify-between border-t border-slate-200 pt-2 text-sm font-semibold text-slate-900">
                      <span>Total Pesanan</span>
                      <span>{{ formatCurrency((order.total ?? 0) + (order.shipping ?? 0)) }}</span>
                    </div>
                  </div>

                  <div class="space-y-2 border-t border-slate-200 pt-3 text-sm font-semibold text-slate-800">
                    <div class="flex items-center justify-between text-base font-bold text-slate-900">
                      <span>Total Semua Pesanan</span>
                      <span>{{ formatCurrency(totals.items + totals.shipping) }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <button type="button" @click="submitPayment" :disabled="!state.selectedMethod || state.submitting"
              class="w-full rounded-lg px-4 py-3 text-sm font-semibold shadow-sm transition flex items-center justify-center gap-2"
              :class="state.selectedMethod && !state.submitting
                ? 'bg-sky-600 text-white hover:bg-sky-700 cursor-pointer'
                : 'bg-slate-300 text-slate-500 cursor-not-allowed'">
              <svg v-if="state.submitting" class="h-4 w-4 animate-spin text-current" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2">
                <circle class="opacity-25" cx="12" cy="12" r="10" />
                <path class="opacity-75" d="M4 12a8 8 0 0 1 8-8" stroke-linecap="round" />
              </svg>
              <span>{{ state.submitting ? 'Loading...' : 'Bayar Sekarang' }}</span>
            </button>
          </aside>
        </div>

        <div v-if="state.showPromo"
          class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/60 px-4 py-10 backdrop-blur-sm"
          role="dialog" aria-modal="true" @click.self="state.showPromo = false">
          <div class="relative w-full max-w-xl rounded-lg bg-white shadow-sm ring-1 ring-slate-100" @click.stop>
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-6 py-5">
              <div class="space-y-1">
                <p class="text-xl font-bold text-slate-900">Promo</p>
              </div>
              <button type="button" class="text-slate-400 transition hover:text-slate-600"
                aria-label="Tutup popup promo" @click="state.showPromo = false">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <path d="M6 6l12 12M18 6 6 18" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </button>
            </div>

            <div class="space-y-6 px-6 py-6">
              <div class="space-y-2">
                <p class="text-sm font-semibold text-slate-800">Kode Promo</p>
                <div class="flex flex-col gap-3 sm:flex-row">
                  <input type="text" v-model="state.promoCode" placeholder="Contoh: QWE123"
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-800 shadow-inner placeholder:text-slate-400 focus:border-sky-400 focus:ring-2 focus:ring-sky-100"
                    autocomplete="off" />
                  <button type="button" @click="applyPromo"
                    class="whitespace-nowrap rounded-lg bg-sky-600 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700">
                    Pakai Promo
                  </button>
                </div>
              </div>

              <hr class="border-slate-200" />

              <div class="space-y-4">
                <p class="text-base font-bold text-slate-900">Promo Lainnya</p>
                <div class="flex flex-col items-center gap-3 rounded-lg bg-slate-50 px-6 py-10 text-center">
                  <svg width="200" height="200" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg"
                    xmlns:xlink="http://www.w3.org/1999/xlink">
                    <rect width="200" height="200" fill="url(#pattern0)" />
                    <defs>
                      <pattern id="pattern0" patternContentUnits="objectBoundingBox" width="1" height="1">
                        <use xlink:href="#image0_2224_309323" transform="scale(0.000333333)" />
                      </pattern>
                      <image id="image0_2224_309323" width="3000" height="3000"
                        xlink:href="data:image/png;base64,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5184q24JN37W1tuv3cNsej4b3bfeLyUG5u2uYygzJba+fKtdfs5n/S7W8ot7nfvDK0Zkf7dO9M83gR994nnHw75X37G72Z4f66s3+PTrPNVG9W5tKe3nvK2nrDqzsaXffStrc79Ydv3w2bH80ffW/w26fp7HtvyFXfHfv0YvvlMrS9cek0qOMu7n3e1ZHludupVj6ZfdParzb53pu75d5umes3TM/Hl6cPXhq/vZ3rLae3d/ntPGW2/nZ3D4fTm89dxH8Tv/+Otv/48vzRnzS6LqzenfrNjC4HaTru3dv69q+9arvv3tjO0xvNTL+fThGfXLletU+cv9FWUabaxeUG8Z3b3nVPp7qtrdF3/ts39e327gaf1lz55Y+2+zT5jTbpT/enXT346V3v/fsH94GOdIgu29vgk7ezcnqwuk49fGN7vPb5cDLl7nZ3nwfr04p87xSbK5epNrw6Pri9HyF5GbmiLE3WPt/Nl4fVabrFnatX7j2ae/d747LtDudXl92ePB3urtxGedjexavpDF68b+Fybntv2M7uvXlXdue9e+mjVe998afs9Tu8C/feW8c7Ok/3tV1tc3t0+LbPky2+7W2v9950b5y32nMXh8+3u1evGX6136X/fLP7MsVu2ePXs9MMn9ceTQbK8Pdt/xvsZvO47eZo3uftDv/K09rZO/W61dzFZdvHofeLe8tOM0+8XRmae7Dv47C5cjvN4fW25j0Zd7fxP8lc79i53mi+Pr3d+peuM4hfua259XSL+GTLe9M3PmV2zj35fk+6zPgyRVxukDbob+tu2bnn2zetOz/Bv7LyfPjUk+8GrvN2P3nete9u3n69i9vQdTdrS8YtS3fruutXK7cp23vv7bud8r2/PTv0pzxdfuP0q9kr2thZPVm5jfz2y9926pXb9Nbe48Or+6eet7pF6xY+yG+nvsyVe+rI7p196+989e3t32PzKbd9OW/wi/Xktz83r4jLu6He3P5koSzfXb9aeTPlMpB34WR2zl2v2uy4N99vdEO23StPk4PVt7Y9fhHf2d441Bn0x9vYLI82N75xZ8ffGffOdpe3sd1u69Z/0+zauPCt752N3cWhN77ZvTLe4l34pI0+nw1I89vg1ufenw04fvm+6+GD+NO+HTmd3jLF7snS3Lb0rltHZqOt6ng9GqyHw8nM5x709+v8/ktrrj4uN/dTlv6UsSH3du+M3wHr06Bd96D81uhwZ209+d0r16tW3j16O/XLZQfpV69as7iuuzh8vmZ6b/0nnH+e/K3PvbXM/IPKwTHLhWmDuKyb11sf1sO35x60WQ2vh9u8sBl+t4bTIX+jrXaaeetvzw33zpN+5Sa785f29rt3f7/jbm6/8cnjt0ez398o1wxNrp6NrbXTDB3Wwy+0/9Srv/uXZ+fy9HnuwWp7ozlcHnZj9926ULf+1ur1jXKZdr0BeW59+hM2v7mOrfrj+aPLF3EZSC8Od8sNXlr98maf9prH9TZu3w3l3+5vMvqD8rBe/a3ve/o2ivjeuvRqO9/3JveU3d6+7ez+pT91ZQefdmT+m43d8Qa0XeRB3r7pg+PVx+lQp341OT5vY31cd7LVdD9p/Oa72d3Nd6e+3b0lw3fbgvV3Znu7s/25+7f3PvW3v+/9Xvd5G2284e70Lu/Gk75/k5zu3E9/MrBdHMoM0gb3n5yMvFu+Oo8G6XB0H23w4s01M7q8DJTb/r7l4eU/adWH77/ZpOHF4fuzeWG32ruy9YfLVN97+97Zb+7rHb3Lq1b+1uEVp6uP937KVn/jNpuhXSj36TfoNl9Nf/yt19t+9y5Og3v70quVP/0Om/Vp06dXswtl3E945S5t+t4r2qX11inyYXvDn77mJtNs+eXpyu3bm/xu0DbqyNqky++bXiy3t3tPNx3aerpHn8w3ORs6xe3Zs+2W613zs7O5u176/XevDf3u5v1u12N+tfX3V37+Heu9+7y7+y9/db7+8Z9Y2vdtK3r1veNu7Pe3qfT3b+8N93t4L++7uK9+96S7W/35PteW9Xt+N7evmn7/t64+frbe713Yfvd8ulue++7W/++ue+5ue+/87q377/x99/75rfl7u8uv33vfu9+9/7/B32Y5TE75/eAAAAAAElFTkSuQmCC" />
                    </defs>
                  </svg>

                  <p class="text-sm font-semibold text-slate-700">Tidak ada promo tersedia saat ini</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>
  </div>

  <!-- Normal Cart Checkout Mode: Full layout with header/footer -->
  <LandingLayout v-else>

    <Head :title="`Pembayaran - ${appName}`" />

    <section class="space-y-6" @keydown.window.escape="state.showPromo = false">
      <div class="flex items-center gap-3">
        <h1 class="text-3xl font-bold text-slate-900">Pembayaran</h1>
      </div>

      <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1.6fr)_minmax(360px,1fr)]">
        <div class="rounded-lg bg-white p-6 shadow-sm">
          <p class="text-lg font-bold text-slate-900">Metode Pembayaran</p>

          <div class="mt-4 space-y-6">
            <div v-for="(group, gIndex) in paymentMethods" :key="`pm-group-${gIndex}`" class="space-y-3">

              <div class="space-y-3">
                <label v-for="method in group.methods" :key="method.id"
                  class="flex cursor-pointer items-center gap-4 rounded-lg border px-5 py-4 transition"
                  :class="state.selectedMethod === method.id ? 'border-sky-500 bg-sky-50' : 'border-slate-200 bg-white hover:border-sky-300'"
                  @click="selectMethod(method.id)">

                  <!-- Radio Button -->
                  <span class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full border-2"
                    :class="state.selectedMethod === method.id ? 'border-sky-500' : 'border-slate-300'">
                    <span class="h-2.5 w-2.5 rounded-full bg-sky-500"
                      v-show="state.selectedMethod === method.id"></span>
                  </span>

                  <!-- Icon -->
                  <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg"
                    :class="state.selectedMethod === method.id ? 'bg-sky-500' : 'bg-slate-100'">
                    <!-- COD Icon -->
                    <svg v-if="group.label === 'Cash on Delivery'" class="h-5 w-5"
                      :class="state.selectedMethod === method.id ? 'text-white' : 'text-slate-600'" viewBox="0 0 24 24"
                      fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke-linecap="round"
                        stroke-linejoin="round" />
                    </svg>
                    <!-- Transfer Icon -->
                    <svg v-else-if="group.label === 'Transfer Manual'" class="h-5 w-5"
                      :class="state.selectedMethod === method.id ? 'text-white' : 'text-slate-600'" viewBox="0 0 24 24"
                      fill="none" stroke="currentColor" stroke-width="2">
                      <rect x="2" y="5" width="20" height="14" rx="2" stroke-linecap="round" stroke-linejoin="round" />
                      <path d="M2 10h20" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                  </div>

                  <!-- Text -->
                  <div class="flex-1">
                    <p class="text-sm font-semibold text-slate-900">{{ method.name }}</p>
                    <p class="text-xs text-slate-500">{{ group.note }}</p>
                  </div>
                </label>
              </div>
            </div>
          </div>
        </div>

        <aside class="space-y-4">
          <div class="rounded-lg bg-white shadow-sm ring-1 ring-slate-100">
            <div class="p-5">
              <button type="button" @click="state.showPromo = true"
                class="flex w-full items-center justify-between gap-3 rounded-md bg-amber-100 px-5 py-4 text-left text-base font-semibold text-slate-800 transition hover:bg-amber-200">
                <span class="inline-flex items-center gap-2">
                  <svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path opacity="0.4"
                      d="M21.8 10.8401C22.19 10.8401 22.5 10.5301 22.5 10.1401V9.21011C22.5 5.11011 21.25 3.86011 17.15 3.86011H7.85C3.75 3.86011 2.5 5.11011 2.5 9.21011V9.68011C2.5 10.0701 2.81 10.3801 3.2 10.3801C4.1 10.3801 4.83 11.1101 4.83 12.0101C4.83 12.9101 4.1 13.6301 3.2 13.6301C2.81 13.6301 2.5 13.9401 2.5 14.3301V14.8001C2.5 18.9001 3.75 20.1501 7.85 20.1501H17.15C21.25 20.1501 22.5 18.9001 22.5 14.8001C22.5 14.4101 22.19 14.1001 21.8 14.1001C20.9 14.1001 20.17 13.3701 20.17 12.4701C20.17 11.5701 20.9 10.8401 21.8 10.8401Z"
                      fill="#F7931E" />
                    <path
                      d="M15.5 15.8799C14.94 15.8799 14.49 15.4299 14.49 14.8799C14.49 14.3299 14.94 13.8799 15.49 13.8799C16.04 13.8799 16.49 14.3299 16.49 14.8799C16.49 15.4299 16.06 15.8799 15.5 15.8799Z"
                      fill="#F7931E" />
                    <path
                      d="M9.49999 10.8799C8.93999 10.8799 8.48999 10.4299 8.48999 9.87988C8.48999 9.32988 8.93999 8.87988 9.48999 8.87988C10.04 8.87988 10.49 9.32988 10.49 9.87988C10.49 10.4299 10.06 10.8799 9.49999 10.8799Z"
                      fill="#F7931E" />
                    <path
                      d="M9.13007 16.4299C8.94007 16.4299 8.75007 16.3599 8.60007 16.2099C8.31007 15.9199 8.31007 15.4399 8.60007 15.1499L15.3301 8.41989C15.6201 8.12989 16.1001 8.12989 16.3901 8.41989C16.6801 8.70989 16.6801 9.18989 16.3901 9.47989L9.66007 16.2099C9.52007 16.3599 9.32007 16.4299 9.13007 16.4299Z"
                      fill="#F7931E" />
                  </svg>

                  Gunakan Promo?
                </span>
                <svg class="h-4 w-4 text-amber-500" viewBox="0 0 20 20" fill="none" stroke="currentColor"
                  stroke-width="1.6">
                  <path d="m8 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </button>
            </div>

            <div class="space-y-4 px-5 py-4">
              <p class="text-xs text-slate-500">*Voucher hanya bisa digunakan untuk 1 Penjual per transaksi.</p>

              <div class="space-y-4 border-t border-slate-100 pt-3">
                <p class="text-base font-bold text-slate-900">Ringkasan Belanja</p>

                <div v-for="(order, index) in orders" :key="`order-${index}`"
                  class="space-y-2 rounded-lg border border-slate-100 bg-slate-50 px-4 py-3">
                  <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-900">{{ order.title }}</p>
                  </div>

                  <div class="text-sm text-slate-800">
                    <div class="flex items-center justify-between">
                      <span class="text-slate-600">Nama Toko</span>
                      <span class="max-w-[60%] text-right font-semibold">{{ order.vendor }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                      <span class="text-slate-600">{{ order.typeLabel }}</span>
                      <span class="font-semibold">{{ formatCurrency(order.total) }}</span>
                    </div>
                  </div>

                  <!-- WhatsApp Tanya Toko Button -->
                  <a v-if="order.whatsapp_link" :href="order.whatsapp_link" target="_blank" rel="noopener noreferrer"
                    class="mt-2 flex w-full items-center justify-center gap-2 rounded-lg border border-emerald-600 bg-white px-4 py-2 text-sm font-semibold text-emerald-600 transition hover:bg-emerald-50">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                      <path
                        d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                    </svg>
                    Tanya Toko (Transfer Manual)
                  </a>

                  <div
                    class="flex items-center justify-between border-t border-slate-200 pt-2 text-sm font-semibold text-slate-900">
                    <span>Total Pesanan</span>
                    <span>{{ formatCurrency((order.total ?? 0) + (order.shipping ?? 0)) }}</span>
                  </div>
                </div>

                <div class="space-y-2 border-t border-slate-200 pt-3 text-sm font-semibold text-slate-800">
                  <div class="flex items-center justify-between text-base font-bold text-slate-900">
                    <span>Total Semua Pesanan</span>
                    <span>{{ formatCurrency(totals.items + totals.shipping) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <button type="button" @click="submitPayment" :disabled="!state.selectedMethod || state.submitting"
            class="w-full rounded-lg px-4 py-3 text-sm font-semibold shadow-sm transition flex items-center justify-center gap-2"
            :class="state.selectedMethod && !state.submitting
              ? 'bg-sky-600 text-white hover:bg-sky-700 cursor-pointer'
              : 'bg-slate-300 text-slate-500 cursor-not-allowed'">
            <svg v-if="state.submitting" class="h-4 w-4 animate-spin text-current" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2">
              <circle class="opacity-25" cx="12" cy="12" r="10" />
              <path class="opacity-75" d="M4 12a8 8 0 0 1 8-8" stroke-linecap="round" />
            </svg>
            <span>{{ state.submitting ? 'Loading...' : 'Bayar Sekarang' }}</span>
          </button>
        </aside>
      </div>

      <div v-if="state.showPromo"
        class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/60 px-4 py-10 backdrop-blur-sm"
        role="dialog" aria-modal="true" @click.self="state.showPromo = false">
        <div class="relative w-full max-w-xl rounded-lg bg-white shadow-sm ring-1 ring-slate-100" @click.stop>
          <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-6 py-5">
            <div class="space-y-1">
              <p class="text-xl font-bold text-slate-900">Promo</p>
            </div>
            <button type="button" class="text-slate-400 transition hover:text-slate-600" aria-label="Tutup popup promo"
              @click="state.showPromo = false">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M6 6l12 12M18 6 6 18" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </button>
          </div>

          <div class="space-y-6 px-6 py-6">
            <div class="space-y-2">
              <p class="text-sm font-semibold text-slate-800">Kode Promo</p>
              <div class="flex flex-col gap-3 sm:flex-row">
                <input type="text" v-model="state.promoCode" placeholder="Contoh: QWE123"
                  class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-800 shadow-inner placeholder:text-slate-400 focus:border-sky-400 focus:ring-2 focus:ring-sky-100"
                  autocomplete="off" />
                <button type="button" @click="applyPromo"
                  class="whitespace-nowrap rounded-lg bg-sky-600 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700">
                  Pakai Promo
                </button>
              </div>
            </div>

            <hr class="border-slate-200" />

            <div class="space-y-4">
              <p class="text-base font-bold text-slate-900">Promo Lainnya</p>
              <div class="flex flex-col items-center gap-3 rounded-lg bg-slate-50 px-6 py-10 text-center">
                <p class="text-sm font-semibold text-slate-700">Tidak ada promo tersedia saat ini</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </LandingLayout>
</template>
