<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import LandingLayout from '@/Layouts/LandingLayout.vue';
import CustomerSidebarMenu from '@/components/Customer/SidebarMenu.vue';
import { ArrowLeft, Send, Store, User } from 'lucide-vue-next';
import axios from 'axios';

interface Sender {
  id: number;
  name: string;
}

interface Message {
  id: number;
  content: string;
  sender_type: 'customer' | 'seller';
  sender: Sender;
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
  messages: Message[];
}

const props = defineProps<{
  conversation: Conversation;
}>();

defineOptions({
  layout: LandingLayout,
});

const messages = ref<Message[]>(props.conversation.messages || []);
const newMessage = ref('');
const isSending = ref(false);
const messagesContainer = ref<HTMLElement | null>(null);
let pollingInterval: ReturnType<typeof setInterval> | null = null;

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
    const response = await axios.post(`/customer/chats/${props.conversation.id}/messages`, {
      content,
    });
    messages.value.push(response.data.message);
    scrollToBottom();
  } catch (error) {
    console.error('Failed to send message:', error);
    newMessage.value = content;
  } finally {
    isSending.value = false;
  }
};

const fetchNewMessages = async () => {
  try {
    const response = await axios.get(`/customer/chats/${props.conversation.id}/messages`, {
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

const formatTime = (dateString: string) => {
  const date = new Date(dateString);
  return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
};

const formatDate = (dateString: string) => {
  const date = new Date(dateString);
  const today = new Date();
  const yesterday = new Date(today);
  yesterday.setDate(yesterday.getDate() - 1);

  if (date.toDateString() === today.toDateString()) {
    return 'Hari ini';
  } else if (date.toDateString() === yesterday.toDateString()) {
    return 'Kemarin';
  } else {
    return date.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long' });
  }
};

const groupedMessages = computed(() => {
  const groups: { date: string; messages: Message[] }[] = [];
  let currentDate = '';

  messages.value.forEach(msg => {
    const msgDate = new Date(msg.created_at).toDateString();
    if (msgDate !== currentDate) {
      currentDate = msgDate;
      groups.push({ date: formatDate(msg.created_at), messages: [msg] });
    } else {
      groups[groups.length - 1].messages.push(msg);
    }
  });

  return groups;
});

onMounted(() => {
  scrollToBottom();
  pollingInterval = setInterval(fetchNewMessages, 5000);
});

onUnmounted(() => {
  if (pollingInterval) {
    clearInterval(pollingInterval);
  }
});
</script>

<template>
  <div class="bg-slate-50">

    <Head :title="`Chat - ${conversation.store.name}`" />

    <div class="mx-auto flex max-w-screen-2xl flex-col gap-6 px-6 py-10">
      <nav class="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
        <a href="/" class="text-sky-600 hover:underline">Beranda</a>
        <span>/</span>
        <Link href="/customer/dashboard/chat" class="text-sky-600 hover:underline">Chat</Link>
        <span>/</span>
        <span class="text-slate-900">{{ conversation.store.name }}</span>
      </nav>

      <div class="grid gap-6 lg:grid-cols-[260px_minmax(0,1fr)]">
        <CustomerSidebarMenu active-key="chat" />

        <main>
          <section class="rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden">
            <!-- Header -->
            <div class="flex items-center gap-3 border-b border-slate-100 px-4 py-3">
              <Link href="/customer/dashboard/chat"
                class="rounded-full p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                <ArrowLeft class="h-5 w-5" />
              </Link>
              <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-slate-100">
                <img v-if="conversation.store_logo" :src="conversation.store_logo" :alt="conversation.store.name"
                  class="h-10 w-10 rounded-full object-cover" />
                <Store v-else class="h-5 w-5 text-slate-400" />
              </div>
              <div class="min-w-0 flex-1">
                <h2 class="font-semibold text-slate-900 truncate">{{ conversation.store.name }}</h2>
                <p v-if="conversation.product" class="text-xs text-slate-500 truncate">{{ conversation.product.name }}
                </p>
              </div>
            </div>

            <!-- Messages -->
            <div ref="messagesContainer" class="h-[400px] overflow-y-auto bg-slate-50 px-4 py-4">
              <div v-for="group in groupedMessages" :key="group.date" class="space-y-3">
                <!-- Date Separator -->
                <div class="flex items-center justify-center">
                  <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-medium text-slate-600">{{ group.date
                    }}</span>
                </div>

                <!-- Messages -->
                <div v-for="msg in group.messages" :key="msg.id" class="flex"
                  :class="msg.sender_type === 'customer' ? 'justify-end' : 'justify-start'">
                  <div class="flex max-w-[80%] gap-2"
                    :class="msg.sender_type === 'customer' ? 'flex-row-reverse' : 'flex-row'">

                    <!-- Avatar (seller only) -->
                    <div v-if="msg.sender_type === 'seller'"
                      class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-slate-200">
                      <Store class="h-4 w-4 text-slate-400" />
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
            <div class="border-t border-slate-100 bg-white p-3">
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
          </section>
        </main>
      </div>
    </div>
  </div>
</template>
