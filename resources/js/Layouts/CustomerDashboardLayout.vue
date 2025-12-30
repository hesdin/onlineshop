<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { SidebarInset, SidebarProvider, SidebarTrigger } from '@/components/ui/sidebar';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Bell, ShoppingBag, Truck, CreditCard, CheckCircle, XCircle, Star, Package, Check, Trash2 } from 'lucide-vue-next';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import axios from 'axios';
import CustomerDashboardSidebar from './CustomerDashboardSidebar.vue';

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

const page = usePage();
const userId = computed(() => (page.props as any).auth?.user?.id);

const fetchNotifications = async () => {
  try {
    const response = await axios.get('/customer/notifications');
    notifications.value = response.data.notifications;
    unreadCount.value = response.data.unread_count;
  } catch (error) {
    console.error('Failed to fetch notifications:', error);
  }
};

const markAsRead = async (id: string) => {
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

const deleteNotification = async (id: string, event: Event) => {
  event.stopPropagation();
  try {
    await axios.delete(`/customer/notifications/${id}`);
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
    case 'shopping-bag':
      return ShoppingBag;
    case 'truck':
      return Truck;
    case 'credit-card':
      return CreditCard;
    case 'check-circle':
      return CheckCircle;
    case 'x-circle':
      return XCircle;
    case 'star':
      return Star;
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
    case 'truck':
      return 'text-purple-600 bg-purple-100';
    case 'credit-card':
      return 'text-green-600 bg-green-100';
    case 'check-circle':
      return 'text-green-600 bg-green-100';
    case 'x-circle':
      return 'text-red-600 bg-red-100';
    case 'star':
      return 'text-amber-600 bg-amber-100';
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
        // Add new notification to the top of the list
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
  <SidebarProvider>
    <div class="flex min-h-screen bg-slate-50">
      <CustomerDashboardSidebar />

      <SidebarInset>
        <header class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-white px-6 py-4">
          <div class="flex items-center gap-3">
            <SidebarTrigger class="-ml-1" />
            <div>
              <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                Akun Saya
              </p>
              <p class="text-sm text-slate-500">Kelola pesanan, alamat, dan preferensi.</p>
            </div>
          </div>

          <div class="flex flex-wrap items-center gap-2">
            <!-- Notifications -->
            <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <Button variant="ghost" size="icon" class="relative text-slate-500 hover:text-slate-700">
                  <Bell class="h-4 w-4" />
                  <Badge v-if="unreadCount > 0"
                    class="absolute -right-1 -top-1 h-4 w-4 rounded-full p-0 flex items-center justify-center bg-red-500 text-[9px] font-semibold text-white border-2 border-white">
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
                      class="text-xs text-blue-600 hover:text-blue-700 font-medium flex items-center gap-1">
                      <Check class="h-3 w-3" />
                      Tandai dibaca
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
                      :class="{ 'bg-blue-50/50': !notification.read_at }">
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
                          class="p-1 rounded hover:bg-red-100 text-slate-400 hover:text-red-500 transition-colors">
                          <Trash2 class="h-3.5 w-3.5" />
                        </button>
                        <div v-if="!notification.read_at" class="h-2 w-2 rounded-full bg-blue-500"></div>
                      </div>
                    </div>
                  </div>
                </div>
              </DropdownMenuContent>
            </DropdownMenu>

            <Button variant="secondary" as-child>
              <Link href="/logout" method="post" as="button">
                Keluar
              </Link>
            </Button>
          </div>
        </header>

        <main class="flex-1 bg-slate-50 px-6 py-6">
          <slot />
        </main>
      </SidebarInset>
    </div>
  </SidebarProvider>
</template>
