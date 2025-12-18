<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { SidebarTrigger } from '@/components/ui/sidebar';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Bell, LogOut, User, Settings, Search } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage();

const user = computed(() => (page.props.auth as any)?.user);

const userInitials = computed(() => {
  if (!user.value?.name) return 'U';
  const names = user.value.name.split(' ');
  if (names.length >= 2) {
    return (names[0][0] + names[1][0]).toUpperCase();
  }
  return user.value.name.substring(0, 2).toUpperCase();
});

// Mock notification count - can be passed as prop later
const notificationCount = 3;
</script>

<template>
  <header
    class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur supports-[backdrop-filter]:bg-white/80">
    <div class="flex h-16 items-center justify-between gap-4 px-6">
      <!-- Left Section - Search Bar -->
      <div class="flex items-center gap-4 flex-1 max-w-xl">
        <div class="relative flex-1">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
          <input type="text" placeholder="Cari produk, pesanan..."
            class="w-full h-9 pl-9 pr-4 text-sm border border-slate-200 rounded-lg bg-slate-50 focus:bg-white focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all" />
        </div>
      </div>

      <!-- Right Section -->
      <div class="flex items-center gap-2">
        <!-- Notifications -->
        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button variant="ghost" size="icon" class="relative hover:bg-slate-100 transition-colors">
              <Bell class="h-5 w-5 text-slate-600" />
              <Badge v-if="notificationCount > 0"
                class="absolute -right-1 -top-1 h-5 w-5 rounded-full p-0 flex items-center justify-center bg-red-500 text-[10px] font-semibold text-white border-2 border-white">
                {{ notificationCount > 9 ? '9+' : notificationCount }}
              </Badge>
              <span class="sr-only">Notifikasi</span>
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end" class="w-80">
            <DropdownMenuLabel>Notifikasi</DropdownMenuLabel>
            <DropdownMenuSeparator />
            <div class="py-2 px-2 text-center text-sm text-slate-500">
              Belum ada notifikasi baru
            </div>
          </DropdownMenuContent>
        </DropdownMenu>

        <!-- User Profile -->
        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button variant="ghost" class="gap-2 hover:bg-slate-100 transition-colors">
              <Avatar class="h-8 w-8 border border-slate-200">
                <AvatarImage :src="user?.avatar_url" :alt="user?.name" />
                <AvatarFallback
                  class="bg-gradient-to-br from-emerald-500 to-emerald-600 text-white text-xs font-semibold">
                  {{ userInitials }}
                </AvatarFallback>
              </Avatar>
              <span class="hidden sm:inline-block text-sm font-medium text-slate-700">
                {{ user?.name || 'User' }}
              </span>
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end" class="w-56">
            <DropdownMenuLabel>
              <div class="flex flex-col space-y-1">
                <p class="text-sm font-medium">{{ user?.name }}</p>
                <p class="text-xs text-slate-500">{{ user?.email }}</p>
              </div>
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuItem as-child>
              <Link href="/profile" class="cursor-pointer">
                <User class="mr-2 h-4 w-4" />
                <span>Profil Saya</span>
              </Link>
            </DropdownMenuItem>
            <DropdownMenuItem as-child>
              <Link href="/seller/store" class="cursor-pointer">
                <Settings class="mr-2 h-4 w-4" />
                <span>Pengaturan Toko</span>
              </Link>
            </DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuItem as-child>
              <Link href="/logout" method="post" as="button" class="w-full cursor-pointer text-red-600">
                <LogOut class="mr-2 h-4 w-4" />
                <span>Keluar</span>
              </Link>
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
      </div>
    </div>
  </header>
</template>
