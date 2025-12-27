<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import SellerDashboardLayout from '@/Layouts/SellerDashboardLayout.vue';
import { MessageCircle, User, Clock } from 'lucide-vue-next';

interface Customer {
  id: number;
  name: string;
  avatar: string | null;
}

interface Product {
  id: number;
  name: string;
}

interface LastMessage {
  id: number;
  content: string;
  sender_type: string;
  created_at: string;
}

interface Conversation {
  id: number;
  customer: Customer;
  product: Product | null;
  last_message: LastMessage | null;
  last_message_at: string;
  unread_count: number;
  customer_is_online: boolean;
}

interface PaginatedConversations {
  data: Conversation[];
  current_page: number;
  last_page: number;
  total: number;
}

const props = defineProps<{
  conversations: PaginatedConversations;
}>();

// Polling interval for auto-refresh (30 seconds)
let pollingInterval: ReturnType<typeof setInterval> | null = null;

onMounted(() => {
  // Start polling for online status updates
  pollingInterval = setInterval(() => {
    router.reload({ only: ['conversations'] });
  }, 30000); // 30 seconds
});

onUnmounted(() => {
  if (pollingInterval) {
    clearInterval(pollingInterval);
  }
});

const formatTime = (dateString: string) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  const now = new Date();
  const diff = now.getTime() - date.getTime();
  const days = Math.floor(diff / (1000 * 60 * 60 * 24));

  if (days === 0) {
    return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
  } else if (days === 1) {
    return 'Kemarin';
  } else if (days < 7) {
    return date.toLocaleDateString('id-ID', { weekday: 'short' });
  } else {
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
  }
};

const truncateText = (text: string, maxLength: number = 50) => {
  if (!text) return '';
  return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
};

const getAvatarUrl = (customer: Customer) => {
  if (customer.avatar) {
    return customer.avatar.startsWith('http') ? customer.avatar : `/storage/${customer.avatar}`;
  }
  return null;
};

const getMessagePrefix = (senderType: string | undefined) => {
  if (senderType === 'seller') return 'Anda: ';
  if (senderType === 'customer') return 'Customer: ';
  return '';
};
</script>

<template>
  <SellerDashboardLayout>

    <Head title="Chat" />

    <div class="mx-auto max-w-4xl">
      <!-- Header -->
      <div class="mb-6 flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-slate-900">Pesan</h1>
          <p class="text-sm text-slate-500">Kelola percakapan dengan pelanggan</p>
        </div>
      </div>

      <!-- Conversation List -->
      <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div v-if="conversations.data.length === 0" class="py-16 text-center">
          <MessageCircle class="mx-auto h-12 w-12 text-slate-300" />
          <p class="mt-4 text-slate-500">Belum ada percakapan</p>
          <p class="mt-1 text-sm text-slate-400">Pesan dari pelanggan akan muncul di sini</p>
        </div>

        <div v-else class="divide-y divide-slate-100 overflow-hidden">
          <Link v-for="conv in conversations.data" :key="conv.id" :href="`/seller/chats/${conv.id}`"
            class="flex items-center gap-4 px-5 py-4 transition hover:bg-slate-50 first:rounded-t-xl last:rounded-b-xl"
            :class="{ 'bg-blue-50/50': conv.unread_count > 0 }">

            <!-- Avatar with Online Status -->
            <div class="relative flex-shrink-0">
              <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">
                <img v-if="getAvatarUrl(conv.customer)" :src="getAvatarUrl(conv.customer)" :alt="conv.customer.name"
                  class="h-12 w-12 rounded-full object-cover" />
                <User v-else class="h-6 w-6 text-slate-400" />
              </div>
              <!-- Online Status Indicator -->
              <span class="absolute bottom-0 right-0 z-10 block h-3 w-3 rounded-full ring-2 ring-white"
                :class="conv.customer_is_online ? 'bg-green-500' : 'bg-gray-300'"></span>
              <!-- Unread Badge -->
              <span v-if="conv.unread_count > 0"
                class="absolute -right-1 -top-1 z-20 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">
                {{ conv.unread_count > 9 ? '9+' : conv.unread_count }}
              </span>
            </div>

            <!-- Content -->
            <div class="min-w-0 flex-1">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <h3 class="font-semibold text-slate-900" :class="{ 'font-bold': conv.unread_count > 0 }">
                    {{ conv.customer.name }}
                  </h3>
                  <span v-if="conv.customer_is_online" class="text-xs text-green-600 font-medium">Online</span>
                </div>
                <span class="text-xs text-slate-400">
                  {{ formatTime(conv.last_message_at) }}
                </span>
              </div>
              <p v-if="conv.product" class="text-xs text-slate-500">
                Produk: {{ conv.product.name }}
              </p>
              <p class="mt-0.5 text-sm text-slate-600 truncate"
                :class="{ 'font-medium text-slate-800': conv.unread_count > 0 }">
                <span class="text-slate-400">{{ getMessagePrefix(conv.last_message?.sender_type) }}</span>
                {{ truncateText(conv.last_message?.content || 'Belum ada pesan') }}
              </p>
            </div>
          </Link>
        </div>

        <!-- Pagination -->
        <div v-if="conversations.last_page > 1" class="border-t border-slate-100 px-5 py-3 text-center">
          <span class="text-sm text-slate-500">
            Halaman {{ conversations.current_page }} dari {{ conversations.last_page }}
          </span>
        </div>
      </div>
    </div>
  </SellerDashboardLayout>
</template>
