<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
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
  if (props.mode === 'reset') {
    return 'Atur ulang kata sandi Anda';
  }

  if (props.mode === 'force') {
    return 'Perbarui kata sandi bawaan sebelum melanjutkan';
  }

  return 'Email terverifikasi, buat kata sandi baru';
});

const description = computed(() => {
  if (props.mode === 'reset') {
    return 'Gunakan tautan aman ini untuk mengganti kata sandi. Pastikan hanya Anda yang memiliki akses ke email ini.';
  }

  if (props.mode === 'force') {
    return 'Demi keamanan, kata sandi bawaan perlu diganti dengan kombinasi yang lebih kuat sebelum Anda menggunakan akun.';
  }

  return 'Terima kasih telah memverifikasi email. Tinggal satu langkah lagi untuk mengaktifkan akun dan mulai bertransaksi.';
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

  <section class="relative flex min-h-screen items-center justify-center overflow-hidden bg-white text-slate-900">
    <div class="pointer-events-none absolute inset-0">
      <span class="absolute -left-24 bottom-10 h-48 w-48 rotate-6 rounded-4xl border border-slate-200/70"></span>
      <span class="absolute -right-16 top-16 h-32 w-32 rounded-full bg-sky-100/80 blur-3xl"></span>
      <span class="absolute -bottom-28 right-1/4 h-56 w-56 rounded-full bg-emerald-100/60 blur-3xl"></span>
      <span class="absolute left-1/3 top-10 h-20 w-20 -rotate-6 rounded-3xl border border-slate-100/70"></span>
    </div>

    <div
      class="relative mx-auto grid w-full max-w-6xl grid-cols-1 gap-10 px-6 py-12 lg:grid-cols-2 lg:items-center lg:gap-16 lg:py-16">
      <div class="space-y-6">
        <div
          class="inline-flex items-center gap-2 rounded-full bg-sky-50 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-sky-700">
          Keamanan Akun
          <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
        </div>
        <h1 class="text-3xl sm:text-4xl font-bold leading-tight text-slate-900">
          {{ heading }}
        </h1>
        <p class="max-w-xl text-sm sm:text-base text-slate-600">
          {{ description }}
        </p>

        <div class="grid gap-4 sm:grid-cols-2">
          <div class="rounded-2xl border border-slate-100 bg-slate-50/60 p-5 shadow-sm">
            <div class="flex items-center gap-3">
              <span class="flex h-10 w-10 items-center justify-center rounded-full bg-sky-100 text-sky-600">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path d="M7 11V7a5 5 0 0 1 10 0v4" stroke-linecap="round" stroke-linejoin="round" />
                  <rect x="5" y="11" width="14" height="10" rx="2" ry="2" />
                  <path d="M12 15v2" stroke-linecap="round" />
                </svg>
              </span>
              <div>
                <p class="text-sm font-semibold text-slate-900">Tautan terenkripsi</p>
                <p class="text-xs text-slate-600">Gunakan tautan ini sebelum kedaluwarsa untuk menjaga akun tetap aman.
                </p>
              </div>
            </div>
          </div>
          <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-3">
              <span class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path d="M12 21s-7-4-7-11V6l7-3 7 3v4c0 7-7 11-7 11Z" stroke-linecap="round"
                    stroke-linejoin="round" />
                  <path d="M9.5 12.5 11 14l3.5-3.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </span>
              <div>
                <p class="text-sm font-semibold text-slate-900">Kata sandi kuat</p>
                <p class="text-xs text-slate-600">Gabungkan huruf besar, kecil, angka, dan simbol unik.</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="relative">
        <div
          class="relative z-10 ml-auto w-full max-w-xl rounded-xl border border-slate-100 bg-white/95 p-8 sm:p-10 shadow-xl">
          <div class="mb-6 flex items-center justify-between gap-4">
            <div>
              <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Langkah terakhir</p>
              <h3 class="text-2xl font-bold text-slate-900">Setel Password Baru</h3>
            </div>
            <div
              class="flex h-10 items-center justify-center rounded-full border border-slate-200 bg-slate-50 px-4 text-[11px] font-bold text-sky-700">
              {{ badgeText }}
            </div>
          </div>

          <form class="space-y-5" @submit.prevent="submit">
            <div class="space-y-2">
              <label for="password" class="text-sm font-semibold text-slate-800">Password Baru</label>
              <div class="relative">
                <input id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'"
                  autocomplete="new-password" required
                  class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 pr-12 text-sm sm:text-base text-slate-800 placeholder:text-slate-400 focus:border-sky-400 focus:bg-white focus:outline-none"
                  placeholder="Minimal 8 karakter" :disabled="form.processing" />
                <button type="button"
                  class="absolute inset-y-0 right-3 flex items-center text-slate-500 transition hover:text-slate-700"
                  @click="showPassword = !showPassword"
                  :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'">
                  <svg v-if="showPassword" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.8">
                    <path d="m3 3 18 18" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8" stroke-linecap="round" stroke-linejoin="round" />
                    <path
                      d="M9.88 4.16a9.75 9.75 0 0 1 10.36 5.34 9.7 9.7 0 0 1-1.57 2.41m-2.13 1.94A9.75 9.75 0 0 1 12 19c-3.9 0-7.23-2.5-9-6a10.88 10.88 0 0 1 2.18-3.18"
                      stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                  <svg v-else class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12Z" stroke-linecap="round"
                      stroke-linejoin="round" />
                    <circle cx="12" cy="12" r="3" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                </button>
              </div>
              <p v-if="form.errors.password" class="text-xs text-red-500">{{ form.errors.password }}</p>
            </div>

            <div class="space-y-2">
              <label for="password_confirmation" class="text-sm font-semibold text-slate-800">Konfirmasi
                Password</label>
              <div class="relative">
                <input id="password_confirmation" v-model="form.password_confirmation"
                  :type="showConfirmation ? 'text' : 'password'" autocomplete="new-password" required
                  class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 pr-12 text-sm sm:text-base text-slate-800 placeholder:text-slate-400 focus:border-sky-400 focus:bg-white focus:outline-none"
                  placeholder="Ulangi password baru" :disabled="form.processing" />
                <button type="button"
                  class="absolute inset-y-0 right-3 flex items-center text-slate-500 transition hover:text-slate-700"
                  @click="showConfirmation = !showConfirmation"
                  :aria-label="showConfirmation ? 'Sembunyikan password' : 'Tampilkan password'">
                  <svg v-if="showConfirmation" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.8">
                    <path d="m3 3 18 18" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8" stroke-linecap="round" stroke-linejoin="round" />
                    <path
                      d="M9.88 4.16a9.75 9.75 0 0 1 10.36 5.34 9.7 9.7 0 0 1-1.57 2.41m-2.13 1.94A9.75 9.75 0 0 1 12 19c-3.9 0-7.23-2.5-9-6a10.88 10.88 0 0 1 2.18-3.18"
                      stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                  <svg v-else class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12Z" stroke-linecap="round"
                      stroke-linejoin="round" />
                    <circle cx="12" cy="12" r="3" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                </button>
              </div>
            </div>

            <div class="rounded-lg bg-slate-50 px-4 py-3 text-sm text-slate-700">
              <p class="font-semibold text-slate-800">Tips keamanan:</p>
              <ul class="mt-2 space-y-1 text-xs sm:text-sm text-slate-600">
                <li>• Minimal 8 karakter dengan huruf besar, kecil, angka, dan simbol.</li>
                <li>• Jangan gunakan ulang kata sandi dari akun lain.</li>
                <li>• Simpan di pengelola kata sandi agar mudah diingat.</li>
              </ul>
            </div>

            <button type="submit" :disabled="form.processing"
              class="flex w-full items-center justify-center rounded-lg bg-sky-600 px-4 py-3 text-sm sm:text-base font-semibold text-white transition hover:bg-sky-700 disabled:bg-slate-200 disabled:text-slate-400">
              {{ form.processing ? 'Menyimpan...' : 'Simpan Password' }}
            </button>
          </form>

          <p class="mt-5 text-center text-sm text-slate-600">
            Sudah ingat kata sandi?
            <Link href="/login" class="font-semibold text-sky-600 hover:underline">Kembali ke login</Link>
          </p>
        </div>
      </div>
    </div>

    <CustomerCareButton />
  </section>
</template>
