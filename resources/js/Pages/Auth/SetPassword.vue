<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { ShieldCheck, Lock, Eye, EyeOff, KeyRound } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import CustomerCareButton from '@/components/CustomerCareButton.vue';

const props = defineProps<{
  mode: 'verify' | 'reset' | 'force';
  signedUrl?: string | null;
}>();

const form = useForm({
  password: '',
  password_confirmation: '',
});

const showPassword = ref(false);
const showConfirmation = ref(false);

const actionUrl = computed(() => props.signedUrl ?? '/force-password');

const heading = computed(() => {
  if (props.mode === 'reset') return 'Atur ulang kata sandi';
  if (props.mode === 'force') return 'Perbarui kata sandi';
  return 'Buat kata sandi baru';
});

const description = computed(() => {
  if (props.mode === 'reset') return 'Amankan akun Anda dengan kata sandi baru.';
  if (props.mode === 'force') return 'Demi keamanan, mohon perbarui kata sandi Anda.';
  return 'Langkah terakhir untuk mengaktifkan akun Anda.';
});

const badgeText = computed(() => (props.mode === 'force' ? 'Wajib diperbarui' : 'Tautan aman'));

const submit = () => {
  form.post(actionUrl.value, {
    preserveScroll: true,
  });
};
</script>

<template>

  <Head title="Setel Password Baru" />

  <section class="min-h-screen bg-slate-50 flex items-center justify-center p-4 lg:p-8 relative overflow-hidden">
    <!-- Simplified blobs for cleaner look -->
    <div class="absolute top-0 right-0 p-12 opacity-50 blur-3xl pointer-events-none">
      <div class="w-64 h-64 bg-sky-100 rounded-full mix-blend-multiply"></div>
    </div>
    <div class="absolute bottom-0 left-0 p-12 opacity-50 blur-3xl pointer-events-none">
      <div class="w-64 h-64 bg-emerald-50 rounded-full mix-blend-multiply"></div>
    </div>

    <div class="w-full max-w-5xl grid lg:grid-cols-2 gap-8 lg:gap-16 items-center relative z-10">

      <!-- Left Info Side -->
      <div class="hidden lg:flex flex-col space-y-8">
        <div class="space-y-4">
          <Badge variant="outline" class="w-fit gap-2 py-1.5 px-3 bg-sky-50 text-sky-700 border-sky-100">
            <ShieldCheck class="w-4 h-4 text-emerald-500" />
            Keamanan Akun
          </Badge>
          <h1 class="text-4xl font-bold tracking-tight text-slate-900 leading-tight">
            {{ heading }}
          </h1>
          <p class="text-slate-500 text-lg leading-relaxed max-w-md">
            {{ description }}
          </p>
        </div>

        <div class="grid gap-4">
          <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 p-4 rounded-xl flex items-start gap-4">
            <div class="p-2.5 bg-sky-50 rounded-lg text-sky-600">
              <Lock class="w-5 h-5" />
            </div>
            <div>
              <h3 class="font-semibold text-slate-900">Tautan Terenkripsi</h3>
              <p class="text-sm text-slate-500 mt-1">Halaman ini diamankan dengan enkripsi end-to-end.</p>
            </div>
          </div>

          <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 p-4 rounded-xl flex items-start gap-4">
            <div class="p-2.5 bg-emerald-50 rounded-lg text-emerald-600">
              <KeyRound class="w-5 h-5" />
            </div>
            <div>
              <h3 class="font-semibold text-slate-900">Kata Sandi Kuat</h3>
              <p class="text-sm text-slate-500 mt-1">Gabungkan huruf, angka, dan simbol untuk keamanan maksimal.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Form Side -->
      <div>
        <Card class="border-slate-200 shadow-xl shadow-slate-200/50">
          <CardHeader>
            <div class="flex items-center justify-between">
              <div class="lg:hidden mb-2">
                <div
                  class="inline-flex items-center gap-2 rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700">
                  Keamanan Akun
                </div>
              </div>
              <!-- Badge for "Tautan aman" -->
              <Badge variant="secondary" class="bg-slate-100 text-slate-600 ml-auto">
                {{ badgeText }}
              </Badge>
            </div>

            <CardTitle class="text-2xl pt-2 lg:pt-0">Setel Password</CardTitle>
            <CardDescription>
              Masukkan kata sandi baru yang aman.
            </CardDescription>
          </CardHeader>

          <CardContent>
            <form @submit.prevent="submit" class="space-y-5">

              <div class="space-y-2">
                <Label for="password">Password Baru</Label>
                <div class="relative">
                  <Input id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'"
                    placeholder="Min. 8 karakter" required class="pr-10" />
                  <button type="button" @click="showPassword = !showPassword"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 focus:outline-none">
                    <Eye v-if="showPassword" class="w-4 h-4" />
                    <EyeOff v-else class="w-4 h-4" />
                  </button>
                </div>
                <p v-if="form.errors.password" class="text-sm text-red-500 font-medium">
                  {{ form.errors.password }}
                </p>
              </div>

              <div class="space-y-2">
                <Label for="password_confirmation">Konfirmasi Password</Label>
                <div class="relative">
                  <Input id="password_confirmation" v-model="form.password_confirmation"
                    :type="showConfirmation ? 'text' : 'password'" placeholder="Ulangi password baru" required
                    class="pr-10" />
                  <button type="button" @click="showConfirmation = !showConfirmation"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 focus:outline-none">
                    <Eye v-if="showConfirmation" class="w-4 h-4" />
                    <EyeOff v-else class="w-4 h-4" />
                  </button>
                </div>
              </div>

              <div class="bg-slate-50 p-4 rounded-lg border border-slate-100">
                <h4 class="text-xs font-semibold uppercase text-slate-500 mb-2">Tips Keamanan</h4>
                <ul class="text-xs text-slate-600 space-y-1.5 list-disc list-inside marker:text-slate-400">
                  <li>Minimal 8 karakter (huruf, angka, simbol)</li>
                  <li>Hindari menggunakan data pribadi (nama/tgl lahir)</li>
                  <li>Jangan gunakan password yang sudah pernah dipakai</li>
                </ul>
              </div>

              <Button type="submit" class="w-full bg-sky-600 hover:bg-sky-700 text-white" :disabled="form.processing">
                {{ form.processing ? 'Menyimpan...' : 'Simpan Password' }}
              </Button>

            </form>
          </CardContent>

          <div class="p-6 pt-0 text-center">
            <Link href="/login" class="text-sm text-sky-600 hover:text-sky-700 hover:underline font-medium">
              Kembali ke halaman login
            </Link>
          </div>
        </Card>
      </div>
    </div>

    <CustomerCareButton />
  </section>
</template>
