<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch, onUnmounted } from 'vue';

const props = defineProps<{
  loginUrl?: string;
}>();

const form = useForm({
  email: '',
});

const emailSent = ref(false);
const sentEmail = ref('');
const cooldownSeconds = ref(0);
let cooldownInterval: ReturnType<typeof setInterval> | null = null;

const canResend = computed(() => cooldownSeconds.value === 0 && !form.processing);

const startCooldown = () => {
  if (cooldownInterval) clearInterval(cooldownInterval);
  cooldownSeconds.value = 150; // 2.5 minutes = 150 seconds
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

const submit = () => {
  form.post('/forgot-password', {
    preserveScroll: true,
    onSuccess: () => {
      sentEmail.value = form.email;
      emailSent.value = true;
      startCooldown();
    },
  });
};

const resendEmail = () => {
  if (!canResend.value) return;
  form.email = sentEmail.value;
  form.post('/forgot-password', {
    preserveScroll: true,
    onSuccess: () => {
      startCooldown();
    },
  });
};

const page = usePage();
const flashSuccess = computed(() => (page.props.flash as any)?.success ?? '');

// If page loads with flash success (e.g., from redirect), show email sent view
watch(
  flashSuccess,
  (newVal) => {
    if (newVal && form.email) {
      sentEmail.value = form.email;
      emailSent.value = true;
      startCooldown();
    }
  },
  { immediate: true }
);

onUnmounted(() => {
  if (cooldownInterval) {
    clearInterval(cooldownInterval);
  }
});
</script>

<template>

  <Head title="Lupa Kata Sandi" />

  <section class="min-h-screen bg-slate-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
      <!-- Card -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-8 sm:p-10">

        <!-- Email Sent Success View -->
        <template v-if="emailSent">
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
            Kami telah mengirimkan tautan reset kata sandi ke
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
            <button v-if="canResend" type="button" @click="resendEmail"
              class="mt-1 font-medium text-sm text-sky-600 hover:text-sky-700 hover:underline">
              Kirim ulang tautan
            </button>
            <p v-else class="mt-1 text-sm text-slate-400">
              <span v-if="form.processing" class="inline-flex items-center gap-1.5">
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
            <Link :href="props.loginUrl || '/login'"
              class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-sky-600">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 12H5M12 19l-7-7 7-7" />
              </svg>
              Kembali ke halaman login
            </Link>
          </div>
        </template>

        <!-- Initial Form View -->
        <template v-else>
          <!-- Icon -->
          <div class="flex justify-center mb-6">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-sky-50">
              <svg class="h-8 w-8 text-sky-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                stroke-linecap="round" stroke-linejoin="round">
                <path
                  d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4" />
              </svg>
            </div>
          </div>

          <!-- Title -->
          <h1 class="text-2xl font-bold text-slate-900 text-center">Lupa Kata Sandi?</h1>
          <p class="mt-2 text-sm text-slate-500 text-center">
            Jangan khawatir, kami akan mengirimkan instruksi untuk mengatur ulang kata sandi Anda.
          </p>

          <!-- Form -->
          <form @submit.prevent="submit" class="mt-8 space-y-5">
            <!-- Email -->
            <div class="space-y-1.5">
              <label for="email" class="text-sm font-medium text-slate-700">Email</label>
              <input id="email" v-model="form.email" type="email" autocomplete="email" required
                placeholder="Masukkan email Anda"
                class="w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-sky-400 focus:outline-none focus:ring-1 focus:ring-sky-400"
                :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-400': form.errors.email }"
                :disabled="form.processing" />
              <p v-if="form.errors.email" class="text-xs text-red-500">{{ form.errors.email }}</p>
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
              {{ form.processing ? 'Mengirim' : 'Reset Kata Sandi' }}
            </button>
          </form>

          <!-- Back to Login -->
          <div class="mt-6 text-center">
            <Link :href="props.loginUrl || '/login'"
              class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-sky-600">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 12H5M12 19l-7-7 7-7" />
              </svg>
              Kembali ke halaman login
            </Link>
          </div>
        </template>

      </div>
    </div>
  </section>
</template>
