<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, reactive, ref, watch } from 'vue';

const props = defineProps({
  isAuthenticated: Boolean,
  authUser: Object,
  megaMenuData: Array,
  cartItems: Array,
  notifications: Array,
});

const emit = defineEmits(['toggleMobileMenu']);

const timers = {
  menu: null,
  profile: null,
  notifications: null,
  cart: null,
};

const clearTimer = (key) => {
  if (timers[key]) {
    clearTimeout(timers[key]);
    timers[key] = null;
  }
};

const state = reactive({
  menuOpen: false,
  profileOpen: false,
  notificationsOpen: false,
  cartOpen: false,
  activeIndex: 0,
});

const cartItems = computed(() => props.cartItems ?? []);

const cartCount = computed(() =>
  cartItems.value.reduce((total, item) => total + Number(item?.qty ?? item?.quantity ?? 0), 0)
);

const normalizePrice = (value) => {
  if (Number.isFinite(value)) return value;
  const cleaned = String(value ?? '').replace(/\D/g, '');
  const numeric = Number.parseInt(cleaned, 10);
  return Number.isNaN(numeric) ? 0 : numeric;
};

const formatPrice = (value) =>
  new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(
    normalizePrice(value)
  );

const cartItemPrice = (item) => normalizePrice(item?.subtotal ?? item?.unit_price ?? item?.price ?? 0);
const cartItemQty = (item) => item?.qty ?? item?.quantity ?? 0;
const cartItemImage = (item) => item?.image || item?.img || 'https://picsum.photos/seed/cart-placeholder/64/64';
const cartItemName = (item) => item?.name ?? 'Produk';

const page = usePage();
const parseQueryFromUrl = (url) => {
  try {
    const parsed = new URL(url, window.location.origin);
    const params = parsed.searchParams;
    return params.get('search') ?? params.get('q') ?? '';
  } catch (e) {
    return '';
  }
};

const searchQuery = ref(page.props.search ?? page.props.q ?? page.props.filters?.search ?? parseQueryFromUrl(page.url));
const defaultSearchUrl = computed(() => '/search');

watch(
  () => page.url,
  (val) => {
    searchQuery.value = parseQueryFromUrl(val);
  }
);

const submitSearch = () => {
  const query = searchQuery.value?.toString().trim() ?? '';
  router.get(defaultSearchUrl.value, { q: query || undefined }, { preserveState: true, preserveScroll: true, replace: true });
};

const openMenu = () => {
  clearTimer('menu');
  state.menuOpen = true;
};

const closeMenuWithDelay = () => {
  clearTimer('menu');
  timers.menu = window.setTimeout(() => {
    state.menuOpen = false;
  }, 120);
};

const openProfile = () => {
  clearTimer('profile');
  state.profileOpen = true;
};

const closeProfileWithDelay = () => {
  clearTimer('profile');
  timers.profile = window.setTimeout(() => {
    state.profileOpen = false;
  }, 120);
};

const openNotifications = () => {
  clearTimer('notifications');
  state.notificationsOpen = true;
};

const closeNotificationsWithDelay = () => {
  clearTimer('notifications');
  timers.notifications = window.setTimeout(() => {
    state.notificationsOpen = false;
  }, 120);
};

const openCart = () => {
  clearTimer('cart');
  state.cartOpen = true;
};

const closeCartWithDelay = () => {
  clearTimer('cart');
  timers.cart = window.setTimeout(() => {
    state.cartOpen = false;
  }, 120);
};

onBeforeUnmount(() => {
  Object.keys(timers).forEach((key) => clearTimer(key));
});
</script>

<template>
  <div class="w-full bg-white/95 backdrop-blur">
    <div class="container relative mx-auto flex flex-wrap items-center gap-4 px-4 py-3 lg:flex-nowrap"
      @mouseenter="closeMenuWithDelay">

      <!-- Mobile Menu Button & Logo -->
      <div class="flex items-center gap-2">
        <button type="button" class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 lg:hidden"
          @click="$emit('toggleMobileMenu')">
          <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
        <Link href="/" aria-label="Kembali ke halaman utama"
          class="hidden h-10 w-24 items-center justify-center rounded-md bg-sky-50 p-2 text-xs font-bold text-sky-600 sm:flex">
        TP-PKK Marketplace
        </Link>
        <Link href="/" aria-label="Kembali ke halaman utama"
          class="flex h-10 w-10 items-center justify-center rounded-md bg-sky-50 text-xs font-bold text-sky-600 sm:hidden">
        TP
        </Link>
      </div>

      <!-- Desktop Category Button -->
      <div class="relative z-40 hidden lg:block">
        <button
          class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
          @mouseenter="openMenu" @mouseleave="closeMenuWithDelay" @focus="openMenu">
          <span class="grid grid-cols-2 gap-0.5">
            <span v-for="i in 4" :key="`dot-${i}`" class="h-1.5 w-1.5 rounded-sm bg-slate-400"></span>
          </span>
          <span>Kategori</span>
        </button>
      </div>

      <!-- Search Bar -->
      <div class="order-last w-full flex-none lg:order-none lg:w-auto lg:flex-1">
        <form class="flex items-center gap-3 rounded-lg border border-slate-300 bg-white px-4 py-2"
          @submit.prevent="submitSearch">
          <button type="submit" aria-label="Cari"
            class="cursor-pointer text-slate-400 transition hover:text-slate-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <circle cx="11" cy="11" r="7" />
              <path d="m16 16 4 4" />
            </svg>
          </button>
          <input v-model="searchQuery"
            class="w-full bg-transparent text-[15px] text-slate-700 placeholder:text-slate-500 outline-none"
            placeholder="Cari produk, jasa, atau vendor" type="text" />
          <button type="button" @click="searchQuery = ''"
            class="text-slate-400 transition hover:text-slate-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 focus-visible:ring-offset-2 focus-visible:ring-offset-white">
            <svg v-if="searchQuery" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2">
              <path d="M18 6 6 18" />
              <path d="m6 6 12 12" />
            </svg>
          </button>
        </form>
      </div>

      <!-- User Actions (Authenticated) -->
      <div class="ml-auto flex items-center gap-4" v-if="isAuthenticated">

        <!-- Notifications -->
        <div class="static lg:relative" @mouseenter="openNotifications" @mouseleave="closeNotificationsWithDelay"
          @focusin="openNotifications" @focusout="closeNotificationsWithDelay">
          <button class="rounded-full p-2 text-slate-400 hover:bg-slate-50 hover:text-slate-600 focus:outline-none"
            type="button" aria-haspopup="true" :aria-expanded="state.notificationsOpen">
            <svg width="24" height="25" viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path
                d="M12.02 21.0299C9.68999 21.0299 7.35999 20.6599 5.14999 19.9199C4.30999 19.6299 3.66999 19.0399 3.38999 18.2699C3.09999 17.4999 3.19999 16.6499 3.65999 15.8899L4.80999 13.9799C5.04999 13.5799 5.26999 12.7799 5.26999 12.3099V9.41992C5.26999 5.69992 8.29999 2.66992 12.02 2.66992C15.74 2.66992 18.77 5.69992 18.77 9.41992V12.3099C18.77 12.7699 18.99 13.5799 19.23 13.9899L20.37 15.8899C20.8 16.6099 20.88 17.4799 20.59 18.2699C20.3 19.0599 19.67 19.6599 18.88 19.9199C16.68 20.6599 14.35 21.0299 12.02 21.0299ZM12.02 4.16992C9.12999 4.16992 6.76999 6.51992 6.76999 9.41992V12.3099C6.76999 13.0399 6.46999 14.1199 6.09999 14.7499L4.94999 16.6599C4.72999 17.0299 4.66999 17.4199 4.79999 17.7499C4.91999 18.0899 5.21999 18.3499 5.62999 18.4899C9.80999 19.8899 14.24 19.8899 18.42 18.4899C18.78 18.3699 19.06 18.0999 19.19 17.7399C19.32 17.3799 19.29 16.9899 19.09 16.6599L17.94 14.7499C17.56 14.0999 17.27 13.0299 17.27 12.2999V9.41992C17.27 6.51992 14.92 4.16992 12.02 4.16992Z"
                fill="#686E76" />
              <path
                d="M13.88 4.43993C13.81 4.43993 13.74 4.42993 13.67 4.40993C13.38 4.32993 13.1 4.26993 12.83 4.22993C11.98 4.11993 11.16 4.17993 10.39 4.40993C10.11 4.49993 9.80999 4.40993 9.61999 4.19993C9.42999 3.98993 9.36999 3.68993 9.47999 3.41993C9.88999 2.36993 10.89 1.67993 12.03 1.67993C13.17 1.67993 14.17 2.35993 14.58 3.41993C14.68 3.68993 14.63 3.98993 14.44 4.19993C14.29 4.35993 14.08 4.43993 13.88 4.43993Z"
                fill="#686E76" />
              <path
                d="M12.02 23.3101C11.03 23.3101 10.07 22.9101 9.36999 22.2101C8.66999 21.5101 8.26999 20.5501 8.26999 19.5601H9.76999C9.76999 20.1501 10.01 20.7301 10.43 21.1501C10.85 21.5701 11.43 21.8101 12.02 21.8101C13.26 21.8101 14.27 20.8001 14.27 19.5601H15.77C15.77 21.6301 14.09 23.3101 12.02 23.3101Z"
                fill="#686E76" />
            </svg>
          </button>

          <div
            class="absolute inset-x-4 top-full mt-2 w-auto overflow-hidden rounded-lg border border-slate-200 bg-white shadow-2xl shadow-slate-200/70 lg:inset-x-auto lg:right-0 lg:w-[380px] lg:max-w-[90vw]"
            v-show="state.notificationsOpen">
            <div class="border-b border-slate-100 px-5 pt-5 pb-3">
              <p class="text-xl font-semibold text-slate-900">Notifikasi</p>
            </div>
            <div class="divide-y divide-slate-100">
              <div class="px-5 py-3" v-for="(notif, index) in notifications" :key="`notif-${index}`">
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                  <div class="grid h-6 w-6 place-items-center rounded-full bg-cyan-100 text-cyan-700">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <rect x="3" y="5" width="18" height="14" rx="2" />
                      <path d="M3 10h18" />
                    </svg>
                  </div>
                  <span>{{ notif.date }}</span>
                </div>
                <p class="mt-3 text-[15px] font-semibold text-slate-700">{{ notif.title }}</p>
                <p class="mt-1 text-sm text-slate-600">{{ notif.description }}</p>
              </div>
            </div>
            <div class="bg-slate-50 px-5 py-3">
              <a href="#" class="text-sm font-semibold text-cyan-700 hover:text-cyan-800">Lihat Semua Notifikasi</a>
            </div>
          </div>
        </div>

        <!-- Cart -->
        <div class="static lg:relative" @mouseenter="openCart" @mouseleave="closeCartWithDelay">
          <button class="relative rounded-full p-2 text-slate-400 hover:bg-slate-50 hover:text-slate-600">
            <svg width="24" height="25" viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path
                d="M18.19 18.25H7.53999C6.54999 18.25 5.59999 17.83 4.92999 17.1C4.25999 16.37 3.92 15.39 4 14.4L4.83 4.44C4.86 4.13 4.74999 3.83001 4.53999 3.60001C4.32999 3.37001 4.04 3.25 3.73 3.25H2C1.59 3.25 1.25 2.91 1.25 2.5C1.25 2.09 1.59 1.75 2 1.75H3.74001C4.47001 1.75 5.15999 2.06 5.64999 2.59C5.91999 2.89 6.12 3.24 6.23 3.63H18.72C19.73 3.63 20.66 4.03 21.34 4.75C22.01 5.48 22.35 6.43 22.27 7.44L21.73 14.94C21.62 16.77 20.02 18.25 18.19 18.25ZM6.28 5.12L5.5 14.52C5.45 15.1 5.64 15.65 6.03 16.08C6.42 16.51 6.95999 16.74 7.53999 16.74H18.19C19.23 16.74 20.17 15.86 20.25 14.82L20.79 7.32001C20.83 6.73001 20.64 6.17001 20.25 5.76001C19.86 5.34001 19.32 5.10999 18.73 5.10999H6.28V5.12Z"
                fill="#686E76" />
              <path
                d="M16.25 23.25C15.15 23.25 14.25 22.35 14.25 21.25C14.25 20.15 15.15 19.25 16.25 19.25C17.35 19.25 18.25 20.15 18.25 21.25C18.25 22.35 17.35 23.25 16.25 23.25ZM16.25 20.75C15.97 20.75 15.75 20.97 15.75 21.25C15.75 21.53 15.97 21.75 16.25 21.75C16.53 21.75 16.75 21.53 16.75 21.25C16.75 20.97 16.53 20.75 16.25 20.75Z"
                fill="#686E76" />
              <path
                d="M8.25 23.25C7.15 23.25 6.25 22.35 6.25 21.25C6.25 20.15 7.15 19.25 8.25 19.25C9.35 19.25 10.25 20.15 10.25 21.25C10.25 22.35 9.35 23.25 8.25 23.25ZM8.25 20.75C7.97 20.75 7.75 20.97 7.75 21.25C7.75 21.53 7.97 21.75 8.25 21.75C8.53 21.75 8.75 21.53 8.75 21.25C8.75 20.97 8.53 20.75 8.25 20.75Z"
                fill="#686E76" />
              <path
                d="M21 9.25H9C8.59 9.25 8.25 8.91 8.25 8.5C8.25 8.09 8.59 7.75 9 7.75H21C21.41 7.75 21.75 8.09 21.75 8.5C21.75 8.91 21.41 9.25 21 9.25Z"
                fill="#686E76" />
            </svg>
            <span v-if="cartCount"
              class="absolute -right-0.5 -top-0.5 grid h-4 min-w-[1rem] place-items-center rounded-full bg-red-500 px-0.5 text-[10px] font-semibold text-white">
              {{ cartCount }}
            </span>
          </button>

          <div
            class="absolute inset-x-4 top-full mt-2 w-auto rounded-lg border border-slate-200 bg-white shadow-xl shadow-slate-200/60 lg:inset-x-auto lg:right-0 lg:w-[460px] lg:max-w-[90vw]"
            v-show="state.cartOpen" @mouseenter="openCart" @mouseleave="closeCartWithDelay">
            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
              <p class="text-xl font-semibold text-slate-900">Keranjang</p>
              <Link href="/cart" class="text-sm font-semibold text-sky-600 hover:text-sky-700">Lihat Selengkapnya</Link>
            </div>
            <div v-if="cartItems.length" class="divide-y divide-slate-100">
              <div class="flex gap-3 px-4 py-3" v-for="(item, index) in cartItems" :key="`cart-${item.id ?? index}`">
                <img class="h-12 w-12 flex-shrink-0 rounded-lg bg-slate-100 object-cover" :src="cartItemImage(item)"
                  :alt="cartItemName(item)" />
                <div class="flex-1">
                  <p class="text-sm font-semibold text-slate-900">{{ cartItemName(item) }}</p>
                  <p class="text-xs text-slate-500">{{ cartItemQty(item) }} Barang</p>
                </div>
                <div class="text-sm font-semibold text-slate-900">{{ formatPrice(cartItemPrice(item)) }}</div>
              </div>
            </div>
            <div v-else class="px-4 py-3 text-sm text-slate-500">
              Keranjangmu masih kosong.
            </div>
          </div>
        </div>

        <!-- Messages / Chat Button -->
        <button class="rounded-full p-2 text-slate-400 hover:bg-slate-50 hover:text-slate-600">
          <svg width="24" height="25" viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M12 23.31C11.31 23.31 10.66 22.96 10.2 22.35L8.7 20.35C8.67 20.31 8.55 20.26 8.5 20.25H8C3.83 20.25 1.25 19.12 1.25 13.5V8.5C1.25 4.08 3.58 1.75 8 1.75H16C20.42 1.75 22.75 4.08 22.75 8.5V13.5C22.75 17.92 20.42 20.25 16 20.25H15.5C15.42 20.25 15.35 20.29 15.3 20.35L13.8 22.35C13.34 22.96 12.69 23.31 12 23.31ZM8 3.25C4.42 3.25 2.75 4.92 2.75 8.5V13.5C2.75 18.02 4.3 18.75 8 18.75H8.5C9.01 18.75 9.59 19.04 9.9 19.45L11.4 21.45C11.75 21.91 12.25 21.91 12.6 21.45L14.1 19.45C14.43 19.01 14.95 18.75 15.5 18.75H16C19.58 18.75 21.25 17.08 21.25 13.5V8.5C21.25 4.92 19.58 3.25 16 3.25H8Z"
              fill="#686E76" />
            <path
              d="M17 9.25H7C6.59 9.25 6.25 8.91 6.25 8.5C6.25 8.09 6.59 7.75 7 7.75H17C17.41 7.75 17.75 8.09 17.75 8.5C17.75 8.91 17.41 9.25 17 9.25Z"
              fill="#686E76" />
            <path
              d="M13 14.25H7C6.59 14.25 6.25 13.91 6.25 13.5C6.25 13.09 6.59 12.75 7 12.75H13C13.41 12.75 13.75 13.09 13.75 13.5C13.75 13.91 13.41 14.25 13 14.25Z"
              fill="#686E76" />
          </svg>
        </button>

        <!-- Profile Dropdown -->
        <div class="static lg:relative" @mouseenter="openProfile" @mouseleave="closeProfileWithDelay"
          @focusin="openProfile" @focusout="closeProfileWithDelay">
          <button type="button"
            class="flex items-center gap-2 rounded-lg bg-slate-50 px-3 py-1.5 text-left transition hover:bg-slate-100 focus:outline-none"
            aria-haspopup="true" :aria-expanded="state.profileOpen">
            <div class="grid h-8 w-8 place-items-center rounded-full bg-slate-300">
              <svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="8" r="3" />
                <path d="M6 20c0-3.3 2.7-6 6-6s6 2.7 6 6" />
              </svg>
            </div>
            <div class="text-left">
              <p class="text-sm font-semibold text-slate-800">{{ authUser?.name }}</p>
              <p class="text-[11px] text-slate-500">{{ authUser?.roles?.[0] ?? 'Super Admin' }}</p>
            </div>
          </button>

          <div
            class="absolute inset-x-4 top-full mt-2 w-auto overflow-hidden rounded-lg border border-slate-200 bg-white shadow-2xl shadow-slate-200/70 lg:inset-x-auto lg:right-0 lg:w-[360px] lg:max-w-[90vw]"
            v-show="state.profileOpen">
            <div class="flex items-start gap-3 px-5 pt-5 pb-4">
              <div class="grid h-12 w-12 place-items-center rounded-full bg-slate-200">
                <svg class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="8" r="4" />
                  <path d="M5 21c0-4 3-7 7-7s7 3 7 7" />
                </svg>
              </div>
              <div class="flex-1">
                <div class="flex items-start justify-between gap-3">
                  <div>
                    <p class="text-base font-semibold text-slate-900">{{ authUser?.name }}</p>
                    <p class="text-sm text-slate-500">{{ authUser?.email }}</p>
                  </div>
                  <Link href="/customer/dashboard/profile"
                    class="inline-flex items-center rounded-lg bg-cyan-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm transition hover:bg-cyan-700">
                  Lihat Profile
                  </Link>
                </div>
              </div>
            </div>

            <div class="px-5 pb-4">
              <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                <div class="grid h-12 w-12 place-items-center rounded-lg bg-cyan-100 text-cyan-700">
                  <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 8a4 4 0 0 1 4-4h6a4 4 0 0 1 4 4v9a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3Z" />
                    <path d="M4 10h16" />
                    <path d="M11 14h5" />
                  </svg>
                </div>
                <div class="flex-1">
                  <div class="flex items-center gap-1 text-sm font-semibold text-slate-700">
                    <span>Saldo Refund</span>
                    <span class="text-slate-400">?</span>
                  </div>
                  <p class="text-lg font-bold text-cyan-700">Rp0</p>
                </div>
                <button type="button"
                  class="rounded-md bg-slate-200 px-3 py-1.5 text-sm font-semibold text-slate-500 shadow-sm transition hover:bg-slate-300">
                  Tarik
                </button>
              </div>
            </div>

            <div class="divide-y divide-slate-100">
              <Link href="/customer/dashboard/payment"
                class="flex items-center gap-3 px-5 py-3 text-slate-800 transition hover:bg-slate-50">
              <svg class="h-6 w-6 text-slate-500" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path opacity="0.4"
                  d="M16.6667 5.86669V14.1334C16.6667 15.4 16.55 16.3 16.25 16.9417C16.25 16.95 16.2416 16.9667 16.2333 16.975C16.05 17.2084 15.8083 17.325 15.525 17.325C15.0833 17.325 14.55 17.0334 13.975 16.4167C13.2917 15.6834 12.2416 15.7417 11.6416 16.5417L10.8 17.6584C10.4667 18.1084 10.025 18.3334 9.58333 18.3334C9.14167 18.3334 8.69998 18.1084 8.36665 17.6584L7.51668 16.5334C6.92502 15.7417 5.88333 15.6834 5.19999 16.4084L5.19165 16.4167C4.24998 17.425 3.41668 17.575 2.93335 16.975C2.92502 16.9667 2.91667 16.95 2.91667 16.9417C2.61667 16.3 2.5 15.4 2.5 14.1334V5.86669C2.5 4.60002 2.61667 3.70002 2.91667 3.05835C2.91667 3.05002 2.91668 3.04169 2.93335 3.03335C3.40835 2.42502 4.24998 2.57502 5.19165 3.58335L5.19999 3.59169C5.88333 4.31669 6.92502 4.25835 7.51668 3.46669L8.36665 2.34169C8.69998 1.89169 9.14167 1.66669 9.58333 1.66669C10.025 1.66669 10.4667 1.89169 10.8 2.34169L11.6416 3.45835C12.2416 4.25835 13.2917 4.31669 13.975 3.58335C14.55 2.96669 15.0833 2.67502 15.525 2.67502C15.8083 2.67502 16.05 2.80002 16.2333 3.03335C16.25 3.04169 16.25 3.05002 16.25 3.05835C16.55 3.70002 16.6667 4.60002 16.6667 5.86669Z"
                  fill="currentColor" />
                <path
                  d="M13.3333 9.16669H6.66666C6.32499 9.16669 6.04166 8.88335 6.04166 8.54169C6.04166 8.20002 6.32499 7.91669 6.66666 7.91669H13.3333C13.675 7.91669 13.9583 8.20002 13.9583 8.54169C13.9583 8.88335 13.675 9.16669 13.3333 9.16669Z"
                  fill="currentColor" />
                <path
                  d="M11.6667 12.0833H6.66666C6.32499 12.0833 6.04166 11.8 6.04166 11.4583C6.04166 11.1166 6.32499 10.8333 6.66666 10.8333H11.6667C12.0083 10.8333 12.2917 11.1166 12.2917 11.4583C12.2917 11.8 12.0083 12.0833 11.6667 12.0833Z"
                  fill="currentColor" />
              </svg>
              <span class="text-sm font-semibold">Pembayaran</span>
              </Link>
              <Link href="/customer/dashboard/transactions"
                class="flex items-center gap-3 px-5 py-3 text-slate-800 transition hover:bg-slate-50">
              <svg class="h-6 w-6 text-slate-500" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M18.3334 5.00002V7.01669C18.3334 8.33335 17.5 9.16669 16.1834 9.16669H13.3334V3.34169C13.3334 2.41669 14.0917 1.66669 15.0167 1.66669C15.925 1.67502 16.7584 2.04169 17.3584 2.64169C17.9584 3.25002 18.3334 4.08335 18.3334 5.00002Z"
                  fill="currentColor" />
                <path opacity="0.4"
                  d="M1.66663 5.83335V17.5C1.66663 18.1917 2.44996 18.5834 2.99996 18.1667L4.42496 17.1C4.75829 16.85 5.22496 16.8834 5.52496 17.1834L6.90829 18.575C7.23329 18.9 7.76663 18.9 8.09163 18.575L9.49163 17.175C9.78329 16.8834 10.25 16.85 10.575 17.1L12 18.1667C12.55 18.575 13.3333 18.1834 13.3333 17.5V3.33335C13.3333 2.41669 14.0833 1.66669 15 1.66669H5.83329H4.99996C2.49996 1.66669 1.66663 3.15835 1.66663 5.00002V5.83335Z"
                  fill="currentColor" />
                <path
                  d="M10 8.125H5C4.65833 8.125 4.375 7.84167 4.375 7.5C4.375 7.15833 4.65833 6.875 5 6.875H10C10.3417 6.875 10.625 7.15833 10.625 7.5C10.625 7.84167 10.3417 8.125 10 8.125Z"
                  fill="currentColor" />
                <path
                  d="M9.375 11.4583H5.625C5.28333 11.4583 5 11.175 5 10.8333C5 10.4916 5.28333 10.2083 5.625 10.2083H9.375C9.71667 10.2083 10 10.4916 10 10.8333C10 11.175 9.71667 11.4583 9.375 11.4583Z"
                  fill="currentColor" />
              </svg>
              <span class="text-sm font-semibold">Daftar Transaksi</span>
              </Link>
              <Link href="/customer/dashboard/address"
                class="flex items-center gap-3 px-5 py-3 text-slate-800 transition hover:bg-slate-50">
              <svg class="h-6 w-6 text-slate-500" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path opacity="0.4"
                  d="M15.5666 16.4499C14.775 17.0333 13.7333 17.3333 12.4916 17.3333H5.50829C5.30829 17.3333 5.10829 17.3249 4.91663 17.2999L10.6666 11.5499L15.5666 16.4499Z"
                  fill="currentColor" />
                <path opacity="0.4"
                  d="M17.3333 5.50829V12.4916C17.3333 13.7333 17.0333 14.775 16.4499 15.5666L11.5499 10.6666L17.2999 4.91663C17.3249 5.10829 17.3333 5.30829 17.3333 5.50829Z"
                  fill="currentColor" />
                <path opacity="0.4"
                  d="M11.55 10.6666L16.45 15.5666C16.2083 15.9166 15.9166 16.2083 15.5666 16.45L10.6666 11.55L4.91663 17.3C4.3833 17.2666 3.89996 17.1583 3.4583 16.9916C1.67496 16.3416 0.666626 14.7583 0.666626 12.4916V5.50829C0.666626 2.47496 2.47496 0.666626 5.50829 0.666626H12.4916C14.7583 0.666626 16.3416 1.67496 16.9916 3.45829C17.1583 3.89996 17.2666 4.38329 17.3 4.91663L11.55 10.6666Z"
                  fill="currentColor" />
                <path
                  d="M11.5499 10.6666L16.4499 15.5666C16.2083 15.9166 15.9166 16.2083 15.5666 16.4499L10.6666 11.5499L4.91659 17.2999C4.38325 17.2666 3.89992 17.1582 3.45825 16.9916L3.78324 16.6666L16.9916 3.45825C17.1582 3.89992 17.2666 4.38325 17.2999 4.91659L11.5499 10.6666Z"
                  fill="currentColor" />
                <path
                  d="M9.19996 5.60828C8.88329 4.23328 7.66663 3.61661 6.59996 3.60828C5.5333 3.60828 4.31663 4.22494 3.99997 5.59994C3.64997 7.12494 4.58329 8.39994 5.42496 9.19994C5.75829 9.51661 6.17496 9.66661 6.59996 9.66661C7.02496 9.66661 7.44163 9.50828 7.77496 9.19994C8.61663 8.39994 9.54996 7.12494 9.19996 5.60828ZM6.62496 6.90828C6.16663 6.90828 5.79163 6.53328 5.79163 6.07494C5.79163 5.61661 6.15829 5.24161 6.62496 5.24161H6.6333C7.09164 5.24161 7.46664 5.61661 7.46664 6.07494C7.46664 6.53328 7.08329 6.90828 6.62496 6.90828Z"
                  fill="currentColor" />
              </svg>
              <span class="text-sm font-semibold">Alamat Pengiriman</span>
              </Link>
              <Link href="/logout" method="post" as="button"
                class="flex items-center gap-3 px-5 py-3 text-left text-red-600 transition hover:bg-red-50">
              <svg class="h-6 w-6 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                <path d="M16 17l5-5-5-5" />
                <path d="M21 12H9" />
              </svg>
              <span class="text-sm font-semibold">Keluar</span>
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Guest Actions -->
      <div class="flex items-center gap-3" v-else>
        <Link href="/login"
          class="rounded-lg border border-sky-500 bg-white px-5 py-2 text-sm font-semibold text-sky-600 transition hover:bg-sky-50">
        Masuk
        </Link>
        <a href="/register-as"
          class="rounded-lg bg-sky-500 px-5 py-2 text-sm font-semibold text-white transition hover:bg-sky-600">
          Daftar
        </a>
      </div>

      <!-- Mega Menu Dropdown -->
      <div class="absolute left-0 right-0 top-full pt-3" v-show="state.menuOpen" @mouseenter="openMenu"
        @mouseleave="closeMenuWithDelay">
        <div
          class="relative w-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl shadow-slate-200/60">
          <div class="relative grid w-full grid-cols-12">
            <div class="col-span-3 border-r border-slate-100 px-5 py-6">
              <div class="flex items-center justify-between text-[13px] font-semibold text-slate-600">
                <span>Kategori Produk & Jasa</span>
                <Link :href="megaMenuData[state.activeIndex]?.url || '#'" class="text-sky-600 hover:text-sky-700">
                Lihat Semua
                </Link>
              </div>
              <div class="mt-4 space-y-1.5 text-sm">
                <Link v-for="(category, index) in megaMenuData"
                  :key="category.slug ?? category.label ?? `category-${index}`" :href="category.url || '#'"
                  class="flex items-center justify-between rounded-lg px-3 py-2 transition"
                  @mouseenter="state.activeIndex = index" :class="state.activeIndex === index
                    ? 'bg-sky-50 font-semibold text-sky-700'
                    : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900'
                    ">
                <span>{{ category.label ?? category }}</span>
                <svg class="h-4 w-4 text-sky-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                  v-if="state.activeIndex === index">
                  <path d="m9 18 6-6-6-6" />
                </svg>
                </Link>
              </div>
            </div>
            <div class="col-span-9 px-6 py-6" v-if="megaMenuData[state.activeIndex]">
              <div class="flex items-center justify-between">
                <p class="text-base font-semibold text-slate-900">
                  {{ megaMenuData[state.activeIndex].label ?? megaMenuData[state.activeIndex] }}
                </p>
                <Link :href="megaMenuData[state.activeIndex].url || '#'"
                  class="text-sm font-semibold text-sky-600 hover:text-sky-700">Lihat Semua</Link>
              </div>
              <div class="mt-4 grid grid-cols-4 gap-x-8 gap-y-3 text-sm text-slate-700">
                <div v-for="(column, columnIndex) in megaMenuData[state.activeIndex].columns"
                  :key="`col-${columnIndex}`" class="space-y-2">
                  <Link v-for="(item, itemIndex) in column"
                    :key="item.slug ?? item.label ?? `item-${columnIndex}-${itemIndex}`" :href="item.url || '#'"
                    class="block transition hover:text-sky-700">
                  {{ item.label ?? item }}
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
