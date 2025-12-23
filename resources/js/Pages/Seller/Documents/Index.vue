<script setup lang="ts">
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import SellerDashboardLayout from '@/Layouts/SellerDashboardLayout.vue';
import RegionSelector from '@/components/RegionSelector.vue';
import {
  Upload, FileText, CheckCircle, Clock, XCircle, Trash2, AlertCircle,
  Building2, FileCheck, Loader2, Camera, ImagePlus, X, MapPin, Landmark, ShieldCheck
} from 'lucide-vue-next';

interface SelectOption {
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
  slug: string | null;
  phone: string | null;
  tagline: string | null;
  description: string | null;
  type: string | null;
  tax_status: string | null;
  bumn_partner: string | null;
  province_id: number | null;
  city_id: number | null;
  district_id: number | null;
  province: string | null;
  city: string | null;
  district: string | null;
  postal_code: string | null;
  address_line: string | null;
  is_umkm: boolean;
  response_time_label: string | null;
  bank_name: string | null;
  bank_account_number: string | null;
  bank_account_name: string | null;
  logo_url: string | null;
  banner_url: string | null;
}

const props = defineProps<{
  store: StoreData;
  sellerDocument: SellerDocumentData | null;
  typeOptions: SelectOption[];
  taxStatusOptions: SelectOption[];
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
  // Store Identity
  name: props.store?.name || '',
  type: props.store?.type || 'umkm',
  tax_status: props.store?.tax_status || 'non_pkp',
  phone: props.store?.phone || '',
  tagline: props.store?.tagline || '',
  description: props.store?.description || '',
  bumn_partner: props.store?.bumn_partner || '',
  is_umkm: props.store?.is_umkm ?? true,

  // Address
  province_id: props.store?.province_id || null,
  city_id: props.store?.city_id || null,
  district_id: props.store?.district_id || null,
  postal_code: props.store?.postal_code || '',
  address_line: props.store?.address_line || '',
  response_time_label: props.store?.response_time_label || '',

  // Bank
  bank_name: props.store?.bank_name || '',
  bank_account_number: props.store?.bank_account_number || '',
  bank_account_name: props.store?.bank_account_name || '',

  // Images
  logo: null as File | null,
  banner: null as File | null,

  // Documents
  ktp: null as File | null,
  npwp: null as File | null,
  nib: null as File | null,
  company_statement: null as File | null,
  supporting_documents: [] as File[],
});

// Logo & Banner previews
const logoPreview = ref<string | null>(props.store?.logo_url || null);
const bannerPreview = ref<string | null>(props.store?.banner_url || null);
const logoInput = ref<HTMLInputElement | null>(null);
const bannerInput = ref<HTMLInputElement | null>(null);

// Document previews
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
    !!form.logo ||
    !!form.banner ||
    (form.supporting_documents?.length ?? 0) > 0,
);

const handleLogoChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0];
  if (file) {
    form.logo = file;
    revokeIfBlob(logoPreview.value);
    logoPreview.value = URL.createObjectURL(file);
  }
};

const handleBannerChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0];
  if (file) {
    form.banner = file;
    revokeIfBlob(bannerPreview.value);
    bannerPreview.value = URL.createObjectURL(file);
  }
};

const removeLogo = () => {
  form.logo = null;
  logoPreview.value = props.store.logo_url;
  if (logoInput.value) logoInput.value.value = '';
};

const removeBanner = () => {
  form.banner = null;
  bannerPreview.value = props.store.banner_url;
  if (bannerInput.value) bannerInput.value.value = '';
};

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
      form.logo = null;
      form.banner = null;
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
        form.logo = null;
        form.banner = null;
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

// Watch for prop changes
watch(() => props.sellerDocument?.ktp_url, (url) => {
  if (!url) return;
  revokeIfBlob(ktpPreview.value);
  ktpPreview.value = url;
  ktpPreviewIsPdf.value = isPdf(url);
  ktpPreviewName.value = null;
});

watch(() => props.sellerDocument?.npwp_url, (url) => {
  if (!url) return;
  revokeIfBlob(npwpPreview.value);
  npwpPreview.value = url;
  npwpPreviewIsPdf.value = isPdf(url);
  npwpPreviewName.value = null;
});

watch(() => props.sellerDocument?.nib_url, (url) => {
  if (!url) return;
  revokeIfBlob(nibPreview.value);
  nibPreview.value = url;
  nibPreviewIsPdf.value = isPdf(url);
  nibPreviewName.value = null;
});

watch(() => props.sellerDocument?.company_statement_url, (url) => {
  if (!url) return;
  revokeIfBlob(companyStatementPreview.value);
  companyStatementPreview.value = url;
  companyStatementPreviewIsPdf.value = isPdf(url);
  companyStatementPreviewName.value = null;
});

watch(() => props.store?.logo_url, (url) => {
  if (!form.logo) logoPreview.value = url;
});

watch(() => props.store?.banner_url, (url) => {
  if (!form.banner) bannerPreview.value = url;
});
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
              Siapkan informasi dan dokumen berikut untuk verifikasi toko Anda. Setelah diverifikasi, Anda dapat mulai
              berjualan.
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

        <!-- Foto & Banner Toko -->
        <div class="rounded-lg border border-slate-200 bg-white p-6">
          <div class="mb-4 flex items-center gap-2">
            <ImagePlus class="h-5 w-5 text-sky-600" />
            <h2 class="text-lg font-semibold text-slate-900">Foto & Banner Toko <span class="text-red-500">*</span></h2>
          </div>

          <div class="space-y-6">
            <!-- Logo Upload -->
            <div class="space-y-2">
              <label class="text-sm font-medium text-slate-700">Logo Toko <span class="text-red-500">*</span></label>
              <div class="flex items-center gap-6">
                <div
                  class="relative h-20 w-20 flex-shrink-0 cursor-pointer overflow-hidden rounded-full border-2 border-dashed border-slate-200 bg-slate-50 transition-colors hover:border-sky-400 hover:bg-slate-100"
                  :class="{ 'pointer-events-none opacity-60': !canEdit }" @click="canEdit && logoInput?.click()">
                  <img v-if="logoPreview" :src="logoPreview" alt="Logo" class="h-full w-full object-cover" />
                  <div v-else class="flex h-full flex-col items-center justify-center gap-1 text-slate-400">
                    <Camera class="h-6 w-6" />
                    <span class="text-[10px] font-medium">Upload</span>
                  </div>
                  <button v-if="logoPreview && form.logo && canEdit" type="button" @click.stop="removeLogo"
                    class="absolute -right-1 -top-1 rounded-full bg-red-500 p-1 text-white shadow-md transition-colors hover:bg-red-600">
                    <X class="h-3 w-3" />
                  </button>
                </div>
                <div class="text-sm text-slate-500">
                  <p class="font-medium text-slate-700">Upload logo toko</p>
                  <p>Format: JPG, PNG, WEBP. Maksimal 2MB.</p>
                </div>
              </div>
              <input ref="logoInput" type="file" accept="image/jpeg,image/png,image/webp" class="hidden"
                @change="handleLogoChange" :disabled="!canEdit" />
              <p v-if="form.errors.logo" class="text-xs text-red-500">{{ form.errors.logo }}</p>
            </div>

            <!-- Banner Upload -->
            <div class="space-y-2">
              <label class="text-sm font-medium text-slate-700">Banner Toko <span class="text-red-500">*</span></label>
              <div class="relative">
                <div
                  class="relative h-40 w-full cursor-pointer overflow-hidden rounded-lg border-2 border-dashed border-slate-200 bg-slate-50 transition-colors hover:border-sky-400 hover:bg-slate-100"
                  :class="{ 'pointer-events-none opacity-60': !canEdit }" @click="canEdit && bannerInput?.click()">
                  <img v-if="bannerPreview" :src="bannerPreview" alt="Banner" class="h-full w-full object-cover" />
                  <div v-else class="flex h-full flex-col items-center justify-center gap-2 text-slate-400">
                    <ImagePlus class="h-10 w-10" />
                    <span class="text-sm font-medium">Klik untuk upload banner (1200 x 400 px)</span>
                  </div>
                </div>
                <button v-if="bannerPreview && form.banner && canEdit" type="button" @click.stop="removeBanner"
                  class="absolute right-2 top-2 rounded-full bg-red-500 p-1.5 text-white shadow-md transition-colors hover:bg-red-600">
                  <X class="h-4 w-4" />
                </button>
              </div>
              <input ref="bannerInput" type="file" accept="image/jpeg,image/png,image/webp" class="hidden"
                @change="handleBannerChange" :disabled="!canEdit" />
              <p v-if="form.errors.banner" class="text-xs text-red-500">{{ form.errors.banner }}</p>
            </div>
          </div>
        </div>

        <!-- Identitas Toko -->
        <div class="rounded-lg border border-slate-200 bg-white p-6">
          <div class="mb-4 flex items-center gap-2">
            <ShieldCheck class="h-5 w-5 text-sky-600" />
            <h2 class="text-lg font-semibold text-slate-900">Identitas Toko <span class="text-red-500">*</span></h2>
          </div>

          <div class="grid gap-4 sm:grid-cols-2">
            <div class="space-y-2 sm:col-span-2">
              <label class="text-sm font-medium text-slate-700">Nama Toko <span class="text-red-500">*</span></label>
              <input type="text" v-model="form.name" :disabled="!canEdit"
                class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-sky-400 focus:outline-none disabled:bg-slate-50 disabled:text-slate-500"
                placeholder="Nama toko" />
              <p v-if="form.errors.name" class="text-xs text-red-500">{{ form.errors.name }}</p>
            </div>

            <div class="space-y-2">
              <label class="text-sm font-medium text-slate-700">Nomor Telepon <span
                  class="text-red-500">*</span></label>
              <input type="tel" v-model="form.phone" :disabled="!canEdit"
                class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-sky-400 focus:outline-none disabled:bg-slate-50 disabled:text-slate-500"
                placeholder="Contoh: 081234567890" />
              <p v-if="form.errors.phone" class="text-xs text-red-500">{{ form.errors.phone }}</p>
            </div>

            <div class="space-y-2">
              <label class="text-sm font-medium text-slate-700">Tagline <span class="text-red-500">*</span></label>
              <input type="text" v-model="form.tagline" :disabled="!canEdit"
                class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-sky-400 focus:outline-none disabled:bg-slate-50 disabled:text-slate-500"
                placeholder="Contoh: UMKM pangan sehat" />
              <p v-if="form.errors.tagline" class="text-xs text-red-500">{{ form.errors.tagline }}</p>
            </div>

            <div class="space-y-2 sm:col-span-2">
              <label class="text-sm font-medium text-slate-700">Deskripsi <span class="text-red-500">*</span></label>
              <textarea v-model="form.description" :disabled="!canEdit" rows="3"
                class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-sky-400 focus:outline-none disabled:bg-slate-50 disabled:text-slate-500"
                placeholder="Ceritakan keunggulan toko, jenis produk, atau layanan yang ditawarkan."></textarea>
              <p v-if="form.errors.description" class="text-xs text-red-500">{{ form.errors.description }}</p>
            </div>
          </div>
        </div>

        <!-- Jenis Toko & Perpajakan -->
        <div class="rounded-lg border border-slate-200 bg-white p-6">
          <div class="mb-4 flex items-center gap-2">
            <Building2 class="h-5 w-5 text-sky-600" />
            <h2 class="text-lg font-semibold text-slate-900">Jenis Toko & Perpajakan <span class="text-red-500">*</span>
            </h2>
          </div>

          <div class="grid gap-4 sm:grid-cols-2">
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

            <div class="space-y-2">
              <label class="text-sm font-medium text-slate-700">Status Pajak <span class="text-red-500">*</span></label>
              <select v-model="form.tax_status" :disabled="!canEdit"
                class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:border-sky-400 focus:outline-none disabled:bg-slate-50 disabled:text-slate-500">
                <option value="">Pilih status pajak</option>
                <option v-for="status in taxStatusOptions" :key="status.value" :value="status.value">
                  {{ status.label }}
                </option>
              </select>
              <p v-if="form.errors.tax_status" class="text-xs text-red-500">{{ form.errors.tax_status }}</p>
            </div>

            <div class="space-y-2 sm:col-span-2">
              <label class="text-sm font-medium text-slate-700">Mitra BUMN (opsional)</label>
              <input type="text" v-model="form.bumn_partner" :disabled="!canEdit"
                class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-sky-400 focus:outline-none disabled:bg-slate-50 disabled:text-slate-500"
                placeholder="Contoh: PT PLN Persero" />
              <p v-if="form.errors.bumn_partner" class="text-xs text-red-500">{{ form.errors.bumn_partner }}</p>
            </div>

            <div class="sm:col-span-2">
              <label
                class="flex items-center justify-between rounded-md border border-slate-200 bg-slate-50/70 px-4 py-3"
                :class="{ 'opacity-60': !canEdit }">
                <div>
                  <p class="text-sm font-medium text-slate-800">Toko UMKM</p>
                  <p class="text-xs text-slate-500">Tandai jika toko Anda merupakan UMKM.</p>
                </div>
                <input type="checkbox" v-model="form.is_umkm" :disabled="!canEdit"
                  class="h-5 w-5 rounded border-slate-300 text-sky-600 focus:ring-sky-500" />
              </label>
            </div>
          </div>
        </div>

        <!-- Rekening Bank -->
        <div class="rounded-lg border border-slate-200 bg-white p-6">
          <div class="mb-4 flex items-center gap-2">
            <Landmark class="h-5 w-5 text-sky-600" />
            <h2 class="text-lg font-semibold text-slate-900">Rekening Bank <span class="text-red-500">*</span></h2>
          </div>

          <div class="grid gap-4 sm:grid-cols-3">
            <div class="space-y-2">
              <label class="text-sm font-medium text-slate-700">Nama Bank <span class="text-red-500">*</span></label>
              <input type="text" v-model="form.bank_name" :disabled="!canEdit"
                class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-sky-400 focus:outline-none disabled:bg-slate-50 disabled:text-slate-500"
                placeholder="Contoh: BCA, Mandiri, BRI" />
              <p v-if="form.errors.bank_name" class="text-xs text-red-500">{{ form.errors.bank_name }}</p>
            </div>

            <div class="space-y-2">
              <label class="text-sm font-medium text-slate-700">Nomor Rekening <span
                  class="text-red-500">*</span></label>
              <input type="text" v-model="form.bank_account_number" :disabled="!canEdit"
                class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-sky-400 focus:outline-none disabled:bg-slate-50 disabled:text-slate-500"
                placeholder="Contoh: 1234567890" />
              <p v-if="form.errors.bank_account_number" class="text-xs text-red-500">{{ form.errors.bank_account_number
              }}</p>
            </div>

            <div class="space-y-2">
              <label class="text-sm font-medium text-slate-700">Atas Nama <span class="text-red-500">*</span></label>
              <input type="text" v-model="form.bank_account_name" :disabled="!canEdit"
                class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-sky-400 focus:outline-none disabled:bg-slate-50 disabled:text-slate-500"
                placeholder="Nama pemilik rekening" />
              <p v-if="form.errors.bank_account_name" class="text-xs text-red-500">{{ form.errors.bank_account_name }}
              </p>
            </div>
          </div>
        </div>

        <!-- Alamat & Respon -->
        <div class="rounded-lg border border-slate-200 bg-white p-6">
          <div class="mb-4 flex items-center gap-2">
            <MapPin class="h-5 w-5 text-sky-600" />
            <h2 class="text-lg font-semibold text-slate-900">Alamat & Respon <span class="text-red-500">*</span></h2>
          </div>

          <div class="space-y-4">
            <RegionSelector v-model:provinceId="form.province_id" v-model:cityId="form.city_id"
              v-model:districtId="form.district_id" :show-district="true" :disabled="!canEdit || form.processing"
              :errors="{
                provinceId: form.errors.province_id,
                cityId: form.errors.city_id,
                districtId: form.errors.district_id,
              }" :initial-province-name="store.province" :initial-city-name="store.city"
              :initial-district-name="store.district" />

            <div class="grid gap-4 sm:grid-cols-2">
              <div class="space-y-2">
                <label class="text-sm font-medium text-slate-700">Kode Pos <span class="text-red-500">*</span></label>
                <input type="text" v-model="form.postal_code" :disabled="!canEdit"
                  class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-sky-400 focus:outline-none disabled:bg-slate-50 disabled:text-slate-500"
                  placeholder="Kode pos" />
                <p v-if="form.errors.postal_code" class="text-xs text-red-500">{{ form.errors.postal_code }}</p>
              </div>

              <div class="space-y-2">
                <label class="text-sm font-medium text-slate-700">Respon Toko <span
                    class="text-red-500">*</span></label>
                <input type="text" v-model="form.response_time_label" :disabled="!canEdit"
                  class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-sky-400 focus:outline-none disabled:bg-slate-50 disabled:text-slate-500"
                  placeholder="Contoh: 24 jam, 1x24 jam" />
                <p v-if="form.errors.response_time_label" class="text-xs text-red-500">{{
                  form.errors.response_time_label }}</p>
              </div>
            </div>

            <div class="space-y-2">
              <label class="text-sm font-medium text-slate-700">Alamat Lengkap <span
                  class="text-red-500">*</span></label>
              <textarea v-model="form.address_line" :disabled="!canEdit" rows="2"
                class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-sky-400 focus:outline-none disabled:bg-slate-50 disabled:text-slate-500"
                placeholder="Alamat lengkap toko"></textarea>
              <p v-if="form.errors.address_line" class="text-xs text-red-500">{{ form.errors.address_line }}</p>
            </div>
          </div>
        </div>

        <!-- Required Documents -->
        <div class="rounded-lg border border-slate-200 bg-white p-6">
          <div class="mb-4 flex items-center gap-2">
            <FileCheck class="h-5 w-5 text-sky-600" />
            <h2 class="text-lg font-semibold text-slate-900">Dokumen Wajib</h2>
          </div>

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
