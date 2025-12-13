<script setup>
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
  SidebarRail,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from '@/components/ui/sidebar';
import {
  CircleUser,
  LayoutDashboard,
  Layers,
  Package,
  ShieldCheck,
  Users,
  FolderKanban,
  ShoppingCart,
  CreditCard,
  TicketPercent,
} from 'lucide-vue-next';

const page = usePage();
const authUser = computed(() => page.props.auth?.user ?? null);
const currentUrl = computed(() => page.url);

const navMain = [
  { title: 'Dashboard', href: '/admin/dashboard', icon: LayoutDashboard },
  { title: 'Users', href: '/admin/users', icon: Users },
  { title: 'Toko', href: '/admin/stores', icon: ShieldCheck },
  { title: 'Kategori', href: '/admin/categories', icon: Layers },
  { title: 'Produk', href: '/admin/products', icon: Package },
  { title: 'Koleksi', href: '/admin/collections', icon: FolderKanban },
  { title: 'Pesanan', href: '/admin/orders', icon: ShoppingCart },
  { title: 'Metode Bayar', href: '/admin/payment-methods', icon: CreditCard },
  { title: 'Kode Promo', href: '/admin/promo-codes', icon: TicketPercent },
];

const isActive = (href) => {
  if (!href || href === '#') {
    return false;
  }

  return currentUrl.value === href || currentUrl.value.startsWith(href);
};
</script>

<template>
  <Sidebar collapsible="icon" variant="inset" class="border-r border-slate-200 bg-white">
    <SidebarHeader class="gap-3 border-b border-slate-100 px-4 py-4">
      <div class="flex items-center gap-2 text-slate-900">
        <!-- <ShieldCheck class="h-5 w-5 text-sky-600" /> -->
        <div class="flex flex-col">
          <span class="text-sm font-semibold tracking-tight">TP-PKK <br> Marketplace</span>
          <!-- <span class="text-xs text-slate-500">Panel Super Admin</span> -->
        </div>
      </div>
    </SidebarHeader>

    <SidebarContent class="px-3">
      <SidebarGroup>
        <SidebarGroupLabel>Menu Utama</SidebarGroupLabel>
        <SidebarGroupContent>
          <SidebarMenu>
            <SidebarMenuItem v-for="item in navMain" :key="item.title">
              <SidebarMenuButton :is-active="isActive(item.href)" :tooltip="item.title" as-child class="text-slate-600">
                <Link :href="item.href">
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
          <CircleUser class="h-5 w-5" />
        </div>
        <div class="min-w-0">
          <p class="truncate text-sm font-semibold text-slate-900">{{ authUser?.name ?? 'Super Admin' }}</p>
          <p class="truncate text-xs text-slate-500">{{ authUser?.email }}</p>
        </div>
      </div>
    </SidebarFooter>
    <SidebarRail />
  </Sidebar>
</template>
