<script setup lang="ts">
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import SellerDashboardLayout from '@/Layouts/SellerDashboardLayout.vue';
import { Upload, FileText, CheckCircle, Clock, XCircle, Trash2, AlertCircle, Building2, FileCheck, Loader2 } from 'lucide-vue-next';

interface CompanyType {
  value: string;
  label: string;
}

interface SupportingDoc {
  id: number;
  name: string;
  url: string;
}

interface SellerDocumentData {
  id: number;
  ktp_status: string;
  npwp_status: string;
  nib_status: string;
  submission_status: string;
  admin_notes: string | null;
  ktp_url: string | null;
  npwp_url: string | null;
  nib_url: string | null;
  company_statement_url: string | null;
  supporting_documents_urls: SupportingDoc[];
}

interface StoreData {
  id: number;
  name: string;
  type: string | null;
  address_line: string | null;
}

const props = defineProps<{
  store: StoreData;
  sellerDocument: SellerDocumentData | null;
  typeOptions: CompanyType[];
}>();

const page = usePage();
const flashSuccess = computed(() => (page.props.flash as { success?: string })?.success ?? '');
const flashError = computed(() => (page.props.flash as { error?: string })?.error ?? '');
const localError = ref('');
const isSubmitting = ref(false);

const isPdf = (value: string) => value.toLowerCase().endsWith('.pdf');
const revokeIfBlob = (url: string | null) => {
  if (url?.startsWith('blob:')) {
    URL.revokeObjectURL(url);
  }
};

const form = useForm({
  name: props.store?.name || '',
  type: props.store?.type || 'umkm',
  address_line: props.store?.address_line || '',
  ktp: null as File | null,
  npwp: null as File | null,
  nib: null as File | null,
  company_statement: null as File | null,
  supporting_documents: [] as File[],
});

const ktpPreview = ref<string | null>(props.sellerDocument?.ktp_url || null);
const ktpPreviewIsPdf = ref(!!ktpPreview.value && isPdf(ktpPreview.value));
const ktpPreviewName = ref<string | null>(null);
const npwpPreview = ref<string | null>(props.sellerDocument?.npwp_url || null);
const npwpPreviewIsPdf = ref(!!npwpPreview.value && isPdf(npwpPreview.value));
const npwpPreviewName = ref<string | null>(null);
const nibPreview = ref<string | null>(props.sellerDocument?.nib_url || null);
const nibPreviewIsPdf = ref(!!nibPreview.value && isPdf(nibPreview.value));
const nibPreviewName = ref<string | null>(null);
const companyStatementPreview = ref<string | null>(props.sellerDocument?.company_statement_url || null);
const companyStatementPreviewIsPdf = ref(!!companyStatementPreview.value && isPdf(companyStatementPreview.value));
const companyStatementPreviewName = ref<string | null>(null);

const isSubmitted = computed(() => props.sellerDocument?.submission_status === 'submitted');
const isApproved = computed(() => props.sellerDocument?.submission_status === 'approved');
const isRejected = computed(() => props.sellerDocument?.submission_status === 'rejected');
const isDraft = computed(() => !props.sellerDocument || props.sellerDocument?.submission_status === 'draft');

const canEdit = computed(() => isDraft.value || isRejected.value);
const requiredUploaded = computed(
  () => !!props.sellerDocument?.ktp_url && !!props.sellerDocument?.npwp_url && !!props.sellerDocument?.nib_url,
);
const hasUnsavedFiles = computed(
  () =>
    !!form.ktp ||
    !!form.npwp ||
    !!form.nib ||
    !!form.company_statement ||
    (form.supporting_documents?.length ?? 0) > 0,
);

const handleFileChange = (event: Event, field: 'ktp' | 'npwp' | 'nib' | 'company_statement') => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0];
  if (file) {
    form[field] = file;
    const url = URL.createObjectURL(file);
    const fileIsPdf = file.type === 'application/pdf' || isPdf(file.name);

    if (field === 'ktp') {
      revokeIfBlob(ktpPreview.value);
      ktpPreview.value = url;
      ktpPreviewIsPdf.value = fileIsPdf;
      ktpPreviewName.value = file.name;
    }

    if (field === 'npwp') {
      revokeIfBlob(npwpPreview.value);
      npwpPreview.value = url;
      npwpPreviewIsPdf.value = fileIsPdf;
      npwpPreviewName.value = file.name;
    }

    if (field === 'nib') {
      revokeIfBlob(nibPreview.value);
      nibPreview.value = url;
      nibPreviewIsPdf.value = fileIsPdf;
      nibPreviewName.value = file.name;
    }

    if (field === 'company_statement') {
      revokeIfBlob(companyStatementPreview.value);
      companyStatementPreview.value = url;
      companyStatementPreviewIsPdf.value = fileIsPdf;
      companyStatementPreviewName.value = file.name;
    }
  }
};

const handleSupportingFiles = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const files = target.files;
  if (files) {
    form.supporting_documents = Array.from(files);
  }
};

const saveDocuments = () => {
  localError.value = '';
  form.post('/seller/documents', {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      form.ktp = null;
      form.npwp = null;
      form.nib = null;
      form.company_statement = null;
      form.supporting_documents = [];
    },
  });
};

const submitForReview = () => {
  localError.value = '';
  isSubmitting.value = true;

  if (hasUnsavedFiles.value) {
    form.post('/seller/documents', {
      preserveScroll: true,
      forceFormData: true,
      onSuccess: () => {
        form.ktp = null;
        form.npwp = null;
        form.nib = null;
        form.company_statement = null;
        form.supporting_documents = [];

        router.post(
          '/seller/documents/submit',
          {},
          {
            preserveScroll: true,
            onFinish: () => {
              isSubmitting.value = false;
            },
          },
        );
      },
      onError: () => {
        isSubmitting.value = false;
      },
    });
    return;
  }

  if (!requiredUploaded.value) {
    localError.value = 'Dokumen wajib belum tersimpan. Klik "Simpan Draft" setelah upload dokumen.';
    isSubmitting.value = false;
    return;
  }

  router.post(
    '/seller/documents/submit',
    {},
    {
      preserveScroll: true,
      onFinish: () => {
        isSubmitting.value = false;
      },
    },
  );
};

const deleteSupportingDoc = (mediaId: number) => {
  if (confirm('Hapus dokumen ini?')) {
    router.delete(`/seller/documents/supporting/${mediaId}`, {
      preserveScroll: true,
    });
  }
};

const getStatusIcon = (status: string) => {
  if (status === 'approved') return CheckCircle;
  if (status === 'rejected') return XCircle;
  return Clock;
};

const getStatusColor = (status: string) => {
  if (status === 'approved') return 'text-green-600';
  if (status === 'rejected') return 'text-red-600';
  return 'text-amber-600';
};

const getStatusBg = (status: string) => {
  if (status === 'approved') return 'bg-green-50 border-green-200';
  if (status === 'rejected') return 'bg-red-50 border-red-200';
  return 'bg-amber-50 border-amber-200';
};

watch(
  () => props.sellerDocument?.ktp_url,
  (url) => {
    if (!url) return;
    revokeIfBlob(ktpPreview.value);
    ktpPreview.value = url;
    ktpPreviewIsPdf.value = isPdf(url);
    ktpPreviewName.value = null;
  },
);

watch(
  () => props.sellerDocument?.npwp_url,
  (url) => {
    if (!url) return;
    revokeIfBlob(npwpPreview.value);
    npwpPreview.value = url;
    npwpPreviewIsPdf.value = isPdf(url);
    npwpPreviewName.value = null;
  },
);

watch(
  () => props.sellerDocument?.nib_url,
  (url) => {
    if (!url) return;
    revokeIfBlob(nibPreview.value);
    nibPreview.value = url;
    nibPreviewIsPdf.value = isPdf(url);
    nibPreviewName.value = null;
  },
);

watch(
  () => props.sellerDocument?.company_statement_url,
  (url) => {
    if (!url) return;
    revokeIfBlob(companyStatementPreview.value);
    companyStatementPreview.value = url;
    companyStatementPreviewIsPdf.value = isPdf(url);
    companyStatementPreviewName.value = null;
  },
);
</script>

<template>
  <SellerDashboardLayout>

    <Head title="Lengkapi Data Usaha" />

    <div class="mx-auto space-y-6">
      <!-- Header -->
      <div class="rounded-lg border border-slate-200 bg-white p-6">
        <div class="flex items-start gap-4">
          <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-sky-100 text-sky-600">
            <Building2 class="h-6 w-6" />
          </div>
          <div class="flex-1">
            <h1 class="text-xl font-bold text-slate-900">Lengkapi Data Usaha</h1>
            <p class="mt-1 text-sm text-slate-600">
              Siapkan dokumen berikut untuk verifikasi toko Anda. Setelah diverifikasi, Anda dapat mulai berjualan.
            </p>
          </div>
        </div>

        <!-- Status Badge -->
        <div v-if="sellerDocument && !isDraft" class="mt-4">
          <div
            :class="['flex items-center gap-2 rounded-lg border px-4 py-3', getStatusBg(sellerDocument.submission_status)]">
            <component :is="getStatusIcon(sellerDocument.submission_status)"
              :class="['h-5 w-5', getStatusColor(sellerDocument.submission_status)]" />
            <div>
              <p :class="['font-medium', getStatusColor(sellerDocument.submission_status)]">
                <span v-if="isSubmitted">Dokumen sedang dalam proses verifikasi</span>
                <span v-else-if="isApproved">Dokumen telah diverifikasi</span>
                <span v-else-if="isRejected">Dokumen ditolak, silakan perbaiki</span>
              </p>
              <p v-if="sellerDocument.admin_notes" class="mt-1 text-sm text-slate-600">
                Catatan: {{ sellerDocument.admin_notes }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Flash Messages -->
      <div v-if="flashSuccess" class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
        {{ flashSuccess }}
      </div>
      <div v-if="flashError" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        {{ flashError }}
      </div>
      <div v-if="localError" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        {{ localError }}
      </div>

      <form @submit.prevent="saveDocuments" class="space-y-6">
        <!-- Company Info -->
        <div class="rounded-lg border border-slate-200 bg-white p-6">
          <h2 class="mb-4 text-lg font-semibold text-slate-900">Informasi Toko</h2>

          <div class="grid gap-4 sm:grid-cols-2">
            <div class="space-y-2">
              <label class="text-sm font-medium text-slate-700">Nama Toko <span class="text-red-500">*</span></label>
              <input type="text" v-model="form.name" :disabled="!canEdit"
                class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-sky-400 focus:outline-none disabled:bg-slate-50 disabled:text-slate-500"
                placeholder="Nama toko" />
              <p v-if="form.errors.name" class="text-xs text-red-500">{{ form.errors.name }}</p>
            </div>

            <div class="space-y-2">
              <label class="text-sm font-medium text-slate-700">Jenis Toko <span class="text-red-500">*</span></label>
              <select v-model="form.type" :disabled="!canEdit"
                class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:border-sky-400 focus:outline-none disabled:bg-slate-50 disabled:text-slate-500">
                <option value="">Pilih jenis toko</option>
                <option v-for="type in typeOptions" :key="type.value" :value="type.value">
                  {{ type.label }}
                </option>
              </select>
              <p v-if="form.errors.type" class="text-xs text-red-500">{{ form.errors.type }}</p>
            </div>

            <div class="space-y-2 sm:col-span-2">
              <label class="text-sm font-medium text-slate-700">Alamat Toko</label>
              <textarea v-model="form.address_line" :disabled="!canEdit" rows="2"
                class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-sky-400 focus:outline-none disabled:bg-slate-50 disabled:text-slate-500"
                placeholder="Alamat lengkap toko"></textarea>
            </div>
          </div>
        </div>

        <!-- Required Documents -->
        <div class="rounded-lg border border-slate-200 bg-white p-6">
          <h2 class="mb-4 text-lg font-semibold text-slate-900">Dokumen Wajib</h2>

          <div class="grid gap-6 sm:grid-cols-3">
            <!-- KTP -->
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <label class="text-sm font-medium text-slate-700">KTP Pemilik <span
                    class="text-red-500">*</span></label>
                <span v-if="sellerDocument?.ktp_status && sellerDocument?.ktp_url"
                  :class="['text-xs font-medium', getStatusColor(sellerDocument.ktp_status)]">
                  {{ sellerDocument.ktp_status === 'approved' ? 'Disetujui' : sellerDocument.ktp_status === 'rejected' ?
                    'Ditolak' : 'Menunggu' }}
                </span>
              </div>
              <div v-if="ktpPreview" class="relative">
                <a :href="ktpPreview" target="_blank" class="block">
                  <div class="h-44 overflow-hidden rounded-lg border border-slate-200 bg-slate-50 sm:h-48">
                    <img v-if="!ktpPreviewIsPdf" :src="ktpPreview" alt="Preview KTP"
                      class="h-full w-full object-cover" />
                    <div v-else class="flex h-full items-center justify-center">
                      <FileText class="h-12 w-12 text-sky-500" />
                    </div>
                  </div>
                </a>
              </div>
              <p v-if="ktpPreviewName" class="text-xs text-slate-500">{{ ktpPreviewName }}</p>
              <label v-if="canEdit"
                class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-600 transition hover:border-sky-400 hover:bg-sky-50 hover:text-sky-600">
                <Upload class="h-4 w-4" />
                <span>{{ ktpPreview ? 'Ganti File' : 'Upload KTP' }}</span>
                <input type="file" class="hidden" accept="image/*,.pdf" @change="(e) => handleFileChange(e, 'ktp')" />
              </label>
              <p v-if="form.errors.ktp" class="text-xs text-red-500">{{ form.errors.ktp }}</p>
            </div>

            <!-- NPWP -->
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <label class="text-sm font-medium text-slate-700">NPWP <span class="text-red-500">*</span></label>
                <span v-if="sellerDocument?.npwp_status && sellerDocument?.npwp_url"
                  :class="['text-xs font-medium', getStatusColor(sellerDocument.npwp_status)]">
                  {{ sellerDocument.npwp_status === 'approved' ? 'Disetujui' : sellerDocument.npwp_status === 'rejected'
                    ? 'Ditolak' : 'Menunggu' }}
                </span>
              </div>
              <div v-if="npwpPreview" class="relative">
                <a :href="npwpPreview" target="_blank" class="block">
                  <div class="h-44 overflow-hidden rounded-lg border border-slate-200 bg-slate-50 sm:h-48">
                    <img v-if="!npwpPreviewIsPdf" :src="npwpPreview" alt="Preview NPWP"
                      class="h-full w-full object-cover" />
                    <div v-else class="flex h-full items-center justify-center">
                      <FileText class="h-12 w-12 text-sky-500" />
                    </div>
                  </div>
                </a>
              </div>
              <p v-if="npwpPreviewName" class="text-xs text-slate-500">{{ npwpPreviewName }}</p>
              <label v-if="canEdit"
                class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-600 transition hover:border-sky-400 hover:bg-sky-50 hover:text-sky-600">
                <Upload class="h-4 w-4" />
                <span>{{ npwpPreview ? 'Ganti File' : 'Upload NPWP' }}</span>
                <input type="file" class="hidden" accept="image/*,.pdf" @change="(e) => handleFileChange(e, 'npwp')" />
              </label>
              <p v-if="form.errors.npwp" class="text-xs text-red-500">{{ form.errors.npwp }}</p>
            </div>

            <!-- NIB -->
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <label class="text-sm font-medium text-slate-700">NIB <span class="text-red-500">*</span></label>
                <span v-if="sellerDocument?.nib_status && sellerDocument?.nib_url"
                  :class="['text-xs font-medium', getStatusColor(sellerDocument.nib_status)]">
                  {{ sellerDocument.nib_status === 'approved' ? 'Disetujui' : sellerDocument.nib_status === 'rejected' ?
                    'Ditolak' : 'Menunggu' }}
                </span>
              </div>
              <div v-if="nibPreview" class="relative">
                <a :href="nibPreview" target="_blank" class="block">
                  <div class="h-44 overflow-hidden rounded-lg border border-slate-200 bg-slate-50 sm:h-48">
                    <img v-if="!nibPreviewIsPdf" :src="nibPreview" alt="Preview NIB"
                      class="h-full w-full object-cover" />
                    <div v-else class="flex h-full items-center justify-center">
                      <FileText class="h-12 w-12 text-sky-500" />
                    </div>
                  </div>
                </a>
              </div>
              <p v-if="nibPreviewName" class="text-xs text-slate-500">{{ nibPreviewName }}</p>
              <label v-if="canEdit"
                class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-600 transition hover:border-sky-400 hover:bg-sky-50 hover:text-sky-600">
                <Upload class="h-4 w-4" />
                <span>{{ nibPreview ? 'Ganti File' : 'Upload NIB' }}</span>
                <input type="file" class="hidden" accept="image/*,.pdf" @change="(e) => handleFileChange(e, 'nib')" />
              </label>
              <p v-if="form.errors.nib" class="text-xs text-red-500">{{ form.errors.nib }}</p>
            </div>
          </div>
        </div>

        <!-- Optional Documents -->
        <div class="rounded-lg border border-slate-200 bg-white p-6">
          <h2 class="mb-2 text-lg font-semibold text-slate-900">Dokumen Pendukung (Opsional)</h2>
          <p class="mb-4 text-sm text-slate-500">BPOM/SPP-PIRT, TDUP, Sertifikat, atau dokumen lainnya</p>

          <div class="grid gap-6 sm:grid-cols-2">
            <!-- Company Statement -->
            <div class="space-y-3">
              <label class="text-sm font-medium text-slate-700">Surat Pernyataan Jenis Toko</label>
              <div v-if="companyStatementPreview" class="relative">
                <a :href="companyStatementPreview" target="_blank" class="block">
                  <div class="h-32 overflow-hidden rounded-lg border border-slate-200 bg-slate-50 sm:h-36">
                    <img v-if="!companyStatementPreviewIsPdf" :src="companyStatementPreview" alt="Preview Surat"
                      class="h-full w-full object-cover" />
                    <div v-else class="flex h-full items-center justify-center">
                      <FileText class="h-10 w-10 text-sky-500" />
                    </div>
                  </div>
                </a>
              </div>
              <p v-if="companyStatementPreviewName" class="text-xs text-slate-500">{{ companyStatementPreviewName }}</p>
              <label v-if="canEdit"
                class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-600 transition hover:border-sky-400 hover:bg-sky-50 hover:text-sky-600">
                <Upload class="h-4 w-4" />
                <span>{{ companyStatementPreview ? 'Ganti File' : 'Upload Surat' }}</span>
                <input type="file" class="hidden" accept="image/*,.pdf"
                  @change="(e) => handleFileChange(e, 'company_statement')" />
              </label>
              <p v-if="form.errors.company_statement" class="text-xs text-red-500">{{ form.errors.company_statement }}
              </p>
            </div>

            <!-- Multiple Supporting Documents -->
            <div class="space-y-3">
              <label class="text-sm font-medium text-slate-700">Dokumen Pendukung Lainnya</label>

              <!-- Existing Documents -->
              <div v-if="sellerDocument?.supporting_documents_urls?.length" class="space-y-2">
                <div v-for="doc in sellerDocument.supporting_documents_urls" :key="doc.id"
                  class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                  <a :href="doc.url" target="_blank"
                    class="flex items-center gap-2 text-sm text-slate-700 hover:text-sky-600">
                    <FileText class="h-4 w-4" />
                    {{ doc.name }}
                  </a>
                  <button v-if="canEdit" type="button" @click="deleteSupportingDoc(doc.id)"
                    class="text-red-500 hover:text-red-700">
                    <Trash2 class="h-4 w-4" />
                  </button>
                </div>
              </div>

              <label v-if="canEdit"
                class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-600 transition hover:border-sky-400 hover:bg-sky-50 hover:text-sky-600">
                <Upload class="h-4 w-4" />
                <span>Upload Dokumen</span>
                <input type="file" class="hidden" accept="image/*,.pdf" multiple @change="handleSupportingFiles" />
              </label>
              <div v-if="form.supporting_documents.length" class="space-y-1 text-xs text-slate-500">
                <p>{{ form.supporting_documents.length }} file dipilih</p>
                <ul class="list-disc pl-4">
                  <li v-for="file in form.supporting_documents" :key="file.name">{{ file.name }}</li>
                </ul>
              </div>
              <p v-if="form.errors.supporting_documents" class="text-xs text-red-500">{{
                form.errors.supporting_documents }}</p>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div v-if="canEdit"
          class="flex flex-wrap items-center justify-between gap-4 rounded-lg border border-slate-200 bg-white p-6">
          <div class="flex items-center gap-2 text-sm text-slate-600">
            <AlertCircle class="h-4 w-4" />
            <span>Pastikan semua dokumen wajib telah diupload sebelum submit</span>
          </div>

          <div class="flex items-center gap-3">
            <button type="submit" :disabled="form.processing || isSubmitting"
              class="rounded-lg border border-slate-300 bg-white px-6 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:opacity-50">
              {{ form.processing ? 'Menyimpan...' : 'Simpan Draft' }}
            </button>

            <button type="button" @click="submitForReview" :disabled="form.processing || isSubmitting"
              class="flex items-center gap-2 rounded-lg bg-sky-600 px-6 py-2.5 text-sm font-medium text-white transition hover:bg-sky-700 disabled:opacity-50">
              <Loader2 v-if="isSubmitting" class="h-4 w-4 animate-spin" />
              {{ isSubmitting ? 'Mengirim...' : 'Submit untuk Verifikasi' }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </SellerDashboardLayout>
</template>
