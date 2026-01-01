<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Sheet, SheetContent, SheetTrigger, SheetHeader, SheetTitle } from '@/components/ui/sheet';
import { User, MapPin, Receipt, ShoppingBag, Star, MessageCircle, Menu, CircleUser } from 'lucide-vue-next';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const isOpen = ref(false);

const menuSections = [
  {
    label: 'Pengaturan',
    items: [
      { key: 'profil', label: 'Profil', href: '/customer/dashboard/profile', icon: 'user' },
      { key: 'alamat', label: 'Alamat Pengiriman', href: '/customer/dashboard/address', icon: 'address' },
    ],
  },
  {
    label: 'Transaksi',
    items: [
      { key: 'transaksi', label: 'Daftar Transaksi', href: '/customer/dashboard/transactions', icon: 'list' },
      { key: 'beli-lagi', label: 'Beli Lagi', href: '/customer/dashboard/reorder', icon: 'history' },
      { key: 'ulasan', label: 'Ulasan', href: '/customer/dashboard/reviews', icon: 'review' },
    ],
  },
  {
    label: 'Chat',
    items: [{ key: 'chat', label: 'Chat', href: '/customer/dashboard/chat', icon: 'chat' }],
  },
];

defineProps({
  activeKey: {
    type: String,
    default: '',
  },
});

// Icon component mapping
const iconComponents = {
  user: User,
  address: MapPin,
  list: Receipt,
  history: ShoppingBag,
  review: Star,
  chat: MessageCircle,
};

const getIcon = (iconName) => iconComponents[iconName] || CircleUser;
</script>

<template>
  <!-- Mobile: Sheet Trigger Button -->
  <div class="lg:hidden">
    <Sheet v-model:open="isOpen">
      <SheetTrigger as-child>
        <Button variant="outline" class="w-full gap-2">
          <Menu class="h-5 w-5" />
          Menu
        </Button>
      </SheetTrigger>
      <SheetContent side="left" class="w-[280px] p-0 overflow-y-auto">
        <SheetHeader class="p-4 border-b border-slate-200">
          <SheetTitle>Menu</SheetTitle>
        </SheetHeader>
        <!-- User Info -->
        <div class="p-4 border-b border-slate-200">
          <div class="flex items-center gap-3">
            <div class="h-12 w-12 flex-shrink-0 overflow-hidden rounded-full bg-slate-100">
              <img v-if="user?.avatar_url" :src="user.avatar_url" :alt="user?.name" class="h-full w-full object-cover">
              <div v-else class="grid h-full w-full place-items-center">
                <User class="h-6 w-6 text-slate-400" />
              </div>
            </div>
            <div>
              <p class="text-sm font-semibold text-slate-900">{{ user?.name ?? 'Guest' }}</p>
              <p class="text-xs text-slate-500">{{ user?.buyer_type_label ?? 'Customer' }}</p>
            </div>
          </div>
        </div>
        <!-- Menu Items -->
        <div class="p-4 space-y-5 text-sm">
          <div v-for="section in menuSections" :key="section.label" class="space-y-2">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
              {{ section.label }}
            </p>
            <nav class="space-y-1">
              <a v-for="item in section.items" :key="item.key" :class="[
                'flex items-center gap-3 rounded-lg px-3 py-2 transition',
                activeKey === item.key
                  ? 'bg-sky-50 font-semibold text-sky-700'
                  : 'text-slate-700 hover:bg-slate-50',
              ]" :href="item.href" @click="isOpen = false">
                <component :is="getIcon(item.icon)" class="h-5 w-5" />
                <span>{{ item.label }}</span>
              </a>
            </nav>
          </div>
        </div>
      </SheetContent>
    </Sheet>
  </div>

  <!-- Desktop: Static Sidebar -->
  <aside class="hidden lg:block space-y-4">
    <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
      <div class="flex items-center gap-3">
        <div class="h-12 w-12 flex-shrink-0 overflow-hidden rounded-full bg-slate-100">
          <img v-if="user?.avatar_url" :src="user.avatar_url" :alt="user?.name" class="h-full w-full object-cover">
          <div v-else class="grid h-full w-full place-items-center">
            <User class="h-6 w-6 text-slate-400" />
          </div>
        </div>
        <div>
          <p class="text-sm font-semibold text-slate-900">{{ user?.name ?? 'Guest' }}</p>
          <p class="text-xs text-slate-500">{{ user?.buyer_type_label ?? 'Customer' }}</p>
        </div>
      </div>
    </div>

    <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
      <div class="space-y-5 text-sm">
        <div v-for="section in menuSections" :key="section.label" class="space-y-2">
          <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
            {{ section.label }}
          </p>
          <nav class="space-y-1">
            <a v-for="item in section.items" :key="item.key" :class="[
              'flex items-center gap-3 rounded-lg px-3 py-2 transition',
              activeKey === item.key
                ? 'bg-sky-50 font-semibold text-sky-700'
                : 'text-slate-700 hover:bg-slate-50',
            ]" :href="item.href">
              <component :is="getIcon(item.icon)" class="h-5 w-5" />
              <span>{{ item.label }}</span>
            </a>
          </nav>
        </div>
      </div>
    </div>
  </aside>
</template>
