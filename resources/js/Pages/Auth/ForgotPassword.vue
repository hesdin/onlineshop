<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import CustomerCareButton from '@/components/CustomerCareButton.vue';
import heroIllustration from '@/../images/illustrations/online-shopping.svg';

const props = defineProps<{
  loginUrl?: string;
}>();

const form = useForm({
  email: '',
});

const submit = () => {
  form.post('/forgot-password', {
    preserveScroll: true,
  });
};

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success ?? '');
</script>

<template>

  <Head title="Lupa Kata Sandi" />

  <section class="relative flex min-h-screen items-center justify-center overflow-hidden bg-white text-slate-900">
    <div class="pointer-events-none absolute inset-0">
      <span class="absolute -left-24 bottom-10 h-40 w-40 rotate-6 rounded-4xl border border-slate-200/70"></span>
      <span class="absolute -right-10 top-10 h-32 w-32 rounded-4xl border border-slate-200/70"></span>
      <span class="absolute -bottom-24 right-1/3 h-56 w-56 rounded-full bg-sky-100/60 blur-3xl"></span>
    </div>

    <div
      class="relative mx-auto flex w-full max-w-6xl flex-col items-center gap-10 px-6 py-12 lg:flex-row lg:items-stretch lg:gap-16 lg:py-16">
      <div class="flex flex-1 flex-col items-center justify-center text-center lg:items-start lg:text-left">
        <div class="max-w-md space-y-6">
          <img :src="heroIllustration" alt="Lupa kata sandi"
            class="mx-auto w-[240px] sm:w-[300px] lg:w-[340px] object-contain" />
          <div class="space-y-2">
            <h2 class="text-2xl sm:text-3xl font-bold">Lupa Kata Sandi</h2>
            <p class="text-sm sm:text-base text-slate-600">
              Masukkan email Anda, kami kirim tautan untuk mengatur ulang kata sandi dengan aman.
            </p>
          </div>
        </div>
      </div>

      <div class="relative flex-1">
        <div
          class="relative z-10 ml-auto w-full max-w-xl rounded-xl border border-slate-100 bg-white p-8 sm:p-10 shadow-lg min-h-[420px]">
          <div class="mb-6 flex items-center justify-between gap-4">
            <Link :href="props.loginUrl || '/login'"
              class="flex h-10 w-10 items-center justify-center rounded-full p-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-700">
              <span class="text-xl font-semibold leading-none">&larr;</span>
            </Link>
            <div
              class="flex h-10 min-w-28 items-center justify-center rounded-xl border border-slate-200 text-[11px] font-bold text-sky-600 px-3">
              TP-PKK Marketplace
            </div>
          </div>

          <div class="space-y-2">
            <h3 class="text-xl sm:text-2xl font-bold text-slate-900">Atur Ulang Kata Sandi</h3>
            <p class="text-sm text-slate-600">
              Masukkan email terdaftar untuk menerima tautan reset kata sandi.
            </p>
          </div>

          <form class="mt-6 space-y-5" @submit.prevent="submit">
            <div class="space-y-2">
              <label for="email" class="text-sm font-semibold text-slate-700">Alamat Email</label>
              <input id="email" v-model="form.email" type="email" autocomplete="email" required
                class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm sm:text-base text-slate-800 placeholder:text-slate-400 focus:border-sky-400 focus:bg-white focus:outline-none"
                placeholder="Masukkan email Anda" :disabled="form.processing" />
              <p v-if="form.errors.email" class="text-xs text-red-500">{{ form.errors.email }}</p>
            </div>

            <button type="submit" :disabled="form.processing"
              class="flex w-full items-center justify-center rounded-md bg-sky-600 py-3 text-sm sm:text-base font-semibold text-white transition hover:bg-sky-700 disabled:bg-slate-200 disabled:text-slate-400">
              {{ form.processing ? 'Mengirim...' : 'Reset Kata Sandi' }}
            </button>

            <p v-if="flashSuccess" class="text-center text-sm text-green-600">
              {{ flashSuccess }}
            </p>
          </form>

          <p class="mt-4 text-center text-sm font-semibold text-sky-600">
            <Link :href="props.loginUrl || '/login'" class="hover:underline">Kembali ke halaman login</Link>
          </p>
        </div>
      </div>
    </div>

    <CustomerCareButton />
  </section>
</template>
