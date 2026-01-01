<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import axios from 'axios';
import { Bell, ShoppingCart, MessageCircle, LayoutGrid, LogOut, ShoppingBag, Receipt, MapPin, Tag, FileText, Home } from 'lucide-vue-next';
import ConfirmationModal from '@/components/ConfirmationModal.vue';

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
      <div class="order-last w-full flex-none px-2 lg:order-none lg:w-auto lg:flex-1 lg:px-0">
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
              <Bell class="h-6 w-6" />
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
              <ShoppingCart class="h-6 w-6" />
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
              <MessageCircle class="h-6 w-6" />
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
                <LayoutGrid class="h-5 w-5 text-slate-500" />
                <span class="text-sm font-semibold">Dashboard Admin</span>
              </Link>
              <button type="button" @click="handleLogout"
                class="flex w-full items-center gap-3 px-5 py-3 text-left text-red-600 transition hover:bg-red-50">
                <LogOut class="h-5 w-5 text-red-500" />
                <span class="text-sm font-semibold">Keluar</span>
              </button>
            </div>

            <!-- Seller Menu -->
            <div v-else-if="isSeller" class="divide-y divide-slate-100">
              <Link href="/seller/dashboard"
                class="flex items-center gap-3 px-5 py-3 text-slate-800 transition hover:bg-slate-50">
                <LayoutGrid class="h-5 w-5 text-slate-500" />
                <span class="text-sm font-semibold">Dashboard Toko</span>
              </Link>
              <Link href="/seller/products"
                class="flex items-center gap-3 px-5 py-3 text-slate-800 transition hover:bg-slate-50">
                <Tag class="h-5 w-5 text-slate-500" />
                <span class="text-sm font-semibold">Produk Saya</span>
              </Link>
              <Link href="/seller/orders"
                class="flex items-center gap-3 px-5 py-3 text-slate-800 transition hover:bg-slate-50">
                <FileText class="h-5 w-5 text-slate-500" />
                <span class="text-sm font-semibold">Pesanan Masuk</span>
              </Link>
              <Link href="/seller/settings"
                class="flex items-center gap-3 px-5 py-3 text-slate-800 transition hover:bg-slate-50">
                <Home class="h-5 w-5 text-slate-500" />
                <span class="text-sm font-semibold">Pengaturan Toko</span>
              </Link>
              <button type="button" @click="handleLogout"
                class="flex w-full items-center gap-3 px-5 py-3 text-left text-red-600 transition hover:bg-red-50">
                <LogOut class="h-5 w-5 text-red-500" />
                <span class="text-sm font-semibold">Keluar</span>
              </button>
            </div>

            <!-- Customer Menu (Default) -->
            <div v-else class="divide-y divide-slate-100">
              <Link href="/customer/dashboard/reorder"
                class="flex items-center gap-3 px-5 py-3 text-slate-800 transition hover:bg-slate-50">
                <ShoppingBag class="h-5 w-5 text-slate-500" />
                <span class="text-sm font-semibold">Beli Lagi</span>
              </Link>
              <Link href="/customer/dashboard/transactions"
                class="flex items-center gap-3 px-5 py-3 text-slate-800 transition hover:bg-slate-50">
                <Receipt class="h-5 w-5 text-slate-500" />
                <span class="text-sm font-semibold">Daftar Transaksi</span>
              </Link>
              <Link href="/customer/dashboard/address"
                class="flex items-center gap-3 px-5 py-3 text-slate-800 transition hover:bg-slate-50">
                <MapPin class="h-5 w-5 text-slate-500" />
                <span class="text-sm font-semibold">Alamat Pengiriman</span>
              </Link>
              <button type="button" @click="handleLogout"
                class="flex w-full items-center gap-3 px-5 py-3 text-left text-red-600 transition hover:bg-red-50">
                <LogOut class="h-5 w-5 text-red-500" />
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
    <ConfirmationModal :show="state.showLogoutConfirmation" title="Konfirmasi Keluar"
      message="Anda yakin ingin keluar dari akun ini?" confirm-text="Ya, Keluar" cancel-text="Batal" variant="danger"
      @confirm="confirmLogout" @cancel="cancelLogout" />
  </div>
</template>
