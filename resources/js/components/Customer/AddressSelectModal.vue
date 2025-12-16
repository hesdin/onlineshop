<script setup>
const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },
  addresses: {
    type: Array,
    default: () => [],
  },
  selectedId: {
    type: [Number, String, null],
    default: null,
  },
  title: {
    type: String,
    default: 'Alamat Pengiriman',
  },
  description: {
    type: String,
    default: 'Pilih alamat pengiriman atau tambah alamat baru',
  },
  emptyMessage: {
    type: String,
    default: 'Belum ada alamat. Tambah alamat terlebih dahulu.',
  },
  showAddButton: {
    type: Boolean,
    default: true,
  },
  addLabel: {
    type: String,
    default: 'Tambah Alamat',
  },
  closeLabel: {
    type: String,
    default: 'Tutup',
  },
  showEditButton: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['update:open', 'add', 'select', 'edit']);

const isSelected = (id) => {
  if (id === undefined || id === null || props.selectedId === undefined || props.selectedId === null) {
    return false;
  }
  return String(id) === String(props.selectedId);
};

const detailLines = (address) => {
  if (Array.isArray(address.lines) && address.lines.length) {
    return address.lines;
  }
  if (address.detail && typeof address.detail === 'string') {
    return address.detail.split('\n').map((line) => line.trim()).filter(Boolean);
  }
  return [];
};

const handleClose = () => {
  emit('update:open', false);
};

const handleAdd = () => {
  emit('add');
};

const handleSelect = (id) => {
  emit('select', id);
};

const handleEdit = (id) => {
  emit('edit', id);
};
</script>

<template>
  <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 px-4 py-10"
    @click.self="handleClose">
    <div class="w-full max-w-3xl rounded-2xl bg-white shadow-2xl ring-1 ring-black/5">
      <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-6 py-5">
        <div>
          <p class="text-lg font-bold text-slate-900">{{ title }}</p>
          <p class="text-sm text-slate-500">{{ description }}</p>
        </div>
        <div class="flex items-center gap-2">
          <button v-if="showAddButton" type="button"
            class="inline-flex items-center gap-2 rounded-lg border border-sky-600 px-4 py-2 text-sm font-semibold text-sky-600 transition hover:bg-sky-50"
            @click.stop="handleAdd">
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6">
              <path d="M10 4v12" stroke-linecap="round" />
              <path d="M4 10h12" stroke-linecap="round" />
            </svg>
            {{ addLabel }}
          </button>
          <button type="button"
            class="rounded-full p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
            @click="handleClose">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path d="m6 6 12 12M6 18 18 6" stroke-linecap="round" />
            </svg>
          </button>
        </div>
      </div>

      <div class="max-h-[70vh] space-y-4 overflow-y-auto px-6 py-5">
        <div v-for="address in addresses" :key="address.id ?? address.label"
          class="relative rounded-2xl border bg-white p-5 transition"
          :class="isSelected(address.id) ? 'border-emerald-300 bg-emerald-50/60 shadow-sm' : 'border-slate-200 hover:border-sky-300 hover:shadow-md'">
          <div class="absolute right-4 top-4">
            <span v-if="isSelected(address.id)"
              class="inline-flex items-center gap-1 rounded-full bg-emerald-500 px-3 py-1 text-xs font-semibold text-white">
              <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                <path
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
              </svg>
              Terpilih
            </span>
            <button v-else type="button"
              class="inline-flex items-center gap-1 rounded-full border border-sky-500 bg-white px-3 py-1 text-xs font-semibold text-sky-600 transition hover:bg-sky-50"
              @click.stop="handleSelect(address.id)">
              <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6">
                <path d="M7 10l2 2 4-4" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M5 5h10v10H5z" />
              </svg>
              Pilih
            </button>
          </div>

          <div class="flex items-start gap-4 pr-24">
            <div class="flex h-11 w-11 items-center justify-center rounded-full"
              :class="isSelected(address.id) ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-500'">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M12 21s7-6.2 7-11.2A7 7 0 0 0 5 9.8C5 14.8 12 21 12 21z" />
                <circle cx="12" cy="9.5" r="2.3" />
              </svg>
            </div>

            <div class="flex-1 space-y-2">
              <div class="flex flex-wrap items-center gap-2 text-sm">
                <p class="font-semibold text-slate-900">{{ address.label || address.tag || 'Alamat' }}</p>
                <span v-if="address.recipient" class="text-xs text-slate-400">•</span>
                <p v-if="address.recipient" class="font-semibold text-slate-700">{{ address.recipient }}</p>
              </div>
              <div class="space-y-1 text-sm text-slate-700">
                <p v-if="address.detail" class="font-medium text-slate-800">{{ address.detail }}</p>
                <template v-for="(line, index) in detailLines(address)" :key="index">
                  <p>{{ line }}</p>
                </template>
                <p v-if="address.phone" class="text-slate-500">{{ address.phone }}</p>
              </div>
              <button v-if="showEditButton && address.canEdit !== false" type="button"
                class="inline-flex items-center gap-1 rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50"
                @click.stop="handleEdit(address.id)">
                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793z" />
                  <path d="M11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                </svg>
                Ubah Alamat
              </button>
            </div>
          </div>
        </div>

        <p v-if="!addresses.length" class="text-sm text-slate-500">
          {{ emptyMessage }}
        </p>
      </div>

      <div class="flex items-center justify-end gap-3 border-t border-slate-200 px-6 py-4">
        <button type="button"
          class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
          @click="handleClose">
          {{ closeLabel }}
        </button>
      </div>
    </div>
  </div>
</template>
