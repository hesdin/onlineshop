<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  isAuthenticated: Boolean,
  authUser: Object,
  megaMenuData: Array,
  cartItems: Array,
});

// Notification state (API-based)
const notifications = ref([]);
const unreadCount = ref(0);

const fetchNotifications = async () => {
  if (props.isAuthenticated !== true) return;
  // Only fetch for customers, not sellers or admins
  const roles = props.authUser?.roles ?? [];
  const hasCustomerRole = roles.includes('customer') || roles.includes('buyer');
  if (!hasCustomerRole) return;
  try {
    const response = await axios.get('/customer/notifications');
    notifications.value = response.data.notifications;
    unreadCount.value = response.data.unread_count;
  } catch (error) {
    console.error('Failed to fetch notifications:', error);
  }
};

const markAsRead = async (id) => {
  try {
    await axios.post(`/customer/notifications/${id}/read`);
    const notification = notifications.value.find(n => n.id === id);
    if (notification) {
      notification.read_at = new Date().toISOString();
    }
    unreadCount.value = Math.max(0, unreadCount.value - 1);
  } catch (error) {
    console.error('Failed to mark as read:', error);
  }
};

const markAllAsRead = async () => {
  try {
    await axios.post('/customer/notifications/read-all');
    notifications.value.forEach(n => {
      n.read_at = new Date().toISOString();
    });
    unreadCount.value = 0;
  } catch (error) {
    console.error('Failed to mark all as read:', error);
  }
};

const handleNotificationClick = (notification) => {
  if (!notification.read_at) {
    markAsRead(notification.id);
  }
  state.notificationsOpen = false;
  if (notification.action_url) {
    router.visit(notification.action_url);
  }
};

const deleteNotification = async (id, event) => {
  event.stopPropagation();
  try {
    await axios.delete(`/customer/notifications/${id}`);
    const wasUnread = notifications.value.find(n => n.id === id)?.read_at === null;
    notifications.value = notifications.value.filter(n => n.id !== id);
    if (wasUnread) {
      unreadCount.value = Math.max(0, unreadCount.value - 1);
    }
  } catch (error) {
    console.error('Failed to delete notification:', error);
  }
};

const deleteAllNotifications = async () => {
  try {
    await axios.delete('/customer/notifications');
    notifications.value = [];
    unreadCount.value = 0;
  } catch (error) {
    console.error('Failed to delete all notifications:', error);
  }
};

const getIconBgColor = (icon) => {
  switch (icon) {
    case 'shopping-bag': return 'bg-blue-100 text-blue-700';
    case 'truck': return 'bg-purple-100 text-purple-700';
    case 'credit-card': return 'bg-green-100 text-green-700';
    case 'check-circle': return 'bg-green-100 text-green-700';
    case 'x-circle': return 'bg-red-100 text-red-700';
    case 'star': return 'bg-amber-100 text-amber-700';
    default: return 'bg-cyan-100 text-cyan-700';
  }
};

// Chat state (API-based)
const conversations = ref([]);
const chatUnreadCount = ref(0);

const fetchConversations = async () => {
  if (props.isAuthenticated !== true) return;
  // Only fetch for customers, not sellers or admins
  const roles = props.authUser?.roles ?? [];
  const hasCustomerRole = roles.includes('customer') || roles.includes('buyer');
  if (!hasCustomerRole) return;
  try {
    const response = await axios.get('/customer/chats');
    conversations.value = response.data.conversations || [];
    const countResponse = await axios.get('/customer/chats/unread-count');
    chatUnreadCount.value = countResponse.data.unread_count || 0;
  } catch (error) {
    console.error('Failed to fetch conversations:', error);
  }
};

const formatChatTime = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  const now = new Date();
  const diff = now.getTime() - date.getTime();
  const days = Math.floor(diff / (1000 * 60 * 60 * 24));
  if (days === 0) {
    return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
  } else if (days === 1) {
    return 'Kemarin';
  } else {
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
  }
};

const logoUrl = '/images/logo-pkk.png';

const emit = defineEmits(['toggleMobileMenu']);

const timers = {
  menu: null,
  profile: null,
  notifications: null,
  cart: null,
  chat: null,
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
  chatOpen: false,
  activeIndex: 0,
  showLogoutConfirmation: false,
});

const handleLogout = () => {
  state.showLogoutConfirmation = true;
  state.profileOpen = false;
};

const confirmLogout = () => {
  router.post('/logout', {}, {
    preserveScroll: true,
    onSuccess: () => {
      // Stay on current page by reloading instead of redirecting to home
      window.location.reload();
    }
  });
};

const cancelLogout = () => {
  state.showLogoutConfirmation = false;
};

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

const isValidImage = (url) => {
  if (!url || typeof url !== 'string') return false;
  const trimmed = url.trim();
  if (!trimmed) return false;
  // Filter out common placeholder URLs
  const placeholderPatterns = [
    'picsum.photos',
    'placeholder',
    'via.placeholder',
    'placehold',
    'dummy',
    'no-image',
    'noimage'
  ];
  return !placeholderPatterns.some(pattern => trimmed.toLowerCase().includes(pattern));
};

const cartItemPrice = (item) => normalizePrice(item?.subtotal ?? item?.unit_price ?? item?.price ?? 0);
const cartItemQty = (item) => item?.qty ?? item?.quantity ?? 0;
const cartItemImage = (item) => {
  const img = item?.image || item?.img || null;
  return isValidImage(img) ? img : null;
};
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

const openChat = () => {
  clearTimer('chat');
  state.chatOpen = true;
  fetchConversations();
};

const closeChatWithDelay = () => {
  clearTimer('chat');
  timers.chat = window.setTimeout(() => {
    state.chatOpen = false;
  }, 120);
};

// Handle browser back button - reload page if loaded from bfcache
onMounted(() => {
  const handlePageShow = (event) => {
    // persisted = true means page was loaded from back-forward cache
    if (event.persisted) {
      window.location.reload();
    }
  };

  window.addEventListener('pageshow', handlePageShow);

  // Fetch notifications and chats if authenticated
  if (props.isAuthenticated === true) {
    fetchNotifications();
    fetchConversations();

    const userId = props.authUser?.id;
    if (userId) {
      // Subscribe to real-time notifications
      (window).Echo.private(`user.${userId}.notifications`)
        .listen('NotificationReceived', (e) => {
          notifications.value.unshift(e.notification);
          unreadCount.value++;
        })
        // Also listen for new chat messages to update chat badge
        .listen('CustomerConversationUpdated', (e) => {
          // Only update if not viewing this conversation
          const activeConvId = (window).activeConversationId;
          if (!activeConvId || activeConvId !== e.conversation?.id) {
            // Find and update the conversation in the list
            const convIndex = conversations.value.findIndex(c => c.id === e.conversation?.id);
            if (convIndex !== -1) {
              // Create a new conversation object with updated data (for Vue reactivity)
              const updatedConv = {
                ...conversations.value[convIndex],
                last_message: e.last_message,
                last_message_at: e.conversation?.last_message_at,
                unread_count: (conversations.value[convIndex].unread_count || 0) + 1
              };
              // Remove from current position and add to top
              conversations.value.splice(convIndex, 1);
              conversations.value = [updatedConv, ...conversations.value];
            }
            // Increment badge count
            chatUnreadCount.value++;
          }
        });
    }
  }
});

onBeforeUnmount(() => {
  Object.keys(timers).forEach((key) => clearTimer(key));

  // Leave Echo channels
  const userId = props.authUser?.id;
  if (userId && props.isAuthenticated === true) {
    (window).Echo.leave(`user.${userId}.notifications`);
  }
});


// Computed login URL with intended redirect
const loginUrl = computed(() => {
  if (typeof window === 'undefined') return '/customer/login';
  const currentPath = window.location.pathname + window.location.search;
  return `/customer/login?intended=${encodeURIComponent(currentPath)}`;
});

// Navigate to login with replace to avoid login page in history
const goToLogin = () => {
  router.visit(loginUrl.value, { replace: true });
};

const isSeller = computed(() => {
  const roles = props.authUser?.roles ?? [];
  return roles.includes('seller') || roles.includes('toko');
});

const isAdmin = computed(() => {
  const roles = props.authUser?.roles ?? [];
  const lowerRoles = roles.map(r => String(r).toLowerCase());
  return lowerRoles.includes('admin') || lowerRoles.includes('super_admin') || lowerRoles.includes('superadmin') || lowerRoles.includes('super admin');
});

const isCustomer = computed(() => {
  const roles = props.authUser?.roles ?? [];
  return roles.includes('customer') || roles.includes('buyer');
});
</script>

<template>
  <div class="w-full bg-white/95 backdrop-blur">
    <div class="container relative mx-auto flex flex-wrap items-center gap-4 px-0 py-2 lg:flex-nowrap"
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
          class="hidden h-10 w-auto items-center justify-center text-xs font-bold text-sky-600 sm:flex">
          <img :src="logoUrl" alt="TP-PKK Marketplace" class="h-full w-full object-contain" decoding="async"
            draggable="false" />
        </Link>
        <Link href="/" aria-label="Kembali ke halaman utama"
          class="flex h-8 w-auto items-center justify-center text-xs font-bold text-sky-600 sm:hidden">
          <img :src="logoUrl" alt="TP-PKK Marketplace" class="h-full w-full object-contain" decoding="async"
            draggable="false" />
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
        <form class="flex items-center gap-3 rounded-md border border-slate-300 bg-white px-4 py-2"
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
            placeholder="Cari di TP-PKK Marketplace" type="text" />
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
      <div v-if="isAuthenticated" class="ml-auto flex items-center gap-2 lg:gap-4">
        <div class="flex items-center gap-2">
          <!-- Notifications -->
          <div class="static lg:relative" @mouseenter="openNotifications" @mouseleave="closeNotificationsWithDelay"
            @focusin="openNotifications" @focusout="closeNotificationsWithDelay">
            <button
              class="relative rounded-full p-2 text-slate-400 hover:bg-slate-50 hover:text-slate-600 focus:outline-none"
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
              <!-- Unread badge -->
              <span v-if="unreadCount > 0"
                class="absolute -right-0.5 -top-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[9px] font-bold text-white ring-2 ring-white">
                {{ unreadCount > 9 ? '9+' : unreadCount }}
              </span>
            </button>

            <div
              class="absolute inset-x-4 top-full mt-2 w-auto overflow-hidden rounded-lg border border-slate-200 bg-white shadow-2xl shadow-slate-200/70 lg:inset-x-auto lg:right-0 lg:w-[380px] lg:max-w-[90vw]"
              v-show="state.notificationsOpen">
              <div class="flex items-center justify-between border-b border-slate-100 px-5 pt-5 pb-3">
                <p class="text-xl font-semibold text-slate-900">Notifikasi</p>
                <div class="flex items-center gap-3">
                  <button v-if="unreadCount > 0" @click="markAllAsRead"
                    class="text-xs font-medium text-cyan-600 hover:text-cyan-700">
                    Tandai dibaca
                  </button>
                  <button v-if="notifications.length > 0" @click="deleteAllNotifications"
                    class="text-xs font-medium text-red-500 hover:text-red-600">
                    Hapus semua
                  </button>
                </div>
              </div>
              <div class="max-h-80 divide-y divide-slate-100 overflow-y-auto">
                <div v-if="notifications.length === 0" class="px-5 py-8 text-center">
                  <svg class="mx-auto h-10 w-10 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.5">
                    <path d="M12 22c1.1 0 2-.9 2-2h-4a2 2 0 0 0 2 2Z" />
                    <path
                      d="M18 16v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2Z" />
                  </svg>
                  <p class="mt-2 text-sm text-slate-500">Belum ada notifikasi</p>
                </div>
                <div v-else v-for="notif in notifications" :key="notif.id" @click="handleNotificationClick(notif)"
                  class="flex cursor-pointer gap-3 px-5 py-3 transition hover:bg-slate-50"
                  :class="{ 'bg-cyan-50/50': !notif.read_at }">
                  <div
                    :class="['grid h-8 w-8 flex-shrink-0 place-items-center rounded-full', getIconBgColor(notif.icon)]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <template v-if="notif.icon === 'shopping-bag'">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" />
                        <line x1="3" y1="6" x2="21" y2="6" />
                        <path d="M16 10a4 4 0 0 1-8 0" />
                      </template>
                      <template v-else-if="notif.icon === 'truck'">
                        <rect x="1" y="3" width="15" height="13" rx="2" />
                        <path d="M16 8h4l3 3v5a2 2 0 0 1-2 2h-1" />
                        <circle cx="5.5" cy="18.5" r="2.5" />
                        <circle cx="18.5" cy="18.5" r="2.5" />
                      </template>
                      <template v-else-if="notif.icon === 'credit-card'">
                        <rect x="1" y="4" width="22" height="16" rx="2" />
                        <line x1="1" y1="10" x2="23" y2="10" />
                      </template>
                      <template v-else-if="notif.icon === 'check-circle'">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                        <polyline points="22 4 12 14.01 9 11.01" />
                      </template>
                      <template v-else-if="notif.icon === 'star'">
                        <polygon
                          points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                      </template>
                      <template v-else>
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                        <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                      </template>
                    </svg>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-700">{{ notif.title }}</p>
                    <p class="mt-0.5 text-xs text-slate-600 line-clamp-2">{{ notif.message }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ notif.created_at }}</p>
                  </div>
                  <div class="flex-shrink-0 flex items-center gap-2">
                    <button @click="deleteNotification(notif.id, $event)"
                      class="p-1 rounded hover:bg-red-100 text-slate-400 hover:text-red-500 transition-colors"
                      title="Hapus">
                      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 6h18" />
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                        <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                      </svg>
                    </button>
                    <span v-if="!notif.read_at" class="block h-2 w-2 rounded-full bg-cyan-500"></span>
                  </div>
                </div>
              </div>
              <div class="border-t border-slate-100 bg-slate-50 px-5 py-3">
                <Link href="/customer/dashboard/transactions"
                  class="text-sm font-semibold text-cyan-700 hover:text-cyan-800">Lihat
                  Semua Pesanan</Link>
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
                <Link href="/cart" class="text-sm font-semibold text-sky-600 hover:text-sky-700">Lihat Selengkapnya
                </Link>
              </div>
              <div v-if="cartItems.length" class="divide-y divide-slate-100">
                <div class="flex gap-3 px-4 py-3" v-for="(item, index) in cartItems" :key="`cart-${item.id ?? index}`">
                  <div class="h-12 w-12 flex-shrink-0 rounded-lg overflow-hidden"
                    :class="cartItemImage(item) ? 'bg-slate-100' : ''">
                    <img v-if="cartItemImage(item)" class="h-full w-full object-cover" :src="cartItemImage(item)"
                      :alt="cartItemName(item)" />
                    <div v-else
                      class="flex h-full w-full items-center justify-center text-[10px] font-medium text-slate-400">
                      No Image
                    </div>
                  </div>
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
          <div v-if="isAuthenticated" class="static lg:relative" @mouseenter="openChat"
            @mouseleave="closeChatWithDelay">
            <button class="relative rounded-full p-2 text-slate-400 hover:bg-slate-50 hover:text-slate-600">
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
              <span v-if="chatUnreadCount > 0"
                class="absolute -right-0.5 -top-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[9px] font-bold text-white ring-2 ring-white">
                {{ chatUnreadCount > 9 ? '9+' : chatUnreadCount }}
              </span>
            </button>

            <!-- Chat Dropdown -->
            <div v-show="state.chatOpen"
              class="absolute inset-x-4 top-full mt-2 w-auto overflow-hidden rounded-lg border border-slate-200 bg-white shadow-2xl shadow-slate-200/70 lg:inset-x-auto lg:right-0 lg:w-[340px] lg:max-w-[90vw]">
              <div class="flex items-center justify-between border-b border-slate-100 px-5 pt-5 pb-3">
                <p class="text-xl font-semibold text-slate-900">Pesan</p>
              </div>
              <div class="max-h-80 divide-y divide-slate-100 overflow-y-auto">
                <div v-if="conversations.length === 0" class="px-5 py-8 text-center">
                  <svg class="mx-auto h-10 w-10 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.5">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                  </svg>
                  <p class="mt-2 text-sm text-slate-500">Belum ada percakapan</p>
                </div>
                <div v-else v-for="conv in conversations" :key="`${conv.id}-${conv.last_message?.id || 0}`"
                  class="flex items-center gap-3 px-5 py-3 cursor-pointer transition hover:bg-slate-50"
                  :class="{ 'bg-blue-50/50': conv.unread_count > 0 }"
                  @click="state.chatOpen = false; router.visit(`/customer/dashboard/chat/${conv.id}`)">
                  <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-slate-100">
                    <img v-if="conv.store?.logo" :src="conv.store.logo" class="h-10 w-10 rounded-full object-cover" />
                    <svg v-else class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                      stroke-width="2">
                      <rect x="3" y="3" width="18" height="18" rx="2" />
                      <path d="m9 9 6 6" />
                      <path d="m15 9-6 6" />
                    </svg>
                  </div>
                  <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between">
                      <p class="font-semibold text-slate-900 truncate" :class="{ 'font-bold': conv.unread_count > 0 }">
                        {{ conv.store?.name || 'Toko' }}
                      </p>
                      <span class="text-xs text-slate-400">{{ formatChatTime(conv.last_message_at) }}</span>
                    </div>
                    <p class="text-sm text-slate-500 truncate"
                      :class="{ 'font-medium text-slate-700': conv.unread_count > 0 }">
                      {{ conv.last_message?.content || 'Belum ada pesan' }}
                    </p>
                  </div>
                  <span v-if="conv.unread_count > 0" class="flex-shrink-0 h-2 w-2 rounded-full bg-sky-500"></span>
                </div>
              </div>
            </div>
          </div>
          <button v-else class="rounded-full p-2 text-slate-400 hover:bg-slate-50 hover:text-slate-600"
            @click="router.visit('/customer/login')">
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
        </div>

        <!-- Profile Dropdown -->
        <div class="static flex-shrink-0 lg:relative" @mouseenter="openProfile" @mouseleave="closeProfileWithDelay"
          @focusin="openProfile" @focusout="closeProfileWithDelay">
          <button type="button"
            class="flex items-center rounded-full p-1.5 transition hover:bg-slate-50 focus:outline-none sm:max-w-full sm:gap-2 sm:rounded-lg sm:bg-slate-50 sm:px-3 sm:py-1.5 sm:text-left sm:hover:bg-slate-100"
            aria-haspopup="true" :aria-expanded="state.profileOpen">
            <div class="h-9 w-9 flex-shrink-0 overflow-hidden rounded-full bg-slate-300">
              <img v-if="authUser?.avatar_url" :src="authUser.avatar_url" alt="Profile"
                class="h-full w-full object-cover" />
              <div v-else class="grid h-full w-full place-items-center">
                <svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="8" r="3" />
                  <path d="M6 20c0-3.3 2.7-6 6-6s6 2.7 6 6" />
                </svg>
              </div>
            </div>
            <div class="hidden min-w-0 text-left sm:block">
              <p class="max-w-[10rem] truncate text-sm font-semibold text-slate-800 sm:max-w-[14rem]">
                {{ authUser?.name }}
              </p>
              <p class="text-[11px] text-slate-500">
                {{ authUser?.roles?.[0] ?? 'Super Admin' }}
              </p>
            </div>
          </button>

          <div
            class="absolute inset-x-4 top-full mt-2 w-auto overflow-hidden rounded-lg border border-slate-200 bg-white shadow-2xl shadow-slate-200/70 lg:inset-x-auto lg:right-0 lg:w-[360px] lg:max-w-[90vw]"
            v-show="state.profileOpen">
            <div class="flex items-start gap-3 px-5 pt-5 pb-4">
              <div class="h-10 w-10 flex-shrink-0 rounded-full bg-slate-200 overflow-hidden">
                <img v-if="authUser?.avatar_url" :src="authUser.avatar_url" alt="Profile"
                  class="h-full w-full object-cover">
                <div v-else class="grid h-full w-full place-items-center">
                  <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <circle cx="12" cy="8" r="4" />
                    <path d="M5 21c0-4 3-7 7-7s7 3 7 7" />
                  </svg>
                </div>
              </div>
              <div class="flex-1">
                <div class="flex items-center justify-between gap-3">
                  <div>
                    <p class="text-base font-semibold text-slate-900">{{ authUser?.name }}</p>
                  </div>
                  <Link v-if="!isAdmin" href="/customer/dashboard/profile"
                    class="inline-flex items-center rounded-sm bg-cyan-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-cyan-700">
                    Lihat Profile
                  </Link>
                </div>
              </div>
            </div>



            <!-- Admin Menu -->
            <div v-if="isAdmin" class="divide-y divide-slate-100">
              <Link href="/admin/dashboard"
                class="flex items-center gap-3 px-5 py-3 text-slate-800 transition hover:bg-slate-50">
                <svg class="h-6 w-6 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2">
                  <rect x="3" y="3" width="7" height="7" />
                  <rect x="14" y="3" width="7" height="7" />
                  <rect x="14" y="14" width="7" height="7" />
                  <rect x="3" y="14" width="7" height="7" />
                </svg>
                <span class="text-sm font-semibold">Dashboard Admin</span>
              </Link>
              <button type="button" @click="handleLogout"
                class="flex w-full items-center gap-3 px-5 py-3 text-left text-red-600 transition hover:bg-red-50">
                <svg class="h-6 w-6 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2">
                  <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                  <path d="M16 17l5-5-5-5" />
                  <path d="M21 12H9" />
                </svg>
                <span class="text-sm font-semibold">Keluar</span>
              </button>
            </div>

            <!-- Seller Menu -->
            <div v-else-if="isSeller" class="divide-y divide-slate-100">
              <Link href="/seller/dashboard"
                class="flex items-center gap-3 px-5 py-3 text-slate-800 transition hover:bg-slate-50">
                <svg class="h-6 w-6 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2">
                  <rect x="3" y="3" width="7" height="7" />
                  <rect x="14" y="3" width="7" height="7" />
                  <rect x="14" y="14" width="7" height="7" />
                  <rect x="3" y="14" width="7" height="7" />
                </svg>
                <span class="text-sm font-semibold">Dashboard Toko</span>
              </Link>
              <Link href="/seller/products"
                class="flex items-center gap-3 px-5 py-3 text-slate-800 transition hover:bg-slate-50">
                <svg class="h-6 w-6 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2">
                  <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z" />
                  <line x1="7" y1="7" x2="7.01" y2="7" />
                </svg>
                <span class="text-sm font-semibold">Produk Saya</span>
              </Link>
              <Link href="/seller/orders"
                class="flex items-center gap-3 px-5 py-3 text-slate-800 transition hover:bg-slate-50">
                <svg class="h-6 w-6 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                  <polyline points="14 2 14 8 20 8" />
                  <line x1="16" y1="13" x2="8" y2="13" />
                  <line x1="16" y1="17" x2="8" y2="17" />
                  <polyline points="10 9 9 9 8 9" />
                </svg>
                <span class="text-sm font-semibold">Pesanan Masuk</span>
              </Link>
              <Link href="/seller/settings"
                class="flex items-center gap-3 px-5 py-3 text-slate-800 transition hover:bg-slate-50">
                <svg class="h-6 w-6 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2">
                  <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                  <polyline points="9 22 9 12 15 12 15 22" />
                </svg>
                <span class="text-sm font-semibold">Pengaturan Toko</span>
              </Link>
              <button type="button" @click="handleLogout"
                class="flex w-full items-center gap-3 px-5 py-3 text-left text-red-600 transition hover:bg-red-50">
                <svg class="h-6 w-6 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2">
                  <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                  <path d="M16 17l5-5-5-5" />
                  <path d="M21 12H9" />
                </svg>
                <span class="text-sm font-semibold">Keluar</span>
              </button>
            </div>

            <!-- Customer Menu (Default) -->
            <div v-else class="divide-y divide-slate-100">
              <Link href="/customer/dashboard/reorder"
                class="flex items-center gap-3 px-5 py-3 text-slate-800 transition hover:bg-slate-50">
                <svg class="h-6 w-6 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2">
                  <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8" />
                  <path d="M21 3v5h-5" />
                  <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16" />
                  <path d="M3 21v-5h5" />
                </svg>
                <span class="text-sm font-semibold">Beli Lagi</span>
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
              <button type="button" @click="handleLogout"
                class="flex w-full items-center gap-3 px-5 py-3 text-left text-red-600 transition hover:bg-red-50">
                <svg class="h-6 w-6 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2">
                  <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                  <path d="M16 17l5-5-5-5" />
                  <path d="M21 12H9" />
                </svg>
                <span class="text-sm font-semibold">Keluar</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Guest Actions -->
      <div class="flex items-center gap-3" v-else>
        <button type="button" @click="goToLogin"
          class="rounded-md border border-sky-500 bg-white px-5 py-2 text-sm font-semibold text-sky-600 transition hover:bg-sky-50">
          Masuk
        </button>
        <a href="/register-as"
          class="rounded-md bg-sky-500 px-5 py-2 text-sm font-semibold text-white transition hover:bg-sky-600">
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
                <Link href="/c" class="text-sky-600 hover:text-sky-700">
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
                  <svg class="h-4 w-4 text-sky-600" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" v-if="state.activeIndex === index">
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
                <Link :href="megaMenuData[state.activeIndex]?.url || '/c'"
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

    <!-- Logout Confirmation Modal -->
    <div v-if="state.showLogoutConfirmation"
      class="fixed inset-0 z-[9999] flex min-h-screen items-center justify-center bg-black/50 px-4"
      @click.self="cancelLogout">
      <div class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-2xl">
        <div class="mb-4 flex items-center gap-3">
          <div class="grid h-12 w-12 place-items-center rounded-full bg-red-100">
            <svg class="h-6 w-6 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
              <path d="M16 17l5-5-5-5" />
              <path d="M21 12H9" />
            </svg>
          </div>
          <div>
            <h3 class="text-xl font-bold text-slate-900">Konfirmasi Keluar</h3>
            <p class="text-sm text-slate-600">Apakah Anda yakin ingin keluar?</p>
          </div>
        </div>

        <p class="mb-6 text-sm text-slate-600">
          Anda akan diarahkan ke halaman utama dan perlu login kembali untuk mengakses akun Anda.
        </p>

        <div class="flex gap-3">
          <button type="button" @click="cancelLogout"
            class="flex-1 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
            Batal
          </button>
          <button type="button" @click="confirmLogout"
            class="flex-1 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700">
            Ya, Keluar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
