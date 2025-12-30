<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { SidebarTrigger } from '@/components/ui/sidebar';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Bell, FileCheck, CheckCircle, XCircle, ShoppingBag, Check, Trash2, Search, Settings, LogOut } from 'lucide-vue-next';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const page = usePage();
const user = computed(() => (page.props.auth as any)?.user);

const userInitials = computed(() => {
  if (!user.value?.name) return 'A';
  const names = user.value.name.split(' ');
  if (names.length >= 2) {
    return (names[0][0] + names[1][0]).toUpperCase();
  }
  return user.value.name.substring(0, 2).toUpperCase();
});

// Dynamic page title based on current URL
const pageTitle = computed(() => {
  const url = page.url;

  // Map URLs to readable titles
  if (url === '/admin/dashboard') return 'Dashboard';
  if (url.startsWith('/admin/users')) return 'Kelola Pengguna';
  if (url.startsWith('/admin/stores')) return 'Kelola Toko';
  if (url.startsWith('/admin/seller-documents')) return 'Verifikasi Dokumen';
  if (url.startsWith('/admin/categories')) return 'Kelola Kategori';
  if (url.startsWith('/admin/products')) return 'Kelola Produk';
  if (url.startsWith('/admin/collections')) return 'Kelola Koleksi';
  if (url.startsWith('/admin/orders')) return 'Kelola Pesanan';
  if (url.startsWith('/admin/payment-methods')) return 'Metode Pembayaran';
  if (url.startsWith('/admin/promo-codes')) return 'Kode Promo';
  if (url.startsWith('/admin/banners')) return 'Kelola Banner';

  return 'Dashboard';
});

// Global Search functionality
const searchQuery = ref('');
const searchResults = ref<Record<string, { label: string; items: any[] }>>({});
const isSearching = ref(false);
const showDropdown = ref(false);
let searchTimeout: ReturnType<typeof setTimeout> | null = null;

// Debounced global search
const performSearch = async () => {
  if (searchQuery.value.length < 2) {
    searchResults.value = {};
    showDropdown.value = false;
    return;
  }

  isSearching.value = true;
  try {
    const response = await axios.get('/admin/search', {
      params: { q: searchQuery.value }
    });
    searchResults.value = response.data.results;
    showDropdown.value = Object.keys(searchResults.value).length > 0;
  } catch (error) {
    console.error('Search failed:', error);
    searchResults.value = {};
  } finally {
    isSearching.value = false;
  }
};

const handleSearchInput = () => {
  if (searchTimeout) {
    clearTimeout(searchTimeout);
  }
  searchTimeout = setTimeout(performSearch, 300);
};

const handleResultClick = (url: string) => {
  showDropdown.value = false;
  searchQuery.value = '';
  router.visit(url);
};

const closeDropdown = () => {
  showDropdown.value = false;
};

// Emit logout event
const emit = defineEmits(['logout']);

const handleLogout = () => {
  emit('logout');
};

// Notification state
type Notification = {
  id: string;
  type: string;
  title: string;
  message: string;
  icon: string;
  action_url: string | null;
  read_at: string | null;
  created_at: string;
};

const notifications = ref<Notification[]>([]);
const unreadCount = ref(0);

const userId = computed(() => user.value?.id);

const fetchNotifications = async () => {
  try {
    const response = await axios.get('/admin/notifications');
    notifications.value = response.data.notifications;
    unreadCount.value = response.data.unread_count;
  } catch (error) {
    console.error('Failed to fetch notifications:', error);
  }
};

const markAsRead = async (id: string) => {
  try {
    await axios.post(`/admin/notifications/${id}/read`);
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
    await axios.post('/admin/notifications/read-all');
    notifications.value.forEach(n => {
      n.read_at = new Date().toISOString();
    });
    unreadCount.value = 0;
  } catch (error) {
    console.error('Failed to mark all as read:', error);
  }
};

const deleteNotification = async (id: string, event: Event) => {
  event.stopPropagation();
  try {
    await axios.delete(`/admin/notifications/${id}`);
    notifications.value = notifications.value.filter(n => n.id !== id);
  } catch (error) {
    console.error('Failed to delete:', error);
  }
};

const handleNotificationClick = (notification: Notification) => {
  if (!notification.read_at) {
    markAsRead(notification.id);
  }
  if (notification.action_url) {
    router.visit(notification.action_url);
  }
};

const getNotificationIcon = (icon: string) => {
  switch (icon) {
    case 'file-check':
      return FileCheck;
    case 'check-circle':
      return CheckCircle;
    case 'x-circle':
      return XCircle;
    case 'shopping-bag':
      return ShoppingBag;
    default:
      return Bell;
  }
};

const getIconColor = (icon: string) => {
  switch (icon) {
    case 'file-check':
      return 'text-blue-600 bg-blue-100';
    case 'check-circle':
      return 'text-green-600 bg-green-100';
    case 'x-circle':
      return 'text-red-600 bg-red-100';
    default:
      return 'text-slate-600 bg-slate-100';
  }
};

// Subscribe to real-time notification updates
onMounted(() => {
  fetchNotifications();

  if (userId.value) {
    (window as any).Echo.private(`user.${userId.value}.notifications`)
      .listen('NotificationReceived', (e: any) => {
        notifications.value.unshift(e.notification);
        unreadCount.value++;
      });
  }
});

onUnmounted(() => {
  if (userId.value) {
    (window as any).Echo.leave(`user.${userId.value}.notifications`);
  }
});
</script>

<template>
  <header
    class="sticky top-0 z-40 border-b border-border bg-card/95 backdrop-blur supports-[backdrop-filter]:bg-card/80">
    <div class="flex h-14 items-center justify-between gap-4 px-6">
      <!-- Left Section -->
      <div class="flex items-center gap-4">
        <SidebarTrigger class="-ml-1" />
        <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
          {{ pageTitle }}
        </p>
      </div>

      <!-- Center Section - Global Search Bar -->
      <div class="hidden md:flex flex-1 max-w-lg mx-4 relative">
        <div class="relative w-full">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
          <input v-model="searchQuery" type="text" placeholder="Cari pengguna, toko, produk, pesanan..."
            @input="handleSearchInput"
            @focus="searchQuery.length >= 2 && (showDropdown = Object.keys(searchResults).length > 0)"
            class="w-full h-9 pl-9 pr-4 text-sm border border-border rounded-lg bg-muted focus:bg-background focus:border-primary focus:outline-none focus:ring-2 focus:ring-ring transition-all" />

          <!-- Loading indicator -->
          <div v-if="isSearching" class="absolute right-3 top-1/2 -translate-y-1/2">
            <div class="h-4 w-4 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
          </div>
        </div>

        <!-- Dropdown Results -->
        <div v-if="showDropdown"
          class="absolute top-full left-0 right-0 mt-1 bg-card border border-border rounded-lg shadow-lg max-h-96 overflow-y-auto z-50">
          <div v-for="(category, key) in searchResults" :key="key" class="border-b border-border last:border-0">
            <div class="px-3 py-2 bg-muted/50">
              <span class="text-xs font-semibold text-muted-foreground uppercase tracking-wide">{{ category.label
              }}</span>
            </div>
            <div v-for="item in category.items" :key="item.id" @click="handleResultClick(item.url)"
              class="flex items-center justify-between px-3 py-2 hover:bg-muted cursor-pointer transition-colors">
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-foreground truncate">{{ item.title }}</p>
                <p v-if="item.subtitle" class="text-xs text-muted-foreground truncate">{{ item.subtitle }}</p>
              </div>
              <Badge v-if="item.badge" variant="secondary" class="ml-2 text-[10px]">{{ item.badge }}</Badge>
            </div>
          </div>

          <!-- No results message -->
          <div v-if="Object.keys(searchResults).length === 0 && !isSearching"
            class="px-3 py-4 text-center text-sm text-muted-foreground">
            Tidak ada hasil ditemukan
          </div>
        </div>

        <!-- Click outside to close -->
        <div v-if="showDropdown" @click="closeDropdown" class="fixed inset-0 z-40"></div>
      </div>

      <!-- Right Section -->
      <div class="flex items-center gap-2">
        <!-- Notifications -->
        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button variant="ghost" size="icon" class="relative hover:bg-accent transition-colors">
              <Bell class="h-4 w-4 text-muted-foreground" />
              <Badge v-if="unreadCount > 0"
                class="absolute -right-1 -top-1 h-4 w-4 rounded-full p-0 flex items-center justify-center bg-destructive text-[9px] font-semibold text-destructive-foreground border-2 border-card">
                {{ unreadCount > 9 ? '9+' : unreadCount }}
              </Badge>
              <span class="sr-only">Notifikasi</span>
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end" class="w-96">
            <div class="flex items-center justify-between px-3 py-2">
              <span class="font-semibold text-sm">Notifikasi</span>
              <div class="flex items-center gap-2">
                <button v-if="unreadCount > 0" @click.stop="markAllAsRead"
                  class="text-xs text-primary hover:text-primary/80 font-medium flex items-center gap-1">
                  <Check class="h-3 w-3" />
                  Tandai dibaca
                </button>
              </div>
            </div>
            <DropdownMenuSeparator />
            <div class="max-h-80 overflow-y-auto">
              <div v-if="notifications.length === 0" class="py-8 px-2 text-center text-sm text-muted-foreground">
                <Bell class="h-8 w-8 mx-auto mb-2 text-muted" />
                <p>Belum ada notifikasi</p>
              </div>
              <div v-else>
                <div v-for="notification in notifications" :key="notification.id"
                  @click="handleNotificationClick(notification)"
                  class="flex gap-3 px-3 py-3 hover:bg-muted cursor-pointer transition-colors border-b border-border last:border-0"
                  :class="{ 'bg-primary/5': !notification.read_at }">
                  <div class="flex-shrink-0">
                    <div class="h-9 w-9 rounded-full flex items-center justify-center"
                      :class="getIconColor(notification.icon)">
                      <component :is="getNotificationIcon(notification.icon)" class="h-4 w-4" />
                    </div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-foreground truncate">
                      {{ notification.title }}
                    </p>
                    <p class="text-xs text-muted-foreground line-clamp-2">
                      {{ notification.message }}
                    </p>
                    <p class="text-[10px] text-muted-foreground mt-1">
                      {{ notification.created_at }}
                    </p>
                  </div>
                  <div class="flex-shrink-0 flex items-center gap-2">
                    <button @click="deleteNotification(notification.id, $event)"
                      class="p-1 rounded hover:bg-destructive/10 text-muted-foreground hover:text-destructive transition-colors">
                      <Trash2 class="h-3.5 w-3.5" />
                    </button>
                    <div v-if="!notification.read_at" class="h-2 w-2 rounded-full bg-primary"></div>
                  </div>
                </div>
              </div>
            </div>
          </DropdownMenuContent>
        </DropdownMenu>

        <!-- User Profile -->
        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button variant="ghost" class="gap-2 hover:bg-accent transition-colors">
              <Avatar class="h-8 w-8 border border-border">
                <AvatarImage v-if="user?.avatar_url" :src="user.avatar_url" :alt="user?.name" />
                <AvatarFallback
                  class="bg-gradient-to-br from-primary to-primary/80 text-primary-foreground text-xs font-semibold">
                  {{ userInitials }}
                </AvatarFallback>
              </Avatar>
              <span class="hidden sm:inline-block text-sm font-medium">
                {{ user?.name || 'Admin' }}
              </span>
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end" class="w-56">
            <DropdownMenuLabel>
              <div class="flex flex-col space-y-1">
                <p class="text-sm font-medium">{{ user?.name }}</p>
                <p class="text-xs text-muted-foreground">{{ user?.email }}</p>
              </div>
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuItem as-child>
              <a href="/" class="cursor-pointer">
                <Settings class="mr-2 h-4 w-4" />
                <span>Lihat Landing</span>
              </a>
            </DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuItem @click="handleLogout" class="cursor-pointer text-destructive">
              <LogOut class="mr-2 h-4 w-4" />
              <span>Keluar</span>
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
      </div>
    </div>
  </header>
</template>
