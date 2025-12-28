<script setup lang="ts">
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Upload, X } from 'lucide-vue-next';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { computed, ref } from 'vue';

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

const form = useForm({
  title: '',
  description: '',
  color_theme: colorThemes[0].value,
  is_active: '1',
  display_mode: 'slider',
  home_image: null as File | null,
  cover_image: null as File | null,
});

const homeImageInput = ref<HTMLInputElement | null>(null);
const heroImageInput = ref<HTMLInputElement | null>(null);

const homePreview = computed(() => (form.home_image ? URL.createObjectURL(form.home_image) : null));
const heroPreview = computed(() => (form.cover_image ? URL.createObjectURL(form.cover_image) : null));

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

const submit = () => {
  form
    .transform((data) => ({
      ...data,
      color_theme: data.color_theme === 'none' ? '' : data.color_theme,
    }))
    .post('/admin/collections', {
      preserveScroll: true,
      forceFormData: true,
    });
};
</script>

<template>
  <div class="space-y-6">

    <Head title="Tambah Koleksi" />

    <div class="flex items-center justify-between gap-3">
      <h1 class="text-xl font-bold tracking-tight text-slate-900">Tambah Koleksi Baru</h1>
      <Button variant="outline" size="sm" @click="router.visit('/admin/collections')" :disabled="form.processing">
        Kembali
      </Button>
    </div>

    <Card>
      <CardHeader>
        <CardTitle>Informasi Koleksi</CardTitle>
        <CardDescription>Tambahkan koleksi produk baru</CardDescription>
      </CardHeader>
      <CardContent>
        <form @submit.prevent="submit" class="space-y-6">
          <div class="space-y-3">
            <Label>Gambar Home (Kartu Koleksi di Beranda)</Label>
            <div class="max-w-sm rounded-lg border border-dashed border-slate-200 bg-slate-50 p-4">
              <div v-if="homePreview"
                class="relative overflow-hidden rounded-lg border border-slate-200 bg-white aspect-[4/3]">
                <img :src="homePreview" alt="Preview gambar home" class="h-full w-full object-cover" />
                <button type="button"
                  class="absolute right-2 top-2 inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/90 text-slate-700 shadow hover:bg-white"
                  @click="removeHomeImage" aria-label="Hapus gambar">
                  <X class="h-4 w-4" />
                </button>
              </div>
              <div v-else class="flex flex-col items-center justify-center gap-2 text-center">
                <Upload class="h-6 w-6 text-slate-400" />
                <p class="text-sm font-semibold text-slate-700">Upload gambar untuk kartu koleksi</p>
                <p class="text-xs text-slate-500">Rekomendasi rasio 4:3, maks 2MB</p>
                <Button type="button" variant="outline" size="sm" @click="triggerHomeInput">
                  Pilih Gambar
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
                <button type="button"
                  class="absolute right-2 top-2 inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/90 text-slate-700 shadow hover:bg-white"
                  @click="removeHeroImage" aria-label="Hapus gambar">
                  <X class="h-4 w-4" />
                </button>
              </div>
              <div v-else class="flex flex-col items-center justify-center gap-2 text-center">
                <Upload class="h-6 w-6 text-slate-400" />
                <p class="text-sm font-semibold text-slate-700">Upload banner hero untuk halaman detail</p>
                <p class="text-xs text-slate-500">Rekomendasi rasio 16:7, maks 2MB</p>
                <Button type="button" variant="outline" size="sm" @click="triggerHeroInput">
                  Pilih Gambar
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

          <div class="space-y-2 pt-2">
            <Label for="is_active">Status Koleksi <span class="text-red-500">*</span></Label>
            <Select v-model="form.is_active">
              <SelectTrigger id="is_active">
                <SelectValue placeholder="Pilih status" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="1">Aktif</SelectItem>
                <SelectItem value="0">Nonaktif</SelectItem>
              </SelectContent>
            </Select>
            <p v-if="form.errors.is_active" class="text-sm text-red-500">{{ form.errors.is_active }}</p>
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
