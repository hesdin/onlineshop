<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

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
  if (props.mode === 'reset') return 'Atur Ulang Kata Sandi';
  if (props.mode === 'force') return 'Perbarui Kata Sandi';
  return 'Buat Kata Sandi Baru';
});

const description = computed(() => {
  if (props.mode === 'reset') return 'Kata sandi baru harus berbeda dari yang sebelumnya.';
  if (props.mode === 'force') return 'Demi keamanan, mohon perbarui kata sandi Anda.';
  return 'Langkah terakhir untuk mengaktifkan akun Anda.';
});

const submit = () => {
  form.post(actionUrl.value, {
    preserveScroll: true,
  });
};
</script>

<template>

  <Head title="Setel Password Baru" />

  <section class="min-h-screen bg-slate-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
      <!-- Card -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-8 sm:p-10">
        <!-- Icon -->
        <div class="flex justify-center mb-6">
          <div class="flex h-16 w-16 items-center justify-center rounded-full bg-sky-50">
            <svg class="h-8 w-8 text-sky-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
              stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="16" r="1" />
              <rect x="3" y="10" width="18" height="12" rx="2" />
              <path d="M7 10V7a5 5 0 0 1 10 0v3" />
            </svg>
          </div>
        </div>

        <!-- Title -->
        <h1 class="text-2xl font-bold text-slate-900 text-center">{{ heading }}</h1>
        <p class="mt-2 text-sm text-slate-500 text-center">{{ description }}</p>

        <!-- Form -->
        <form @submit.prevent="submit" class="mt-8 space-y-5">
          <!-- Password -->
          <div class="space-y-1.5">
            <label for="password" class="text-sm font-medium text-slate-700">Kata Sandi</label>
            <div class="relative">
              <input id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'"
                placeholder="••••••••" required
                class="w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-sky-400 focus:outline-none focus:ring-1 focus:ring-sky-400"
                :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-400': form.errors.password }" />
              <button type="button" @click="showPassword = !showPassword"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                <svg v-if="showPassword" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="1.5">
                  <path d="M1 12s3.5-7 11-7 11 7 11 7-3.5 7-11 7S1 12 1 12Z" />
                  <circle cx="12" cy="12" r="3" />
                </svg>
                <svg v-else class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24" />
                  <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7.5 0 11 7 11 7a13.16 13.16 0 0 1-1.67 2.68" />
                  <path d="M6.61 6.61A13.526 13.526 0 0 0 1 12s3.5 7 11 7a9.74 9.74 0 0 0 5.39-1.61" />
                  <line x1="2" y1="2" x2="22" y2="22" />
                </svg>
              </button>
            </div>
            <p class="text-xs text-slate-400">Minimal 8 karakter.</p>
            <p v-if="form.errors.password" class="text-xs text-red-500">{{ form.errors.password }}</p>
          </div>

          <!-- Confirm Password -->
          <div class="space-y-1.5">
            <label for="password_confirmation" class="text-sm font-medium text-slate-700">Konfirmasi Kata Sandi</label>
            <div class="relative">
              <input id="password_confirmation" v-model="form.password_confirmation"
                :type="showConfirmation ? 'text' : 'password'" placeholder="••••••••" required
                class="w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-sky-400 focus:outline-none focus:ring-1 focus:ring-sky-400" />
              <button type="button" @click="showConfirmation = !showConfirmation"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                <svg v-if="showConfirmation" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="1.5">
                  <path d="M1 12s3.5-7 11-7 11 7 11 7-3.5 7-11 7S1 12 1 12Z" />
                  <circle cx="12" cy="12" r="3" />
                </svg>
                <svg v-else class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24" />
                  <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7.5 0 11 7 11 7a13.16 13.16 0 0 1-1.67 2.68" />
                  <path d="M6.61 6.61A13.526 13.526 0 0 0 1 12s3.5 7 11 7a9.74 9.74 0 0 0 5.39-1.61" />
                  <line x1="2" y1="2" x2="22" y2="22" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Submit Button -->
          <button type="submit" :disabled="form.processing"
            class="w-full flex items-center justify-center gap-2 rounded-lg bg-sky-500 py-3 text-sm font-semibold text-white transition hover:bg-sky-600 disabled:bg-slate-200 disabled:text-slate-400">
            <svg v-if="form.processing" class="h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none"
              viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
              </path>
            </svg>
            {{ form.processing ? 'Menyimpan' : 'Simpan Kata Sandi' }}
          </button>
        </form>

        <!-- Back to Login -->
        <div class="mt-6 text-center">
          <Link href="/login" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-sky-600">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round">
              <path d="M19 12H5M12 19l-7-7 7-7" />
            </svg>
            Kembali ke halaman login
          </Link>
        </div>
      </div>
    </div>
  </section>
</template>
