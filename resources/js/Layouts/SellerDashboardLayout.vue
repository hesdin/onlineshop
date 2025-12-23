<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { SidebarInset, SidebarProvider } from '@/components/ui/sidebar';
import SellerDashboardSidebar from './SellerDashboardSidebar.vue';
import SellerDashboardHeader from '@/components/SellerDashboardHeader.vue';
import DocumentStatusBanner from '@/components/DocumentStatusBanner.vue';
import { computed } from 'vue';

const page = usePage();

const needsAttention = computed(() => {
  const docStatus = (page.props.auth as any).seller_document;
  if (!docStatus) return true;
  return !docStatus.is_approved;
});
</script>

<template>
  <SidebarProvider>
    <!-- Banner at top level, outside sidebar structure -->
    <DocumentStatusBanner v-if="needsAttention"
      :status="($page.props.auth as any).seller_document?.submission_status || 'draft'"
      :admin-notes="($page.props.auth as any).seller_document?.admin_notes" class="fixed top-0 left-0 right-0" />

    <div class="flex min-h-screen w-full bg-slate-50" :class="{ 'pt-[42px]': needsAttention }">
      <SellerDashboardSidebar />

      <SidebarInset class="flex-1 w-full !m-0">
        <SellerDashboardHeader />

        <main class="flex-1 w-full max-w-full bg-slate-50 px-6 py-6">
          <slot />
        </main>
      </SidebarInset>
    </div>
  </SidebarProvider>
</template>
