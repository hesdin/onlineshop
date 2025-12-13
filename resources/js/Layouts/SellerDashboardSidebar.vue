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
} from '@/components/ui/sidebar';
import { Box, CircleUserRound, LayoutDashboard, ShoppingBag, Store } from 'lucide-vue-next';

const page = usePage();
const currentUrl = computed(() => page.url);
const authUser = computed(() => page.props.auth?.user ?? null);

const navMain = [
    { title: 'Dashboard', href: '/seller/dashboard', icon: LayoutDashboard },
    { title: 'Produk Saya', href: '/seller/products', icon: Box },
    { title: 'Pesanan', href: '/seller/orders', icon: ShoppingBag },
    { title: 'Toko', href: '/seller/store', icon: Store },
];

const isActive = (href: string) => currentUrl.value === href || currentUrl.value.startsWith(href);
</script>

<template>
    <Sidebar collapsible="icon" variant="inset" class="border-r border-slate-200 bg-white">
        <SidebarHeader class="gap-3 border-b border-slate-100 px-4 py-4">
            <div class="flex flex-col">
                <span class="text-sm font-semibold tracking-tight text-slate-900">Seller Center</span>
                <span class="text-xs text-slate-500">Kelola toko & pesanan</span>
            </div>
        </SidebarHeader>

        <SidebarContent class="px-3">
            <SidebarGroup>
                <SidebarGroupLabel>Navigasi</SidebarGroupLabel>
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
