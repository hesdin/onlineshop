<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import heroIllustration from '@/../images/illustrations/delivering.svg';

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

  if (!form.notRobot) {
    try {
      const token = await requestRecaptchaToken('register_seller');
      form['g-recaptcha-response'] = token;
      form.notRobot = true;
    } catch (error) {
      form.setError('notRobot', 'Gagal memverifikasi reCAPTCHA.');
      console.error(error);
      return;
    }
  }
  if (!form.agree) {
    form.setError('agree', 'Anda harus menyetujui syarat dan ketentuan.');
  }

  if (form.hasErrors) {
    return;
  }

  await form.post('/register/seller', {
    preserveScroll: true,
    onSuccess: () => {
      localSuccess.value = flashSuccess.value || 'Registrasi seller berhasil diverifikasi.';
    },
  });
};

const handleRecaptcha = async (event: Event) => {
  event.preventDefault();
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
  }
};
</script>

<template>
  <Head title="Daftar Penjual - PKK UMKM" />

  <section class="flex min-h-screen items-center justify-center bg-linear-to-br from-sky-400 via-sky-500 to-sky-600 px-4 py-6">
    <div class="flex w-full max-w-6xl flex-col gap-6 lg:flex-row">
      <div class="flex flex-1 flex-col justify-between p-6 text-white lg:p-8">
        <div class="space-y-6">
          <h1 class="text-2xl font-bold leading-tight sm:text-3xl">4 Langkah Mudah Berjualan di TP-PKK</h1>

          <ol class="space-y-5 text-sm leading-relaxed sm:text-base">
            <li class="flex gap-4">
              <span class="mt-0.5 flex h-12 w-12 flex-none items-center justify-center rounded-full bg-white/90 text-sky-600 shadow-md">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
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
              <span class="mt-0.5 flex h-12 w-12 flex-none items-center justify-center rounded-full bg-white/90 text-sky-600 shadow-md">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
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
              <span class="mt-0.5 flex h-12 w-12 flex-none items-center justify-center rounded-full bg-white/90 text-sky-600 shadow-md">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
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
              <span class="mt-0.5 flex h-12 w-12 flex-none items-center justify-center rounded-full bg-white/90 text-sky-600 shadow-md">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
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
        <div class="rounded-lg bg-white p-8 shadow-2xl ring-1 ring-slate-100">
          <div class="mb-6 flex items-start justify-between gap-4">
            <div>
              <h2 class="text-2xl font-bold text-slate-900">Daftar Sebagai Penjual</h2>
              <p class="mt-1 text-sm text-slate-600">Masukkan data pemilik usaha untuk memulai.</p>
            </div>
            <div class="flex h-10 min-w-28 items-center justify-center rounded-xl border border-slate-200 px-3 text-[11px] font-bold text-sky-600">
              TP-PKK Marketplace
            </div>
          </div>

          <form class="space-y-4 text-sm" @submit.prevent="submit">
            <div class="space-y-1">
              <label class="block text-sm font-semibold text-slate-700">Nama Lengkap Pemilik Perusahaan</label>
              <input
                type="text"
                name="owner_name"
                v-model="form.owner_name"
                required
                class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-sky-400 focus:outline-none"
                placeholder="John"
              />
            </div>

            <div class="space-y-1">
              <label class="block text-sm font-semibold text-slate-700">Alamat Email</label>
              <input
                type="email"
                name="email"
                v-model="form.email"
                required
                class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-sky-400 focus:outline-none"
                placeholder="John@email.com"
              />
            </div>

            <div class="space-y-1">
              <label class="block text-sm font-semibold text-slate-700">No Handphone</label>
              <div class="flex items-center rounded-lg border border-slate-200 bg-white text-sm text-slate-800 focus-within:border-sky-400">
                <span class="flex h-11 items-center border-r border-slate-200 px-3 text-slate-600">+62</span>
                <input
                  type="tel"
                  name="phone"
                  v-model="form.phone"
                  required
                  class="w-full bg-transparent px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none"
                  placeholder="81200000000"
                />
              </div>
            </div>

            <div class="space-y-1">
              <label class="block text-sm font-semibold text-slate-700">
                Kode Referal <span class="normal-case font-normal text-slate-400">Opsional</span>
              </label>
              <input
                type="text"
                name="referral"
                v-model="form.referral"
                class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-sky-400 focus:outline-none"
                placeholder="Kosongkan jika tidak ada"
              />
            </div>

            <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-4">
              <div class="flex items-center justify-between">
                <label class="flex items-center gap-3 text-xs text-slate-600">
                  <input
                    type="checkbox"
                    :checked="form.notRobot"
                    @click="handleRecaptcha"
                    required
                    class="h-5 w-5 rounded border-slate-300 text-sky-600 focus:ring-sky-500"
                  />
                  I'm not a robot
                </label>
                <div class="h-6 w-20 rounded bg-slate-200"></div>
              </div>
              <p v-if="form.errors.notRobot" class="mt-2 text-xs text-red-500">{{ form.errors.notRobot }}</p>
            </div>

            <label class="flex items-start gap-2 text-xs text-slate-600">
              <input
                type="checkbox"
                v-model="form.agree"
                required
                class="mt-0.5 h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500"
              />
              <span>
                Saya sudah membaca dan menyetujui
                <a href="#" class="font-semibold text-sky-600 hover:underline">Syarat dan Ketentuan</a>
                serta
                <a href="#" class="font-semibold text-sky-600 hover:underline">Kebijakan Privasi</a>
                yang berlaku
              </span>
            </label>
            <p v-if="form.errors.agree" class="text-xs text-red-500">{{ form.errors.agree }}</p>

            <button
              type="submit"
              class="w-full rounded-md bg-sky-600 py-3 text-sm font-semibold text-white transition hover:bg-sky-700 disabled:bg-slate-200 disabled:text-slate-400"
              :disabled="form.processing || !form.agree || !form.notRobot"
            >
              Daftar
            </button>

            <p v-if="localSuccess" class="text-center text-sm text-green-600">
              {{ localSuccess }}
            </p>
            <p v-else-if="flashSuccess" class="text-center text-sm text-green-600">
              {{ flashSuccess }}
            </p>

            <div class="flex items-center gap-3 text-xs text-slate-400">
              <span class="h-px flex-1 bg-slate-200"></span>
              <span>atau</span>
              <span class="h-px flex-1 bg-slate-200"></span>
            </div>

            <button
              type="button"
              class="flex w-full items-center justify-center gap-3 rounded-lg border border-slate-200 bg-white py-2.5 text-sm font-semibold text-slate-700 transition hover:border-sky-200 hover:text-sky-600"
            >
              <span class="flex h-5 w-5 items-center justify-center rounded-full bg-white">
                <svg class="h-5 w-5" viewBox="0 0 533.5 544.3" aria-hidden="true">
                  <path
                    fill="#4285F4"
                    d="M533.5 278.4c0-17.4-1.6-34.1-4.6-50.2H272.1v95.1h147c-6.3 34-25 62.9-53.3 82v68h86.1c50.4-46.4 81.6-114.8 81.6-194.9z"
                  />
                  <path
                    fill="#34A853"
                    d="M272.1 544.3c72.1 0 132.7-23.9 176.9-64.9l-86.1-68c-24 16.1-54.7 25.7-90.8 25.7-69.8 0-128.9-47-150.1-110.2h-89.1v69.4c44 87.4 134.1 148 239.2 148z"
                  />
                  <path
                    fill="#FBBC05"
                    d="M122 326.9c-5.6-16.9-8.8-34.9-8.8-53.3s3.2-36.4 8.8-53.3v-69.4H32.9C11.9 194.9 0 234.3 0 276.9s11.9 82 32.9 121.4L122 326.9z"
                  />
                  <path
                    fill="#EA4335"
                    d="M272.1 107.7c39.2 0 74.3 13.5 102.1 40l76.6-76.6C404.7 24.1 344.1 0 272.1 0 167 0 76.9 60.6 32.9 155.5l89.1 69.4C143.2 154.7 202.3 107.7 272.1 107.7z"
                  />
                </svg>
              </span>
              Daftar dengan Google
            </button>
          </form>
        </div>

        <div class="rounded-lg bg-white/90 py-4 text-center text-sm text-slate-700 shadow-md">
          Sudah punya akun?
          <Link href="/login" class="font-semibold text-sky-600 hover:underline">Login</Link>
        </div>
      </div>
    </div>
  </section>
</template>
