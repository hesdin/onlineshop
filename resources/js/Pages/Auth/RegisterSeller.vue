<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import heroIllustration from '@/../images/illustrations/delivering.svg';

const logoUrl = '/images/logo-pkk.png';

const form = useForm({
  owner_name: '',
  email: '',
  phone: '',
  referral: '',
  agree: false,
  notRobot: false,
  'g-recaptcha-response': '',
});

const localSuccess = ref('');
const page = usePage();
const siteKey = computed(() => page.props.recaptcha?.siteKey ?? '');
const flashSuccess = computed(() => page.props.flash?.success ?? '');
const loadingRecaptcha = ref(false);

let recaptchaScriptPromise: Promise<void> | null = null;
const loadRecaptchaScript = () => {
  if (typeof window === 'undefined') return Promise.reject();
  if (window.grecaptcha) return Promise.resolve();
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
    if (!window.grecaptcha) {
      reject(new Error('reCAPTCHA belum tersedia'));
      return;
    }

    window.grecaptcha.ready(() => {
      window.grecaptcha
        .execute(siteKey.value, { action })
        .then(resolve)
        .catch((err: unknown) => reject(err));
    });
  });
};

const withTimeout = <T,>(promise: Promise<T>, ms: number, message: string) => {
  return new Promise<T>((resolve, reject) => {
    const timeoutId = window.setTimeout(() => reject(new Error(message)), ms);
    promise
      .then((value) => {
        window.clearTimeout(timeoutId);
        resolve(value);
      })
      .catch((error: unknown) => {
        window.clearTimeout(timeoutId);
        reject(error);
      });
  });
};

watch(
  () => form.notRobot,
  (val) => {
    if (val) {
      form.clearErrors('notRobot');
    }
  }
);

watch(
  () => form.agree,
  (val) => {
    if (val) {
      form.clearErrors('agree');
    }
  }
);

const submit = async () => {
  form.clearErrors();
  localSuccess.value = '';

  if (!form.agree) {
    form.setError('agree', 'Anda harus menyetujui syarat dan ketentuan.');
  }

  if (form.hasErrors) {
    return;
  }

  loadingRecaptcha.value = true;
  form.notRobot = false;
  form['g-recaptcha-response'] = '';
  form.clearErrors('notRobot');

  try {
    const token = await withTimeout(
      requestRecaptchaToken('register_seller'),
      15000,
      'Verifikasi reCAPTCHA melebihi batas waktu.'
    );
    form['g-recaptcha-response'] = token;
    form.notRobot = true;
  } catch (error) {
    form.notRobot = false;
    form.setError('notRobot', error instanceof Error ? error.message : 'Gagal memverifikasi reCAPTCHA.');
    console.error(error);
    return;
  } finally {
    loadingRecaptcha.value = false;
  }

  await form.post('/register/seller', {
    preserveScroll: true,
    onSuccess: () => {
      localSuccess.value = flashSuccess.value || 'Registrasi berhasil. Silakan cek email Anda untuk verifikasi.';
    },
    onError: (errors) => {
      const recaptchaError = errors['g-recaptcha-response'];
      if (recaptchaError) {
        form.notRobot = false;
        form['g-recaptcha-response'] = '';
        form.setError('notRobot', recaptchaError);
      }
    },
  });
};

const handleRecaptcha = async (event: Event) => {
  if (loadingRecaptcha.value) return;

  loadingRecaptcha.value = true;
  form.notRobot = false;
  form['g-recaptcha-response'] = '';
  form.clearErrors('notRobot');

  try {
    const token = await withTimeout(
      requestRecaptchaToken('register_seller'),
      15000,
      'Verifikasi reCAPTCHA melebihi batas waktu.'
    );
    form['g-recaptcha-response'] = token;
    form.notRobot = true;
  } catch (error) {
    form.notRobot = false;
    form.setError('notRobot', error instanceof Error ? error.message : 'Gagal memverifikasi reCAPTCHA.');
    console.error(error);
  } finally {
    loadingRecaptcha.value = false;
  }
};
</script>

<template>

  <Head title="Daftar Penjual - PKK UMKM" />

  <section
    class="flex min-h-screen items-center justify-center bg-linear-to-br from-sky-400 via-sky-500 to-sky-600 px-4 py-6">
    <div class="flex w-full max-w-6xl flex-col gap-6 lg:flex-row">
      <div class="flex flex-1 flex-col justify-between p-6 text-white lg:p-8">
        <div class="space-y-6">
          <h1 class="text-2xl font-bold leading-tight sm:text-3xl">4 Langkah Mudah Berjualan di TP-PKK</h1>

          <ol class="space-y-5 text-sm leading-relaxed sm:text-base">
            <li class="flex gap-4">
              <span
                class="mt-0.5 flex h-12 w-12 flex-none items-center justify-center rounded-full bg-white/90 text-sky-600 shadow-md">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                  stroke-linecap="round" stroke-linejoin="round">
                  <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Z" />
                  <path d="M5.8 19.1a7 7 0 0 1 12.4 0" />
                </svg>
              </span>
              <div>
                <p class="font-semibold">1. Daftarkan Akun</p>
                <p>Isi data diri di halaman ini atau daftar dengan akun Google.</p>
              </div>
            </li>

            <li class="flex gap-4">
              <span
                class="mt-0.5 flex h-12 w-12 flex-none items-center justify-center rounded-full bg-white/90 text-sky-600 shadow-md">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                  stroke-linecap="round" stroke-linejoin="round">
                  <path d="M4 6h16v12H4Z" />
                  <path d="m22 7-10 6L2 7" />
                </svg>
              </span>
              <div>
                <p class="font-semibold">2. Konfirmasi Email</p>
                <p>Buka email Anda, lakukan konfirmasi, dan buat password baru.</p>
              </div>
            </li>

            <li class="flex gap-4">
              <span
                class="mt-0.5 flex h-12 w-12 flex-none items-center justify-center rounded-full bg-white/90 text-sky-600 shadow-md">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                  stroke-linecap="round" stroke-linejoin="round">
                  <path d="M7 4h10v4H7z" />
                  <path d="M5 8h14v12H5z" />
                  <path d="M9 12h6M9 16h6" />
                </svg>
              </span>
              <div>
                <p class="font-semibold">3. Lengkapi Data Usaha</p>
                <p>Siapkan KTP, NPWP, nama & jenis perusahaan, beserta alamat toko.</p>
              </div>
            </li>

            <li class="flex gap-4">
              <span
                class="mt-0.5 flex h-12 w-12 flex-none items-center justify-center rounded-full bg-white/90 text-sky-600 shadow-md">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                  stroke-linecap="round" stroke-linejoin="round">
                  <path d="M12 17v4" />
                  <path d="M8 21h8" />
                  <path d="m9 11 3-3 3 3" />
                  <path d="M12 4v10" />
                </svg>
              </span>
              <div>
                <p class="font-semibold">4. Unggah Produk</p>
                <p>Foto produk dan isi deskripsi, lalu mulai berjualan.</p>
              </div>
            </li>
          </ol>
        </div>

        <p class="pt-6 text-sm text-white/80">
          Butuh bantuan?
          <a href="#" class="font-semibold underline">Hubungi Kami</a>
        </p>
      </div>

      <div class="flex flex-1 flex-col gap-4">
        <div class="rounded-md bg-white p-8 shadow-2xl ring-1 ring-slate-100">
          <div class="mb-6 flex items-start justify-between gap-4">
            <div>
              <h2 class="text-2xl font-bold text-slate-900">Daftar Sebagai Penjual</h2>
              <p class="mt-1 text-sm text-slate-600">Masukkan data pemilik usaha untuk memulai.</p>
            </div>
            <div class="flex h-14 w-24 items-center justify-center text-xs font-bold text-sky-600">
              <img :src="logoUrl" alt="TP-PKK Marketplace" class="h-full w-full object-contain" decoding="async"
                draggable="false" />
            </div>
          </div>

          <form class="space-y-4 text-sm" @submit.prevent="submit">
            <div class="space-y-1">
              <label class="block text-sm font-semibold text-slate-700">Nama Lengkap Pemilik Perusahaan</label>
              <input type="text" name="owner_name" v-model="form.owner_name" required :class="[
                'w-full rounded-md border bg-white px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none',
                form.errors.owner_name ? 'border-red-400 bg-red-50 focus:border-red-500' : 'border-slate-200 focus:border-sky-400',
              ]" placeholder="John" />
              <p v-if="form.errors.owner_name" class="text-xs text-red-500">{{ form.errors.owner_name }}</p>
            </div>

            <div class="space-y-1">
              <label class="block text-sm font-semibold text-slate-700">Alamat Email</label>
              <input type="email" name="email" v-model="form.email" required :class="[
                'w-full rounded-md border bg-white px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none',
                form.errors.email ? 'border-red-400 bg-red-50 focus:border-red-500' : 'border-slate-200 focus:border-sky-400',
              ]" placeholder="John@email.com" />
              <p v-if="form.errors.email" class="text-xs text-red-500">{{ form.errors.email }}</p>
            </div>

            <div class="space-y-1">
              <label class="block text-sm font-semibold text-slate-700">No Handphone</label>
              <div :class="[
                'flex items-center rounded-md border bg-white text-sm text-slate-800',
                form.errors.phone ? 'border-red-400' : 'border-slate-200 focus-within:border-sky-400',
              ]">
                <span class="flex h-11 items-center border-r border-slate-200 px-3 text-slate-600">+62</span>
                <input type="tel" name="phone" v-model="form.phone" required
                  class="w-full bg-transparent px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none"
                  placeholder="81200000000" />
              </div>
              <p v-if="form.errors.phone" class="text-xs text-red-500">{{ form.errors.phone }}</p>
            </div>

            <div class="space-y-1">
              <label class="block text-sm font-semibold text-slate-700">
                Kode Referal <span class="normal-case font-normal text-slate-400">Opsional</span>
              </label>
              <input type="text" name="referral" v-model="form.referral" :class="[
                'w-full rounded-md border bg-white px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none',
                form.errors.referral ? 'border-red-400 bg-red-50 focus:border-red-500' : 'border-slate-200 focus:border-sky-400',
              ]" placeholder="Kosongkan jika tidak ada" />
              <p v-if="form.errors.referral" class="text-xs text-red-500">{{ form.errors.referral }}</p>
            </div>

            <div class="mt-4">
              <!-- Checkbox Widget -->
              <div
                class="flex h-[74px] w-full items-center justify-between rounded-[3px] border border-[#d3d3d3] bg-[#f9f9f9] px-3 shadow-[0_0_4px_1px_rgba(0,0,0,0.08)]">
                <div class="flex items-center gap-3">
                  <div
                    class="flex h-[28px] w-[28px] cursor-pointer items-center justify-center rounded-[2px] border-[2px] bg-white transition-all"
                    :class="form.notRobot ? 'border-transparent' : 'border-[#c1c1c1] hover:border-[#b2b2b2]'"
                    @click="handleRecaptcha">
                    <svg v-if="loadingRecaptcha" class="h-5 w-5 animate-spin text-sky-600"
                      xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                      </path>
                    </svg>
                    <svg v-else-if="form.notRobot" class="h-7 w-7" viewBox="0 0 48 48"
                      xmlns="http://www.w3.org/2000/svg">
                      <path d="M20 34L10 24L12.8 21.2L20 28.4L35.2 13.2L38 16L20 34Z" fill="#009E55" stroke="#009E55"
                        stroke-width="2" />
                    </svg>
                  </div>
                  <span class="text-[14px] font-normal text-[#282828]">I'm not a robot</span>
                </div>

                <div class="flex flex-col items-center gap-0.5">
                  <svg class="h-8 w-8" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                      d="M24 2C11.8 2 2 11.8 2 24C2 36.2 11.8 46 24 46C36.2 46 46 36.2 46 24C46 11.8 36.2 2 24 2ZM36.6 22H32.4C31.8 17.6 28.4 14.2 24 14.2V10C30.8 10 36.4 15.2 36.6 22ZM24 38V33.8C19.6 33.2 16.2 29.8 15.6 25.4H11.4C12 32.8 17.2 38 24 38ZM11.4 22.6H15.6C16.2 18.2 19.6 14.8 24 14.2V10C17.2 10 12 15.2 11.4 22.6ZM24 33.8V38C30.8 38 36.4 32.8 36.6 25.4H32.4C31.8 29.8 28.4 33.2 24 33.8Z"
                      fill="#bbdefb" />
                    <path
                      d="M44 24C44 13 35 4 24 4V8C32.8 8 40 15.2 40 24H44ZM24 44C35 44 44 35 44 24H40C40 32.8 32.8 40 24 40V44ZM4 24C4 35 13 44 24 44V40C15.2 40 8 32.8 8 24H4ZM24 4V8C15.2 8 8 15.2 8 24H4C4 13 13 4 24 4Z"
                      fill="#e3f2fd" opacity="0.8" />
                    <path d="M24 10V14.2C28.4 14.8 31.8 18.2 32.4 22.6H36.6C36 15.2 30.8 10 24 10Z" fill="#90caf9" />
                    <path d="M36.6 25.4H32.4C31.8 29.8 28.4 33.2 24 33.8V38C30.8 38 36 32.8 36.6 25.4Z"
                      fill="#64b5f6" />
                    <path d="M11.4 25.4H15.6C16.2 29.8 19.6 33.2 24 33.8V38C17.2 38 12 32.8 11.4 25.4Z"
                      fill="#42a5f5" />
                  </svg>
                  <span class="text-[10px] text-[#555555]">reCAPTCHA</span>
                  <div class="text-[8px] text-[#555555]">
                    <a href="https://www.google.com/intl/en/policies/privacy/" target="_blank"
                      class="hover:underline">Privacy</a>
                    -
                    <a href="https://www.google.com/intl/en/policies/terms/" target="_blank"
                      class="hover:underline">Terms</a>
                  </div>
                </div>
              </div>

              <!-- Branding Footer (Mimicking the badge) -->
              <div class="mt-2 flex items-center justify-center text-[10px] text-slate-400">
                protected by
                <span class="mx-1 font-semibold text-slate-500">reCAPTCHA</span>
                <a href="https://www.google.com/intl/en/policies/privacy/" target="_blank"
                  class="ml-1 hover:underline">Privacy</a>
                <span class="mx-1">-</span>
                <a href="https://www.google.com/intl/en/policies/terms/" target="_blank"
                  class="hover:underline">Terms</a>
              </div>

              <p v-if="form.errors.notRobot" class="mt-2 text-xs text-red-500">{{ form.errors.notRobot }}</p>
            </div>

            <label class="flex items-start gap-2 text-xs text-slate-600">
              <input type="checkbox" v-model="form.agree" required
                class="mt-0.5 h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500" />
              <span>
                Saya sudah membaca dan menyetujui
                <a href="#" class="font-semibold text-sky-600 hover:underline">Syarat dan Ketentuan</a>
                serta
                <a href="#" class="font-semibold text-sky-600 hover:underline">Kebijakan Privasi</a>
                yang berlaku
              </span>
            </label>
            <p v-if="form.errors.agree" class="text-xs text-red-500">{{ form.errors.agree }}</p>

            <button type="submit"
              class="mt-4 flex w-full items-center justify-center gap-2 rounded-md bg-sky-600 py-3 text-sm font-semibold text-white transition hover:bg-sky-700 disabled:bg-slate-200 disabled:text-slate-400"
              :disabled="form.processing || loadingRecaptcha || !form.agree">
              <svg v-if="form.processing || loadingRecaptcha" class="h-5 w-5 animate-spin"
                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
              </svg>
              {{ form.processing ? 'Memproses...' : loadingRecaptcha ? 'Memverifikasi...' : 'Daftar' }}
            </button>

          </form>
        </div>

        <div class="rounded-md bg-white/90 py-4 text-center text-sm text-slate-700 shadow-md">
          Sudah punya akun?
          <Link href="/login" class="font-semibold text-sky-600 hover:underline">Login</Link>
        </div>
      </div>
    </div>

    <!-- Success Modal -->
    <Teleport to="body">
      <div v-if="localSuccess || flashSuccess"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
        <div class="w-full max-w-md rounded-lg bg-white p-8 text-center shadow-xl">
          <!-- Checkmark Icon -->
          <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-teal-50">
            <svg class="h-10 w-10 text-teal-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
          </div>

          <!-- Title -->
          <h3 class="mb-3 text-xl font-bold text-slate-800">Konfirmasi Email Anda</h3>

          <!-- Message -->
          <p class="mb-6 text-sm leading-relaxed text-slate-600">
            Terima kasih telah melakukan registrasi, cek email Anda untuk melakukan aktivasi akun TP-PKK Marketplace.
          </p>

          <!-- Button -->
          <Link href="/"
            class="inline-block rounded-md bg-teal-500 px-6 py-3 text-sm font-semibold text-white transition hover:bg-teal-600">
            Kembali ke beranda
          </Link>
        </div>
      </div>
    </Teleport>
  </section>
</template>
