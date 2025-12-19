<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import LandingLayout from '@/Layouts/LandingLayout.vue';
import CustomerSidebarMenu from '@/components/Customer/SidebarMenu.vue';
import { MessageCircle, Store } from 'lucide-vue-next';

interface LastMessage {
  id: number;
  content: string;
  created_at: string;
}

interface ConversationStore {
  id: number;
  name: string;
}

interface Conversation {
  id: number;
  store: ConversationStore;
  store_logo: string | null;
  product: { id: number; name: string } | null;
  last_message: LastMessage | null;
  last_message_at: string;
  unread_count: number;
}

const props = defineProps<{
  conversations: Conversation[];
}>();

defineOptions({
  layout: LandingLayout,
});

const formatTime = (dateString: string | null) => {
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

const truncateText = (text: string, maxLength: number = 50) => {
  if (!text) return '';
  return text.length > maxLength ? text.slice(0, maxLength) + '...' : text;
};
</script>

<template>
  <div class="bg-slate-50">

    <Head title="Chat" />

    <div class="mx-auto flex max-w-screen-2xl flex-col gap-6 px-6 py-10">
      <nav class="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
        <a href="/" class="text-sky-600 hover:underline">Beranda</a>
        <span>/</span>
        <span class="text-slate-900">Chat</span>
      </nav>

      <div class="grid gap-6 lg:grid-cols-[260px_minmax(0,1fr)]">
        <CustomerSidebarMenu active-key="chat" />

        <main class="space-y-6">
          <section class="rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-4">
              <h2 class="text-xl font-semibold text-slate-900">Chat</h2>
              <p class="text-sm text-slate-500">Percakapan dengan toko</p>
            </div>

            <!-- Conversation List -->
            <div v-if="conversations.length === 0" class="px-6 py-12 text-center">
              <MessageCircle class="mx-auto h-12 w-12 text-slate-300" />
              <p class="mt-3 font-medium text-slate-700">Belum ada percakapan</p>
              <p class="mt-1 text-sm text-slate-500">Mulai chat dengan toko dari halaman produk</p>
            </div>

            <div v-else class="divide-y divide-slate-100">
              <Link v-for="conv in conversations" :key="conv.id" :href="`/customer/dashboard/chat/${conv.id}`"
                class="flex items-center gap-4 px-6 py-4 transition hover:bg-slate-50"
                :class="{ 'bg-blue-50/50': conv.unread_count > 0 }">

                <!-- Store Avatar -->
                <div class="relative flex-shrink-0">
                  <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">
                    <img v-if="conv.store_logo" :src="conv.store_logo" :alt="conv.store.name"
                      class="h-12 w-12 rounded-full object-cover" />
                    <Store v-else class="h-6 w-6 text-slate-400" />
                  </div>
                  <span v-if="conv.unread_count > 0"
                    class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">
                    {{ conv.unread_count > 9 ? '9+' : conv.unread_count }}
                  </span>
                </div>

                <!-- Content -->
                <div class="min-w-0 flex-1">
                  <div class="flex items-center justify-between gap-2">
                    <p class="font-semibold text-slate-900 truncate" :class="{ 'font-bold': conv.unread_count > 0 }">
                      {{ conv.store.name }}
                    </p>
                    <span class="flex-shrink-0 text-xs text-slate-400">{{ formatTime(conv.last_message_at) }}</span>
                  </div>
                  <p v-if="conv.product" class="text-xs text-slate-500 truncate">{{ conv.product.name }}</p>
                  <p class="text-sm text-slate-500 truncate"
                    :class="{ 'font-medium text-slate-700': conv.unread_count > 0 }">
                    {{ conv.last_message?.content || 'Belum ada pesan' }}
                  </p>
                </div>
              </Link>
            </div>
          </section>
        </main>
      </div>
    </div>
  </div>
</template>
