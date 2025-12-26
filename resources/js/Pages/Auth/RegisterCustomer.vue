<script setup lang="ts">
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import { computed, ref, watch, onUnmounted } from 'vue';

const form = useForm({
  name: '',
  email: '',
  phone: '',
  referral: '',
  agree: false,
  notRobot: false,
  'g-recaptcha-response': '',
});

const registrationSuccess = ref(false);
const sentEmail = ref('');
const page = usePage();
const siteKey = computed(() => (page.props.recaptcha as any)?.siteKey ?? '');
const flashSuccess = computed(() => (page.props.flash as any)?.success ?? '');
const loadingRecaptcha = ref(false);
const cooldownSeconds = ref(0);
const resending = ref(false);
let cooldownInterval: ReturnType<typeof setInterval> | null = null;

const canResend = computed(() => cooldownSeconds.value === 0 && !resending.value);

const startCooldown = () => {
  if (cooldownInterval) clearInterval(cooldownInterval);
  cooldownSeconds.value = 150; // 2.5 minutes
  cooldownInterval = setInterval(() => {
    if (cooldownSeconds.value > 0) {
      cooldownSeconds.value--;
    }
    if (cooldownSeconds.value <= 0) {
      clearInterval(cooldownInterval!);
      cooldownInterval = null;
    }
  }, 1000);
};

const formatCooldown = computed(() => {
  const minutes = Math.floor(cooldownSeconds.value / 60);
  const seconds = cooldownSeconds.value % 60;
  return `${minutes}:${seconds.toString().padStart(2, '0')}`;
});

const resendActivation = () => {
  if (!canResend.value) return;
  resending.value = true;
  router.post('/register/customer/resend', { email: sentEmail.value }, {
    preserveScroll: true,
    onSuccess: () => {
      startCooldown();
    },
    onFinish: () => {
      resending.value = false;
    },
  });
};

onUnmounted(() => {
  if (cooldownInterval) clearInterval(cooldownInterval);
});

let recaptchaScriptPromise: Promise<void> | null = null;
const loadRecaptchaScript = () => {
  if (typeof window === 'undefined') return Promise.reject();
  if ((window as any).grecaptcha) return Promise.resolve();
  if (recaptchaScriptPromise) return recaptchaScriptPromise;

  recaptchaScriptPromise = new Promise((resolve, reject) => {
    const script = document.createElement('script');
    script.src = `https://www.google.com/recaptcha/api.js?render=${siteKey.value}`;
    script.async = true;
    script.onload = () => resolve();
    script.onerror = () => reject(new Error('Gagal memuat reCAPTCHA'));
    document.head.appendChild(script);
  });

  return recaptchaScriptPromise;
};

const requestRecaptchaToken = async (action: string) => {
  if (!siteKey.value) {
    throw new Error('Site key reCAPTCHA belum disetel.');
  }

  await loadRecaptchaScript();

  return new Promise<string>((resolve, reject) => {
    const grecaptcha = (window as any).grecaptcha;
    if (!grecaptcha) {
      reject(new Error('reCAPTCHA belum tersedia'));
      return;
    }

    grecaptcha.ready(() => {
      grecaptcha
        .execute(siteKey.value, { action })
        .then(resolve)
        .catch((err: unknown) => reject(err));
    });
  });
};

watch(
  () => form.agree,
  (val) => {
    if (val) {
      form.clearErrors('agree');
    }
  }
);

watch(
  () => form.notRobot,
  (val) => {
    if (val) {
      form.clearErrors('notRobot');
    }
  }
);

const handleRecaptcha = async () => {
  if (loadingRecaptcha.value) return;

  loadingRecaptcha.value = true;
  form.notRobot = false;
  form['g-recaptcha-response'] = '';
  form.clearErrors('notRobot');

  try {
    const token = await requestRecaptchaToken('register_customer');
    form['g-recaptcha-response'] = token;
    form.notRobot = true;
  } catch (error) {
    form.notRobot = false;
    form.setError('notRobot', 'Gagal memverifikasi reCAPTCHA.');
    console.error(error);
  } finally {
    loadingRecaptcha.value = false;
  }
};

const submit = async () => {
  form.clearErrors();

  if (!form.agree) {
    form.setError('agree', 'Anda harus menyetujui syarat dan ketentuan.');
    return;
  }

  if (!form.notRobot) {
    form.setError('notRobot', 'Silakan verifikasi bahwa Anda bukan robot.');
    return;
  }

  await form.post('/register/customer', {
    preserveScroll: true,
    onSuccess: () => {
      sentEmail.value = form.email;
      registrationSuccess.value = true;
      startCooldown();
    },
  });
};
</script>

<template>

  <Head title="Daftar Pembeli" />

  <section class="min-h-screen bg-slate-50 flex items-center justify-center p-4 py-8">
    <div class="w-full max-w-lg">
      <!-- Card -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-8 sm:p-10">

        <!-- Success View (After Registration) -->
        <template v-if="registrationSuccess">
          <!-- Icon -->
          <div class="flex justify-center mb-6">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-sky-50">
              <svg class="h-8 w-8 text-sky-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="4" width="20" height="16" rx="2" />
                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
              </svg>
            </div>
          </div>

          <!-- Title -->
          <h1 class="text-2xl font-bold text-slate-900 text-center">Cek Email Anda</h1>
          <p class="mt-2 text-sm text-slate-500 text-center">
            Kami telah mengirimkan tautan aktivasi ke
            <span class="font-medium text-slate-700">{{ sentEmail }}</span>
          </p>

          <!-- Open Gmail Button -->
          <a href="https://mail.google.com" target="_blank"
            class="mt-8 w-full flex items-center justify-center gap-2 rounded-lg bg-sky-500 py-3 text-sm font-semibold text-white transition hover:bg-sky-600">
            Buka Gmail
          </a>

          <!-- Resend Link -->
          <div class="mt-5 text-center">
            <p class="text-sm text-slate-500">
              Tidak menerima email?
            </p>
            <button v-if="canResend" type="button" @click="resendActivation"
              class="mt-1 font-medium text-sm text-sky-600 hover:text-sky-700 hover:underline">
              Kirim ulang tautan
            </button>
            <p v-else class="mt-1 text-sm text-slate-400">
              <span v-if="resending" class="inline-flex items-center gap-1.5">
                <svg class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                  </path>
                </svg>
                Mengirim ulang...
              </span>
              <span v-else>Kirim ulang dalam <span class="font-medium text-slate-500">{{ formatCooldown }}</span></span>
            </p>
          </div>

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
        </template>

        <!-- Registration Form View -->
        <template v-else>
          <!-- Back Arrow -->
          <Link href="/register-as"
            class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-sky-600 mb-4 w-fit">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M19 12H5M12 19l-7-7 7-7" />
            </svg>
            Kembali
          </Link>

          <!-- Icon -->
          <div class="flex justify-center mb-6">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-sky-50">
              <svg class="h-8 w-8 text-sky-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                <circle cx="9" cy="7" r="4" />
                <line x1="19" y1="8" x2="19" y2="14" />
                <line x1="22" y1="11" x2="16" y2="11" />
              </svg>
            </div>
          </div>

          <!-- Title -->
          <h1 class="text-2xl font-bold text-slate-900 text-center">Buat Akun</h1>
          <p class="mt-2 text-sm text-slate-500 text-center">
            Daftar untuk mulai berbelanja
          </p>

          <!-- Form -->
          <form @submit.prevent="submit" class="mt-8 space-y-4">
            <!-- Name -->
            <div class="space-y-1.5">
              <label for="name" class="text-sm font-medium text-slate-700">Nama Lengkap</label>
              <input id="name" v-model="form.name" type="text" required placeholder="Masukkan nama lengkap"
                class="w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-sky-400 focus:outline-none focus:ring-1 focus:ring-sky-400"
                :class="{ 'border-red-400': form.errors.name }" />
              <p v-if="form.errors.name" class="text-xs text-red-500">{{ form.errors.name }}</p>
            </div>

            <!-- Email -->
            <div class="space-y-1.5">
              <label for="email" class="text-sm font-medium text-slate-700">Email</label>
              <input id="email" v-model="form.email" type="email" required placeholder="nama@email.com"
                class="w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-sky-400 focus:outline-none focus:ring-1 focus:ring-sky-400"
                :class="{ 'border-red-400': form.errors.email }" />
              <p v-if="form.errors.email" class="text-xs text-red-500">{{ form.errors.email }}</p>
            </div>

            <!-- Phone -->
            <div class="space-y-1.5">
              <label for="phone" class="text-sm font-medium text-slate-700">No. Telepon</label>
              <div
                class="flex rounded-lg border border-slate-200 focus-within:border-sky-400 focus-within:ring-1 focus-within:ring-sky-400"
                :class="{ 'border-red-400': form.errors.phone }">
                <span
                  class="flex items-center border-r border-slate-200 px-3 text-sm text-slate-500 bg-slate-50 rounded-l-lg">+62</span>
                <input id="phone" v-model="form.phone" type="tel" required placeholder="812xxxxxxxx"
                  class="w-full bg-white px-3 py-3 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none rounded-r-lg" />
              </div>
              <p v-if="form.errors.phone" class="text-xs text-red-500">{{ form.errors.phone }}</p>
            </div>

            <!-- Referral -->
            <div class="space-y-1.5">
              <label for="referral" class="text-sm font-medium text-slate-700">
                Kode Referral <span class="font-normal text-slate-400">(Opsional)</span>
              </label>
              <input id="referral" v-model="form.referral" type="text" placeholder="Masukkan kode referral"
                class="w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-sky-400 focus:outline-none focus:ring-1 focus:ring-sky-400" />
            </div>

            <!-- Terms Checkbox -->
            <div class="pt-2">
              <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" v-model="form.agree"
                  class="mt-0.5 h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500" />
                <span class="text-xs text-slate-600 leading-relaxed">
                  Saya menyetujui
                  <a href="#" class="font-medium text-sky-600 hover:underline">Syarat dan Ketentuan</a>
                  serta
                  <a href="#" class="font-medium text-sky-600 hover:underline">Kebijakan Privasi</a>
                </span>
              </label>
              <p v-if="form.errors.agree" class="mt-1 text-xs text-red-500">{{ form.errors.agree }}</p>
            </div>

            <!-- reCAPTCHA Widget -->
            <div class="pt-2">
              <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 px-4 py-2.5">
                <div class="flex items-center gap-2.5">
                  <div
                    class="flex h-6 w-6 cursor-pointer items-center justify-center rounded border-2 bg-white transition-all"
                    :class="form.notRobot ? 'border-green-500' : 'border-slate-300 hover:border-slate-400'"
                    @click="handleRecaptcha">
                    <svg v-if="loadingRecaptcha" class="h-3.5 w-3.5 animate-spin text-sky-500"
                      xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                      </path>
                    </svg>
                    <svg v-else-if="form.notRobot" class="h-4 w-4 text-green-500" viewBox="0 0 24 24" fill="none"
                      stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                      <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                  </div>
                  <span class="text-sm text-slate-600">Saya bukan robot</span>
                </div>
                <div class="flex items-center gap-1">
                  <svg class="h-6 w-6" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                      d="M24 2C11.8 2 2 11.8 2 24C2 36.2 11.8 46 24 46C36.2 46 46 36.2 46 24C46 11.8 36.2 2 24 2ZM36.6 22H32.4C31.8 17.6 28.4 14.2 24 14.2V10C30.8 10 36.4 15.2 36.6 22ZM24 38V33.8C19.6 33.2 16.2 29.8 15.6 25.4H11.4C12 32.8 17.2 38 24 38ZM11.4 22.6H15.6C16.2 18.2 19.6 14.8 24 14.2V10C17.2 10 12 15.2 11.4 22.6ZM24 33.8V38C30.8 38 36.4 32.8 36.6 25.4H32.4C31.8 29.8 28.4 33.2 24 33.8Z"
                      fill="#bbdefb" />
                    <path
                      d="M44 24C44 13 35 4 24 4V8C32.8 8 40 15.2 40 24H44ZM24 44C35 44 44 35 44 24H40C40 32.8 32.8 40 24 40V44ZM4 24C4 35 13 44 24 44V40C15.2 40 8 32.8 8 24H4ZM24 4V8C15.2 8 8 15.2 8 24H4C4 13 13 4 24 4Z"
                      fill="#e3f2fd" opacity="0.8" />
                  </svg>
                  <span class="text-[9px] text-slate-400">reCAPTCHA</span>
                </div>
              </div>
              <p v-if="form.errors.notRobot" class="mt-1 text-xs text-red-500">{{ form.errors.notRobot }}</p>
            </div>

            <!-- Submit Button -->
            <button type="submit" :disabled="form.processing || loadingRecaptcha || !form.agree || !form.notRobot"
              class="w-full flex items-center justify-center gap-2 rounded-lg bg-sky-500 py-3 text-sm font-semibold text-white transition hover:bg-sky-600 disabled:bg-slate-200 disabled:text-slate-400">
              <svg v-if="form.processing" class="h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
              </svg>
              {{ form.processing ? 'Mendaftarkan' : 'Daftar' }}
            </button>
          </form>

          <!-- Login Link -->
          <div class="mt-6 text-center">
            <p class="text-sm text-slate-500">
              Sudah punya akun?
              <Link href="/login" class="font-medium text-sky-600 hover:underline">Masuk</Link>
            </p>
          </div>

          <!-- reCAPTCHA Branding -->
          <div class="mt-4 text-center text-[10px] text-slate-400">
            Dilindungi oleh reCAPTCHA
            <a href="https://www.google.com/intl/en/policies/privacy/" target="_blank"
              class="hover:underline">Privacy</a>
            &
            <a href="https://www.google.com/intl/en/policies/terms/" target="_blank" class="hover:underline">Terms</a>
          </div>
        </template>

      </div>
    </div>
  </section>
</template>

<style>
.grecaptcha-badge {
  visibility: hidden !important;
}
</style>
