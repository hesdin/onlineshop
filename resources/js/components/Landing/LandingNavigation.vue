<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed, reactive, watch } from 'vue';
import LandingTopBar from '@/components/Landing/LandingTopBar.vue';
import LandingHeader from '@/components/Landing/LandingHeader.vue';
import LandingMobileMenu from '@/components/Landing/LandingMobileMenu.vue';
import { syncCartStore, useCartStore } from '@/Stores/cartStore';

const page = usePage();

const defaultNotifications = [
  {
    date: '25 November 2025',
    title: 'Pesananmu Menunggu Pembayaran',
    description: 'Pesananmu berhasil dibuat, silahkan menyelesaikan pembayaran.',
  },
];

const authUser = computed(() => page.props.auth?.user ?? null);
const isAuthenticated = computed(() => Boolean(authUser.value));
const megaMenuData = computed(() => page.props.megaMenu ?? []);
const notifications = computed(() => page.props.notifications ?? defaultNotifications);
const cartStore = useCartStore();
const cartItems = cartStore.items;

const syncCart = () => {
  if (page.props.cart === undefined) {
    return;
  }
  syncCartStore(page.props.cart ?? null);
};

watch(
  () => page.props.cart?.updated_at ?? null,
  () => syncCart(),
  { immediate: true }
);

const state = reactive({
  mobileMenuOpen: false,
});

const toggleMobileMenu = () => {
  state.mobileMenuOpen = !state.mobileMenuOpen;
};

const closeMobileMenu = () => {
  state.mobileMenuOpen = false;
};
</script>

<template>
  <header class="fixed inset-x-0 top-0 isolate z-50 bg-white/95 shadow-sm backdrop-blur">
    <LandingTopBar />
    <LandingHeader :is-authenticated="isAuthenticated" :auth-user="authUser" :mega-menu-data="megaMenuData"
      :cart-items="cartItems" :notifications="notifications" @toggle-mobile-menu="toggleMobileMenu" />
  </header>

  <LandingMobileMenu :is-open="state.mobileMenuOpen" :mega-menu-data="megaMenuData" @close="closeMobileMenu" />
</template>
