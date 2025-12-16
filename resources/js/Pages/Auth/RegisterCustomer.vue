<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import CustomerCareButton from '@/components/CustomerCareButton.vue';
import heroIllustration from '@/../images/illustrations/online-shopping.svg';

const form = useForm({
  name: '',
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
    const token = await requestRecaptchaToken('register_customer');
    form['g-recaptcha-response'] = token;
    form.notRobot = true;
  } catch (error) {
    form.notRobot = false;
    form.setError('notRobot', 'Gagal memverifikasi reCAPTCHA.');
    console.error(error);
    return;
  } finally {
    loadingRecaptcha.value = false;
  }

  await form.post('/register/customer', {
    preserveScroll: true,
    onSuccess: () => {
      localSuccess.value = flashSuccess.value || 'Registrasi berhasil. Cek email untuk verifikasi dan buat password baru.';
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
</script>

<template>

  <Head title="Daftar - PKK UMKM" />

  <section class="relative flex min-h-screen items-center justify-center bg-slate-100 px-4 py-6">
    <div
      class="relative mx-auto flex w-full max-w-5xl flex-col overflow-hidden rounded-lg bg-white shadow-sm md:flex-row">
      <div
        class="flex w-full flex-col justify-between border-b border-slate-100 px-8 py-10 sm:py-12 md:w-1/2 md:border-b-0 md:border-r">
        <div>
          <div class="mb-8 flex items-start justify-between gap-4">
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Daftar</h1>
            <div
              class="flex h-10 min-w-28 items-center justify-center rounded-xl border border-slate-200 px-3 text-[11px] font-bold text-sky-600">
              TP-PKK Marketplace
            </div>
          </div>

          <form class="space-y-4 text-sm" @submit.prevent="submit">
            <div class="space-y-1">
              <label class="block text-sm font-semibold text-slate-700">Nama</label>
              <input type="text" name="name" v-model="form.name" required
                :class="['w-full rounded-lg border bg-slate-50 px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:bg-white focus:outline-none', form.errors.name ? 'border-red-400 bg-red-50 focus:border-red-500' : 'border-slate-200 focus:border-sky-400']"
                placeholder="John" />
              <p v-if="form.errors.name" class="text-xs text-red-500">{{ form.errors.name }}</p>
            </div>

            <div class="space-y-1">
              <label class="block text-sm font-semibold text-slate-700">Alamat Email</label>
              <input type="email" name="email" v-model="form.email" required
                :class="['w-full rounded-lg border bg-slate-50 px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:bg-white focus:outline-none', form.errors.email ? 'border-red-400 bg-red-50 focus:border-red-500' : 'border-slate-200 focus:border-sky-400']"
                placeholder="john@email.com" />
              <p v-if="form.errors.email" class="text-xs text-red-500">{{ form.errors.email }}</p>
            </div>

            <div class="space-y-1">
              <label class="block text-sm font-semibold text-slate-700">Telepon</label>
              <div
                :class="['flex items-center rounded-lg border bg-slate-50 text-sm text-slate-800 focus-within:bg-white', form.errors.phone ? 'border-red-400 focus-within:border-red-500' : 'border-slate-200 focus-within:border-sky-400']">
                <span class="flex h-10 items-center border-r border-slate-200 px-3 text-slate-600">+62</span>
                <input type="tel" name="phone" v-model="form.phone" required
                  class="w-full bg-transparent px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none"
                  placeholder="8123456789" />
              </div>
              <p v-if="form.errors.phone" class="text-xs text-red-500">{{ form.errors.phone }}</p>
            </div>

            <div class="space-y-1">
              <label class="block text-xs font-semibold uppercase tracking-wide text-slate-600">
                Kode Referal <span class="normal-case font-normal text-slate-400">(Tidak Wajib)</span>
              </label>
              <input type="text" name="referral" v-model="form.referral"
                :class="['w-full rounded-lg border bg-slate-50 px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:bg-white focus:outline-none', form.errors.referral ? 'border-red-400 bg-red-50 focus:border-red-500' : 'border-slate-200 focus:border-sky-400']"
                placeholder="Masukkan kode referal" />
              <p v-if="form.errors.referral" class="text-xs text-red-500">{{ form.errors.referral }}</p>
            </div>

            <div class="pt-2">
              <label class="flex items-start gap-2 text-xs text-slate-600">
                <input type="checkbox" required v-model="form.agree"
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

            <button type="submit"
              class="mt-4 w-full rounded-md bg-sky-600 py-3 text-sm font-semibold text-white transition hover:bg-sky-700 disabled:bg-slate-200 disabled:text-slate-400"
              :disabled="form.processing || !form.agree || !form.notRobot">
              Daftar
            </button>

            <p v-if="localSuccess" class="text-center text-sm text-green-600">
              {{ localSuccess }}
            </p>
            <p v-else-if="flashSuccess" class="text-center text-sm text-green-600">
              {{ flashSuccess }}
            </p>
          </form>
        </div>

        <div class="mt-8 border-t border-slate-100 pt-6 text-center text-xs sm:text-sm text-slate-500">
          Sudah punya akun TP-PKK Marketplace?
          <Link href="/login" class="font-semibold text-sky-600 hover:underline">Masuk</Link>
        </div>
      </div>

      <div
        class="flex w-full flex-col bg-gradient-to-br from-sky-400 via-sky-500 to-sky-600 px-8 py-10 text-white sm:px-10 sm:py-12 md:w-1/2">
        <div class="max-w-sm">
          <h2 class="text-2xl font-bold">Belanja Efisien Kemana Saja</h2>
          <p class="mt-4 text-sm leading-relaxed text-white/90">
            Dengan berbagai kemudahan berbelanja di TP-PKK Marketplace, proses transaksi menjadi cepat dan efisien tanpa
            harus
            melewati proses yang merepotkan. Barang akan sampai kemana pun yang Anda inginkan dengan aman.
          </p>
        </div>

        <div class="mt-10 flex flex-1 items-center justify-center">
          <img :src="heroIllustration" alt="Belanja di TP-PKK Marketplace" class="w-56 sm:w-64 lg:w-72" />
        </div>
      </div>
    </div>

    <CustomerCareButton />
  </section>
</template>

<style>
.grecaptcha-badge {
  visibility: hidden !important;
}
</style>
