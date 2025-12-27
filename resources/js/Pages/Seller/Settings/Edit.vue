<script setup lang="ts">
import SellerDashboardLayout from '@/Layouts/SellerDashboardLayout.vue';
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Badge } from '@/components/ui/badge';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Separator } from '@/components/ui/separator';
import { CheckCircle2, ShieldCheck, Camera, ImagePlus, X, Building2, MapPin, Landmark, FileText, Clock, XCircle, FileCheck, Upload, Trash2, Loader2, AlertCircle, ChevronRight, RotateCcw } from 'lucide-vue-next';
import RegionSelector from '@/components/RegionSelector.vue';
import AlertBanner from '@/components/AlertBanner.vue';

type SelectOption = {
  value: string;
  label: string;
};

type StorePayload = {
  id?: number;
  name: string;
  slug: string;
  phone: string | null;
  tagline: string | null;
  description: string | null;
  type: string;
  tax_status: string;
  bumn_partner: string | null;
  city: string | null;
  province: string | null;
  district: string | null;
  province_id: number | null;
  city_id: number | null;
  district_id: number | null;
  postal_code: string | null;
  address_line: string | null;
  is_umkm: boolean;
  response_time_label: string | null;
  bank_name: string | null;
  bank_account_number: string | null;
  bank_account_name: string | null;
  logo_url: string | null;
  banner_url: string | null;
};

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

const props = defineProps<{
  store: StorePayload;
  hasStore: boolean;
  typeOptions: SelectOption[];
  taxStatusOptions: SelectOption[];
  sellerDocument: SellerDocumentData | null;
}>();

defineOptions({
  layout: SellerDashboardLayout,
});

const page = usePage();
const flash = computed(() => (page.props.flash ?? {}) as Record<string, string>);
const flashSuccess = computed(() => flash.value.success ?? '');
const flashInfo = computed(() => flash.value.info ?? '');
const flashError = computed(() => flash.value.error ?? '');
const showSuccess = ref(false);

// Watch flash object directly to detect new messages even if content is the same
watch(() => page.props.flash, (newFlash) => {
  const flashData = newFlash as Record<string, string> | undefined;
  if (flashData?.success) {
    showSuccess.value = true;
    setTimeout(() => {
      showSuccess.value = false;
    }, 5000);
  }
}, { deep: true, immediate: true });

// Document helpers
const isPdf = (value: string) => value.toLowerCase().endsWith('.pdf');

const getStatusIcon = (status: string) => {
  if (status === 'approved') return CheckCircle2;
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

const getSubmissionStatusLabel = (status: string) => {
  if (status === 'approved') return 'Terverifikasi';
  if (status === 'rejected') return 'Ditolak';
  if (status === 'submitted') return 'Menunggu Verifikasi';
  return 'Draft';
};

const buildFormState = (store: StorePayload) => ({
  name: store.name ?? '',
  slug: store.slug ?? '',
  phone: store.phone ?? '',
  tagline: store.tagline ?? '',
  description: store.description ?? '',
  type: store.type ?? props.typeOptions[0]?.value ?? 'umkm',
  tax_status: store.tax_status ?? props.taxStatusOptions[0]?.value ?? 'non_pkp',
  bumn_partner: store.bumn_partner ?? '',
  province_id: store.province_id ?? null,
  city_id: store.city_id ?? null,
  district_id: store.district_id ?? null,
  postal_code: store.postal_code ?? '',
  address_line: store.address_line ?? '',
  is_umkm: store.is_umkm ?? true,
  response_time_label: store.response_time_label ?? '',
  bank_name: store.bank_name ?? '',
  bank_account_number: store.bank_account_number ?? '',
  bank_account_name: store.bank_account_name ?? '',
  logo: null as File | null,
  banner: null as File | null,
  // Documents
  ktp: null as File | null,
  npwp: null as File | null,
  nib: null as File | null,
  company_statement: null as File | null,
  supporting_documents: [] as File[],
});

const form = useForm(buildFormState(props.store));

// Image preview states
const logoPreview = ref<string | null>(props.store.logo_url);
const bannerPreview = ref<string | null>(props.store.banner_url);
const logoInput = ref<HTMLInputElement | null>(null);
const bannerInput = ref<HTMLInputElement | null>(null);

// Document preview states
const ktpPreview = ref<string | null>(props.sellerDocument?.ktp_url || null);
const ktpPreviewIsPdf = ref(!!ktpPreview.value && isPdf(ktpPreview.value));
const npwpPreview = ref<string | null>(props.sellerDocument?.npwp_url || null);
const npwpPreviewIsPdf = ref(!!npwpPreview.value && isPdf(npwpPreview.value));
const nibPreview = ref<string | null>(props.sellerDocument?.nib_url || null);
const nibPreviewIsPdf = ref(!!nibPreview.value && isPdf(nibPreview.value));
const companyStatementPreview = ref<string | null>(props.sellerDocument?.company_statement_url || null);
const companyStatementPreviewIsPdf = ref(!!companyStatementPreview.value && isPdf(companyStatementPreview.value));

// Submission states
const isSubmitting = ref(false);
const localError = ref('');
const isDraft = computed(() => !props.sellerDocument || props.sellerDocument?.submission_status === 'draft');
const isSubmitted = computed(() => props.sellerDocument?.submission_status === 'submitted');
const isApproved = computed(() => props.sellerDocument?.submission_status === 'approved');
const isRejected = computed(() => props.sellerDocument?.submission_status === 'rejected');
const canEdit = computed(() => isDraft.value || isRejected.value);

const handleLogoChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0];
  if (file) {
    form.logo = file;
    logoPreview.value = URL.createObjectURL(file);
  }
};

const handleBannerChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0];
  if (file) {
    form.banner = file;
    bannerPreview.value = URL.createObjectURL(file);
  }
};

const removeLogo = () => {
  form.logo = null;
  logoPreview.value = props.store.logo_url;
  if (logoInput.value) {
    logoInput.value.value = '';
  }
};

const removeBanner = () => {
  form.banner = null;
  bannerPreview.value = props.store.banner_url;
  if (bannerInput.value) {
    bannerInput.value.value = '';
  }
};

// Document upload handlers
const handleFileChange = (event: Event, field: 'ktp' | 'npwp' | 'nib' | 'company_statement') => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0];
  if (file) {
    form[field] = file;
    const url = URL.createObjectURL(file);
    const fileIsPdf = file.type === 'application/pdf' || isPdf(file.name);

    if (field === 'ktp') {
      ktpPreview.value = url;
      ktpPreviewIsPdf.value = fileIsPdf;
    }
    if (field === 'npwp') {
      npwpPreview.value = url;
      npwpPreviewIsPdf.value = fileIsPdf;
    }
    if (field === 'nib') {
      nibPreview.value = url;
      nibPreviewIsPdf.value = fileIsPdf;
    }
    if (field === 'company_statement') {
      companyStatementPreview.value = url;
      companyStatementPreviewIsPdf.value = fileIsPdf;
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

const deleteSupportingDoc = (mediaId: number) => {
  if (confirm('Hapus dokumen ini?')) {
    router.delete(`/seller/settings/documents/supporting/${mediaId}`, {
      preserveScroll: true,
    });
  }
};

const submit = () => {
  localError.value = '';
  if (props.hasStore) {
    form.transform((data) => {
      const formData: Record<string, unknown> = {
        ...data,
        _method: 'PUT',
      };
      // Remove null files to avoid sending empty values
      if (!formData.logo) delete formData.logo;
      if (!formData.banner) delete formData.banner;
      if (!formData.ktp) delete formData.ktp;
      if (!formData.npwp) delete formData.npwp;
      if (!formData.nib) delete formData.nib;
      if (!formData.company_statement) delete formData.company_statement;
      if (!formData.supporting_documents || (formData.supporting_documents as File[]).length === 0) {
        delete formData.supporting_documents;
      }
      return formData;
    }).post('/seller/settings', {
      preserveScroll: true,
      forceFormData: true,
      onSuccess: () => {
        // Reset file inputs after success
        form.ktp = null;
        form.npwp = null;
        form.nib = null;
        form.company_statement = null;
        form.supporting_documents = [];
      },
    });
  } else {
    form.transform((data) => {
      const formData: Record<string, unknown> = { ...data };
      if (!formData.logo) delete formData.logo;
      if (!formData.banner) delete formData.banner;
      if (!formData.ktp) delete formData.ktp;
      if (!formData.npwp) delete formData.npwp;
      if (!formData.nib) delete formData.nib;
      if (!formData.company_statement) delete formData.company_statement;
      if (!formData.supporting_documents || (formData.supporting_documents as File[]).length === 0) {
        delete formData.supporting_documents;
      }
      return formData;
    }).post('/seller/settings', { preserveScroll: true, forceFormData: true });
  }
};

const submitForReview = () => {
  localError.value = '';

  // Validate required documents
  const hasKtp = form.ktp || ktpPreview.value;
  const hasNpwp = form.npwp || npwpPreview.value;
  const hasNib = form.nib || nibPreview.value;

  if (!hasKtp || !hasNpwp || !hasNib) {
    const missingDocs = [];
    if (!hasKtp) missingDocs.push('KTP');
    if (!hasNpwp) missingDocs.push('NPWP');
    if (!hasNib) missingDocs.push('NIB');

    localError.value = `Dokumen wajib belum lengkap: ${missingDocs.join(', ')}. Silakan upload dokumen yang diperlukan sebelum submit untuk verifikasi.`;

    // Scroll to documents section
    const docSection = document.querySelector('[class*="Dokumen Verifikasi"]');
    if (docSection) {
      docSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    return;
  }

  isSubmitting.value = true;

  // Save first, then submit
  form.transform((data) => {
    const formData: Record<string, unknown> = {
      ...data,
      _method: 'PUT',
    };
    if (!formData.logo) delete formData.logo;
    if (!formData.banner) delete formData.banner;
    if (!formData.ktp) delete formData.ktp;
    if (!formData.npwp) delete formData.npwp;
    if (!formData.nib) delete formData.nib;
    if (!formData.company_statement) delete formData.company_statement;
    if (!formData.supporting_documents || (formData.supporting_documents as File[]).length === 0) {
      delete formData.supporting_documents;
    }
    return formData;
  }).post('/seller/settings', {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      // After save, submit for review
      router.post('/seller/settings/submit', {}, {
        preserveScroll: true,
        onFinish: () => {
          isSubmitting.value = false;
        },
      });
    },
    onError: () => {
      isSubmitting.value = false;
    },
  });
};


const generateSlug = (value?: string | number | null) =>
  value
    ?.toString()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '') ?? '';

const originalSlug = computed(() => props.store.slug ?? '');

// Track if user manually edited the slug
const slugManuallyEdited = ref(false);

// Watch for manual slug changes
watch(
  () => form.slug,
  (newSlug, oldSlug) => {
    // If user manually changes the slug (not triggered by auto-generation)
    if (newSlug !== generateSlug(form.name) && newSlug !== oldSlug) {
      slugManuallyEdited.value = true;
    }
  }
);

// Auto-generate slug from name (realtime)
watch(
  () => form.name,
  (value) => {
    if (form.processing) {
      return;
    }

    // If user has manually edited the slug, don't auto-update
    if (slugManuallyEdited.value) {
      return;
    }

    const newSlug = generateSlug(value);
    form.slug = newSlug || originalSlug.value;
  },
);

// Reset slug to auto-generated value
const resetSlug = () => {
  slugManuallyEdited.value = false;
  form.slug = generateSlug(form.name) || originalSlug.value;
};

watch(
  () => props.store,
  (nextStore) => {
    if (!nextStore) {
      return;
    }

    const nextState = buildFormState(nextStore);
    form.defaults(nextState);
    form.reset();
    Object.assign(form, nextState);
    form.clearErrors();

    // Reset slug manual edit flag
    slugManuallyEdited.value = false;

    // Update previews
    logoPreview.value = nextStore.logo_url;
    bannerPreview.value = nextStore.banner_url;
  },
  { deep: true, immediate: true },
);

// Watch for sellerDocument changes
watch(
  () => props.sellerDocument,
  (nextDoc) => {
    if (nextDoc) {
      ktpPreview.value = nextDoc.ktp_url || null;
      ktpPreviewIsPdf.value = !!ktpPreview.value && isPdf(ktpPreview.value);
      npwpPreview.value = nextDoc.npwp_url || null;
      npwpPreviewIsPdf.value = !!npwpPreview.value && isPdf(npwpPreview.value);
      nibPreview.value = nextDoc.nib_url || null;
      nibPreviewIsPdf.value = !!nibPreview.value && isPdf(nibPreview.value);
      companyStatementPreview.value = nextDoc.company_statement_url || null;
      companyStatementPreviewIsPdf.value = !!companyStatementPreview.value && isPdf(companyStatementPreview.value);
    }
  },
  { deep: true },
);
</script>

<template>
  <div class="min-h-screen bg-slate-50/50 pt-0">

    <Head title="Kelola Toko" />

    <div class="px-4 py-8 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Kelola Toko</h1>
            <p class="mt-1 text-sm text-slate-500">Lengkapi informasi toko dan dokumen verifikasi</p>
          </div>
          <Button variant="ghost" size="sm" as-child class="self-start">
            <Link href="/seller/dashboard" class="gap-2">
              <ChevronRight class="h-4 w-4 rotate-180" />
              Dashboard
            </Link>
          </Button>
        </div>
      </div>

      <!-- Floating Success Alert -->
      <Teleport to="body">
        <div v-if="showSuccess && flashSuccess"
          class="fixed top-20 left-1/2 -translate-x-1/2 z-[9999] min-w-[600px] max-w-2xl shadow-lg rounded-lg overflow-hidden">
          <AlertBanner type="success" :message="flashSuccess" :show="showSuccess" :dismissible="true"
            @close="showSuccess = false" />
        </div>
      </Teleport>

      <!-- Alerts -->

      <div v-if="flashError" class="mb-6">
        <div class="flex items-center gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3">
          <AlertCircle class="h-5 w-5 flex-shrink-0 text-red-600" />
          <p class="text-sm text-red-800">{{ flashError }}</p>
        </div>
      </div>

      <div v-if="flashInfo" class="mb-6 w-full max-w-4xl min-w-[800px] mx-auto">
        <div class="flex items-center gap-3 rounded-lg border border-slate-200 bg-white px-4 py-3">
          <AlertCircle class="h-5 w-5 flex-shrink-0 text-slate-500" />
          <p class="text-sm text-slate-700">{{ flashInfo }}</p>
        </div>
      </div>

      <form @submit.prevent="submit" class="space-y-8">
        <!-- Store Branding Card -->
        <Card class="border-0 shadow-sm">
          <CardHeader class="border-b bg-slate-50/50">
            <div class="flex items-center gap-2">
              <ImagePlus class="h-5 w-5 text-indigo-600" />
              <CardTitle>Foto & Banner Toko <span class="text-red-500">*</span></CardTitle>
            </div>
          </CardHeader>
          <CardContent class="space-y-6">
            <!-- Logo Upload (moved to top) -->
            <div class="space-y-2">
              <Label>Logo Toko <span class="text-red-500">*</span></Label>
              <div class="flex items-center gap-6">
                <div
                  class="relative h-20 w-20 overflow-hidden rounded-full border-2 border-dashed border-slate-200 bg-slate-50 hover:border-indigo-400 hover:bg-slate-100 transition-colors cursor-pointer flex-shrink-0"
                  @click="logoInput?.click()">
                  <img v-if="logoPreview" :src="logoPreview" alt="Store Logo" class="h-full w-full object-cover" />
                  <div v-else class="flex h-full flex-col items-center justify-center gap-1 text-slate-400">
                    <Camera class="h-6 w-6" />
                    <span class="text-[10px] font-medium">Upload</span>
                  </div>
                  <button v-if="logoPreview && form.logo" type="button" @click.stop="removeLogo"
                    class="absolute -top-1 -right-1 rounded-full bg-red-500 p-1 text-white shadow-md hover:bg-red-600 transition-colors">
                    <X class="h-3 w-3" />
                  </button>
                </div>
                <div class="text-xs text-slate-500">
                  <p class="font-medium text-slate-700">Upload logo toko</p>
                  <p>Format: JPG, PNG, WEBP. Maksimal 2MB.</p>
                  <p>Ukuran yang disarankan: 200 x 200 px</p>
                </div>
              </div>
              <input ref="logoInput" type="file" accept="image/jpeg,image/png,image/webp" class="hidden"
                @change="handleLogoChange" />
              <p v-if="form.errors.logo" class="text-xs text-red-600">{{ form.errors.logo }}</p>
            </div>

            <!-- Banner Upload -->
            <div class="space-y-2">
              <Label>Hero Banner <span class="text-red-500">*</span></Label>
              <div class="relative">
                <div
                  class="relative h-64 w-full overflow-hidden rounded-lg border-2 border-dashed border-slate-200 bg-slate-50 hover:border-indigo-400 hover:bg-slate-100 transition-colors cursor-pointer"
                  @click="bannerInput?.click()">
                  <img v-if="bannerPreview" :src="bannerPreview" alt="Store Banner"
                    class="h-full w-full object-cover" />
                  <div v-else class="flex h-full flex-col items-center justify-center gap-2 text-slate-400">
                    <ImagePlus class="h-10 w-10" />
                    <span class="text-sm font-medium">Klik untuk upload banner (1200 x 400 px)</span>
                  </div>
                </div>
                <button v-if="bannerPreview && form.banner" type="button" @click.stop="removeBanner"
                  class="absolute top-2 right-2 rounded-full bg-red-500 p-1.5 text-white shadow-md hover:bg-red-600 transition-colors">
                  <X class="h-4 w-4" />
                </button>
              </div>
              <input ref="bannerInput" type="file" accept="image/jpeg,image/png,image/webp" class="hidden"
                @change="handleBannerChange" />
              <p v-if="form.errors.banner" class="text-xs text-red-600">{{ form.errors.banner }}</p>
            </div>
          </CardContent>
        </Card>

        <!-- Grid for side-by-side cards -->
        <div class="grid gap-6 lg:grid-cols-2">
          <Card class="border-0 shadow-sm">
            <CardHeader class="border-b bg-slate-50/50">
              <div class="flex items-center gap-2">
                <ShieldCheck class="h-5 w-5 text-indigo-600" />
                <CardTitle>Identitas Toko <span class="text-red-500">*</span></CardTitle>
              </div>
            </CardHeader>
            <CardContent class="space-y-4 pt-6">
              <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-2 sm:col-span-2">
                  <Label for="name">Nama Toko <span class="text-red-500">*</span></Label>
                  <Input id="name" v-model="form.name" placeholder="Nama toko"
                    :class="form.errors.name ? 'border-red-500' : ''" />
                  <p v-if="form.errors.name" class="text-xs text-red-600">
                    {{ form.errors.name }}
                  </p>
                </div>

                <div class="space-y-2 sm:col-span-2">
                  <div class="flex items-center justify-between">
                    <Label for="slug">Slug</Label>
                    <button v-if="!hasStore && slugManuallyEdited" type="button" @click="resetSlug"
                      class="flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-700 font-medium transition-colors">
                      <RotateCcw class="h-3 w-3" />
                      Reset ke Auto
                    </button>
                  </div>
                  <div class="relative">
                    <Input id="slug" v-model="form.slug" :disabled="hasStore" :class="[
                      hasStore ? 'bg-slate-50' : (slugManuallyEdited ? 'bg-amber-50 border-amber-300' : 'bg-green-50 border-green-300'),
                      form.errors.slug ? 'border-red-500' : '',
                      'pr-20'
                    ]" />
                    <Badge :variant="hasStore ? 'secondary' : (slugManuallyEdited ? 'outline' : 'secondary')" :class="[
                      'absolute right-2 top-1/2 -translate-y-1/2 text-[11px]',
                      !hasStore && !slugManuallyEdited && 'bg-green-100 text-green-700 border-green-200',
                      !hasStore && slugManuallyEdited && 'bg-amber-100 text-amber-700 border-amber-200'
                    ]">
                      {{ hasStore ? 'Terkunci' : (slugManuallyEdited ? 'Manual' : 'Auto') }}
                    </Badge>
                  </div>
                  <p class="text-xs text-slate-500">
                    Slug digunakan di URL toko.
                    <span v-if="!hasStore && !slugManuallyEdited" class="text-green-600 font-medium">
                      Otomatis berubah saat Nama Toko diubah.
                    </span>
                    <span v-else-if="!hasStore && slugManuallyEdited" class="text-amber-600 font-medium">
                      Mode manual aktif.
                    </span>
                  </p>
                  <p v-if="form.errors.slug" class="text-xs text-red-600">
                    {{ form.errors.slug }}
                  </p>
                </div>

                <div class="space-y-2 sm:col-span-2">
                  <Label for="phone">Nomor Telepon <span class="text-red-500">*</span></Label>
                  <Input id="phone" v-model="form.phone" type="tel" placeholder="Contoh: 081234567890"
                    :class="form.errors.phone ? 'border-red-500' : ''" />
                  <p v-if="form.errors.phone" class="text-xs text-red-600">
                    {{ form.errors.phone }}
                  </p>
                </div>

                <div class="space-y-2 sm:col-span-2">
                  <Label for="tagline">Tagline <span class="text-red-500">*</span></Label>
                  <Input id="tagline" v-model="form.tagline" placeholder="Contoh: UMKM pangan sehat"
                    :class="form.errors.tagline ? 'border-red-500' : ''" />
                  <p v-if="form.errors.tagline" class="text-xs text-red-600">
                    {{ form.errors.tagline }}
                  </p>
                </div>

                <div class="space-y-2 sm:col-span-2">
                  <Label for="description">Deskripsi <span class="text-red-500">*</span></Label>
                  <Textarea id="description" v-model="form.description" rows="4"
                    placeholder="Ceritakan keunggulan toko, jenis produk, atau layanan yang ditawarkan."
                    :class="form.errors.description ? 'border-red-500' : ''" />
                  <p v-if="form.errors.description" class="text-xs text-red-600">
                    {{ form.errors.description }}
                  </p>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card class="border-0 shadow-sm">
            <CardHeader class="border-b bg-slate-50/50">
              <div class="flex items-center gap-2">
                <Building2 class="h-5 w-5 text-indigo-600" />
                <CardTitle>Jenis Toko & Perpajakan <span class="text-red-500">*</span></CardTitle>
              </div>
            </CardHeader>
            <CardContent class="space-y-4 pt-6">
              <!-- Jenis Toko - Full Width -->
              <div class="space-y-2">
                <Label for="type">Jenis Toko <span class="text-red-500">*</span></Label>
                <Select v-model="form.type" class="w-full">
                  <SelectTrigger id="type" class="w-full" :class="form.errors.type ? 'border-red-500' : ''">
                    <SelectValue placeholder="Pilih jenis" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="option in typeOptions" :key="option.value" :value="option.value">
                      {{ option.label }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <p v-if="form.errors.type" class="text-xs text-red-600">
                  {{ form.errors.type }}
                </p>
              </div>

              <!-- Status Pajak - Full Width -->
              <div class="space-y-2">
                <Label for="tax_status">Status Pajak <span class="text-red-500">*</span></Label>
                <Select v-model="form.tax_status" class="w-full">
                  <SelectTrigger id="tax_status" class="w-full" :class="form.errors.tax_status ? 'border-red-500' : ''">
                    <SelectValue placeholder="Pilih status pajak" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="option in taxStatusOptions" :key="option.value" :value="option.value">
                      {{ option.label }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <p v-if="form.errors.tax_status" class="text-xs text-red-600">
                  {{ form.errors.tax_status }}
                </p>
              </div>

              <div class="space-y-2">
                <Label for="bumn_partner">Mitra BUMN (opsional)</Label>
                <Input id="bumn_partner" v-model="form.bumn_partner" placeholder="Contoh: PT PLN Persero"
                  :class="form.errors.bumn_partner ? 'border-red-500' : ''" />
                <p v-if="form.errors.bumn_partner" class="text-xs text-red-600">
                  {{ form.errors.bumn_partner }}
                </p>
              </div>

              <label
                class="flex items-center justify-between rounded-md border border-slate-200 bg-slate-50/70 px-4 py-3">
                <div>
                  <p class="text-sm font-medium text-slate-800">Toko UMKM</p>
                  <p class="text-xs text-slate-500">Tandai jika toko Anda merupakan UMKM.</p>
                </div>
                <Switch v-model:checked="form.is_umkm" />
              </label>
            </CardContent>
          </Card>
        </div>

        <!-- Bank Account Card -->
        <Card class="border-0 shadow-sm">
          <CardHeader class="border-b bg-slate-50/50">
            <div class="flex items-center gap-2">
              <Landmark class="h-5 w-5 text-indigo-600" />
              <CardTitle>Rekening Bank <span class="text-red-500">*</span></CardTitle>
            </div>
          </CardHeader>
          <CardContent class="space-y-4 pt-6">
            <div class="grid gap-4 sm:grid-cols-3">
              <div class="space-y-2">
                <Label for="bank_name">Nama Bank <span class="text-red-500">*</span></Label>
                <Input id="bank_name" v-model="form.bank_name" placeholder="Contoh: BCA, Mandiri, BRI"
                  :class="form.errors.bank_name ? 'border-red-500' : ''" />
                <p v-if="form.errors.bank_name" class="text-xs text-red-600">
                  {{ form.errors.bank_name }}
                </p>
              </div>

              <div class="space-y-2">
                <Label for="bank_account_number">Nomor Rekening <span class="text-red-500">*</span></Label>
                <Input id="bank_account_number" v-model="form.bank_account_number" placeholder="Contoh: 1234567890"
                  :class="form.errors.bank_account_number ? 'border-red-500' : ''" />
                <p v-if="form.errors.bank_account_number" class="text-xs text-red-600">
                  {{ form.errors.bank_account_number }}
                </p>
              </div>

              <div class="space-y-2">
                <Label for="bank_account_name">Atas Nama <span class="text-red-500">*</span></Label>
                <Input id="bank_account_name" v-model="form.bank_account_name" placeholder="Nama pemilik rekening"
                  :class="form.errors.bank_account_name ? 'border-red-500' : ''" />
                <p v-if="form.errors.bank_account_name" class="text-xs text-red-600">
                  {{ form.errors.bank_account_name }}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card class="border-0 shadow-sm">
          <CardHeader class="border-b bg-slate-50/50">
            <div class="flex items-center gap-2">
              <MapPin class="h-5 w-5 text-indigo-600" />
              <CardTitle>Alamat & Respon <span class="text-red-500">*</span></CardTitle>
            </div>
          </CardHeader>
          <CardContent class="space-y-4 pt-6">
            <RegionSelector v-model:provinceId="form.province_id" v-model:cityId="form.city_id"
              v-model:districtId="form.district_id" :show-district="true" :disabled="form.processing" :errors="{
                provinceId: form.errors.province_id,
                cityId: form.errors.city_id,
                districtId: form.errors.district_id,
              }" :initial-province-name="props.store.province" :initial-city-name="props.store.city"
              :initial-district-name="props.store.district" />

            <div class="space-y-2">
              <Label for="postal_code">Kode Pos <span class="text-red-500">*</span></Label>
              <Input id="postal_code" v-model="form.postal_code" placeholder="Kode pos"
                :class="form.errors.postal_code ? 'border-red-500' : ''" />
              <p v-if="form.errors.postal_code" class="text-xs text-red-600">
                {{ form.errors.postal_code }}
              </p>
            </div>

            <div class="space-y-2">
              <Label for="address_line">Alamat Lengkap <span class="text-red-500">*</span></Label>
              <Textarea id="address_line" v-model="form.address_line" rows="3" placeholder="Alamat lengkap"
                :class="form.errors.address_line ? 'border-red-500' : ''" />
              <p v-if="form.errors.address_line" class="text-xs text-red-600">
                {{ form.errors.address_line }}
              </p>
            </div>

            <div class="space-y-2">
              <Label for="response_time_label">Respon Toko <span class="text-red-500">*</span></Label>
              <Input id="response_time_label" v-model="form.response_time_label"
                placeholder="Contoh: 24 jam, 1x24 jam, 2 jam"
                :class="form.errors.response_time_label ? 'border-red-500' : ''" />
              <p v-if="form.errors.response_time_label" class="text-xs text-red-600">
                {{ form.errors.response_time_label }}
              </p>
            </div>
          </CardContent>
          <Separator />
          <CardContent class="flex items-center justify-end">
            <div class="flex items-center gap-3">
              <Button type="button" variant="outline" as-child>
                <Link href="/seller/dashboard">Batal</Link>
              </Button>
              <Button type="submit" :disabled="form.processing">
                {{ hasStore ? 'Simpan perubahan' : 'Buat toko' }}
              </Button>
            </div>
          </CardContent>
        </Card>
      </form>

      <!-- Documents Section (Editable) -->
      <Card class="mt-8 border-0 shadow-sm">
        <CardHeader class="border-b bg-slate-50/50">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <FileCheck class="h-5 w-5 text-indigo-600" />
              <CardTitle>Dokumen Verifikasi <span class="text-red-500">*</span></CardTitle>
            </div>
            <div v-if="sellerDocument">
              <Badge v-if="sellerDocument.submission_status === 'approved'"
                class="bg-green-100 text-green-700 border-green-200 hover:bg-green-100 gap-1.5 pl-2 pr-3 py-1">
                <CheckCircle2 class="h-3.5 w-3.5" />
                <span>Terverifikasi</span>
              </Badge>
              <Badge v-else-if="sellerDocument.submission_status === 'submitted'"
                class="bg-amber-100 text-amber-700 border-amber-200 hover:bg-amber-100 gap-1.5 pl-2 pr-3 py-1">
                <Clock class="h-3.5 w-3.5" />
                <span>Menunggu Verifikasi</span>
              </Badge>
              <Badge v-else-if="sellerDocument.submission_status === 'rejected'"
                class="bg-red-100 text-red-700 border-red-200 hover:bg-red-100 gap-1.5 pl-2 pr-3 py-1">
                <XCircle class="h-3.5 w-3.5" />
                <span>Ditolak</span>
              </Badge>
              <Badge v-else variant="secondary">Draft</Badge>
            </div>
            <Badge v-else variant="secondary">Draft</Badge>
          </div>
        </CardHeader>
        <CardContent class="pt-6">
          <!-- Status Alert -->
          <div v-if="sellerDocument?.admin_notes" class="mb-4">
            <Alert :class="getStatusBg(sellerDocument.submission_status)">
              <component :is="getStatusIcon(sellerDocument.submission_status)" class="h-4 w-4" />
              <AlertTitle>Catatan Admin</AlertTitle>
              <AlertDescription>{{ sellerDocument.admin_notes }}</AlertDescription>
            </Alert>
          </div>

          <!-- Local Error -->
          <Alert v-if="localError" variant="destructive" class="mb-4">
            <AlertCircle class="h-4 w-4" />
            <AlertTitle>Error</AlertTitle>
            <AlertDescription>{{ localError }}</AlertDescription>
          </Alert>

          <!-- Required Documents Grid -->
          <div class="grid gap-6 sm:grid-cols-3">
            <!-- KTP -->
            <div class="space-y-2">
              <div class="flex items-center justify-between">
                <Label>KTP Pemilik <span class="text-red-500">*</span></Label>
                <span v-if="sellerDocument?.ktp_status"
                  :class="['text-xs font-medium', getStatusColor(sellerDocument.ktp_status)]">
                  {{ sellerDocument.ktp_status === 'approved' ? 'Disetujui' : sellerDocument.ktp_status === 'rejected' ?
                    'Ditolak' : 'Menunggu' }}
                </span>
              </div>
              <label
                class="group relative flex h-48 cursor-pointer flex-col items-center justify-center overflow-hidden rounded-lg border-2 border-dashed border-slate-200 bg-slate-50 transition-colors hover:border-indigo-400 hover:bg-slate-100"
                :class="{ 'pointer-events-none opacity-60': !canEdit }">
                <input type="file" accept="image/jpeg,image/png,image/webp,application/pdf" class="hidden"
                  @change="(e) => handleFileChange(e, 'ktp')" :disabled="!canEdit" />
                <div v-if="ktpPreview" class="h-full w-full">
                  <img v-if="!ktpPreviewIsPdf" :src="ktpPreview" alt="KTP" class="h-full w-full object-cover" />
                  <div v-else class="flex h-full flex-col items-center justify-center gap-1">
                    <FileText class="h-8 w-8 text-indigo-500" />
                    <span class="text-xs text-slate-500">PDF</span>
                  </div>
                </div>
                <div v-else class="flex flex-col items-center gap-2 text-slate-400">
                  <Upload class="h-6 w-6" />
                  <span class="text-xs">Upload KTP</span>
                </div>
              </label>
              <p v-if="form.errors.ktp" class="text-xs text-red-500">{{ form.errors.ktp }}</p>
            </div>

            <!-- NPWP -->
            <div class="space-y-2">
              <div class="flex items-center justify-between">
                <Label>NPWP <span class="text-red-500">*</span></Label>
                <span v-if="sellerDocument?.npwp_status"
                  :class="['text-xs font-medium', getStatusColor(sellerDocument.npwp_status)]">
                  {{ sellerDocument.npwp_status === 'approved' ? 'Disetujui' : sellerDocument.npwp_status === 'rejected'
                    ? 'Ditolak' : 'Menunggu' }}
                </span>
              </div>
              <label
                class="group relative flex h-48 cursor-pointer flex-col items-center justify-center overflow-hidden rounded-lg border-2 border-dashed border-slate-200 bg-slate-50 transition-colors hover:border-indigo-400 hover:bg-slate-100"
                :class="{ 'pointer-events-none opacity-60': !canEdit }">
                <input type="file" accept="image/jpeg,image/png,image/webp,application/pdf" class="hidden"
                  @change="(e) => handleFileChange(e, 'npwp')" :disabled="!canEdit" />
                <div v-if="npwpPreview" class="h-full w-full">
                  <img v-if="!npwpPreviewIsPdf" :src="npwpPreview" alt="NPWP" class="h-full w-full object-cover" />
                  <div v-else class="flex h-full flex-col items-center justify-center gap-1">
                    <FileText class="h-8 w-8 text-indigo-500" />
                    <span class="text-xs text-slate-500">PDF</span>
                  </div>
                </div>
                <div v-else class="flex flex-col items-center gap-2 text-slate-400">
                  <Upload class="h-6 w-6" />
                  <span class="text-xs">Upload NPWP</span>
                </div>
              </label>
              <p v-if="form.errors.npwp" class="text-xs text-red-500">{{ form.errors.npwp }}</p>
            </div>

            <!-- NIB -->
            <div class="space-y-2">
              <div class="flex items-center justify-between">
                <Label>NIB <span class="text-red-500">*</span></Label>
                <span v-if="sellerDocument?.nib_status"
                  :class="['text-xs font-medium', getStatusColor(sellerDocument.nib_status)]">
                  {{ sellerDocument.nib_status === 'approved' ? 'Disetujui' : sellerDocument.nib_status === 'rejected' ?
                    'Ditolak' : 'Menunggu' }}
                </span>
              </div>
              <label
                class="group relative flex h-48 cursor-pointer flex-col items-center justify-center overflow-hidden rounded-lg border-2 border-dashed border-slate-200 bg-slate-50 transition-colors hover:border-indigo-400 hover:bg-slate-100"
                :class="{ 'pointer-events-none opacity-60': !canEdit }">
                <input type="file" accept="image/jpeg,image/png,image/webp,application/pdf" class="hidden"
                  @change="(e) => handleFileChange(e, 'nib')" :disabled="!canEdit" />
                <div v-if="nibPreview" class="h-full w-full">
                  <img v-if="!nibPreviewIsPdf" :src="nibPreview" alt="NIB" class="h-full w-full object-cover" />
                  <div v-else class="flex h-full flex-col items-center justify-center gap-1">
                    <FileText class="h-8 w-8 text-indigo-500" />
                    <span class="text-xs text-slate-500">PDF</span>
                  </div>
                </div>
                <div v-else class="flex flex-col items-center gap-2 text-slate-400">
                  <Upload class="h-6 w-6" />
                  <span class="text-xs">Upload NIB</span>
                </div>
              </label>
              <p v-if="form.errors.nib" class="text-xs text-red-500">{{ form.errors.nib }}</p>
            </div>
          </div>

          <!-- Optional Documents -->
          <div class="mt-6">
            <Separator class="mb-4" />
            <h4 class="mb-3 text-sm font-medium text-slate-700">Dokumen Pendukung (Opsional)</h4>

            <div class="grid gap-6 sm:grid-cols-2">
              <!-- Company Statement -->
              <div class="space-y-2">
                <Label>Surat Pernyataan</Label>
                <label
                  class="group relative flex h-40 cursor-pointer flex-col items-center justify-center overflow-hidden rounded-lg border-2 border-dashed border-slate-200 bg-slate-50 transition-colors hover:border-indigo-400 hover:bg-slate-100"
                  :class="{ 'pointer-events-none opacity-60': !canEdit }">
                  <input type="file" accept="image/jpeg,image/png,image/webp,application/pdf" class="hidden"
                    @change="(e) => handleFileChange(e, 'company_statement')" :disabled="!canEdit" />
                  <div v-if="companyStatementPreview" class="h-full w-full">
                    <img v-if="!companyStatementPreviewIsPdf" :src="companyStatementPreview" alt="Surat"
                      class="h-full w-full object-cover" />
                    <div v-else class="flex h-full flex-col items-center justify-center gap-1">
                      <FileText class="h-8 w-8 text-indigo-500" />
                      <span class="text-xs text-slate-500">PDF</span>
                    </div>
                  </div>
                  <div v-else class="flex flex-col items-center gap-2 text-slate-400">
                    <Upload class="h-6 w-6" />
                    <span class="text-xs">Upload Surat</span>
                  </div>
                </label>
                <p v-if="form.errors.company_statement" class="text-xs text-red-500">{{ form.errors.company_statement }}
                </p>
              </div>

              <!-- Supporting Documents -->
              <div class="space-y-2">
                <Label>Dokumen Lainnya</Label>
                <label
                  class="group relative flex h-28 cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-slate-200 bg-slate-50 transition-colors hover:border-indigo-400 hover:bg-slate-100"
                  :class="{ 'pointer-events-none opacity-60': !canEdit }">
                  <input type="file" accept="image/jpeg,image/png,image/webp,application/pdf" multiple class="hidden"
                    @change="handleSupportingFiles" :disabled="!canEdit" />
                  <div class="flex flex-col items-center gap-2 text-slate-400">
                    <Upload class="h-6 w-6" />
                    <span class="text-xs">Upload (Multiple)</span>
                  </div>
                </label>
                <!-- Existing supporting docs -->
                <div v-if="sellerDocument?.supporting_documents_urls?.length" class="space-y-2">
                  <div v-for="doc in sellerDocument.supporting_documents_urls" :key="doc.id"
                    class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                    <a :href="doc.url" target="_blank"
                      class="flex items-center gap-2 text-sm text-slate-700 hover:text-indigo-600">
                      <FileText class="h-4 w-4" />
                      {{ doc.name }}
                    </a>
                    <button v-if="canEdit" type="button" @click="deleteSupportingDoc(doc.id)"
                      class="text-slate-400 hover:text-red-500">
                      <Trash2 class="h-4 w-4" />
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Submit Actions -->
          <div class="mt-6 flex items-center justify-between border-t border-slate-200 pt-6">
            <p class="text-sm text-slate-500">
              <span v-if="isApproved" class="flex items-center gap-2 text-green-600">
                <CheckCircle2 class="h-4 w-4" />
                Toko Anda sudah terverifikasi
              </span>
              <span v-else-if="isSubmitted" class="flex items-center gap-2 text-amber-600">
                <Clock class="h-4 w-4" />
                Menunggu verifikasi dari admin (1-3 hari kerja)
              </span>
              <span v-else>
                Pastikan semua data terisi dengan benar sebelum submit
              </span>
            </p>
            <div class="flex items-center gap-3">
              <Button v-if="canEdit" type="button" variant="outline" @click="submit" :disabled="form.processing">
                <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                Simpan Draft
              </Button>
              <Button v-if="canEdit" type="button" @click="submitForReview" :disabled="form.processing || isSubmitting">
                <Loader2 v-if="isSubmitting" class="mr-2 h-4 w-4 animate-spin" />
                Submit untuk Verifikasi
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
</template>
