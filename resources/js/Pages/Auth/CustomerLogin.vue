<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import AlertBanner from '@/components/AlertBanner.vue';

const form = useForm({
  email: '',
  password: '',
});

const resendForm = useForm({});

const loginCustomerImage = '/images/login-customer.png';

const showPassword = ref(false);
const localSuccess = ref('');
const resendSuccess = ref('');

// Detect if error is about email verification
const isVerificationError = computed(() => {
  const emailError = form.errors.email || '';
  return emailError.includes('verifikasi') || emailError.includes('terverifikasi');
});

const submit = () => {
  resendSuccess.value = '';
  form.post('/customer/login', {
    replace: true,
    onFinish: () => form.reset('password'),
  });
};

const resendVerification = () => {
  if (!form.email) return;

  resendForm.transform(() => ({
    email: form.email,
  })).post('/register/customer/resend', {
    preserveScroll: true,
    onSuccess: () => {
      resendSuccess.value = 'Email verifikasi telah dikirim ulang. Silakan cek inbox atau folder spam Anda.';
      form.clearErrors('email');
    },
  });
};

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success ?? '');

watch(
  flashSuccess,
  (newVal) => {
    if (newVal) {
      localSuccess.value = newVal;
      setTimeout(() => {
        localSuccess.value = '';
      }, 8000);
    }
  },
  { immediate: true }
);

const closeAlert = () => {
  localSuccess.value = '';
};

const closeResendAlert = () => {
  resendSuccess.value = '';
};
</script>

<template>

  <Head title="Login Pembeli" />

  <section class="min-h-screen bg-[#E5E5E5] flex items-center justify-center p-4 lg:p-8">
    <div class="w-full max-w-5xl">
      <div class="bg-white rounded-md shadow-sm overflow-hidden lg:flex min-h-[600px]">

        <!-- Left Side - Hero -->
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-sky-500 to-sky-600 p-10 flex-col justify-center">
          <div class="space-y-4">
            <img :src="loginCustomerImage" alt="Login Customer" class="w-full max-w-[220px] object-contain mx-auto" />
            <h2 class="text-2xl font-bold text-white">Selamat Datang Kembali</h2>
            <p class="text-sky-100 leading-relaxed">
              Masuk ke akun Anda untuk melanjutkan berbelanja produk UMKM terbaik dari seluruh Indonesia.
            </p>
          </div>

          <div class="mt-10 space-y-4">
            <div class="flex items-center gap-3">
              <div class="flex h-8 w-8 items-center justify-center rounded-full bg-white/20">
                <svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
              </div>
              <span class="text-sm text-white">Belanja aman & terpercaya</span>
            </div>
            <div class="flex items-center gap-3">
              <div class="flex h-8 w-8 items-center justify-center rounded-full bg-white/20">
                <svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
              </div>
              <span class="text-sm text-white">Ribuan produk UMKM berkualitas</span>
            </div>
            <div class="flex items-center gap-3">
              <div class="flex h-8 w-8 items-center justify-center rounded-full bg-white/20">
                <svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
              </div>
              <span class="text-sm text-white">Pengiriman ke seluruh Indonesia</span>
            </div>
          </div>
        </div>

        <!-- Right Side - Form -->
        <div class="lg:w-1/2 p-8 sm:p-10 flex flex-col justify-center">
          <!-- Back Arrow -->
          <Link href="/login"
            class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-sky-600 mb-4 w-fit">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M19 12H5M12 19l-7-7 7-7" />
            </svg>
            Kembali
          </Link>

          <h1 class="text-2xl font-bold text-slate-900">Login Pembeli</h1>
          <p class="mt-2 text-sm text-slate-500">Masuk ke akun pembeli Anda</p>

          <!-- Success Alert -->
          <AlertBanner type="success" :message="localSuccess" :show="!!localSuccess" :dismissible="true" class="mt-6"
            @close="closeAlert" />

          <!-- Resend Success Alert -->
          <AlertBanner type="success" :message="resendSuccess" :show="!!resendSuccess" :dismissible="true" class="mt-6"
            @close="closeResendAlert" />

          <form class="mt-6 space-y-4" @submit.prevent="submit">
            <!-- Email -->
            <div class="space-y-1.5">
              <label for="email" class="text-sm font-medium text-slate-700">Email</label>
              <input id="email" v-model="form.email" type="email" autocomplete="email" required
                placeholder="nama@email.com"
                class="w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-sky-400 focus:outline-none focus:ring-1 focus:ring-sky-400"
                :class="{ 'border-red-400': form.errors.email }" :disabled="form.processing" />
              <p v-if="form.errors.email" class="text-xs text-red-500">{{ form.errors.email }}</p>

              <!-- Resend Verification Button -->
              <div v-if="isVerificationError && form.email" class="mt-2">
                <button type="button" @click="resendVerification" :disabled="resendForm.processing"
                  class="inline-flex items-center gap-2 text-sm font-medium text-sky-600 hover:text-sky-700 hover:underline disabled:opacity-50">
                  <svg v-if="resendForm.processing" class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                  </svg>
                  <svg v-else class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path
                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                  </svg>
                  {{ resendForm.processing ? 'Mengirim...' : 'Kirim Ulang Email Verifikasi' }}
                </button>
              </div>
            </div>

            <!-- Password -->
            <div class="space-y-1.5">
              <label for="password" class="text-sm font-medium text-slate-700">Kata Sandi</label>
              <div
                class="flex items-center rounded-lg border border-slate-200 focus-within:border-sky-400 focus-within:ring-1 focus-within:ring-sky-400"
                :class="{ 'border-red-400': form.errors.password }">
                <input id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'"
                  autocomplete="current-password" required placeholder="Masukkan kata sandi"
                  class="w-full bg-white px-4 py-3 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none rounded-lg"
                  :disabled="form.processing" />
                <button type="button" @click="showPassword = !showPassword"
                  class="px-3 text-slate-400 hover:text-slate-600">
                  <svg v-if="!showPassword" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
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
              <p v-if="form.errors.password" class="text-xs text-red-500">{{ form.errors.password }}</p>
            </div>

            <!-- Forgot Password Link -->
            <div class="text-right">
              <Link href="/forgot-password" class="text-sm font-medium text-sky-600 hover:underline">
                Lupa kata sandi?
              </Link>
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
              {{ form.processing ? 'Memproses' : 'Masuk' }}
            </button>
          </form>

          <!-- Register Link -->
          <div class="mt-6 pt-6 border-t border-slate-100 text-center">
            <p class="text-sm text-slate-500">
              Belum punya akun?
              <Link href="/register-as" class="font-medium text-sky-600 hover:underline">Daftar Sekarang</Link>
            </p>
          </div>
        </div>

      </div>
    </div>
  </section>
</template>
