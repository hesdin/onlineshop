<script setup lang="ts">
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Upload, X } from 'lucide-vue-next';
import { ref } from 'vue';

defineOptions({
  layout: AdminDashboardLayout,
});

const form = useForm({
  title: '',
  alt_text: '',
  url: '',
  type: 'hero_slider',
  position: 0,
  is_active: '1',
  starts_at: '',
  ends_at: '',
  image: null as File | null,
});

const imagePreview = ref<string | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

const handleFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0];

  if (file) {
    form.image = file;
    imagePreview.value = URL.createObjectURL(file);
  }
};

const removeImage = () => {
  form.image = null;
  imagePreview.value = null;
  if (fileInput.value) {
    fileInput.value.value = '';
  }
};

const triggerFileInput = () => {
  fileInput.value?.click();
};

const submit = () => {
  form.post('/admin/banners', {
    forceFormData: true,
  });
};
</script>

<template>
  <div class="space-y-6">

    <Head title="Tambah Banner" />

    <div class="flex items-center justify-between gap-3">
      <h1 class="text-xl font-bold tracking-tight text-slate-900">Tambah Banner Baru</h1>
      <Button variant="outline" size="sm" @click="router.visit('/admin/banners')" :disabled="form.processing">
        Kembali
      </Button>
    </div>

    <form @submit.prevent="submit" class="space-y-6">
      <div class="grid gap-6 lg:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Informasi Banner</CardTitle>
            <CardDescription>Detail banner yang akan ditampilkan</CardDescription>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="space-y-2">
              <Label for="title">Judul <span class="text-red-500">*</span></Label>
              <Input id="title" v-model="form.title" placeholder="Judul banner" />
              <p v-if="form.errors.title" class="text-sm text-red-500">{{ form.errors.title }}</p>
            </div>

            <div class="space-y-2">
              <Label for="alt_text">Teks Alternatif</Label>
              <Input id="alt_text" v-model="form.alt_text" placeholder="Deskripsi gambar untuk aksesibilitas" />
              <p v-if="form.errors.alt_text" class="text-sm text-red-500">{{ form.errors.alt_text }}</p>
            </div>

            <div class="space-y-2">
              <Label for="url">URL Link</Label>
              <Input id="url" v-model="form.url" placeholder="https://..." />
              <p v-if="form.errors.url" class="text-sm text-red-500">{{ form.errors.url }}</p>
            </div>

            <div class="space-y-2">
              <Label for="type">Tipe Banner <span class="text-red-500">*</span></Label>
              <Select v-model="form.type">
                <SelectTrigger>
                  <SelectValue placeholder="Pilih tipe" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="hero_slider">Hero Slider (Utama)</SelectItem>
                  <SelectItem value="hero_promo">Hero Promo (Samping)</SelectItem>
                </SelectContent>
              </Select>
              <p v-if="form.errors.type" class="text-sm text-red-500">{{ form.errors.type }}</p>
            </div>

            <div class="space-y-2">
              <Label for="position">Posisi</Label>
              <Input id="position" v-model.number="form.position" type="number" min="0" />
              <p class="text-xs text-slate-500">Semakin kecil angka, semakin di depan urutan</p>
              <p v-if="form.errors.position" class="text-sm text-red-500">{{ form.errors.position }}</p>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Gambar & Jadwal</CardTitle>
            <CardDescription>Upload gambar dan atur jadwal tampil</CardDescription>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="space-y-2">
              <Label>Gambar Banner <span class="text-red-500">*</span></Label>
              <div
                class="relative flex h-40 cursor-pointer items-center justify-center rounded-lg border-2 border-dashed border-slate-300 bg-slate-50 transition hover:border-sky-400 hover:bg-sky-50"
                @click="triggerFileInput">
                <template v-if="imagePreview">
                  <img :src="imagePreview" alt="Preview" class="h-full w-full rounded-lg object-cover" />
                  <button type="button"
                    class="absolute right-2 top-2 rounded-full bg-red-500 p-1 text-white hover:bg-red-600"
                    @click.stop="removeImage">
                    <X class="h-4 w-4" />
                  </button>
                </template>
                <template v-else>
                  <div class="flex flex-col items-center gap-2 text-slate-500">
                    <Upload class="h-8 w-8" />
                    <span class="text-sm">Klik untuk upload gambar</span>
                    <span class="text-xs">PNG, JPG, max 2MB</span>
                  </div>
                </template>
              </div>
              <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="handleFileChange" />
              <p v-if="form.errors.image" class="text-sm text-red-500">{{ form.errors.image }}</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
              <div class="space-y-2">
                <Label for="starts_at">Mulai Tampil</Label>
                <Input id="starts_at" v-model="form.starts_at" type="datetime-local" />
                <p v-if="form.errors.starts_at" class="text-sm text-red-500">{{ form.errors.starts_at }}</p>
              </div>

              <div class="space-y-2">
                <Label for="ends_at">Selesai Tampil</Label>
                <Input id="ends_at" v-model="form.ends_at" type="datetime-local" />
                <p v-if="form.errors.ends_at" class="text-sm text-red-500">{{ form.errors.ends_at }}</p>
              </div>
            </div>

            <div class="space-y-2 pt-2">
              <Label for="is_active">Status Banner <span class="text-red-500">*</span></Label>
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
          </CardContent>
        </Card>
      </div>

      <div class="flex justify-end gap-3">
        <Button variant="outline" as-child>
          <Link href="/admin/banners">Batal</Link>
        </Button>
        <Button type="submit" :disabled="form.processing">
          {{ form.processing ? 'Menyimpan...' : 'Simpan Banner' }}
        </Button>
      </div>
    </form>
  </div>
</template>
