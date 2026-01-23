<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarGroup,
  SidebarGroupContent,
  SidebarGroupLabel,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarRail,
  useSidebar,
} from '@/components/ui/sidebar';
import { Box, CircleUserRound, LayoutDashboard, MessageCircle, Settings, ShoppingBag, Star, Users } from 'lucide-vue-next';
import DocumentProgressTracker from '@/components/DocumentProgressTracker.vue';

const page = usePage();
const currentUrl = computed(() => page.url);
const needsVerification = computed(() => {
  const docStatus = (page.props.auth as any).seller_document;
  if (!docStatus) return true;
  return !docStatus.is_approved;
});
const authUser = computed(() => (page.props.auth as any)?.user ?? null);
const store = computed(() => (page.props.auth as any)?.user?.store ?? null);

const { toggleSidebar, state } = useSidebar();
const isCollapsed = computed(() => state.value === 'collapsed');

const navMain = [
  { title: 'Dashboard', href: '/seller/dashboard', icon: LayoutDashboard },
  { title: 'Produk Saya', href: '/seller/products', icon: Box },
  { title: 'Pesanan', href: '/seller/orders', icon: ShoppingBag },
  { title: 'Customer', href: '/seller/customers', icon: Users },
  { title: 'Ulasan', href: '/seller/reviews', icon: Star },
  { title: 'Chat', href: '/seller/chats', icon: MessageCircle },
  { title: 'Promo Saya', href: '/seller/promo-codes', icon: ShoppingBag },
  { title: 'Settings', href: '/seller/settings', icon: Settings },
];

const isActive = (href: string) => currentUrl.value === href || currentUrl.value.startsWith(href);

const storeInitials = computed(() => {
  if (!store.value?.name) return 'SC';
  return store.value.name.substring(0, 2).toUpperCase();
});

const storeName = computed(() => store.value?.name || 'Seller Center');
</script>

<template>
  <Sidebar collapsible="icon" variant="sidebar" class="border-r border-slate-200 bg-white">
    <SidebarHeader class="border-b border-slate-100" :class="isCollapsed ? 'px-2 py-3' : 'px-4 py-2'">
      <div class="flex items-center gap-2" :class="isCollapsed ? 'flex-col' : 'justify-between'">
        <div class="flex items-center gap-2 min-w-0" :class="{ 'flex-1': !isCollapsed }">
          <div class="h-12 w-12 rounded-sm flex items-center justify-center shrink-0 overflow-hidden"
            :class="store?.logo_url ? 'bg-white' : 'bg-gradient-to-br from-blue-500 to-blue-600'">
            <img v-if="store?.logo_url" :src="store.logo_url" :alt="storeName" class="h-full w-full object-cover" />
            <span v-else class="text-white font-bold text-sm">{{ storeInitials }}</span>
          </div>
          <!-- Show store name only if there's no logo -->
          <div v-if="!isCollapsed && !store?.logo_url" class="flex flex-col min-w-0">
            <span class="text-xl font-bold tracking-tight text-slate-900 truncate">{{ storeName }}</span>
          </div>
        </div>
        <button @click="toggleSidebar"
          class="shrink-0 p-1.5 bg-slate-200 hover:bg-slate-300 rounded-sm transition-colors">
          <svg v-if="!isCollapsed" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
            class="text-slate-600" viewBox="0 0 16 16">
            <path fill-rule="evenodd"
              d="M10 3.5a.5.5 0 0 0-.5-.5h-8a.5.5 0 0 0-.5.5v9a.5.5 0 0 0 .5.5h8a.5.5 0 0 0 .5-.5v-2a.5.5 0 0 1 1 0v2A1.5 1.5 0 0 1 9.5 14h-8A1.5 1.5 0 0 1 0 12.5v-9A1.5 1.5 0 0 1 1.5 2h8A1.5 1.5 0 0 1 11 3.5v2a.5.5 0 0 1-1 0z" />
            <path fill-rule="evenodd"
              d="M4.146 8.354a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H14.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708z" />
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
            class="text-slate-600" viewBox="0 0 16 16">
            <path fill-rule="evenodd"
              d="M6 3.5a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-2a.5.5 0 0 0-1 0v2A1.5 1.5 0 0 0 6.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-8A1.5 1.5 0 0 0 5 3.5v2a.5.5 0 0 0 1 0z" />
            <path fill-rule="evenodd"
              d="M11.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H1.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z" />
          </svg>
        </button>
      </div>
    </SidebarHeader>

    <SidebarContent class="px-3">
      <div
        v-if="!isCollapsed && ($page.props.auth as any).seller_document?.exists && !($page.props.auth as any).seller_document?.is_approved"
        class="mb-3 mt-1.5">
        <DocumentProgressTracker :documents-uploaded="($page.props.auth as any).seller_document.documents_uploaded"
          :submission-status="($page.props.auth as any).seller_document.submission_status" />
      </div>

      <SidebarGroup>
        <SidebarGroupContent>
          <SidebarMenu>
            <SidebarMenuItem v-for="item in navMain" :key="item.title">
              <SidebarMenuButton :is-active="isActive(item.href)" :tooltip="item.title" as-child>
                <Link :href="item.href" class="text-slate-600">
                  <component :is="item.icon" class="h-4 w-4" />
                  <span>{{ item.title }}</span>
                </Link>
              </SidebarMenuButton>
            </SidebarMenuItem>
          </SidebarMenu>
        </SidebarGroupContent>
      </SidebarGroup>
    </SidebarContent>

    <SidebarFooter class="border-t border-slate-100 px-3 py-4">
      <div class="flex items-center gap-3 rounded-lg border border-slate-100 bg-slate-50/80 p-3">
        <div class="grid h-10 w-10 place-items-center rounded-full bg-white text-slate-500">
          <CircleUserRound class="h-5 w-5" />
        </div>
        <div class="min-w-0">
          <p class="truncate text-sm font-semibold text-slate-900">{{ authUser?.name ?? 'Seller' }}</p>
          <p class="truncate text-xs text-slate-500">{{ authUser?.email }}</p>
        </div>
      </div>
    </SidebarFooter>
    <SidebarRail />
  </Sidebar>
</template>
