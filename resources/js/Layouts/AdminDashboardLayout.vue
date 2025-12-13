<script setup>
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { SidebarInset, SidebarProvider, SidebarTrigger } from '@/components/ui/sidebar';
import { Bell } from 'lucide-vue-next';
import AdminDashboardSidebar from './AdminDashboardSidebar.vue';
</script>

<template>
  <SidebarProvider>
    <!-- WRAPPER UTAMA: sidebar + konten -->
    <div class="flex min-h-screen bg-slate-50 min-w-screen">
      <!-- Sidebar di kiri -->
      <AdminDashboardSidebar />

      <!-- Konten di kanan -->
      <SidebarInset>
        <header class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-white px-6 py-4">
          <div class="flex items-center gap-3">
            <SidebarTrigger class="-ml-1" />
            <div>
              <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                Dashboard
              </p>
              <!-- <h1 class="text-2xl font-semibold text-slate-900">
                                Panel Super Admin
                            </h1>
                            <p class="text-sm text-slate-500">
                                Pantau kinerja marketplace dan kelola vendor.
                            </p> -->
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

        <main class="flex-1 px-6 py-6 bg-slate-50">
          <slot />
        </main>
      </SidebarInset>
    </div>
  </SidebarProvider>
</template>
