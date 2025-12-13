<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import LandingLayout from '@/Layouts/LandingLayout.vue';

const props = defineProps({
  appName: {
    type: String,
    default: 'TP-PKK Marketplace',
  },
  groups: {
    type: Array,
    default: () => [],
  },
});

const letters = computed(() => props.groups.map((group) => group.letter));
const activeLetter = ref(letters.value[0] || 'A');

watch(
  letters,
  (value) => {
    if (value.includes(activeLetter.value)) return;
    activeLetter.value = value[0] || 'A';
  },
  { immediate: true }
);

const activeGroup = computed(() => props.groups.find((group) => group.letter === activeLetter.value) || null);

const selectLetter = (letter) => {
  activeLetter.value = letter;
};

const hasCategories = computed(() => (activeGroup.value?.categories?.length ?? 0) > 0);
</script>

<template>
  <LandingLayout>

    <Head :title="`Semua Kategori - ${props.appName}`" />

    <section class="space-y-6">
      <header class="space-y-2">
        <nav class="flex items-center gap-2 text-xs text-slate-500">
          <Link class="text-sky-600 hover:underline" href="/">Beranda</Link>
          <span>/</span>
          <span class="font-semibold text-slate-900">Semua Kategori</span>
        </nav>
        <div class="flex flex-wrap items-center gap-3">
          <h1 class="text-2xl font-bold text-slate-900">Semua Kategori</h1>
          <p class="text-sm text-slate-500">Pilih kategori berdasarkan alfabet</p>
        </div>
      </header>

      <div class="flex flex-wrap gap-2 rounded-lg bg-white p-3 shadow-sm ring-1 ring-slate-100 sm:p-4">
        <button v-for="letter in letters" :key="letter" type="button"
          class="min-w-[32px] rounded-md px-3 py-2 text-sm font-semibold transition" :class="activeLetter === letter
            ? 'bg-sky-100 text-sky-700 ring-1 ring-sky-200'
            : 'text-slate-600 hover:bg-slate-50'" @click="selectLetter(letter)">
          {{ letter }}
        </button>
      </div>

      <div v-if="activeGroup" class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-slate-100">
        <div
          class="mb-6 inline-flex h-12 w-12 items-center justify-center rounded-md bg-sky-100 text-lg font-bold text-sky-700">
          {{ activeGroup.letter }}
        </div>

        <div v-if="hasCategories" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
          <div v-for="category in activeGroup.categories" :key="category.id"
            class="space-y-3 border-b border-slate-100 pb-4 last:border-none">
            <Link class="text-base font-semibold text-slate-800 hover:text-sky-700" :href="`/c/${category.slug}`">
              {{ category.name }}
            </Link>

            <div class="grid gap-2 text-sm text-slate-700">
              <Link v-for="child in category.children" :key="child.id" class="hover:text-sky-700"
                :href="`/c/${child.slug}`">
                {{ child.name }}
              </Link>
              <p v-if="!category.children || category.children.length === 0" class="text-slate-400">
                Belum ada sub-kategori.
              </p>
            </div>
          </div>
        </div>

        <div v-else class="rounded-md bg-slate-50 p-6 text-center text-slate-500">
          Belum ada kategori untuk huruf ini.
        </div>
      </div>
    </section>
  </LandingLayout>
</template>
