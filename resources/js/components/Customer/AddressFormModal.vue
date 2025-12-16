<script setup>
import { computed } from 'vue';
import RegionSelector from '@/components/RegionSelector.vue';

const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: 'Tambah Alamat Pengiriman',
  },
  description: {
    type: String,
    default: 'Lengkapi data alamatmu untuk melanjutkan.',
  },
  form: {
    type: Object,
    required: true,
  },
  submitLabel: {
    type: String,
    default: 'Simpan Alamat',
  },
  cancelLabel: {
    type: String,
    default: 'Batal',
  },
  showDefaultToggle: {
    type: Boolean,
    default: true,
  },
  initialRegionNames: {
    type: Object,
    default: () => ({}),
  },
});

const emit = defineEmits(['update:open', 'submit']);

const form = computed(() => props.form ?? {});
const isProcessing = computed(() => Boolean(form.value?.processing));
const regionNames = computed(() => ({
  province: props.initialRegionNames?.province ?? '',
  city: props.initialRegionNames?.city ?? '',
  district: props.initialRegionNames?.district ?? '',
}));

const handleClose = () => {
  if (isProcessing.value) return;
  emit('update:open', false);
};

const handleSubmit = () => {
  emit('submit');
};
</script>

<template>
  <div v-if="open" class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-900/70 px-4 py-10"
    @click.self="handleClose">
    <div class="w-full max-w-2xl rounded-md bg-white shadow-2xl ring-1 ring-black/5">
      <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-6 py-5">
        <div>
          <p class="text-lg font-bold text-slate-900">{{ title }}</p>
          <p class="text-sm text-slate-500">{{ description }}</p>
        </div>
        <button type="button" class="p-2 text-slate-400 transition hover:text-slate-600" :disabled="isProcessing"
          @click="handleClose">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="m6 6 12 12M6 18 18 6" stroke-linecap="round" />
          </svg>
        </button>
      </div>

      <form class="max-h-[75vh] space-y-4 overflow-y-auto px-6 py-5" @submit.prevent="handleSubmit">
        <div class="grid gap-4 sm:grid-cols-2">
          <div>
            <label class="text-sm font-semibold text-slate-700">Label Alamat</label>
            <input v-model="form.label" type="text"
              class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100"
              :disabled="isProcessing" placeholder="Rumah / Kantor" />
            <p v-if="form.errors?.label" class="mt-1 text-xs text-red-500">{{ form.errors.label }}</p>
          </div>
          <div>
            <label class="text-sm font-semibold text-slate-700">Nama Penerima</label>
            <input v-model="form.recipient_name" type="text"
              class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100"
              :disabled="isProcessing" placeholder="Nama Lengkap" />
            <p v-if="form.errors?.recipient_name" class="mt-1 text-xs text-red-500">
              {{ form.errors.recipient_name }}
            </p>
          </div>
        </div>

        <div>
          <label class="text-sm font-semibold text-slate-700">Nomor Telepon</label>
          <input v-model="form.phone" type="tel"
            class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100"
            :disabled="isProcessing" placeholder="08xxxxxxxxxx" />
          <p v-if="form.errors?.phone" class="mt-1 text-xs text-red-500">{{ form.errors.phone }}</p>
        </div>

        <div>
          <label class="text-sm font-semibold text-slate-700">Provinsi, Kota, Kecamatan</label>
          <div class="mt-2 rounded-lg border border-slate-200 p-3">
            <RegionSelector v-model:provinceId="form.province_id" v-model:cityId="form.city_id"
              v-model:districtId="form.district_id" :show-district="true" :disabled="isProcessing"
              :province-required="true" :city-required="true" :district-required="true" :errors="{
                provinceId: form.errors?.province_id,
                cityId: form.errors?.city_id,
                districtId: form.errors?.district_id,
              }" :initial-province-name="regionNames.province" :initial-city-name="regionNames.city"
              :initial-district-name="regionNames.district" />
          </div>
          <div class="mt-1 space-y-0.5 text-xs text-red-500">
            <p v-if="form.errors?.province_id">{{ form.errors.province_id }}</p>
            <p v-if="form.errors?.city_id">{{ form.errors.city_id }}</p>
            <p v-if="form.errors?.district_id">{{ form.errors.district_id }}</p>
          </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
          <div>
            <label class="text-sm font-semibold text-slate-700">Kode Pos</label>
            <input v-model="form.postal_code" type="text"
              class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100"
              :disabled="isProcessing" placeholder="Kode Pos" />
            <p v-if="form.errors?.postal_code" class="mt-1 text-xs text-red-500">{{ form.errors.postal_code }}</p>
          </div>
          <div>
            <label class="text-sm font-semibold text-slate-700">Catatan untuk Kurir (Opsional)</label>
            <input v-model="form.note" type="text"
              class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100"
              :disabled="isProcessing" placeholder="Patokan, warna rumah, dll" />
            <p v-if="form.errors?.note" class="mt-1 text-xs text-red-500">{{ form.errors.note }}</p>
          </div>
        </div>

        <div>
          <label class="text-sm font-semibold text-slate-700">Alamat Lengkap</label>
          <textarea v-model="form.address_line" rows="3"
            class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100"
            :disabled="isProcessing" placeholder="Jalan, nomor rumah, RT/RW"></textarea>
          <p v-if="form.errors?.address_line" class="mt-1 text-xs text-red-500">{{ form.errors.address_line }}</p>
        </div>

        <label v-if="showDefaultToggle" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700">
          <input type="checkbox" class="h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500"
            v-model="form.is_default" :disabled="isProcessing" />
          Jadikan sebagai alamat utama
        </label>

        <div class="flex flex-wrap items-center justify-end gap-3 pt-2">
          <button type="button"
            class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60"
            :disabled="isProcessing" @click="handleClose">
            {{ cancelLabel }}
          </button>
          <button type="submit"
            class="rounded-lg bg-sky-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-sky-700 disabled:cursor-not-allowed disabled:opacity-60"
            :disabled="isProcessing">
            {{ isProcessing ? 'Menyimpan...' : submitLabel }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
