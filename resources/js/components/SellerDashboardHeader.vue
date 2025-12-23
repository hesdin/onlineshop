<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
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
import { Badge } from '@/components/ui/badge';
import { Bell, LogOut, User, Settings, Search, ShoppingBag, CheckCircle, AlertTriangle, Package, Check, Trash2, MessageCircle } from 'lucide-vue-next';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const page = usePage();

const user = computed(() => (page.props.auth as any)?.user);

const userInitials = computed(() => {
  if (!user.value?.name) return 'U';
  const names = user.value.name.split(' ');
  if (names.length >= 2) {
    return (names[0][0] + names[1][0]).toUpperCase();
  }
  return user.value.name.substring(0, 2).toUpperCase();
});

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
const isLoadingNotifications = ref(false);
let pollingInterval: ReturnType<typeof setInterval> | null = null;

const fetchNotifications = async () => {
  try {
    isLoadingNotifications.value = true;
    const response = await axios.get('/seller/notifications');
    notifications.value = response.data.notifications;
    unreadCount.value = response.data.unread_count;
  } catch (error) {
    console.error('Failed to fetch notifications:', error);
  } finally {
    isLoadingNotifications.value = false;
  }
};

const markAsRead = async (id: string) => {
  try {
    await axios.post(`/seller/notifications/${id}/read`);
    const notification = notifications.value.find(n => n.id === id);
    if (notification) {
      notification.read_at = new Date().toISOString();
    }
    unreadCount.value = Math.max(0, unreadCount.value - 1);
  } catch (error) {
    console.error('Failed to mark notification as read:', error);
  }
};

const markAllAsRead = async () => {
  try {
    await axios.post('/seller/notifications/read-all');
    notifications.value.forEach(n => {
      n.read_at = new Date().toISOString();
    });
    unreadCount.value = 0;
  } catch (error) {
    console.error('Failed to mark all notifications as read:', error);
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

const deleteNotification = async (id: string, event: Event) => {
  event.stopPropagation();
  try {
    await axios.delete(`/seller/notifications/${id}`);
    notifications.value = notifications.value.filter(n => n.id !== id);
    // Update unread count if the deleted notification was unread
    const wasUnread = notifications.value.find(n => n.id === id && !n.read_at);
    if (wasUnread) {
      unreadCount.value = Math.max(0, unreadCount.value - 1);
    }
  } catch (error) {
    console.error('Failed to delete notification:', error);
  }
};

const deleteAllNotifications = async () => {
  showDeleteAllModal.value = true;
};

const confirmDeleteAll = async () => {
  try {
    await axios.delete('/seller/notifications');
    notifications.value = [];
    unreadCount.value = 0;
    showDeleteAllModal.value = false;
  } catch (error) {
    console.error('Failed to delete all notifications:', error);
  }
};

const cancelDeleteAll = () => {
  showDeleteAllModal.value = false;
};

const getNotificationIcon = (icon: string) => {
  switch (icon) {
    case 'shopping-bag':
      return ShoppingBag;
    case 'check-circle':
      return CheckCircle;
    case 'alert-triangle':
      return AlertTriangle;
    case 'package':
      return Package;
    default:
      return Bell;
  }
};

const getIconColor = (icon: string) => {
  switch (icon) {
    case 'shopping-bag':
      return 'text-blue-600 bg-blue-100';
    case 'check-circle':
      return 'text-green-600 bg-green-100';
    case 'alert-triangle':
      return 'text-amber-600 bg-amber-100';
    default:
      return 'text-slate-600 bg-slate-100';
  }
};

// Start polling on mount
onMounted(() => {
  fetchNotifications();
  pollingInterval = setInterval(fetchNotifications, 30000); // Poll every 30 seconds
});

// Clean up on unmount
onUnmounted(() => {
  if (pollingInterval) {
    clearInterval(pollingInterval);
  }
});

// Logout confirmation
const showLogoutModal = ref(false);
const showDeleteAllModal = ref(false);

const handleLogout = () => {
  showLogoutModal.value = true;
};

const confirmLogout = () => {
  router.post('/logout');
};

const cancelLogout = () => {
  showLogoutModal.value = false;
};
</script>

<template>
  <header
    class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur supports-[backdrop-filter]:bg-white/80">
    <div class="flex h-16 items-center justify-between gap-4 px-6">
      <!-- Left Section - Search Bar -->
      <div class="flex items-center gap-4 flex-1 max-w-xl">
        <div class="relative flex-1">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
          <input type="text" placeholder="Cari produk, pesanan..."
            class="w-full h-9 pl-9 pr-4 text-sm border border-slate-200 rounded-lg bg-slate-50 focus:bg-white focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all" />
        </div>
      </div>

      <!-- Right Section -->
      <div class="flex items-center gap-2">
        <!-- Chat -->
        <Button variant="ghost" size="icon" class="relative hover:bg-slate-100 transition-colors" as-child>
          <Link href="/seller/chats">
            <MessageCircle class="h-5 w-5 text-slate-600" />
            <Badge v-if="(page.props as any).unread_chat_count > 0"
              class="absolute -right-1 -top-1 h-5 w-5 rounded-full p-0 flex items-center justify-center bg-emerald-500 text-[10px] font-semibold text-white border-2 border-white">
              {{ (page.props as any).unread_chat_count > 9 ? '9+' : (page.props as any).unread_chat_count }}
            </Badge>
            <span class="sr-only">Chat</span>
          </Link>
        </Button>

        <!-- Notifications -->
        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button variant="ghost" size="icon" class="relative hover:bg-slate-100 transition-colors">
              <Bell class="h-5 w-5 text-slate-600" />
              <Badge v-if="unreadCount > 0"
                class="absolute -right-1 -top-1 h-5 w-5 rounded-full p-0 flex items-center justify-center bg-red-500 text-[10px] font-semibold text-white border-2 border-white">
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
                  class="text-xs text-emerald-600 hover:text-emerald-700 font-medium flex items-center gap-1">
                  <Check class="h-3 w-3" />
                  Tandai dibaca
                </button>
                <button v-if="notifications.length > 0" @click.stop="deleteAllNotifications"
                  class="text-xs text-red-500 hover:text-red-600 font-medium flex items-center gap-1">
                  <Trash2 class="h-3 w-3" />
                  Hapus semua
                </button>
              </div>
            </div>
            <DropdownMenuSeparator />
            <div class="max-h-80 overflow-y-auto">
              <div v-if="notifications.length === 0" class="py-8 px-2 text-center text-sm text-slate-500">
                <Bell class="h-8 w-8 mx-auto mb-2 text-slate-300" />
                <p>Belum ada notifikasi</p>
              </div>
              <div v-else>
                <div v-for="notification in notifications" :key="notification.id"
                  @click="handleNotificationClick(notification)"
                  class="flex gap-3 px-3 py-3 hover:bg-slate-50 cursor-pointer transition-colors border-b border-slate-100 last:border-0"
                  :class="{ 'bg-emerald-50/50': !notification.read_at }">
                  <div class="flex-shrink-0">
                    <div class="h-9 w-9 rounded-full flex items-center justify-center"
                      :class="getIconColor(notification.icon)">
                      <component :is="getNotificationIcon(notification.icon)" class="h-4 w-4" />
                    </div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-slate-900 truncate">
                      {{ notification.title }}
                    </p>
                    <p class="text-xs text-slate-500 line-clamp-2">
                      {{ notification.message }}
                    </p>
                    <p class="text-[10px] text-slate-400 mt-1">
                      {{ notification.created_at }}
                    </p>
                  </div>
                  <div class="flex-shrink-0 flex items-center gap-2">
                    <button @click="deleteNotification(notification.id, $event)"
                      class="p-1 rounded hover:bg-red-100 text-slate-400 hover:text-red-500 transition-colors"
                      title="Hapus notifikasi">
                      <Trash2 class="h-3.5 w-3.5" />
                    </button>
                    <div v-if="!notification.read_at" class="h-2 w-2 rounded-full bg-emerald-500"></div>
                  </div>
                </div>
              </div>
            </div>
          </DropdownMenuContent>
        </DropdownMenu>

        <!-- User Profile -->
        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button variant="ghost" class="gap-2 hover:bg-slate-100 transition-colors">
              <Avatar class="h-8 w-8 border border-slate-200">
                <AvatarImage :src="user?.avatar_url" :alt="user?.name" />
                <AvatarFallback
                  class="bg-gradient-to-br from-emerald-500 to-emerald-600 text-white text-xs font-semibold">
                  {{ userInitials }}
                </AvatarFallback>
              </Avatar>
              <span class="hidden sm:inline-block text-sm font-medium text-slate-700">
                {{ user?.name || 'User' }}
              </span>
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end" class="w-56">
            <DropdownMenuLabel>
              <div class="flex flex-col space-y-1">
                <p class="text-sm font-medium">{{ user?.name }}</p>
                <p class="text-xs text-slate-500">{{ user?.email }}</p>
              </div>
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuItem as-child>
              <Link href="/seller/profile" class="cursor-pointer">
                <User class="mr-2 h-4 w-4" />
                <span>Profil Saya</span>
              </Link>
            </DropdownMenuItem>
            <DropdownMenuItem as-child>
              <Link href="/seller/settings" class="cursor-pointer">
                <Settings class="mr-2 h-4 w-4" />
                <span>Pengaturan Toko</span>
              </Link>
            </DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuItem @click="handleLogout" class="cursor-pointer text-red-600">
              <LogOut class="mr-2 h-4 w-4" />
              <span>Keluar</span>
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
      </div>
    </div>
  </header>

  <!-- Logout Confirmation Modal -->
  <div v-if="showLogoutModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
    @click.self="cancelLogout">
    <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-2xl">
      <div class="mb-4 flex items-center gap-3">
        <div class="grid h-12 w-12 place-items-center rounded-full bg-red-100">
          <LogOut class="h-6 w-6 text-red-600" />
        </div>
        <div>
          <h3 class="text-lg font-bold text-slate-900">Konfirmasi Keluar</h3>
          <p class="text-sm text-slate-500">Apakah Anda yakin ingin keluar?</p>
        </div>
      </div>

      <p class="mb-6 text-sm text-slate-600">
        Anda akan keluar dari akun dan perlu login kembali untuk mengakses dashboard.
      </p>

      <div class="flex gap-3">
        <Button variant="outline" class="flex-1" @click="cancelLogout">
          Batal
        </Button>
        <Button variant="destructive" class="flex-1" @click="confirmLogout">
          Ya, Keluar
        </Button>
      </div>
    </div>
  </div>

  <!-- Delete All Notifications Confirmation Modal -->
  <div v-if="showDeleteAllModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
    @click.self="cancelDeleteAll">
    <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-2xl">
      <div class="mb-4 flex items-center gap-3">
        <div class="grid h-12 w-12 place-items-center rounded-full bg-red-100">
          <Trash2 class="h-6 w-6 text-red-600" />
        </div>
        <div>
          <h3 class="text-lg font-bold text-slate-900">Hapus Semua Notifikasi</h3>
          <p class="text-sm text-slate-500">Tindakan ini tidak dapat dibatalkan</p>
        </div>
      </div>

      <p class="mb-6 text-sm text-slate-600">
        Semua notifikasi akan dihapus secara permanen dari daftar Anda.
      </p>

      <div class="flex gap-3">
        <Button variant="outline" class="flex-1" @click="cancelDeleteAll">
          Batal
        </Button>
        <Button variant="destructive" class="flex-1" @click="confirmDeleteAll">
          Ya, Hapus Semua
        </Button>
      </div>
    </div>
  </div>
</template>
