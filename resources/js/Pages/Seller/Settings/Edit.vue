<script setup lang="ts">
import SellerDashboardLayout from '@/Layouts/SellerDashboardLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
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
import { CheckCircle2, ShieldCheck, Camera, ImagePlus, X, Building2, MapPin, Landmark } from 'lucide-vue-next';
import RegionSelector from '@/components/RegionSelector.vue';

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

const props = defineProps<{
  store: StorePayload;
  hasStore: boolean;
  typeOptions: SelectOption[];
  taxStatusOptions: SelectOption[];
}>();

defineOptions({
  layout: SellerDashboardLayout,
});

const page = usePage();
const flash = computed(() => (page.props.flash ?? {}) as Record<string, string>);
const flashSuccess = computed(() => flash.value.success ?? '');
const flashInfo = computed(() => flash.value.info ?? '');
const flashError = computed(() => flash.value.error ?? '');
const showSuccess = ref(!!flashSuccess.value);

watch(flashSuccess, (value) => {
  showSuccess.value = !!value;
  if (value) {
    setTimeout(() => {
      showSuccess.value = false;
    }, 3000);
  }
});

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
});

const form = useForm(buildFormState(props.store));

// Image preview states
const logoPreview = ref<string | null>(props.store.logo_url);
const bannerPreview = ref<string | null>(props.store.banner_url);
const logoInput = ref<HTMLInputElement | null>(null);
const bannerInput = ref<HTMLInputElement | null>(null);

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

const submit = () => {
  if (props.hasStore) {
    form.transform((data) => {
      const formData: Record<string, unknown> = {
        ...data,
        _method: 'PUT',
      };
      // Remove null files to avoid sending empty values
      if (!formData.logo) delete formData.logo;
      if (!formData.banner) delete formData.banner;
      return formData;
    }).post('/seller/settings', {
      preserveScroll: true,
      forceFormData: true,
    });
  } else {
    form.transform((data) => {
      const formData: Record<string, unknown> = { ...data };
      if (!formData.logo) delete formData.logo;
      if (!formData.banner) delete formData.banner;
      return formData;
    }).post('/seller/settings', { preserveScroll: true, forceFormData: true });
  }
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

watch(
  () => form.name,
  (value, _oldValue, onCleanup) => {
    if (form.processing) {
      return;
    }

    if (props.hasStore && form.slug && form.slug !== originalSlug.value) {
      return;
    }

    const newSlug = generateSlug(value);
    form.slug = newSlug || originalSlug.value;

    if (!props.hasStore) {
      return;
    }

    onCleanup(() => {
      if (!form.processing && props.hasStore && !form.slug) {
        form.slug = originalSlug.value;
      }
    });
  },
);

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

    // Update previews
    logoPreview.value = nextStore.logo_url;
    bannerPreview.value = nextStore.banner_url;
  },
  { deep: true, immediate: true },
);
</script>

<template>
  <div class="space-y-6 w-full px-2 sm:px-4 lg:px-6">

    <Head title="Profil Toko" />

    <div class="flex items-center justify-between gap-3">
      <div>
        <h1 class="text-2xl font-semibold text-slate-900">Kelola Toko</h1>
        <p class="text-sm text-slate-500">Lengkapi informasi toko agar pembeli lebih percaya.</p>
      </div>
      <Button variant="outline" size="sm" as-child>
        <Link href="/seller/dashboard">
          Kembali ke dashboard
        </Link>
      </Button>
    </div>

    <Alert v-if="showSuccess && flashSuccess" variant="default"
      class="flex items-center gap-2 border-green-200 bg-green-50 text-green-700">
      <CheckCircle2 class="h-5 w-5 text-green-600" />
      <div class="flex flex-wrap gap-1 text-sm">
        <span class="font-semibold">Berhasil.</span>
        <span>{{ flashSuccess }}</span>
      </div>
    </Alert>

    <Alert v-if="flashError" variant="destructive">
      <AlertTitle>Perlu tindakan</AlertTitle>
      <AlertDescription>{{ flashError }}</AlertDescription>
    </Alert>

    <Alert v-if="flashInfo" variant="default" class="border-slate-200 bg-slate-50">
      <AlertTitle>Informasi</AlertTitle>
      <AlertDescription>{{ flashInfo }}</AlertDescription>
    </Alert>

    <form @submit.prevent="submit" class="space-y-6">
      <!-- Store Branding Card -->
      <Card>
        <CardHeader>
          <div class="flex items-center gap-2">
            <ImagePlus class="h-5 w-5 text-indigo-600" />
            <CardTitle>Foto & Banner Toko</CardTitle>
          </div>
        </CardHeader>
        <CardContent class="space-y-6">
          <!-- Logo Upload (moved to top) -->
          <div class="space-y-2">
            <Label>Logo Toko</Label>
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
              <div class="text-sm text-slate-500">
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
            <Label>Hero Banner</Label>
            <div class="relative">
              <div
                class="relative h-48 w-full overflow-hidden rounded-lg border-2 border-dashed border-slate-200 bg-slate-50 hover:border-indigo-400 hover:bg-slate-100 transition-colors cursor-pointer"
                @click="bannerInput?.click()">
                <img v-if="bannerPreview" :src="bannerPreview" alt="Store Banner" class="h-full w-full object-cover" />
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
        <Card>
          <CardHeader>
            <div class="flex items-center gap-2">
              <ShieldCheck class="h-5 w-5 text-indigo-600" />
              <CardTitle>Identitas Toko</CardTitle>
            </div>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2">
              <div class="space-y-2 sm:col-span-2">
                <Label for="name">Nama Toko</Label>
                <Input id="name" v-model="form.name" placeholder="Nama toko"
                  :class="form.errors.name ? 'border-red-500' : ''" />
                <p v-if="form.errors.name" class="text-xs text-red-600">
                  {{ form.errors.name }}
                </p>
              </div>

              <div class="space-y-2 sm:col-span-2">
                <Label for="slug">Slug</Label>
                <div class="relative">
                  <Input id="slug" v-model="form.slug" :disabled="hasStore" class="bg-slate-50 pr-20"
                    :class="form.errors.slug ? 'border-red-500' : ''" />
                  <Badge variant="secondary" class="absolute right-2 top-1/2 -translate-y-1/2 text-[11px]">
                    {{ hasStore ? 'Terkunci' : 'Auto' }}
                  </Badge>
                </div>
                <p class="text-xs text-slate-500">Slug digunakan di URL toko.</p>
                <p v-if="form.errors.slug" class="text-xs text-red-600">
                  {{ form.errors.slug }}
                </p>
              </div>

              <div class="space-y-2 sm:col-span-2">
                <Label for="phone">Nomor Telepon</Label>
                <Input id="phone" v-model="form.phone" type="tel" placeholder="Contoh: 081234567890"
                  :class="form.errors.phone ? 'border-red-500' : ''" />
                <p v-if="form.errors.phone" class="text-xs text-red-600">
                  {{ form.errors.phone }}
                </p>
              </div>

              <div class="space-y-2 sm:col-span-2">
                <Label for="tagline">Tagline</Label>
                <Input id="tagline" v-model="form.tagline" placeholder="Contoh: UMKM pangan sehat"
                  :class="form.errors.tagline ? 'border-red-500' : ''" />
                <p v-if="form.errors.tagline" class="text-xs text-red-600">
                  {{ form.errors.tagline }}
                </p>
              </div>

              <div class="space-y-2 sm:col-span-2">
                <Label for="description">Deskripsi</Label>
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

        <Card>
          <CardHeader>
            <div class="flex items-center gap-2">
              <Building2 class="h-5 w-5 text-indigo-600" />
              <CardTitle>Jenis Toko & Perpajakan</CardTitle>
            </div>
          </CardHeader>
          <CardContent class="space-y-4">
            <!-- Jenis Toko - Full Width -->
            <div class="space-y-2">
              <Label for="type">Jenis Toko</Label>
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
              <Label for="tax_status">Status Pajak</Label>
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
      <Card>
        <CardHeader>
          <div class="flex items-center gap-2">
            <Landmark class="h-5 w-5 text-indigo-600" />
            <CardTitle>Rekening Bank</CardTitle>
          </div>
        </CardHeader>
        <CardContent class="space-y-4">
          <div class="grid gap-4 sm:grid-cols-3">
            <div class="space-y-2">
              <Label for="bank_name">Nama Bank</Label>
              <Input id="bank_name" v-model="form.bank_name" placeholder="Contoh: BCA, Mandiri, BRI"
                :class="form.errors.bank_name ? 'border-red-500' : ''" />
              <p v-if="form.errors.bank_name" class="text-xs text-red-600">
                {{ form.errors.bank_name }}
              </p>
            </div>

            <div class="space-y-2">
              <Label for="bank_account_number">Nomor Rekening</Label>
              <Input id="bank_account_number" v-model="form.bank_account_number" placeholder="Contoh: 1234567890"
                :class="form.errors.bank_account_number ? 'border-red-500' : ''" />
              <p v-if="form.errors.bank_account_number" class="text-xs text-red-600">
                {{ form.errors.bank_account_number }}
              </p>
            </div>

            <div class="space-y-2">
              <Label for="bank_account_name">Atas Nama</Label>
              <Input id="bank_account_name" v-model="form.bank_account_name" placeholder="Nama pemilik rekening"
                :class="form.errors.bank_account_name ? 'border-red-500' : ''" />
              <p v-if="form.errors.bank_account_name" class="text-xs text-red-600">
                {{ form.errors.bank_account_name }}
              </p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <div class="flex items-center gap-2">
            <MapPin class="h-5 w-5 text-indigo-600" />
            <CardTitle>Alamat & Respon</CardTitle>
          </div>
        </CardHeader>
        <CardContent class="space-y-4">
          <RegionSelector v-model:provinceId="form.province_id" v-model:cityId="form.city_id"
            v-model:districtId="form.district_id" :show-district="true" :disabled="form.processing" :errors="{
              provinceId: form.errors.province_id,
              cityId: form.errors.city_id,
              districtId: form.errors.district_id,
            }" :initial-province-name="props.store.province" :initial-city-name="props.store.city"
            :initial-district-name="props.store.district" />

          <div class="space-y-2">
            <Label for="postal_code">Kode Pos</Label>
            <Input id="postal_code" v-model="form.postal_code" placeholder="Kode pos"
              :class="form.errors.postal_code ? 'border-red-500' : ''" />
            <p v-if="form.errors.postal_code" class="text-xs text-red-600">
              {{ form.errors.postal_code }}
            </p>
          </div>

          <div class="space-y-2">
            <Label for="address_line">Alamat Lengkap</Label>
            <Textarea id="address_line" v-model="form.address_line" rows="3" placeholder="Alamat lengkap"
              :class="form.errors.address_line ? 'border-red-500' : ''" />
            <p v-if="form.errors.address_line" class="text-xs text-red-600">
              {{ form.errors.address_line }}
            </p>
          </div>

          <div class="space-y-2">
            <Label for="response_time_label">Respon Toko</Label>
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
  </div>
</template>
