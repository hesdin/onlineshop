<script setup lang="ts">
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import { computed, ref, watch, onUnmounted } from 'vue';

const registerSellerImage = '/images/register-seller.png';

const form = useForm({
  owner_name: '',
  email: '',
  phone: '',
  referral: '',
  agree: false,
  notRobot: false,
  'g-recaptcha-response': '',
});

const registrationSuccess = ref(false);
const sentEmail = ref('');
const showSellerAgreementModal = ref(false);
const showPrivacyPolicyModal = ref(false);
const page = usePage();
const siteKey = computed(() => (page.props.recaptcha as any)?.siteKey ?? '');
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
  router.post('/register/seller/resend', { email: sentEmail.value }, {
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
    if (val) form.clearErrors('agree');
  }
);

watch(
  () => form.notRobot,
  (val) => {
    if (val) form.clearErrors('notRobot');
  }
);

const handleRecaptcha = async () => {
  if (loadingRecaptcha.value) return;

  loadingRecaptcha.value = true;
  form.notRobot = false;
  form['g-recaptcha-response'] = '';
  form.clearErrors('notRobot');

  try {
    const token = await requestRecaptchaToken('register_seller');
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

const handleCheckboxClick = (e: MouseEvent) => {
  if (!form.agree) {
    // Jika belum dicentang, cegah centang otomatis dan munculkan modal
    e.preventDefault();
    showSellerAgreementModal.value = true;
  }
  // Jika sudah dicentang, biarkan perilaku default (uncheck) terjadi
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

  await form.post('/register/seller', {
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

  <Head title="Daftar Penjual" />

  <section class="min-h-screen bg-[#E5E5E5] flex items-center justify-center p-4 lg:p-8">
    <div :class="registrationSuccess ? 'max-w-md' : 'max-w-5xl'" class="w-full">
      <div class="bg-white rounded-md shadow-sm overflow-hidden"
        :class="{ 'lg:flex min-h-[600px]': !registrationSuccess, 'p-8 sm:p-10': registrationSuccess }">

        <!-- Left Side - Hero (hidden on success) -->
        <div v-if="!registrationSuccess"
          class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-sky-500 to-sky-600 p-10 flex-col justify-center">
          <div class="space-y-4">
            <img :src="registerSellerImage" alt="Register Seller" class="w-full max-w-sm object-contain mx-auto" />
            <h2 class="text-2xl font-bold text-white">4 Langkah Mudah Berjualan</h2>
            <p class="text-sky-100 leading-relaxed">
              Mulai jual produkmu di TP-PKK Marketplace dengan proses registrasi yang cepat dan mudah.
            </p>
          </div>

          <div class="mt-8 space-y-4">
            <div class="flex items-start gap-3">
              <div
                class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-white/20 text-white font-semibold text-sm">
                1</div>
              <div>
                <p class="font-semibold text-white">Daftarkan Akun</p>
                <p class="text-sm text-sky-100">Isi data diri di halaman ini</p>
              </div>
            </div>
            <div class="flex items-start gap-3">
              <div
                class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-white/20 text-white font-semibold text-sm">
                2</div>
              <div>
                <p class="font-semibold text-white">Konfirmasi Email</p>
                <p class="text-sm text-sky-100">Buka email dan buat password</p>
              </div>
            </div>
            <div class="flex items-start gap-3">
              <div
                class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-white/20 text-white font-semibold text-sm">
                3</div>
              <div>
                <p class="font-semibold text-white">Lengkapi Data Usaha</p>
                <p class="text-sm text-sky-100">Siapkan KTP, NPWP, dan alamat toko</p>
              </div>
            </div>
            <div class="flex items-start gap-3">
              <div
                class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-white/20 text-white font-semibold text-sm">
                4</div>
              <div>
                <p class="font-semibold text-white">Unggah Produk</p>
                <p class="text-sm text-sky-100">Foto dan deskripsi, lalu mulai berjualan</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Side - Form -->
        <div :class="registrationSuccess ? 'w-full' : 'lg:w-1/2 p-8 sm:p-10'" class="flex flex-col justify-center">

          <!-- Success View -->
          <template v-if="registrationSuccess">
            <div class="flex justify-center mb-6">
              <div class="flex h-16 w-16 items-center justify-center rounded-full bg-sky-50">
                <svg class="h-8 w-8 text-sky-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="1.5">
                  <rect x="2" y="4" width="20" height="16" rx="2" />
                  <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                </svg>
              </div>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 text-center">Cek Email Anda</h1>
            <p class="mt-2 text-sm text-slate-500 text-center">
              Kami telah mengirimkan tautan aktivasi ke
              <span class="font-medium text-slate-700">{{ sentEmail }}</span>
            </p>
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
                <span v-else>Kirim ulang dalam <span class="font-medium text-slate-500">{{ formatCooldown
                }}</span></span>
              </p>
            </div>

            <div class="mt-6 text-center">
              <Link href="/seller/login"
                class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-sky-600">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M19 12H5M12 19l-7-7 7-7" />
                </svg>
                Kembali ke halaman login
              </Link>
            </div>
          </template>

          <!-- Registration Form -->
          <template v-else>
            <!-- Back Arrow -->
            <Link href="/register-as"
              class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-sky-600 mb-4 w-fit">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 12H5M12 19l-7-7 7-7" />
              </svg>
              Kembali
            </Link>

            <h1 class="text-2xl font-bold text-slate-900">Daftar Penjual</h1>
            <p class="mt-2 text-sm text-slate-500">Masukkan data pemilik usaha untuk memulai</p>

            <form @submit.prevent="submit" class="mt-6 space-y-4">
              <!-- Owner Name -->
              <div class="space-y-1.5">
                <label for="owner_name" class="text-sm font-medium text-slate-700">Nama Pemilik</label>
                <input id="owner_name" v-model="form.owner_name" type="text" required
                  placeholder="Nama lengkap pemilik usaha"
                  class="w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-sky-400 focus:outline-none focus:ring-1 focus:ring-sky-400"
                  :class="{ 'border-red-400': form.errors.owner_name }" />
                <p v-if="form.errors.owner_name" class="text-xs text-red-500">{{ form.errors.owner_name }}</p>
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
                <div class="flex items-start gap-3">
                  <input type="checkbox" v-model="form.agree" @click="handleCheckboxClick"
                    class="mt-0.5 h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500 cursor-pointer" />
                  <span class="text-xs text-slate-600 leading-relaxed">
                    Saya menyetujui
                    <a href="#" @click.prevent.stop="showSellerAgreementModal = true"
                      class="font-medium text-sky-600 hover:underline">Syarat dan Ketentuan</a>
                    serta
                    <a href="#" @click.prevent.stop="showPrivacyPolicyModal = true"
                      class="font-medium text-sky-600 hover:underline">Kebijakan Privasi</a>
                  </span>
                </div>
                <p v-if="form.errors.agree" class="mt-1 text-xs text-red-500">{{ form.errors.agree }}</p>
              </div>

              <!-- reCAPTCHA Widget -->
              <div class="pt-2">
                <div
                  class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 px-4 py-2.5">
                  <div class="flex items-center gap-2.5">
                    <div
                      class="flex h-6 w-6 cursor-pointer items-center justify-center rounded border-2 bg-white transition-all"
                      :class="form.notRobot ? 'border-green-500' : 'border-slate-300 hover:border-slate-400'"
                      @click="handleRecaptcha">
                      <svg v-if="loadingRecaptcha" class="h-3.5 w-3.5 animate-spin text-sky-500"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
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
            <div class="mt-6 pt-6 border-t border-slate-100 text-center">
              <p class="text-sm text-slate-500">
                Sudah punya akun?
                <Link href="/seller/login" class="font-medium text-sky-600 hover:underline">Masuk</Link>
              </p>
            </div>
          </template>

        </div>
      </div>
    </div>

    <!-- Seller Agreement Modal -->
    <Teleport to="body">
      <div v-if="showSellerAgreementModal"
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 px-4 backdrop-blur-sm"
        @click.self="showSellerAgreementModal = false">
        <div class="relative w-full max-w-2xl transform rounded-lg bg-white p-8 shadow-2xl">
          <button @click="showSellerAgreementModal = false"
            class="absolute right-4 top-4 rounded-full p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path
                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" />
            </svg>
          </button>

          <h2 class="text-2xl font-bold text-slate-900 mb-4">Perjanjian Penjual</h2>

          <div class="max-h-96 overflow-y-auto pr-2 space-y-4 text-sm text-slate-700">
            <p class="font-semibold text-slate-900">Dengan mendaftar sebagai penjual di PKK Sulsel Mart, Anda menyetujui
              hal-hal berikut:</p>

            <div class="space-y-4">
              <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                <h3 class="font-semibold text-amber-900 mb-2 flex items-center gap-2">
                  <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                      d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                      clip-rule="evenodd" />
                  </svg>
                  1. Tanggung Jawab Transaksi
                </h3>
                <p class="text-amber-900"><strong>Segala bentuk transaksi antara pembeli dan toko merupakan tanggung
                    jawab
                    penuh kedua belah pihak.</strong> Platform pkksulselmart.com <strong>tidak bertanggung
                    jawab</strong>
                  atas sengketa, kerugian, atau masalah yang timbul dari transaksi tersebut.</p>
              </div>

              <div>
                <h3 class="font-semibold text-slate-900 mb-2">2. Kewajiban Penjual</h3>
                <ul class="list-disc list-inside space-y-1.5 ml-2 text-slate-700">
                  <li>Memberikan informasi produk yang akurat dan jujur</li>
                  <li>Memenuhi pesanan sesuai dengan deskripsi produk</li>
                  <li>Berkomunikasi dengan pembeli secara profesional</li>
                  <li>Mematuhi semua peraturan yang berlaku</li>
                  <li>Menjaga reputasi platform dengan layanan terbaik</li>
                </ul>
              </div>

              <div>
                <h3 class="font-semibold text-slate-900 mb-2">3. Penyelesaian Sengketa</h3>
                <p>Penjual dan pembeli bertanggung jawab untuk menyelesaikan sengketa secara langsung. Platform hanya
                  menyediakan sarana komunikasi dan tidak ikut serta dalam penyelesaian perselisihan.</p>
              </div>

              <div>
                <h3 class="font-semibold text-slate-900 mb-2">4. Ketentuan Lainnya</h3>
                <p>Penjual wajib mematuhi semua kebijakan platform dan undang-undang yang berlaku. Pelanggaran dapat
                  mengakibatkan penangguhan atau penutupan akun.</p>
              </div>
            </div>
          </div>

          <div class="mt-6 flex justify-end gap-3">
            <button @click="showSellerAgreementModal = false"
              class="rounded-lg border border-slate-200 bg-white px-6 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
              Tutup
            </button>
            <button @click="showSellerAgreementModal = false; form.agree = true"
              class="rounded-lg bg-sky-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-sky-700">
              Saya Mengerti & Setuju
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Privacy Policy Modal -->
    <Teleport to="body">
      <div v-if="showPrivacyPolicyModal"
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 px-4 backdrop-blur-sm"
        @click.self="showPrivacyPolicyModal = false">
        <div class="relative w-full max-w-2xl transform rounded-lg bg-white p-8 shadow-2xl">
          <button @click="showPrivacyPolicyModal = false"
            class="absolute right-4 top-4 rounded-full p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path
                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" />
            </svg>
          </button>

          <h2 class="text-2xl font-bold text-slate-900 mb-4">Kebijakan Privasi</h2>

          <div class="max-h-96 overflow-y-auto pr-2 space-y-4 text-sm text-slate-700">
            <p>Di PKK Sulsel Mart, kami sangat menjaga privasi dan data pribadi pengguna kami. Kebijakan Privasi ini
              menjelaskan bagaimana kami mengumpulkan, menggunakan, dan melindungi informasi Anda.</p>

            <div class="space-y-4">
              <div>
                <h3 class="font-semibold text-slate-900 mb-2">1. Informasi yang Kami Kumpulkan</h3>
                <p>Kami mengumpulkan data yang Anda berikan langsung saat mendaftar, seperti nama, email, nomor
                  telepon,
                  dan data usaha lainnya yang diperlukan untuk operasional toko.</p>
              </div>

              <div>
                <h3 class="font-semibold text-slate-900 mb-2">2. Penggunaan Informasi</h3>
                <p>Data Anda digunakan untuk memproses pendaftaran, memverifikasi akun, memudahkan komunikasi antara
                  penjual dan pembeli, serta meningkatkan kualitas layanan platform kami.</p>
              </div>

              <div>
                <h3 class="font-semibold text-slate-900 mb-2">3. Keamanan Data</h3>
                <p>Kami menerapkan standar keamanan teknis dan organisasi untuk melindungi data Anda dari akses yang
                  tidak
                  sah, kehilangan, atau penyalahgunaan.</p>
              </div>

              <div>
                <h3 class="font-semibold text-slate-900 mb-2">4. Berbagi Informasi</h3>
                <p>Kami tidak akan menjual atau menyewakan data pribadi Anda kepada pihak ketiga. Informasi hanya
                  dibagikan untuk tujuan transaksi atau jika diwajibkan oleh hukum.</p>
              </div>
            </div>
          </div>

          <div class="mt-6 flex justify-end">
            <button @click="showPrivacyPolicyModal = false"
              class="rounded-lg bg-sky-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-sky-700">
              Saya Mengerti
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </section>
</template>

<style>
.grecaptcha-badge {
  visibility: hidden !important;
}
</style>
