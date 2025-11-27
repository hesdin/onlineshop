<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupContent,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarInset,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarProvider,
    SidebarRail,
    SidebarTrigger,
} from '@/components/ui/sidebar';
import {
    Bell,
    CircleUser,
    LayoutDashboard,
    Layers,
    Package,
    ShieldCheck,
} from 'lucide-vue-next';

const page = usePage();
const authUser = computed(() => page.props.auth?.user ?? null);
const currentUrl = computed(() => page.url);

const navMain = [
    { title: 'Dashboard', href: '/dashboard', icon: LayoutDashboard },
    { title: 'Produk', href: '#products', icon: Package },
    { title: 'Kategori', href: '/categories', icon: Layers },
];

const isActive = (href) => {
    if (!href || href === '#') {
        return false;
    }

    return currentUrl.value === href || currentUrl.value.startsWith(href);
};
</script>

<template>
    <SidebarProvider>
        <Sidebar collapsible="icon" variant="inset" class="border-r border-slate-200 bg-white">
            <SidebarHeader class="gap-3 border-b border-slate-100 px-4 py-4">
                <div class="flex items-center gap-2 text-slate-900">
                    <ShieldCheck class="h-5 w-5 text-sky-600" />
                    <div class="flex flex-col">
                        <span class="text-sm font-semibold tracking-tight">TP-PKK Marketplace</span>
                        <span class="text-xs text-slate-500">Panel Super Admin</span>
                    </div>
                </div>
            </SidebarHeader>

            <SidebarContent class="px-3">
                <SidebarGroup>
                    <SidebarGroupLabel>Menu Utama</SidebarGroupLabel>
                    <SidebarGroupContent>
                        <SidebarMenu>
                            <SidebarMenuItem v-for="item in navMain" :key="item.title">
                                <SidebarMenuButton
                                    :is-active="isActive(item.href)"
                                    :tooltip="item.title"
                                    as-child
                                    class="text-slate-600"
                                >
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

        <SidebarInset>
            <header class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-white px-6 py-4">
                <div class="flex items-center gap-3">
                    <SidebarTrigger class="-ml-1" />
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Dashboard</p>
                        <h1 class="text-2xl font-semibold text-slate-900">Panel Super Admin</h1>
                        <p class="text-sm text-slate-500">Pantau kinerja marketplace dan kelola vendor.</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <Button variant="ghost" size="icon" class="text-slate-500 hover:text-slate-700">
                        <Bell class="h-4 w-4" />
                        <span class="sr-only">Notifikasi</span>
                    </Button>
                    <Button variant="outline" as-child>
                        <Link href="/" class="text-slate-700 hover:text-slate-900">
                            Lihat Landing
                        </Link>
                    </Button>
                    <Button variant="secondary" as-child>
                        <Link href="/logout" method="post" as="button">
                            Keluar
                        </Link>
                    </Button>
                </div>
            </header>

            <div class="flex-1 px-6 py-6">
                <slot />
            </div>
        </SidebarInset>
    </SidebarProvider>
</template>
