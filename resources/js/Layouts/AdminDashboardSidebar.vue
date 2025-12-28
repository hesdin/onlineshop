<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import logoPkk from '/public/images/logo-pkk.png';
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
  ShieldAlert,
  Users,
  FolderKanban,
  ShoppingCart,
  CreditCard,
  TicketPercent,
  Image,
  Store,
  ChevronRight,
} from 'lucide-vue-next';

const page = usePage();
const authUser = computed(() => page.props.auth?.user ?? null);
const currentUrl = computed(() => page.url);

// Grouped navigation - simplified with single primary color
const navGroups = [
  {
    label: 'Menu Utama',
    items: [
      { title: 'Dashboard', href: '/admin/dashboard', icon: LayoutDashboard },
    ],
  },
  {
    label: 'Kelola Pengguna',
    items: [
      { title: 'Users', href: '/admin/users', icon: Users },
      { title: 'Toko', href: '/admin/stores', icon: Store },
      { title: 'Verifikasi', href: '/admin/seller-documents', icon: ShieldAlert },
    ],
  },
  {
    label: 'Kelola Produk',
    items: [
      { title: 'Kategori', href: '/admin/categories', icon: Layers },
      { title: 'Produk', href: '/admin/products', icon: Package },
      { title: 'Koleksi', href: '/admin/collections', icon: FolderKanban },
    ],
  },
  {
    label: 'Transaksi',
    items: [
      { title: 'Pesanan', href: '/admin/orders', icon: ShoppingCart },
      { title: 'Metode Bayar', href: '/admin/payment-methods', icon: CreditCard },
      { title: 'Kode Promo', href: '/admin/promo-codes', icon: TicketPercent },
    ],
  },
  {
    label: 'Tampilan',
    items: [
      { title: 'Banner', href: '/admin/banners', icon: Image },
    ],
  },
];

const isActive = (href) => {
  if (!href || href === '#') {
    return false;
  }
  return currentUrl.value === href || currentUrl.value.startsWith(href);
};

const getUserInitials = () => {
  if (!authUser.value?.name) return 'SA';
  const names = authUser.value.name.split(' ');
  if (names.length >= 2) {
    return (names[0][0] + names[1][0]).toUpperCase();
  }
  return authUser.value.name.substring(0, 2).toUpperCase();
};
</script>

<template>
  <Sidebar collapsible="icon" variant="inset" class="border-r border-border/50 bg-card/50 backdrop-blur-sm">
    <!-- Header with Logo -->
    <SidebarHeader class="border-b border-border/50 px-2 py-2">
      <div class="flex">
        <div class="h-10 w-auto flex">
          <img :src="logoPkk" alt="Logo PKK" class="h-full w-full object-contain" />
        </div>
      </div>
    </SidebarHeader>

    <!-- Navigation Content -->
    <SidebarContent class="px-3 py-2 overflow-y-auto flex-1">
      <SidebarGroup v-for="group in navGroups" :key="group.label">
        <SidebarGroupLabel class="text-[10px] font-bold text-muted-foreground/70 uppercase tracking-widest px-3 mb-1">
          {{ group.label }}
        </SidebarGroupLabel>
        <SidebarGroupContent>
          <SidebarMenu>
            <SidebarMenuItem v-for="item in group.items" :key="item.title">
              <SidebarMenuButton :is-active="isActive(item.href)" :tooltip="item.title" as-child>
                <Link :href="item.href" :class="[
                  'group flex items-center gap-3 rounded-lg px-3 transition-all duration-200',
                  isActive(item.href)
                    ? 'bg-primary text-primary-foreground shadow-md shadow-primary/25 py-2.5'
                    : 'text-muted-foreground hover:text-foreground hover:bg-muted/80 py-2'
                ]">
                  <component :is="item.icon" :class="[
                    'h-5 w-5 transition-all duration-200',
                    isActive(item.href) ? 'text-primary-foreground' : 'text-primary'
                  ]" />
                  <span class="text-sm font-medium flex-1">{{ item.title }}</span>
                  <ChevronRight v-if="isActive(item.href)" class="h-4 w-4 opacity-60" />
                </Link>
              </SidebarMenuButton>
            </SidebarMenuItem>
          </SidebarMenu>
        </SidebarGroupContent>
      </SidebarGroup>
    </SidebarContent>

    <!-- Footer with User Info -->
    <SidebarFooter class="border-t border-border/50 px-3 py-4">
      <div
        class="flex items-center gap-3 rounded-xl bg-gradient-to-r from-muted/80 to-muted/40 p-3 border border-border/50">
        <div
          class="h-10 w-10 rounded-full bg-gradient-to-br from-primary to-primary/80 flex items-center justify-center text-primary-foreground font-bold text-sm shadow-lg shadow-primary/25">
          {{ getUserInitials() }}
        </div>
        <div class="min-w-0 flex-1">
          <p class="truncate text-sm font-semibold text-foreground">{{ authUser?.name ?? 'Super Admin' }}</p>
          <p class="truncate text-xs text-muted-foreground">{{ authUser?.email }}</p>
        </div>
      </div>
    </SidebarFooter>
    <SidebarRail />
  </Sidebar>
</template>
