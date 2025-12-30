<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, nextTick, computed } from 'vue';
import SellerDashboardLayout from '@/Layouts/SellerDashboardLayout.vue';
import { ArrowLeft, Send, User, Package } from 'lucide-vue-next';
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
  read_at: string | null;
}

interface Customer {
  id: number;
  name: string;
  avatar: string | null;
}

interface Product {
  id: number;
  name: string;
  slug: string;
}

interface Conversation {
  id: number;
  customer: Customer;
  product: Product | null;
  messages: Message[];
}

const props = defineProps<{
  conversation: Conversation;
}>();

const messages = ref<Message[]>(props.conversation.messages);
const newMessage = ref('');
const isSending = ref(false);
const messagesContainer = ref<HTMLElement | null>(null);

const lastMessageId = computed(() => {
  if (messages.value.length === 0) return 0;
  return Math.max(...messages.value.map(m => m.id));
});

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
    const response = await axios.post(`/seller/chats/${props.conversation.id}/messages`, {
      content,
    });

    messages.value.push(response.data.message);
    scrollToBottom();
  } catch (error) {
    console.error('Failed to send message:', error);
    newMessage.value = content; // Restore message on error
  } finally {
    isSending.value = false;
  }
};

const formatTime = (dateString: string) => {
  const date = new Date(dateString);
  return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
};

const formatDate = (dateString: string) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long' });
};

const getAvatarUrl = (user: Customer | Sender | null) => {
  if (!user) return null;
  if (user.avatar) {
    return user.avatar.startsWith('http') ? user.avatar : `/storage/${user.avatar}`;
  }
  return null;
};

// Group messages by date
const groupedMessages = computed(() => {
  const groups: { date: string; messages: Message[] }[] = [];
  let currentDate = '';

  messages.value.forEach((msg) => {
    const msgDate = new Date(msg.created_at).toDateString();
    if (msgDate !== currentDate) {
      currentDate = msgDate;
      groups.push({ date: msg.created_at, messages: [msg] });
    } else {
      groups[groups.length - 1].messages.push(msg);
    }
  });

  return groups;
});

// Subscribe to real-time messages via WebSocket
onMounted(() => {
  scrollToBottom();

  // Track that we're viewing this conversation (prevents header badge update)
  (window as any).activeConversationId = props.conversation.id;

  // Listen for new messages on this conversation's private channel
  (window as any).Echo.private(`conversation.${props.conversation.id}`)
    .listen('MessageSent', (e: { message: Message }) => {
      // Only add if not already in the list (avoid duplicates)
      if (!messages.value.some(m => m.id === e.message.id)) {
        messages.value.push(e.message);
        scrollToBottom();

        // Mark message as read if it's from customer (we're viewing)
        if (e.message.sender_type === 'customer') {
          axios.post(`/seller/chats/${props.conversation.id}/read`).catch(() => { });
        }
      }
    });
});

onUnmounted(() => {
  // Clear active conversation tracking
  (window as any).activeConversationId = null;

  // Leave the channel when component is destroyed
  (window as any).Echo.leave(`conversation.${props.conversation.id}`);
});
</script>

<template>
  <SellerDashboardLayout>

    <Head :title="`Chat - ${conversation.customer.name}`" />

    <div class="mx-auto flex h-[calc(100vh-180px)] max-w-4xl flex-col">
      <!-- Header -->
      <div class="flex items-center gap-4 rounded-t-xl border border-b-0 border-slate-200 bg-white px-5 py-4">
        <Link href="/seller/chats"
          class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
          <ArrowLeft class="h-5 w-5" />
        </Link>

        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-slate-100">
          <img v-if="getAvatarUrl(conversation.customer)" :src="getAvatarUrl(conversation.customer)"
            :alt="conversation.customer.name" class="h-10 w-10 rounded-full object-cover" />
          <User v-else class="h-5 w-5 text-slate-400" />
        </div>

        <div class="min-w-0 flex-1">
          <h1 class="font-semibold text-slate-900">{{ conversation.customer.name }}</h1>
          <p v-if="conversation.product" class="flex items-center gap-1 text-xs text-slate-500">
            <Package class="h-3 w-3" />
            {{ conversation.product.name }}
          </p>
        </div>
      </div>

      <!-- Messages -->
      <div ref="messagesContainer" class="flex-1 overflow-y-auto border-x border-slate-200 bg-slate-50 px-5 py-4">

        <div v-if="messages.length === 0" class="flex h-full items-center justify-center">
          <p class="text-slate-400">Belum ada pesan. Mulai percakapan!</p>
        </div>

        <div v-else>
          <div v-for="group in groupedMessages" :key="group.date" class="mb-4">
            <!-- Date Separator -->
            <div class="mb-4 flex items-center justify-center">
              <span class="rounded-full bg-slate-200 px-3 py-1 text-xs text-slate-600">
                {{ formatDate(group.date) }}
              </span>
            </div>

            <!-- Messages -->
            <div v-for="msg in group.messages" :key="msg.id" class="mb-3 flex"
              :class="msg.sender_type === 'seller' ? 'justify-end' : 'justify-start'">

              <div class="flex max-w-[70%] gap-2"
                :class="msg.sender_type === 'seller' ? 'flex-row-reverse' : 'flex-row'">

                <!-- Avatar (only for customer) -->
                <div v-if="msg.sender_type === 'customer'"
                  class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-slate-200">
                  <img v-if="getAvatarUrl(msg.sender)" :src="getAvatarUrl(msg.sender)"
                    class="h-8 w-8 rounded-full object-cover" />
                  <User v-else class="h-4 w-4 text-slate-400" />
                </div>

                <!-- Message Bubble -->
                <div class="rounded-2xl px-4 py-2" :class="msg.sender_type === 'seller'
                  ? 'bg-sky-500 text-white rounded-br-md'
                  : 'bg-white text-slate-800 rounded-bl-md shadow-sm'">
                  <p class="text-sm whitespace-pre-wrap">{{ msg.content }}</p>
                  <p class="mt-1 text-[10px]" :class="msg.sender_type === 'seller' ? 'text-sky-100' : 'text-slate-400'">
                    {{ formatTime(msg.created_at) }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Input -->
      <div class="rounded-b-xl border border-t-0 border-slate-200 bg-white p-4">
        <form @submit.prevent="sendMessage" class="flex items-center gap-3">
          <input v-model="newMessage" type="text"
            class="flex-1 rounded-full border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-sky-400 focus:bg-white focus:outline-none"
            placeholder="Ketik pesan..." :disabled="isSending" />
          <button type="submit" :disabled="!newMessage.trim() || isSending"
            class="flex h-10 w-10 items-center justify-center rounded-full bg-sky-500 text-white transition hover:bg-sky-600 disabled:cursor-not-allowed disabled:opacity-50">
            <Send class="h-5 w-5" />
          </button>
        </form>
      </div>
    </div>
  </SellerDashboardLayout>
</template>
