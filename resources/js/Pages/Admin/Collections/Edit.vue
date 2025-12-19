<script setup lang="ts">
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Upload, X, Plus, Search } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';

const props = defineProps<{
  collection: {
    id: number;
    title: string;
    description: string | null;
    color_theme: string | null;
    display_mode: 'slider' | 'image_only' | null;
    is_active: boolean;
    home_image_url: string | null;
    cover_image_url: string | null;
    product_ids: number[] | unknown;
    selected_products: { id: number; name: string }[];
  };
  products: { id: number; name: string }[];
  filters: {
    product_search?: string | null;
  };
}>();

defineOptions({
  layout: AdminDashboardLayout,
});

const colorThemes = [
  { value: 'from-sky-600 to-sky-500', label: 'Sky' },
  { value: 'from-amber-500 to-orange-400', label: 'Amber' },
  { value: 'from-emerald-500 to-teal-400', label: 'Emerald' },
  { value: 'from-indigo-500 to-blue-500', label: 'Indigo' },
  { value: 'from-purple-500 to-fuchsia-500', label: 'Purple' },
  { value: 'from-pink-500 to-rose-400', label: 'Pink' },
  { value: 'from-slate-700 to-slate-600', label: 'Slate' },
];

const normalizeIds = (value: unknown): number[] => {
  if (Array.isArray(value)) return value.map((id) => Number(id)).filter((id) => Number.isFinite(id));
  return [];
};

const form = useForm({
  title: props.collection.title,
  description: props.collection.description ?? '',
  color_theme: props.collection.color_theme ?? 'none',
  is_active: props.collection.is_active,
  display_mode: props.collection.display_mode ?? 'slider',
  home_image: null as File | null,
  cover_image: null as File | null,
  product_ids: normalizeIds(props.collection.product_ids),
});

const homeImageInput = ref<HTMLInputElement | null>(null);
const heroImageInput = ref<HTMLInputElement | null>(null);

const homePreview = computed(() => {
  if (form.home_image) return URL.createObjectURL(form.home_image);
  return props.collection.home_image_url;
});

const heroPreview = computed(() => {
  if (form.cover_image) return URL.createObjectURL(form.cover_image);
  return props.collection.cover_image_url;
});

const triggerHomeInput = () => {
  homeImageInput.value?.click();
};

const triggerHeroInput = () => {
  heroImageInput.value?.click();
};

const handleHomeChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0] ?? null;
  form.home_image = file;
};

const handleHeroChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0] ?? null;
  form.cover_image = file;
};

const removeHomeImage = () => {
  form.home_image = null;
  if (homeImageInput.value) homeImageInput.value.value = '';
};

const removeHeroImage = () => {
  form.cover_image = null;
  if (heroImageInput.value) heroImageInput.value.value = '';
};

const selectedProducts = ref<{ id: number; name: string }[]>(props.collection.selected_products ?? []);

const isImageOnlyMode = computed(() => form.display_mode === 'image_only');

const addProduct = (product: { id: number; name: string }) => {
  if (isImageOnlyMode.value) return;
  const id = Number(product.id);
  if (!Number.isFinite(id)) return;
  if (!form.product_ids.includes(id)) {
    form.product_ids = [...form.product_ids, id];
  }
  if (!selectedProducts.value.some((p) => p.id === id)) {
    selectedProducts.value = [...selectedProducts.value, { id, name: product.name }];
  }
};

const removeProduct = (id: number) => {
  form.product_ids = form.product_ids.filter((value) => value !== id);
  selectedProducts.value = selectedProducts.value.filter((p) => p.id !== id);
};

const productSearch = ref(props.filters?.product_search ?? '');
const debouncedProductSearch = useDebounceFn((value: string) => {
  if (isImageOnlyMode.value) return;
  router.get(
    `/admin/collections/${props.collection.id}/edit`,
    { product_search: value || undefined },
    { preserveState: true, preserveScroll: true, replace: true, only: ['products', 'filters'] },
  );
}, 350);

watch(productSearch, (value) => debouncedProductSearch(value));

watch(
  () => form.display_mode,
  (value) => {
    if (value !== 'image_only') return;
    form.product_ids = [];
    selectedProducts.value = [];
    productSearch.value = '';
  },
);

const submit = () => {
  form
    .transform((data) => ({
      ...data,
      color_theme: data.color_theme === 'none' ? '' : data.color_theme,
      _method: 'put',
    }))
    .post(`/admin/collections/${props.collection.id}`, {
      preserveScroll: true,
      forceFormData: true,
    });
};
</script>

<template>
  <div class="space-y-6">

    <Head title="Edit Koleksi" />

    <div class="flex items-center justify-between gap-3">
      <h1 class="text-xl font-bold tracking-tight text-slate-900">Edit Koleksi</h1>
      <Button variant="outline" size="sm" @click="router.visit('/admin/collections')" :disabled="form.processing">
        Kembali
      </Button>
    </div>

    <Card>
      <CardHeader>
        <CardTitle>Informasi Koleksi</CardTitle>
        <CardDescription>Edit informasi koleksi {{ collection.title }}</CardDescription>
      </CardHeader>
      <CardContent>
        <form @submit.prevent="submit" class="space-y-6">
          <div class="space-y-3">
            <Label>Gambar Home (Kartu Koleksi di Beranda)</Label>
            <div class="max-w-sm rounded-lg border border-dashed border-slate-200 bg-slate-50 p-4">
              <div v-if="homePreview"
                class="relative overflow-hidden rounded-lg border border-slate-200 bg-white aspect-[4/3]">
                <img :src="homePreview" alt="Preview gambar home" class="h-full w-full object-cover" />
                <button v-if="form.home_image" type="button"
                  class="absolute right-2 top-2 inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/90 text-slate-700 shadow hover:bg-white"
                  @click="removeHomeImage" aria-label="Hapus gambar">
                  <X class="h-4 w-4" />
                </button>
              </div>
              <div v-else class="flex flex-col items-center justify-center gap-2 text-center">
                <Upload class="h-6 w-6 text-slate-400" />
                <p class="text-sm font-semibold text-slate-700">Upload gambar untuk kartu koleksi</p>
                <p class="text-xs text-slate-500">Rekomendasi rasio 4:3, maks 2MB</p>
              </div>
              <div class="mt-3 flex flex-wrap items-center gap-2">
                <Button type="button" variant="outline" size="sm" @click="triggerHomeInput">
                  Pilih Gambar
                </Button>
                <Button v-if="form.home_image" type="button" variant="outline" size="sm" @click="removeHomeImage">
                  <X class="h-4 w-4" />
                  Hapus
                </Button>
              </div>
              <input ref="homeImageInput" type="file" accept="image/*" class="hidden" @change="handleHomeChange" />
              <p v-if="form.errors.home_image" class="mt-2 text-xs text-red-600">{{ form.errors.home_image }}</p>
            </div>
          </div>

          <div class="space-y-3">
            <Label>Banner Hero (Halaman Detail Koleksi)</Label>
            <div class="rounded-lg border border-dashed border-slate-200 bg-slate-50 p-4">
              <div v-if="heroPreview" class="relative overflow-hidden rounded-lg border border-slate-200 bg-white">
                <img :src="heroPreview" alt="Preview banner hero" class="h-40 w-full object-cover" />
                <button v-if="form.cover_image" type="button"
                  class="absolute right-2 top-2 inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/90 text-slate-700 shadow hover:bg-white"
                  @click="removeHeroImage" aria-label="Hapus gambar">
                  <X class="h-4 w-4" />
                </button>
              </div>
              <div v-else class="flex flex-col items-center justify-center gap-2 text-center">
                <Upload class="h-6 w-6 text-slate-400" />
                <p class="text-sm font-semibold text-slate-700">Upload banner hero untuk halaman detail</p>
                <p class="text-xs text-slate-500">Rekomendasi rasio 16:7, maks 2MB</p>
              </div>
              <div class="mt-3 flex flex-wrap items-center gap-2">
                <Button type="button" variant="outline" size="sm" @click="triggerHeroInput">
                  Pilih Gambar
                </Button>
                <Button v-if="form.cover_image" type="button" variant="outline" size="sm" @click="removeHeroImage">
                  <X class="h-4 w-4" />
                  Hapus
                </Button>
              </div>
              <input ref="heroImageInput" type="file" accept="image/*" class="hidden" @change="handleHeroChange" />
              <p v-if="form.errors.cover_image" class="mt-2 text-xs text-red-600">{{ form.errors.cover_image }}</p>
            </div>
          </div>

          <div class="space-y-2">
            <Label for="title">Judul</Label>
            <Input id="title" v-model="form.title" :class="{ 'border-red-500': form.errors.title }" />
            <p v-if="form.errors.title" class="text-xs text-red-600">{{ form.errors.title }}</p>
          </div>

          <div class="space-y-2">
            <Label for="color_theme">Tema Warna</Label>
            <Select v-model="form.color_theme">
              <SelectTrigger>
                <SelectValue placeholder="Pilih tema warna" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="none">Tanpa warna</SelectItem>
                <SelectItem v-for="theme in colorThemes" :key="theme.value" :value="theme.value">
                  {{ theme.label }}
                </SelectItem>
              </SelectContent>
            </Select>
            <p v-if="form.errors.color_theme" class="text-xs text-red-600">{{ form.errors.color_theme }}</p>
            <div class="mt-2 h-10 w-full rounded-lg"
              :class="[form.color_theme === 'none' ? 'bg-slate-100' : 'bg-linear-to-r', form.color_theme === 'none' ? '' : form.color_theme]" />
          </div>

          <div class="space-y-2">
            <Label for="display_mode">Tipe Tampilan di Home</Label>
            <Select v-model="form.display_mode">
              <SelectTrigger>
                <SelectValue placeholder="Pilih tipe tampilan" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="image_only">Gambar saja</SelectItem>
                <SelectItem value="slider">Gambar + slider produk</SelectItem>
              </SelectContent>
            </Select>
            <p v-if="form.errors.display_mode" class="text-xs text-red-600">{{ form.errors.display_mode }}</p>
            <p class="text-xs text-slate-500">
              Pilih <span class="font-semibold">Gambar saja</span> untuk menampilkan banner klikable di sela koleksi.
            </p>
          </div>

          <div class="space-y-2">
            <Label for="description">Deskripsi</Label>
            <Textarea id="description" v-model="form.description" rows="3"
              :class="{ 'border-red-500': form.errors.description }" />
            <p v-if="form.errors.description" class="text-xs text-red-600">{{ form.errors.description }}</p>
          </div>

          <div v-if="!isImageOnlyMode" class="space-y-3">
            <div class="flex items-center justify-between gap-3">
              <Label>Produk dalam koleksi</Label>
              <span class="text-xs text-slate-500">{{ form.product_ids.length }} produk dipilih</span>
            </div>

            <div v-if="selectedProducts.length" class="flex flex-wrap gap-2">
              <button v-for="product in selectedProducts" :key="`selected-${product.id}`" type="button"
                class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-700 hover:bg-slate-200"
                @click="removeProduct(product.id)" :title="`Hapus ${product.name}`">
                <span class="max-w-[240px] truncate">{{ product.name }}</span>
                <X class="h-3.5 w-3.5" />
              </button>
            </div>
            <div v-else class="rounded-lg border border-slate-200 bg-white p-4 text-sm text-slate-500">
              Belum ada produk dipilih.
            </div>

            <div class="space-y-2">
              <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                  <Search class="h-4 w-4" />
                </span>
                <Input v-model="productSearch" placeholder="Cari produk untuk ditambahkan..." class="pl-9" />
              </div>
              <div class="max-h-56 overflow-auto rounded-lg border border-slate-200 bg-white">
                <div v-if="!products.length" class="p-4 text-sm text-slate-500">
                  Tidak ada hasil.
                </div>
                <div v-for="product in products" :key="`product-${product.id}`"
                  class="flex items-center justify-between gap-3 border-b border-slate-100 p-3 last:border-b-0">
                  <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-slate-900">{{ product.name }}</p>
                    <p class="text-xs text-slate-500">ID: {{ product.id }}</p>
                  </div>
                  <Button type="button" size="sm" variant="outline" @click="addProduct(product)"
                    :disabled="form.product_ids.includes(product.id)">
                    <Plus class="h-4 w-4" />
                    Tambah
                  </Button>
                </div>
              </div>
              <p v-if="form.errors.product_ids" class="text-xs text-red-600">{{ form.errors.product_ids }}</p>
            </div>
          </div>
          <div v-else class="rounded-lg border border-slate-200 bg-white p-4 text-sm text-slate-600">
            Mode <span class="font-semibold">Gambar saja</span>: koleksi ini tidak menampilkan slider produk di Home.
          </div>

          <div class="flex items-center gap-2">
            <Switch id="is_active" :checked="form.is_active" @update:checked="(val) => (form.is_active = val)" />
            <Label for="is_active" class="font-normal cursor-pointer">Aktif</Label>
          </div>

          <div class="flex gap-3">
            <Button type="submit" :disabled="form.processing">Simpan</Button>
            <Button variant="outline" type="button" as-child>
              <Link href="/admin/collections">Batal</Link>
            </Button>
          </div>
        </form>
      </CardContent>
    </Card>
  </div>
</template>
