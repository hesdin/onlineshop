<script setup lang="ts">
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { FileText } from 'lucide-vue-next';
import AlertBanner from '@/components/AlertBanner.vue';

type DocumentPayload = {
  id: number;
  submission_status: string;
  admin_notes: string | null;
  submitted_at: string | null;
  reviewed_at: string | null;
  reviewer_name: string | null;
  ktp_status: string;
  npwp_status: string;
  nib_status: string;
  ktp_url: string | null;
  npwp_url: string | null;
  nib_url: string | null;
  company_statement_url: string | null;
  supporting_documents_urls: { id: number; name: string; url: string }[];
};

const props = defineProps<{
  document: DocumentPayload;
  store: { id: number | null; name: string | null; type: string | null; is_verified: boolean };
  owner: { id: number | null; name: string | null; email: string | null };
}>();

defineOptions({
  layout: AdminDashboardLayout,
});

const page = usePage();
const flash = computed(() => (page.props.flash ?? {}) as Record<string, string>);
const flashSuccess = computed(() => flash.value.success ?? '');
const flashError = computed(() => flash.value.error ?? '');
const showSuccess = ref(false);
const showError = ref(false);

// Watch for success messages
watch(() => page.props.flash, (newFlash) => {
  const flashData = newFlash as Record<string, string> | undefined;
  if (flashData?.success) {
    showSuccess.value = true;
    setTimeout(() => {
      showSuccess.value = false;
    }, 5000);
  }
  if (flashData?.error) {
    showError.value = true;
    setTimeout(() => {
      showError.value = false;
    }, 5000);
  }
}, { deep: true, immediate: true });

const isPdf = (url: string) => url.toLowerCase().indexOf('.pdf') !== -1;

const form = useForm({
  submission_status: props.document.submission_status,
  ktp_status: props.document.ktp_status,
  npwp_status: props.document.npwp_status,
  nib_status: props.document.nib_status,
  admin_notes: props.document.admin_notes ?? '',
});

const submit = () => {
  form.put(`/admin/seller-documents/${props.document.id}`, {
    preserveScroll: true,
  });
};

const approveAll = () => {
  form.submission_status = 'approved';
  submit();
};

const reject = () => {
  form.submission_status = 'rejected';
  submit();
};

const statusBadge = (value: string) => {
  const map: Record<string, string> = {
    draft: 'bg-slate-100 text-slate-700',
    submitted: 'bg-amber-100 text-amber-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
  };
  return map[value] ?? 'bg-slate-100 text-slate-700';
};

const previewOpen = ref(false);
const previewUrl = ref<string | null>(null);
const previewTitle = ref<string>('');

const openPreview = (url: string, title: string) => {
  previewUrl.value = url;
  previewTitle.value = title;
  previewOpen.value = true;
};

const closePreview = () => {
  previewOpen.value = false;
  setTimeout(() => {
    previewUrl.value = null;
    previewTitle.value = '';
  }, 200);
};
</script>

<template>
  <div class="space-y-6">

    <Head :title="`Review Dokumen #${document.id}`" />

    <!-- Floating Success Alert -->
    <Teleport to="body">
      <div v-if="showSuccess && flashSuccess"
        class="fixed top-20 left-1/2 -translate-x-1/2 z-[9999] min-w-[600px] max-w-2xl shadow-lg rounded-lg overflow-hidden">
        <AlertBanner type="success" :message="flashSuccess" :show="showSuccess" :dismissible="true"
          @close="showSuccess = false" />
      </div>
    </Teleport>

    <!-- Floating Error Alert -->
    <Teleport to="body">
      <div v-if="showError && flashError"
        class="fixed top-20 left-1/2 -translate-x-1/2 z-[9999] min-w-[600px] max-w-2xl shadow-lg rounded-lg overflow-hidden">
        <AlertBanner type="error" :message="flashError" :show="showError" :dismissible="true"
          @close="showError = false" />
      </div>
    </Teleport>

    <div class="flex flex-wrap items-center justify-between gap-3">
      <div class="space-y-1">
        <h1 class="text-xl font-bold tracking-tight text-slate-900">Review Dokumen</h1>
        <div class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
          <span class="font-medium text-slate-700">{{ store.name ?? '-' }}</span>
          <span>•</span>
          <span>{{ owner.name ?? '-' }} ({{ owner.email ?? '-' }})</span>
          <span>•</span>
          <span :class="`rounded-sm px-2 py-1 text-xs font-semibold ${statusBadge(document.submission_status)}`">
            {{ document.submission_status.toUpperCase() }}
          </span>
        </div>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <Button variant="outline" size="sm" @click="router.visit('/admin/seller-documents')"
          :disabled="form.processing">
          Kembali
        </Button>
        <Button variant="outline" :disabled="form.processing" @click="reject">Tolak</Button>
        <Button :disabled="form.processing" @click="approveAll">Setujui</Button>
      </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
      <Card class="lg:col-span-2 border-slate-200 bg-white shadow-sm">
        <CardHeader>
          <CardTitle>Dokumen</CardTitle>
          <CardDescription>Klik dokumen untuk melihat preview.</CardDescription>
        </CardHeader>
        <CardContent class="space-y-6">
          <div class="grid gap-6 sm:grid-cols-3">
            <div class="space-y-2">
              <Label>KTP</Label>
              <div v-if="document.ktp_url" @click="openPreview(document.ktp_url, 'KTP')"
                class="block cursor-pointer transition hover:opacity-90">
                <div class="h-44 overflow-hidden rounded-lg border border-slate-200 bg-slate-50 sm:h-48">
                  <img v-if="!isPdf(document.ktp_url)" :src="document.ktp_url" alt="KTP"
                    class="h-full w-full object-cover" />
                  <div v-else class="flex h-full items-center justify-center">
                    <FileText class="h-12 w-12 text-sky-500" />
                  </div>
                </div>
              </div>
              <p v-else class="text-xs text-slate-500">Belum diunggah.</p>
              <select v-model="form.ktp_status" class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm">
                <option value="pending">Menunggu</option>
                <option value="approved">Disetujui</option>
                <option value="rejected">Ditolak</option>
              </select>
              <p v-if="form.errors.ktp_status" class="text-xs text-red-600">{{ form.errors.ktp_status }}</p>
            </div>

            <div class="space-y-2">
              <Label>NPWP</Label>
              <div v-if="document.npwp_url" @click="openPreview(document.npwp_url, 'NPWP')"
                class="block cursor-pointer transition hover:opacity-90">
                <div class="h-44 overflow-hidden rounded-lg border border-slate-200 bg-slate-50 sm:h-48">
                  <img v-if="!isPdf(document.npwp_url)" :src="document.npwp_url" alt="NPWP"
                    class="h-full w-full object-cover" />
                  <div v-else class="flex h-full items-center justify-center">
                    <FileText class="h-12 w-12 text-sky-500" />
                  </div>
                </div>
              </div>
              <p v-else class="text-xs text-slate-500">Belum diunggah.</p>
              <select v-model="form.npwp_status" class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm">
                <option value="pending">Menunggu</option>
                <option value="approved">Disetujui</option>
                <option value="rejected">Ditolak</option>
              </select>
              <p v-if="form.errors.npwp_status" class="text-xs text-red-600">{{ form.errors.npwp_status }}</p>
            </div>

            <div class="space-y-2">
              <Label>NIB</Label>
              <div v-if="document.nib_url" @click="openPreview(document.nib_url, 'NIB')"
                class="block cursor-pointer transition hover:opacity-90">
                <div class="h-44 overflow-hidden rounded-lg border border-slate-200 bg-slate-50 sm:h-48">
                  <img v-if="!isPdf(document.nib_url)" :src="document.nib_url" alt="NIB"
                    class="h-full w-full object-cover" />
                  <div v-else class="flex h-full items-center justify-center">
                    <FileText class="h-12 w-12 text-sky-500" />
                  </div>
                </div>
              </div>
              <p v-else class="text-xs text-slate-500">Belum diunggah.</p>
              <select v-model="form.nib_status" class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm">
                <option value="pending">Menunggu</option>
                <option value="approved">Disetujui</option>
                <option value="rejected">Ditolak</option>
              </select>
              <p v-if="form.errors.nib_status" class="text-xs text-red-600">{{ form.errors.nib_status }}</p>
            </div>
          </div>

          <div class="space-y-2">
            <Label>Dokumen Pendukung</Label>
            <div v-if="document.supporting_documents_urls?.length" class="space-y-2">
              <button v-for="doc in document.supporting_documents_urls" :key="doc.id"
                @click="openPreview(doc.url, doc.name)" type="button"
                class="flex w-full items-center justify-between rounded-md border border-slate-200 bg-white px-4 py-3 text-sm transition hover:bg-slate-50">
                <span class="truncate text-slate-800">{{ doc.name }}</span>
                <span class="text-xs text-slate-500">Preview</span>
              </button>
            </div>
            <p v-else class="text-xs text-slate-500">Tidak ada dokumen pendukung.</p>
          </div>
        </CardContent>
      </Card>

      <div class="space-y-6">
        <Card class="border-slate-200 bg-white shadow-sm">
          <CardHeader>
            <CardTitle>Catatan Admin</CardTitle>
            <CardDescription>Berikan alasan jika dokumen ditolak.</CardDescription>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="space-y-2">
              <Label>Status Pengajuan</Label>
              <select v-model="form.submission_status"
                class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm">
                <option value="draft">Draft</option>
                <option value="submitted">Menunggu</option>
                <option value="approved">Disetujui</option>
                <option value="rejected">Ditolak</option>
              </select>
              <p v-if="form.errors.submission_status" class="text-xs text-red-600">{{ form.errors.submission_status }}
              </p>
            </div>

            <div class="space-y-2">
              <Label>Catatan</Label>
              <Textarea v-model="form.admin_notes" rows="6" placeholder="Tulis catatan untuk penjual..." />
              <p v-if="form.errors.admin_notes" class="text-xs text-red-600">{{ form.errors.admin_notes }}</p>
            </div>

            <Button class="w-full" :disabled="form.processing" @click="submit">
              Simpan Review
            </Button>

            <div class="text-xs text-slate-500 space-y-1">
              <p v-if="document.submitted_at">Submitted: {{ document.submitted_at }}</p>
              <p v-if="document.reviewed_at">Reviewed: {{ document.reviewed_at }}<span v-if="document.reviewer_name">
                  oleh {{ document.reviewer_name }}</span></p>
              <p>Status toko: <span class="font-semibold">{{ store.is_verified ? 'Terverifikasi' : 'Belum' }}</span></p>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- Preview Modal -->
    <Dialog :open="previewOpen" @update:open="(val) => !val && closePreview()">
      <DialogContent class="max-w-5xl max-h-[90vh] p-0">
        <DialogHeader class="px-6 py-4 border-b border-slate-200">
          <DialogTitle>{{ previewTitle }}</DialogTitle>
        </DialogHeader>
        <div class="p-6 overflow-auto max-h-[calc(90vh-80px)]">
          <div v-if="previewUrl" class="flex items-center justify-center">
            <img v-if="previewUrl && !isPdf(previewUrl)" :src="previewUrl" :alt="previewTitle"
              class="max-w-full h-auto rounded-lg" />
            <iframe v-else-if="previewUrl && isPdf(previewUrl)" :src="previewUrl"
              class="w-full h-[70vh] rounded-lg border border-slate-200" title="PDF Preview" />
          </div>
        </div>
      </DialogContent>
    </Dialog>
  </div>
</template>
