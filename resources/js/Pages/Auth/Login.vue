<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const roleLabel = 'Admin';
const postUrl = '/admin/login';

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.post(postUrl, {
    onFinish: () => form.reset('password'),
  });
};

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success ?? '');
</script>

<template>

  <Head :title="`Login ${roleLabel}`" />

  <section class="relative flex min-h-screen items-center justify-center overflow-hidden bg-sky-600 text-white">
    <div class="pointer-events-none absolute inset-0">
      <div class="absolute inset-0 bg-linear-to-b from-sky-500/40 via-sky-500/10 to-sky-700/50"></div>
      <span class="absolute -left-24 bottom-10 h-40 w-40 rotate-6 rounded-4xl border border-white/10"></span>
      <span class="absolute -right-10 top-10 h-32 w-32 rounded-4xl border border-white/10"></span>
      <span class="absolute -bottom-24 right-1/3 h-56 w-56 rounded-full bg-sky-500/30 blur-3xl"></span>
    </div>

    <div class="relative mx-auto flex w-full max-w-4xl justify-center px-6 py-12 lg:py-16">
      <div class="relative z-10 w-full max-w-lg rounded-lg bg-white p-8 sm:p-10 text-slate-900 shadow-2xl">
        <div class="flex items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <Link href="/login"
              class="flex h-10 w-10 items-center justify-center rounded-full p-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-700">
            <span class="text-xl font-semibold leading-none">&larr;</span>
            </Link>
            <h3 class="text-2xl sm:text-3xl font-bold text-slate-900">Login {{ roleLabel }}</h3>
          </div>
          <div
            class="flex h-10 w-28 items-center justify-center rounded-lg border border-slate-200 text-[11px] font-bold text-sky-600 p-3">
            TP-PKK Marketplace
          </div>
        </div>

        <form class="mt-8 space-y-5" @submit.prevent="submit">
          <div class="space-y-1.5">
            <label class="text-sm font-semibold text-slate-600" for="email">Email</label>
            <input id="email" v-model="form.email" type="email" autocomplete="email"
              class="w-full rounded-md border border-slate-200 bg-slate-50 px-3 py-3 text-sm sm:text-base text-slate-800 placeholder:text-slate-400 focus:border-sky-400 focus:bg-white focus:outline-none"
              placeholder="admin@email.com" :disabled="form.processing" />
            <p v-if="form.errors.email" class="text-sm text-red-500">{{ form.errors.email }}</p>
          </div>

          <div class="space-y-1.5">
            <label class="text-sm font-semibold text-slate-600" for="password">Kata Sandi</label>
            <div
              class="flex items-center rounded-md border border-slate-200 bg-slate-50 px-3 focus-within:border-sky-400 focus-within:bg-white">
              <input id="password" v-model="form.password" type="password" autocomplete="current-password"
                class="w-full bg-transparent py-3 text-sm sm:text-base text-slate-800 placeholder:text-slate-400 focus:outline-none"
                placeholder="Masukan Kata Sandi" :disabled="form.processing" />
              <button type="button" class="p-1 text-slate-400 hover:text-slate-600">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path d="M1 12s3.5-7 11-7 11 7 11 7-3.5 7-11 7S1 12 1 12Z" />
                  <circle cx="12" cy="12" r="3" />
                </svg>
              </button>
            </div>
            <p v-if="form.errors.password" class="text-sm text-red-500">{{ form.errors.password }}</p>
          </div>

          <label
            class="flex items-center gap-3 rounded-md border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700"
            for="remember">
            <input id="remember" v-model="form.remember" type="checkbox"
              class="h-5 w-5 rounded border-slate-300 text-sky-600 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-0"
              :disabled="form.processing" />
            <div class="flex flex-col">
              <span class="font-semibold text-slate-800">Ingat saya</span>
              <span class="text-xs text-slate-500">Tetap login di perangkat ini.</span>
            </div>
          </label>

          <button type="submit"
            class="mt-4 flex w-full items-center justify-center rounded-md bg-sky-600 py-3 text-sm sm:text-base font-semibold text-white transition hover:bg-sky-700 disabled:bg-slate-200 disabled:text-slate-400"
            :disabled="form.processing">
            {{ form.processing ? 'Memproses...' : 'Login' }}
          </button>

          <p v-if="form.errors.email && !form.errors.password" class="text-center text-sm text-red-500">
            {{ form.errors.email }}
          </p>

          <p v-if="flashSuccess" class="text-center text-sm text-green-600">
            {{ flashSuccess }}
          </p>

          <div class="mt-3 rounded-md bg-slate-50 px-4 py-3 text-center text-xs text-slate-500">
            Akses Admin diberikan oleh tim pengelola marketplace. <br> Hubungi administrator jika membutuhkan akun baru.
          </div>
        </form>
      </div>
    </div>
  </section>
</template>
