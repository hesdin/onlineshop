<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { SidebarInset, SidebarProvider } from '@/components/ui/sidebar';
import { Button } from '@/components/ui/button';
import { LogOut } from 'lucide-vue-next';
import { ref } from 'vue';
import AdminDashboardSidebar from './AdminDashboardSidebar.vue';
import AdminDashboardHeader from './AdminDashboardHeader.vue';

// Logout confirmation
const showLogoutModal = ref(false);

const handleLogout = () => {
  showLogoutModal.value = true;
};

const confirmLogout = () => {
  router.post('/logout');
};

const cancelLogout = () => {
  showLogoutModal.value = false;
};
</script>

<template>
  <SidebarProvider>
    <div class="flex min-h-screen bg-background min-w-screen">
      <AdminDashboardSidebar />

      <SidebarInset>
        <AdminDashboardHeader @logout="handleLogout" />

        <main class="flex-1 px-6 py-6 bg-background">
          <slot />
        </main>
      </SidebarInset>
    </div>
  </SidebarProvider>

  <!-- Logout Confirmation Modal -->
  <div v-if="showLogoutModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
    @click.self="cancelLogout">
    <div class="w-full max-w-md rounded-xl bg-card border border-border p-6 shadow-2xl">
      <div class="mb-4 flex items-center gap-3">
        <div class="grid h-12 w-12 place-items-center rounded-full bg-destructive/10">
          <LogOut class="h-6 w-6 text-destructive" />
        </div>
        <div>
          <h3 class="text-lg font-bold text-foreground">Konfirmasi Keluar</h3>
          <p class="text-sm text-muted-foreground">Apakah Anda yakin ingin keluar?</p>
        </div>
      </div>

      <p class="mb-6 text-sm text-muted-foreground">
        Anda akan keluar dari akun dan perlu login kembali untuk mengakses dashboard.
      </p>

      <div class="flex gap-3">
        <Button variant="outline" class="flex-1" @click="cancelLogout">
          Batal
        </Button>
        <Button variant="destructive" class="flex-1" @click="confirmLogout">
          Ya, Keluar
        </Button>
      </div>
    </div>
  </div>
</template>
