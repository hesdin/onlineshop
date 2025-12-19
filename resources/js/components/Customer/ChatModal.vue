<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { X, Send, MessageCircle, User, Store } from 'lucide-vue-next';
import axios from 'axios';

interface Sender {
  id: number;
  name: string;
  avatar: string | null;
}

interface Message {
  id: number;
  content: string;
  sender_type: 'customer' | 'seller';
  sender: Sender;
  created_at: string;
}

interface StoreInfo {
  id: number;
  name: string;
  logo: string | null;
}

const props = defineProps<{
  storeId: number;
  storeName: string;
  storeLogo?: string | null;
  productId?: number | null;
  productName?: string | null;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
}>();

const page = usePage();
const isAuthenticated = computed(() => Boolean((page.props as any).auth?.user));

const isOpen = ref(false);
const isLoading = ref(false);
const isSending = ref(false);
const messages = ref<Message[]>([]);
const conversationId = ref<number | null>(null);
const newMessage = ref('');
const messagesContainer = ref<HTMLElement | null>(null);
let pollingInterval: ReturnType<typeof setInterval> | null = null;

const lastMessageId = computed(() => {
  if (messages.value.length === 0) return 0;
  return Math.max(...messages.value.map(m => m.id));
});

const open = async () => {
  if (!isAuthenticated.value) {
    const currentUrl = window.location.pathname + window.location.search;
    window.location.href = `/customer/login?intended=${encodeURIComponent(currentUrl)}`;
    return;
  }

  isOpen.value = true;
  isLoading.value = true;

  try {
    const response = await axios.get(`/customer/chats/store/${props.storeId}`);
    if (response.data.conversation) {
      conversationId.value = response.data.conversation.id;
      messages.value = response.data.messages || [];
    }
    scrollToBottom();
    startPolling();
  } catch (error) {
    console.error('Failed to load chat:', error);
  } finally {
    isLoading.value = false;
  }
};

const close = () => {
  isOpen.value = false;
  stopPolling();
  emit('close');
};

const scrollToBottom = () => {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
  });
};

const sendMessage = async () => {
  if (!newMessage.value.trim() || isSending.value) return;

  isSending.value = true;
  const content = newMessage.value.trim();
  newMessage.value = '';

  try {
    if (conversationId.value) {
      // Send to existing conversation
      const response = await axios.post(`/customer/chats/${conversationId.value}/messages`, {
        content,
      });
      messages.value.push(response.data.message);
    } else {
      // Start new conversation
      const response = await axios.post('/customer/chats/start', {
        store_id: props.storeId,
        product_id: props.productId || null,
        message: content,
      });
      conversationId.value = response.data.conversation_id;
      messages.value.push(response.data.message);
      startPolling();
    }
    scrollToBottom();
  } catch (error) {
    console.error('Failed to send message:', error);
    newMessage.value = content; // Restore on error
  } finally {
    isSending.value = false;
  }
};

const fetchNewMessages = async () => {
  if (!conversationId.value) return;

  try {
    const response = await axios.get(`/customer/chats/${conversationId.value}/messages`, {
      params: { after_id: lastMessageId.value },
    });

    if (response.data.messages.length > 0) {
      messages.value.push(...response.data.messages);
      scrollToBottom();
    }
  } catch (error) {
    console.error('Failed to fetch messages:', error);
  }
};

const startPolling = () => {
  if (pollingInterval) return;
  pollingInterval = setInterval(fetchNewMessages, 5000);
};

const stopPolling = () => {
  if (pollingInterval) {
    clearInterval(pollingInterval);
    pollingInterval = null;
  }
};

const formatTime = (dateString: string) => {
  const date = new Date(dateString);
  return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
};

const getAvatarUrl = (avatar: string | null) => {
  if (!avatar) return null;
  return avatar.startsWith('http') ? avatar : `/storage/${avatar}`;
};

onUnmounted(() => {
  stopPolling();
});

// Expose open method
defineExpose({ open });
</script>

<template>
  <!-- Chat Button -->
  <Button variant="outline" size="sm" class="gap-2" @click="open">
    <MessageCircle class="h-4 w-4" />
    Chat Penjual
  </Button>

  <!-- Chat Modal/Drawer -->
  <Teleport to="body">
    <Transition name="fade">
      <div v-if="isOpen" class="fixed inset-0 z-50 flex items-end justify-center sm:items-center sm:p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50" @click="close"></div>

        <!-- Modal -->
        <div
          class="relative flex h-[85vh] w-full max-w-md flex-col overflow-hidden rounded-t-2xl bg-white shadow-2xl sm:h-[500px] sm:rounded-2xl">
          <!-- Header -->
          <div class="flex items-center gap-3 border-b border-slate-200 px-4 py-3">
            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-slate-100">
              <img v-if="storeLogo" :src="storeLogo" :alt="storeName" class="h-10 w-10 rounded-full object-cover" />
              <Store v-else class="h-5 w-5 text-slate-400" />
            </div>
            <div class="min-w-0 flex-1">
              <h3 class="font-semibold text-slate-900 truncate">{{ storeName }}</h3>
              <p v-if="productName" class="text-xs text-slate-500 truncate">{{ productName }}</p>
            </div>
            <button @click="close" class="rounded-full p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
              <X class="h-5 w-5" />
            </button>
          </div>

          <!-- Messages -->
          <div ref="messagesContainer" class="flex-1 overflow-y-auto bg-slate-50 px-4 py-4">
            <!-- Loading -->
            <div v-if="isLoading" class="flex h-full items-center justify-center">
              <div class="h-8 w-8 animate-spin rounded-full border-2 border-slate-200 border-t-sky-500"></div>
            </div>

            <!-- Empty State -->
            <div v-else-if="messages.length === 0" class="flex h-full flex-col items-center justify-center text-center">
              <MessageCircle class="h-12 w-12 text-slate-300" />
              <p class="mt-3 font-medium text-slate-700">Mulai Percakapan</p>
              <p class="mt-1 text-sm text-slate-500">Tanya penjual tentang produk ini</p>
            </div>

            <!-- Messages List -->
            <div v-else class="space-y-3">
              <div v-for="msg in messages" :key="msg.id" class="flex"
                :class="msg.sender_type === 'customer' ? 'justify-end' : 'justify-start'">
                <div class="flex max-w-[80%] gap-2"
                  :class="msg.sender_type === 'customer' ? 'flex-row-reverse' : 'flex-row'">

                  <!-- Avatar (seller only) -->
                  <div v-if="msg.sender_type === 'seller'"
                    class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-slate-200">
                    <img v-if="getAvatarUrl(msg.sender?.avatar)" :src="getAvatarUrl(msg.sender?.avatar)"
                      class="h-7 w-7 rounded-full object-cover" />
                    <Store v-else class="h-4 w-4 text-slate-400" />
                  </div>

                  <!-- Bubble -->
                  <div class="rounded-2xl px-3 py-2" :class="msg.sender_type === 'customer'
                    ? 'bg-sky-500 text-white rounded-br-md'
                    : 'bg-white text-slate-800 rounded-bl-md shadow-sm'">
                    <p class="text-sm whitespace-pre-wrap">{{ msg.content }}</p>
                    <p class="mt-1 text-[10px]"
                      :class="msg.sender_type === 'customer' ? 'text-sky-100' : 'text-slate-400'">
                      {{ formatTime(msg.created_at) }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Input -->
          <div class="border-t border-slate-200 bg-white p-3">
            <form @submit.prevent="sendMessage" class="flex items-center gap-2">
              <input v-model="newMessage" type="text"
                class="flex-1 rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm placeholder:text-slate-400 focus:border-sky-400 focus:bg-white focus:outline-none"
                placeholder="Ketik pesan..." :disabled="isSending" />
              <button type="submit" :disabled="!newMessage.trim() || isSending"
                class="flex h-9 w-9 items-center justify-center rounded-full bg-sky-500 text-white transition hover:bg-sky-600 disabled:cursor-not-allowed disabled:opacity-50">
                <Send class="h-4 w-4" />
              </button>
            </form>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
