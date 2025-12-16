<script setup lang="ts">
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
} from '@/components/ui/command';
import { computed, ref, watch } from 'vue';
import { ArrowLeft, Factory, Check, ChevronsUpDown } from 'lucide-vue-next';
import RegionSelector from '@/components/RegionSelector.vue';

const props = defineProps({
  store: {
    type: Object,
    required: true,
  },
  regionOptions: {
    type: Object,
    default: () => ({}),
  },
  typeOptions: {
    type: Array,
    default: () => [],
  },
  taxStatusOptions: {
    type: Array,
    default: () => [],
  },
  sellerOptions: {
    type: Array,
    default: () => [],
  },
});

const form = useForm({
  name: props.store.name ?? '',
  slug: props.store.slug ?? '',
  tagline: props.store.tagline ?? '',
  description: props.store.description ?? '',
  type: props.store.type ?? 'umkm',
  tax_status: props.store.tax_status ?? 'non_pkp',
  bumn_partner: props.store.bumn_partner ?? '',
  province_id: props.store.province_id ?? null,
  city_id: props.store.city_id ?? null,
  district_id: props.store.district_id ?? null,
  postal_code: props.store.postal_code ?? '',
  address_line: props.store.address_line ?? '',
  is_verified: props.store.is_verified ?? false,
  is_umkm: props.store.is_umkm ?? true,
  response_time_label: props.store.response_time_label ?? '',
  rating: props.store.rating ?? '',
  transactions_count: props.store.transactions_count ?? '',
  user_id: props.store.user_id ?? null,
  owner_id_card: null as File | null,
  nib_document: null as File | null,
  npwp_document: null as File | null,
  business_license: null as File | null,
  pkp_document: null as File | null,
});

const submit = () => {
  form.post('/admin/stores', {
    forceFormData: true,
    preserveScroll: true,
  });
};

const generateSlug = (value?: string) =>
  value
    ?.toString()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '') ?? '';

watch(
  () => form.name,
  (value) => {
    if (!form.slug && value) {
      form.slug = generateSlug(value);
    }
  },
);

const typeHelpText = computed(() => {
  switch (form.type) {
    case 'vendor':
      return 'Vendor besar dengan proses pengadaan B2B.';
    case 'koperasi':
      return 'Koperasi atau BUMDes yang melayani berbagai kategori.';
    case 'premium':
      return 'Partner strategis dengan SLA layanan khusus.';
    default:
      return 'Pelaku UMKM yang memerlukan pendampingan dan promosi.';
  }
});

defineOptions({
  layout: AdminDashboardLayout,
});

const sellerPopoverOpen = ref(false);
const sellerSearch = ref('');

const selectedSeller = computed(() =>
  props.sellerOptions.find((s: any) => s.id === Number(form.user_id)) ?? null,
);

const filteredSellerOptions = computed(() => {
  if (!sellerSearch.value) {
    return props.sellerOptions.slice(0, 8);
  }

  const term = sellerSearch.value.toLowerCase();
  return props.sellerOptions.filter(
    (option: any) =>
      option.name.toLowerCase().includes(term) || option.email.toLowerCase().includes(term),
  );
});
</script>

<template>
  <div class="space-y-6">

    <Head title="Tambah Toko" />

    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tambah Toko</p>
        <h1 class="text-2xl font-bold text-slate-900">Lengkapi Profil Toko Baru</h1>
        <p class="text-sm text-slate-500">Pastikan detail vendor diisi lengkap sebelum proses verifikasi.</p>
      </div>
      <Button variant="outline" as-child>
        <Link href="/admin/stores">
          <ArrowLeft class="mr-2 h-4 w-4" />
          Kembali
        </Link>
      </Button>
    </div>

    <form @submit.prevent="submit" class="space-y-6">
      <Card class="border-slate-200">
        <CardHeader>
          <CardTitle>Informasi Utama</CardTitle>
          <CardDescription>Data ini akan ditampilkan di halaman profil toko.</CardDescription>
        </CardHeader>
        <CardContent class="space-y-4">
          <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-2">
              <Label class="flex items-center gap-1">
                Nama Toko <span class="text-red-500">*</span>
              </Label>
              <Input v-model="form.name" placeholder="Contoh: Nusantara Craft Hub" :disabled="form.processing" />
              <p v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</p>
            </div>
            <div class="space-y-2">
              <Label>Slug</Label>
              <Input v-model="form.slug" placeholder="otomatis" :disabled="form.processing" />
              <p v-if="form.errors.slug" class="text-sm text-red-500">{{ form.errors.slug }}</p>
            </div>
          </div>
          <div class="space-y-2">
            <Label>Tagline</Label>
            <Input v-model="form.tagline" placeholder="Kurasi produk kriya terbaik dari UMKM lokal"
              :disabled="form.processing" />
            <p v-if="form.errors.tagline" class="text-sm text-red-500">{{ form.errors.tagline }}</p>
          </div>
          <div class="space-y-2">
            <Label>Deskripsi</Label>
            <Textarea v-model="form.description" rows="4"
              placeholder="Cerita singkat toko, keunggulan, dan produk unggulan" :disabled="form.processing" />
            <p v-if="form.errors.description" class="text-sm text-red-500">{{ form.errors.description }}</p>
          </div>
        </CardContent>
      </Card>

      <Card class="border-slate-200">
        <CardHeader>
          <CardTitle>Pemilik Toko</CardTitle>
          <CardDescription>Hubungkan toko ke akun seller yang emailnya sudah terverifikasi.</CardDescription>
        </CardHeader>
        <CardContent class="space-y-4">
          <div class="space-y-2">
            <Label class="flex items-center gap-1">
              Pilih Seller <span class="text-red-500">*</span>
            </Label>
            <Popover v-model:open="sellerPopoverOpen">
              <PopoverTrigger as-child>
                <Button variant="outline" role="combobox" class="w-full justify-between"
                  :class="form.errors.user_id ? 'border-red-500' : ''">
                  <span class="truncate">
                    {{ selectedSeller ? `${selectedSeller.name} — ${selectedSeller.email}` : 'Pilih seller
                    terverifikasi' }}
                  </span>
                  <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                </Button>
              </PopoverTrigger>
              <PopoverContent class="w-[320px] p-0">
                <Command>
                  <CommandInput v-model="sellerSearch" placeholder="Cari seller (nama/email)..." />
                  <CommandEmpty>Seller tidak ditemukan.</CommandEmpty>
                  <CommandGroup>
                    <CommandItem v-for="seller in filteredSellerOptions" :key="seller.id" :value="seller.name"
                      @select="() => { form.user_id = seller.id; sellerPopoverOpen = false; }">
                      <Check class="mr-2 h-4 w-4" :class="form.user_id === seller.id ? 'opacity-100' : 'opacity-0'" />
                      <div>
                        <p class="text-sm">{{ seller.name }}</p>
                        <p class="text-xs text-slate-500">{{ seller.email }}</p>
                      </div>
                    </CommandItem>
                  </CommandGroup>
                </Command>
              </PopoverContent>
            </Popover>
            <p class="text-xs text-slate-500">Hanya menampilkan user role seller dengan email terverifikasi.</p>
            <p v-if="form.errors.user_id" class="text-xs text-red-600">
              {{ form.errors.user_id }}
            </p>
          </div>
        </CardContent>
      </Card>

      <Card class="border-slate-200">
        <CardHeader>
          <CardTitle>Klasifikasi & Pajak</CardTitle>
          <CardDescription>Menentukan akses fitur dan kebutuhan pendampingan.</CardDescription>
        </CardHeader>
        <CardContent class="grid gap-4 md:grid-cols-2">
          <div class="space-y-2">
            <Label class="flex items-center gap-1">
              Jenis Toko <span class="text-red-500">*</span>
            </Label>
            <div class="rounded-lg border border-slate-200">
              <select v-model="form.type"
                class="w-full rounded-md border-0 px-3 py-2 text-sm text-slate-700 focus:ring-0"
                :disabled="form.processing">
                <option v-for="option in typeOptions" :key="option.value" :value="option.value">
                  {{ option.label }}
                </option>
              </select>
              <p class="px-3 pb-3 text-xs text-slate-500">{{ typeHelpText }}</p>
            </div>
            <p v-if="form.errors.type" class="text-sm text-red-500">{{ form.errors.type }}</p>
          </div>
          <div class="space-y-2">
            <Label class="flex items-center gap-1">
              Status Pajak <span class="text-red-500">*</span>
            </Label>
            <div class="rounded-lg border border-slate-200">
              <select v-model="form.tax_status"
                class="w-full rounded-md border-0 px-3 py-2 text-sm text-slate-700 focus:ring-0"
                :disabled="form.processing">
                <option v-for="option in taxStatusOptions" :key="option.value" :value="option.value">
                  {{ option.label }}
                </option>
              </select>
              <p class="px-3 pb-3 text-xs text-slate-500">Pastikan status pajak sesuai dokumen legal.</p>
            </div>
            <p v-if="form.errors.tax_status" class="text-sm text-red-500">{{ form.errors.tax_status }}</p>
          </div>
          <div class="space-y-2">
            <Label>Mitra BUMN (opsional)</Label>
            <Input v-model="form.bumn_partner" placeholder="Contoh: PT PLN Persero" :disabled="form.processing" />
            <p v-if="form.errors.bumn_partner" class="text-sm text-red-500">
              {{ form.errors.bumn_partner }}
            </p>
          </div>
          <div class="grid gap-3 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
            <label class="flex items-center justify-between">
              <div>
                <p class="text-sm font-semibold">Toko Terverifikasi?</p>
                <p class="text-xs text-slate-500">Berikan akses penuh untuk vendor terpercaya.</p>
              </div>
              <Switch v-model:checked="form.is_verified" :disabled="form.processing" />
            </label>
            <label class="flex items-center justify-between">
              <div>
                <p class="text-sm font-semibold">Termasuk UMKM?</p>
                <p class="text-xs text-slate-500">Centang bila membutuhkan dukungan khusus.</p>
              </div>
              <Switch v-model:checked="form.is_umkm" :disabled="form.processing" />
            </label>
          </div>
        </CardContent>
      </Card>

      <Card class="border-slate-200">
        <CardHeader>
          <CardTitle>Dokumen Verifikasi</CardTitle>
          <CardDescription>Unggah berkas pendukung untuk verifikasi toko.</CardDescription>
        </CardHeader>
        <CardContent class="space-y-4">
          <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-2">
              <Label class="flex items-center gap-1">
                KTP Pemilik <span class="text-red-500">*</span>
              </Label>
              <Input type="file" accept=".pdf,.jpg,.jpeg,.png"
                @change="(e: Event) => { const t = e.target as HTMLInputElement; form.owner_id_card = t.files?.[0] ?? null; }"
                :disabled="form.processing" />
              <p class="text-xs text-slate-500">Format PDF/JPG/PNG, maks 5MB.</p>
              <p v-if="form.errors.owner_id_card" class="text-sm text-red-500">
                {{ form.errors.owner_id_card }}
              </p>
            </div>
            <div class="space-y-2">
              <Label class="flex items-center gap-1">
                NIB / Izin Usaha OSS <span class="text-red-500">*</span>
              </Label>
              <Input type="file" accept=".pdf,.jpg,.jpeg,.png"
                @change="(e: Event) => { const t = e.target as HTMLInputElement; form.nib_document = t.files?.[0] ?? null; }"
                :disabled="form.processing" />
              <p class="text-xs text-slate-500">WAJIB, format PDF/JPG/PNG, maks 5MB.</p>
              <p v-if="form.errors.nib_document" class="text-sm text-red-500">
                {{ form.errors.nib_document }}
              </p>
            </div>
            <div class="space-y-2">
              <Label>NPWP Badan (opsional)</Label>
              <Input type="file" accept=".pdf,.jpg,.jpeg,.png"
                @change="(e: Event) => { const t = e.target as HTMLInputElement; form.npwp_document = t.files?.[0] ?? null; }"
                :disabled="form.processing" />
              <p class="text-xs text-slate-500">Opsional, upload jika tersedia.</p>
              <p v-if="form.errors.npwp_document" class="text-sm text-red-500">
                {{ form.errors.npwp_document }}
              </p>
            </div>
            <div class="space-y-2">
              <Label>SIUP/Legalitas Usaha (opsional)</Label>
              <Input type="file" accept=".pdf,.jpg,.jpeg,.png"
                @change="(e: Event) => { const t = e.target as HTMLInputElement; form.business_license = t.files?.[0] ?? null; }"
                :disabled="form.processing" />
              <p class="text-xs text-slate-500">Opsional, unggah SIUP/akta/izin usaha lain.</p>
              <p v-if="form.errors.business_license" class="text-sm text-red-500">
                {{ form.errors.business_license }}
              </p>
            </div>
            <div class="space-y-2 md:col-span-2">
              <Label>Sertifikat PKP (opsional)</Label>
              <Input type="file" accept=".pdf,.jpg,.jpeg,.png"
                @change="(e: Event) => { const t = e.target as HTMLInputElement; form.pkp_document = t.files?.[0] ?? null; }"
                :disabled="form.processing" />
              <p class="text-xs text-slate-500">Opsional, upload jika status pajak PKP.</p>
              <p v-if="form.errors.pkp_document" class="text-sm text-red-500">
                {{ form.errors.pkp_document }}
              </p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card class="border-slate-200">
        <CardHeader>
          <CardTitle>Lokasi & Statistik</CardTitle>
          <CardDescription>Data ini membantu pengguna menemukan toko yang relevan.</CardDescription>
        </CardHeader>
        <CardContent class="space-y-4">
          <RegionSelector v-model:provinceId="form.province_id" v-model:cityId="form.city_id"
            v-model:districtId="form.district_id" :show-district="true" :disabled="form.processing"
            :provinces-prop="regionOptions.provinces" :cities-prop="regionOptions.cities"
            :districts-prop="regionOptions.districts" :errors="{
              provinceId: form.errors.province_id,
              cityId: form.errors.city_id,
              districtId: form.errors.district_id,
            }" />
          <div class="space-y-2">
            <Label>Kode Pos</Label>
            <Input v-model="form.postal_code" placeholder="Kode pos" :disabled="form.processing" />
            <p v-if="form.errors.postal_code" class="text-sm text-red-500">{{ form.errors.postal_code }}</p>
          </div>
          <div class="space-y-2">
            <Label>Alamat Lengkap</Label>
            <Textarea v-model="form.address_line" rows="3" placeholder="Jl. Braga No. 10, Sumur Bandung, Kota Bandung"
              :disabled="form.processing" />
            <p v-if="form.errors.address_line" class="text-sm text-red-500">{{ form.errors.address_line }}</p>
          </div>
          <div class="grid gap-4 md:grid-cols-3">
            <div class="space-y-2">
              <Label>Respons Time</Label>
              <Input v-model="form.response_time_label" placeholder="24 jam" :disabled="form.processing" />
              <p v-if="form.errors.response_time_label" class="text-sm text-red-500">
                {{ form.errors.response_time_label }}
              </p>
            </div>
            <div class="space-y-2">
              <Label>Rating</Label>
              <Input v-model="form.rating" type="number" step="0.1" min="0" max="5" placeholder="4.5"
                :disabled="form.processing" />
              <p v-if="form.errors.rating" class="text-sm text-red-500">{{ form.errors.rating }}</p>
            </div>
            <div class="space-y-2">
              <Label>Total Transaksi</Label>
              <Input v-model="form.transactions_count" type="number" min="0" placeholder="0"
                :disabled="form.processing" />
              <p v-if="form.errors.transactions_count" class="text-sm text-red-500">
                {{ form.errors.transactions_count }}
              </p>
            </div>
          </div>
        </CardContent>
        <CardFooter class="flex flex-wrap items-center justify-between gap-2">
          <p class="text-sm text-slate-500">
            Data yang lengkap membantu tim kurasi mempercepat proses verifikasi.
          </p>
          <div class="flex gap-2">
            <Button type="button" variant="outline" as-child :disabled="form.processing">
              <Link href="/admin/stores">Batal</Link>
            </Button>
            <Button type="submit" :disabled="form.processing">
              {{ form.processing ? 'Menyimpan...' : 'Simpan Toko' }}
            </Button>
          </div>
        </CardFooter>
      </Card>
    </form>

    <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/60 p-4 text-sm text-slate-500">
      <p class="font-medium text-slate-700">Tip Verifikasi</p>
      <p class="mt-1 flex items-center gap-2 text-xs text-slate-500">
        <Factory class="h-4 w-4 text-slate-400" />
        Simpan dokumen pendukung seperti SIUP, NPWP, dan katalog produk sebelum mengundang toko ke campaign.
      </p>
    </div>
  </div>
</template>
