<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, reactive, watch, nextTick, ref, onBeforeUnmount } from 'vue';
import LandingLayout from '@/Layouts/LandingLayout.vue';
import AddressFormModal from '@/components/Customer/AddressFormModal.vue';
import AddressSelectModal from '@/components/Customer/AddressSelectModal.vue';

const goBack = () => window.history.back();

const props = defineProps({
  appName: {
    type: String,
    default: 'TP-PKK Marketplace',
  },
  groups: {
    type: Array,
    default: () => [],
  },
  addresses: {
    type: Array,
    default: () => [],
  },
  shippingOptions: {
    type: Array,
    default: () => [],
  },
  noteLimit: {
    type: Number,
    default: 100,
  },
  isBuyNow: {
    type: Boolean,
    default: false,
  },
});

const state = reactive({
  itemStates: [],
  selectedAddressIndex: 0,
  addressModalOpen: false,
  addressFormOpen: false,
  shippingSelections: {},
  notification: {
    show: false,
    message: '',
    type: 'error',
  },
  validationTriggered: false,
  paymentProcessing: false,
});

const addressForm = useForm({
  label: '',
  recipient_name: '',
  phone: '',
  province_id: null,
  city_id: null,
  district_id: null,
  postal_code: '',
  address_line: '',
  is_default: false,
  note: '',
});
const editingAddressId = ref(null);
const regionInitialNames = reactive({
  province: '',
  city: '',
  district: '',
});
const addressLoading = ref(false);
const addressSuccessVisible = ref(false);
const addressSuccessMessage = ref('');
let addressSuccessTimer = null;
const PAYMENT_LOADING_DURATION_MS = 800;
let paymentProcessingTimer = null;
let paymentProcessingStartedAt = 0;
let paymentRedirectTimeout = null;

const showAddressSuccess = (message = 'Alamat berhasil disimpan.') => {
  addressSuccessMessage.value = message;
  addressSuccessVisible.value = true;
  if (addressSuccessTimer) {
    clearTimeout(addressSuccessTimer);
  }
  addressSuccessTimer = setTimeout(() => {
    addressSuccessVisible.value = false;
    addressSuccessMessage.value = '';
    addressSuccessTimer = null;
  }, 3000);
};

const globalLoadingMessage = computed(() => {
  if (state.paymentProcessing) {
    return 'Loading...';
  }
  if (addressLoading.value) {
    return 'Menyimpan alamat...';
  }
  return 'Mohon tunggu...';
});

const formatCurrency = (value) =>
  new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(
    Number.parseInt(value ?? 0, 10) || 0
  );

const isValidImage = (url) => {
  if (!url || typeof url !== 'string') return false;
  const trimmed = url.trim();
  if (!trimmed) return false;
  // Filter out common placeholder URLs
  const placeholderPatterns = [
    'picsum.photos',
    'placeholder',
    'via.placeholder',
    'placehold',
    'dummy',
    'no-image',
    'noimage'
  ];
  return !placeholderPatterns.some(pattern => trimmed.toLowerCase().includes(pattern));
};

const initializeState = () => {
  state.itemStates = (props.groups ?? []).map((group, groupIndex) =>
    (group.items ?? []).map((item, itemIndex) => ({
      checked: true,
      price: item.price ?? item.unit_price ?? 0,
      qty: item.qty ?? item.quantity ?? 1,
      type: item.type ?? 'Barang',
      weight: item.weight ?? 0,
      note: item.note ?? '',
      savedNote: item.note ?? '',
      noteOpen: false,
      shipping_method: item.shipping_method || '',
    }))
  );

  const defaultAddrIndex = Math.max(
    0,
    (props.addresses ?? []).findIndex((addr) => addr.is_default) ?? 0
  );
  state.selectedAddressIndex = defaultAddrIndex === -1 ? 0 : defaultAddrIndex;
};

watch(
  () => props.groups,
  () => initializeState(),
  { immediate: true, deep: true }
);

watch(
  () => props.addresses,
  () => {
    const defaultIdx = (props.addresses ?? []).findIndex((addr) => addr.is_default);
    if (defaultIdx >= 0) {
      state.selectedAddressIndex = defaultIdx;
    }
  },
  { immediate: true, deep: true }
);

const ADDRESS_REQUIRED_FIELDS = [
  { key: 'label', message: 'Label alamat wajib diisi.' },
  { key: 'recipient_name', message: 'Nama penerima wajib diisi.' },
  { key: 'phone', message: 'Nomor telepon wajib diisi.' },
  { key: 'province_id', message: 'Provinsi wajib dipilih.' },
  { key: 'city_id', message: 'Kota/Kabupaten wajib dipilih.' },
  { key: 'district_id', message: 'Kecamatan wajib dipilih.' },
  { key: 'postal_code', message: 'Kode pos wajib diisi.' },
  { key: 'address_line', message: 'Alamat lengkap wajib diisi.' },
];

const isEmptyAddressField = (value) => {
  if (value === null || value === undefined) return true;
  if (typeof value === 'string') {
    return value.trim().length === 0;
  }
  return false;
};

const validateAddressForm = () => {
  addressForm.clearErrors();
  let isValid = true;

  ADDRESS_REQUIRED_FIELDS.forEach(({ key, message }) => {
    if (isEmptyAddressField(addressForm[key])) {
      addressForm.setError(key, message);
      isValid = false;
    }
  });

  return isValid;
};

const closeAddressForm = () => {
  if (addressForm.processing) return;
  state.addressFormOpen = false;
  editingAddressId.value = null;
};

const handleAddressFormToggle = (value) => {
  if (!value) {
    closeAddressForm();
    return;
  }
  state.addressFormOpen = true;
};

const submitAddressForm = () => {
  if (!validateAddressForm()) {
    return;
  }

  const isEditing = editingAddressId.value !== null;
  const endpoint = isEditing ? `/customer/dashboard/address/${editingAddressId.value}` : '/customer/dashboard/address';
  const method = isEditing ? 'put' : 'post';

  addressLoading.value = true;
  addressForm[method](endpoint, {
    preserveScroll: true,
    preserveState: true,
    replace: true,
    onSuccess: () => {
      state.addressFormOpen = false;
      editingAddressId.value = null;
      addressForm.reset();
      addressForm.is_default = (addresses.value?.length ?? 0) === 0;
      showAddressSuccess(isEditing ? 'Alamat berhasil diperbarui.' : 'Alamat berhasil disimpan.');
    },
    onFinish: () => {
      addressLoading.value = false;
    },
  });
};

const groups = computed(() => props.groups ?? []);
const addresses = computed(() => props.addresses ?? []);
const selectedAddress = computed(() => addresses.value[state.selectedAddressIndex] ?? addresses.value[0] ?? {});
const selectedAddressId = computed(() => {
  const address = addresses.value[state.selectedAddressIndex] ?? addresses.value[0];
  if (!address) return null;
  return address.id ?? state.selectedAddressIndex;
});
const addressOptions = computed(() =>
  addresses.value.map((addr, index) => {
    const locationParts = [
      addr.district ? addr.district : null,
      addr.city ? addr.city : null,
      addr.province ? addr.province : null,
    ].filter(Boolean);

    return {
      id: addr.id ?? index,
      label: addr.tag || addr.label || 'Alamat',
      recipient: addr.recipient_name || addr.name,
      detail: addr.address_line || addr.detail,
      lines: locationParts.length ? [locationParts.join(', '), addr.postal_code].filter(Boolean) : [],
      phone: addr.phone,
      canEdit: true,
    };
  })
);
const selectedItems = computed(() =>
  (props.groups ?? []).flatMap((group, groupIndex) =>
    (group.items ?? []).map((item, itemIndex) => ({
      id: item.id,
      checked: state.itemStates[groupIndex]?.[itemIndex]?.checked ?? true,
    }))
  )
);
const selectedIds = computed(() => selectedItems.value.filter((item) => item.checked && item.id).map((item) => item.id));
const hasSelection = computed(() => selectedIds.value.length > 0);

const orderSummaries = computed(() =>
  groups.value
    .map((group, groupIndex) => {
      const selected = (state.itemStates[groupIndex] ?? [])
        .map((item, idx) => ({ item, idx }))
        .filter(({ item }) => item.checked);
      if (!selected.length) return null;

      const itemCount = selected.reduce((sum, entry) => sum + entry.item.qty, 0);
      const total = selected.reduce((sum, entry) => {
        const product = group.items?.[entry.idx];
        const price = product?.price ?? entry.item.price ?? 0;
        return sum + price * entry.item.qty;
      }, 0);
      const totalWeight = selected.reduce(
        (sum, entry) => sum + (entry.item.weight || 0) * entry.item.qty,
        0
      );
      const hasOnlyServices = selected.every((entry) => entry.item.type === 'Jasa');
      const typeLabel = hasOnlyServices
        ? 'Total Harga Jasa'
        : `Total Harga ${itemCount} Barang${totalWeight ? ` (${(totalWeight / 1000).toFixed(2)} kg)` : ''}`;

      return {
        vendor: group.vendor,
        total,
        typeLabel,
        benefit: group.benefit === 'Gratis Ongkir' ? 'Gratis Ongkir' : null,
      };
    })
    .filter(Boolean)
);

const selectedTotal = computed(() =>
  state.itemStates.reduce((sum, group, groupIndex) => {
    return (
      sum +
      group.reduce((inner, item, itemIndex) => {
        if (!item.checked) return inner;
        const product = props.groups?.[groupIndex]?.items?.[itemIndex];
        const price = product?.price ?? item.price ?? 0;
        return inner + price * item.qty;
      }, 0)
    );
  }, 0)
);

const toggleNote = (groupIndex, itemIndex) => {
  const stateItem = state.itemStates[groupIndex]?.[itemIndex];
  if (!stateItem) return;
  stateItem.noteOpen = !stateItem.noteOpen;
};

const focusNoteInput = (groupIndex, itemIndex) => {
  nextTick(() => {
    const textarea = document.getElementById(`note-${groupIndex}-${itemIndex}`);
    if (textarea) {
      textarea.focus();
      textarea.setSelectionRange(textarea.value.length, textarea.value.length);
    }
  });
};

const openNoteEditor = (groupIndex, itemIndex) => {
  const stateItem = state.itemStates[groupIndex]?.[itemIndex];
  if (!stateItem) return;
  if (!stateItem.noteOpen) {
    stateItem.noteOpen = true;
  }
  focusNoteInput(groupIndex, itemIndex);
};

const limitNoteLength = (groupIndex, itemIndex) => {
  const stateItem = state.itemStates[groupIndex]?.[itemIndex];
  if (!stateItem) return;
  stateItem.note = (stateItem.note || '').slice(0, props.noteLimit || 100);
};

const selectAddress = (index) => {
  state.selectedAddressIndex = index;
  state.addressModalOpen = false;
  showAddressSuccess('Alamat berhasil dipilih.');
};

const handleAddressSelect = (id) => {
  const index = addresses.value.findIndex((addr, idx) => String(addr.id ?? idx) === String(id));
  if (index >= 0) {
    selectAddress(index);
  }
};

const handleAddressEdit = (id) => {
  state.addressModalOpen = false;
  openAddressForm(id);
};

const openAddressForm = (addressId = null) => {
  addressForm.reset();
  addressForm.clearErrors();
  regionInitialNames.province = '';
  regionInitialNames.city = '';
  regionInitialNames.district = '';

  if (addressId !== null) {
    const target = addresses.value.find((addr) => String(addr.id) === String(addressId));
    if (target) {
      editingAddressId.value = target.id;
      addressForm.label = target.label || target.tag || '';
      addressForm.recipient_name = target.recipient_name || target.name || '';
      addressForm.phone = target.phone || '';
      addressForm.province_id = target.province_id ?? null;
      addressForm.city_id = target.city_id ?? null;
      addressForm.district_id = target.district_id ?? null;
      addressForm.postal_code = target.postal_code || '';
      addressForm.address_line = target.address_line || '';
      addressForm.is_default = Boolean(target.is_default);
      addressForm.note = target.note || '';
      regionInitialNames.province = target.province || '';
      regionInitialNames.city = target.city || '';
      regionInitialNames.district = target.district || '';
    } else {
      editingAddressId.value = null;
      addressForm.is_default = false;
    }
  } else {
    editingAddressId.value = null;
    addressForm.is_default = (addresses.value?.length ?? 0) === 0;
  }

  state.addressFormOpen = true;
};

const openNewAddress = () => {
  state.addressModalOpen = false;
  openAddressForm();
};

const selectShipping = (groupIndex, itemIndex, value, cartItemId) => {
  if (!state.itemStates[groupIndex] || !state.itemStates[groupIndex][itemIndex]) return;
  state.itemStates[groupIndex][itemIndex].shipping_method = value;

  // Skip API call for buy-now items (no cart item in database)
  if (!cartItemId || String(cartItemId).startsWith('buy-now-')) return;

  router.put(
    `/cart/items/${cartItemId}/shipping`,
    {
      shipping_method: value || null,
    },
    {
      preserveScroll: true,
      preserveState: true,
      only: [],
    }
  );
};

const saveNote = (groupIndex, itemIndex, cartItemId) => {
  const stateItem = state.itemStates[groupIndex]?.[itemIndex];
  if (!stateItem) return;

  limitNoteLength(groupIndex, itemIndex);

  const note = stateItem.note || '';
  stateItem.noteOpen = false;

  if (!cartItemId || String(cartItemId).startsWith('buy-now-') || stateItem.savedNote === note) {
    return;
  }

  router.put(`/cart/items/${cartItemId}/note`, { note }, {
    preserveScroll: true,
    preserveState: true,
    only: [],
    onSuccess: () => {
      stateItem.savedNote = note;
    },
  });
};

const handleNoteKeydown = (event) => {
  if (event.key !== 'Enter' || event.shiftKey || event.metaKey || event.ctrlKey || event.altKey) {
    return;
  }

  event.preventDefault();
  event.target?.blur?.();
};

const showNotification = (message, type = 'error') => {
  state.notification.message = message;
  state.notification.type = type;
  state.notification.show = true;

  setTimeout(() => {
    state.notification.show = false;
  }, 3000);
};

const startPaymentProcessing = () => {
  if (paymentProcessingTimer) {
    clearTimeout(paymentProcessingTimer);
    paymentProcessingTimer = null;
  }
  if (paymentRedirectTimeout) {
    clearTimeout(paymentRedirectTimeout);
    paymentRedirectTimeout = null;
  }
  paymentProcessingStartedAt = Date.now();
  state.paymentProcessing = true;
};

const stopPaymentProcessing = () => {
  const elapsed = Date.now() - paymentProcessingStartedAt;
  const remaining = PAYMENT_LOADING_DURATION_MS - elapsed;

  if (remaining > 0) {
    paymentProcessingTimer = setTimeout(() => {
      state.paymentProcessing = false;
      paymentProcessingTimer = null;
    }, remaining);
  } else {
    state.paymentProcessing = false;
  }
};

const proceedToPayment = () => {
  if (!hasSelection.value) return;

  // Validate that all selected items have shipping method
  let hasError = false;
  let missingCount = 0;

  state.itemStates.forEach((group, groupIndex) => {
    group.forEach((item, itemIndex) => {
      if (item.checked && !item.shipping_method) {
        hasError = true;
        missingCount++;
      }
    });
  });

  if (hasError) {
    state.validationTriggered = true;
    showNotification(`${missingCount} produk belum memilih metode pengiriman`);
    return;
  }

  state.validationTriggered = false;

  // Collect shipping selections by storeId
  const shippingSelections = {};
  props.groups.forEach((group, groupIndex) => {
    // We assume one shipping method per store (order).
    // We take the method from the first checked item in the group.
    const groupState = state.itemStates[groupIndex];
    const checkedItemIndex = group.items.findIndex((item, idx) => groupState[idx].checked);

    if (checkedItemIndex !== -1) {
      const method = groupState[checkedItemIndex].shipping_method;
      if (method && group.storeId) {
        shippingSelections[group.storeId] = method;
      }
    }
  });

  startPaymentProcessing();

  paymentRedirectTimeout = setTimeout(() => {
    router.get('/cart/payment', {
      items: selectedIds.value,
      shipping_selections: shippingSelections
    }, {
      preserveScroll: true,
      onFinish: () => {
        stopPaymentProcessing();
        paymentRedirectTimeout = null;
      },
    });
  }, PAYMENT_LOADING_DURATION_MS);
};

onBeforeUnmount(() => {
  if (paymentProcessingTimer) {
    clearTimeout(paymentProcessingTimer);
  }
  if (paymentRedirectTimeout) {
    clearTimeout(paymentRedirectTimeout);
  }
  if (addressSuccessTimer) {
    clearTimeout(addressSuccessTimer);
  }
});
</script>

<template>
  <!-- Buy Now Mode: Minimal layout without header/footer -->
  <div v-if="isBuyNow" class="min-h-screen bg-slate-50 font-sans text-slate-900">

    <Head :title="`Checkout - ${appName}`" />

    <!-- Minimal Header for Buy Now -->
    <header class="fixed top-0 left-0 right-0 z-40 border-b border-slate-200 bg-white shadow-sm">
      <div class="container mx-auto flex h-16 items-center justify-between px-4">
        <div class="flex items-center gap-3">
          <button type="button" @click="goBack"
            class="flex h-10 w-10 items-center justify-center rounded-full text-slate-600 transition hover:bg-slate-100">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd"
                d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                clip-rule="evenodd" />
            </svg>
          </button>
          <h1 class="text-lg font-semibold text-slate-900">Checkout</h1>
        </div>
      </div>
    </header>

    <main class="container mx-auto space-y-12 px-4 py-10 pt-24">
      <section class="space-y-6">
        <div class="flex items-center gap-2">
          <h1 class="text-3xl font-bold text-slate-900">Checkout</h1>
        </div>

        <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,2.4fr)_minmax(320px,1fr)]">
          <div class="space-y-4">
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
              <p class="text-base font-semibold text-slate-800">Alamat Pengiriman</p>
              <div class="mt-2 text-sm text-slate-600" v-if="addresses.length">
                <p class="font-semibold text-slate-900">{{ selectedAddress.tag }} - {{ selectedAddress.name }}</p>
                <p class="text-slate-700">{{ selectedAddress.phone }}</p>
                <p class="text-slate-600">{{ selectedAddress.detail }}</p>
              </div>
              <p v-else class="mt-2 text-sm text-slate-500">Belum ada alamat tersimpan.</p>
              <button type="button"
                class="mt-3 inline-flex w-full items-center justify-center rounded-md border border-sky-600 px-4 py-2 text-sm font-semibold text-sky-600 transition hover:bg-sky-50"
                @click="state.addressModalOpen = true">
                Pilih Alamat Lain
              </button>
            </div>

            <div v-if="!groups.length"
              class="rounded-lg border border-dashed border-slate-200 bg-white p-6 text-sm text-slate-600">
              Tidak ada item untuk checkout.
            </div>

            <div v-for="(group, groupIndex) in groups" :key="group.storeId ?? groupIndex"
              class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
              <div class="flex items-center gap-3 border-b border-slate-100 px-5 py-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100">
                  <svg class="h-6 w-6 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.5">
                    <circle cx="12" cy="8" r="4" />
                    <path d="M5 21c0-4 3-7 7-7s7 3 7 7" />
                  </svg>
                </div>
                <div class="flex-1">
                  <p class="text-sm font-semibold text-slate-900">{{ group.vendor }}</p>
                  <p class="text-xs text-slate-500">{{ group.location || 'Lokasi tidak tersedia' }}</p>
                </div>
                <span v-if="group.benefit"
                  class="inline-flex items-center gap-1 rounded-md bg-sky-50 px-3 py-1 text-[11px] font-semibold text-sky-700">
                  {{ group.benefit }}
                </span>
              </div>

              <div class="divide-y divide-slate-100">
                <div v-for="(item, itemIndex) in group.items" :key="item.id ?? itemIndex" class="space-y-3 px-5 py-4">
                  <div class="flex items-start gap-4">
                    <div class="h-20 w-20 overflow-hidden rounded-lg border border-slate-100"
                      :class="isValidImage(item.img) ? 'bg-slate-50' : ''">
                      <img v-if="isValidImage(item.img)" :src="item.img" :alt="item.name"
                        class="h-full w-full object-cover" />
                      <div v-else
                        class="flex h-full w-full items-center justify-center text-xs font-medium text-slate-400">
                        No Image
                      </div>
                    </div>

                    <div class="flex-1 space-y-2">

                      <p class="text-sm font-semibold leading-snug text-slate-900">
                        <Link v-if="item.url" :href="item.url" class="hover:text-sky-600">{{ item.name }}</Link>
                        <span v-else>{{ item.name }}</span>
                      </p>

                      <div class="flex flex-wrap items-center gap-2 text-[11px] font-semibold text-slate-600">
                        <span v-for="tag in item.tags" :key="tag" class="rounded-sm bg-slate-100 px-2.5 py-1">{{ tag
                        }}</span>
                        <span v-if="state.itemStates?.[groupIndex]?.[itemIndex]?.shipping_method === 'delivery'"
                          class="inline-flex items-center gap-1 rounded-sm bg-blue-50 px-2.5 py-1 text-blue-700">
                          Diantar ke Tempat
                        </span>
                        <span v-else-if="state.itemStates?.[groupIndex]?.[itemIndex]?.shipping_method === 'pickup'"
                          class="inline-flex items-center gap-1 rounded-sm bg-amber-50 px-2.5 py-1 text-amber-700">
                          Ambil di Toko
                        </span>
                      </div>

                      <div class="relative">
                        <div
                          class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 flex items-center justify-center">
                          <span class="flex h-7 w-7 items-center justify-center text-sky-600">
                            <svg width="16" height="16" viewBox="0 0 20 20" fill="none"
                              xmlns="http://www.w3.org/2000/svg">
                              <path opacity="0.4"
                                d="M18.3334 11.6665V14.1665C18.3334 15.5498 17.2167 16.6665 15.8334 16.6665H15C15 15.7498 14.25 14.9998 13.3334 14.9998C12.4167 14.9998 11.6667 15.7498 11.6667 16.6665H8.33335C8.33335 15.7498 7.58335 14.9998 6.66669 14.9998C5.75002 14.9998 5.00002 15.7498 5.00002 16.6665H4.16669C2.78335 16.6665 1.66669 15.5498 1.66669 14.1665V11.6665H10.8334C11.75 11.6665 12.5 10.9165 12.5 9.99984V4.1665H14.0334C14.6334 4.1665 15.1834 4.49151 15.4834 5.00818L16.9083 7.49984H15.8334C15.375 7.49984 15 7.87484 15 8.33317V10.8332C15 11.2915 15.375 11.6665 15.8334 11.6665H18.3334Z"
                                fill="#00A6F4" />
                              <path
                                d="M6.66667 18.3333C7.58714 18.3333 8.33333 17.5871 8.33333 16.6667C8.33333 15.7462 7.58714 15 6.66667 15C5.74619 15 5 15.7462 5 16.6667C5 17.5871 5.74619 18.3333 6.66667 18.3333Z"
                                fill="#00A6F4" />
                              <path
                                d="M13.3334 18.3333C14.2538 18.3333 15 17.5871 15 16.6667C15 15.7462 14.2538 15 13.3334 15C12.4129 15 11.6667 15.7462 11.6667 16.6667C11.6667 17.5871 12.4129 18.3333 13.3334 18.3333Z"
                                fill="#00A6F4" />
                              <path
                                d="M18.3333 10.4417V11.6667H15.8333C15.375 11.6667 15 11.2917 15 10.8333V8.33333C15 7.875 15.375 7.5 15.8333 7.5H16.9083L18.1167 9.61665C18.2583 9.86665 18.3333 10.15 18.3333 10.4417Z"
                                fill="#00A6F4" />
                              <path
                                d="M11.6666 1.6665H4.99996C3.37496 1.6665 2.02497 2.83317 1.73331 4.37484H5.83329C6.17496 4.37484 6.45829 4.65817 6.45829 4.99984C6.45829 5.3415 6.17496 5.62484 5.83329 5.62484H1.66663V6.87484H4.16663C4.50829 6.87484 4.79163 7.15817 4.79163 7.49984C4.79163 7.8415 4.50829 8.12484 4.16663 8.12484H1.66663V9.37484H2.49996C2.84163 9.37484 3.12496 9.65817 3.12496 9.99984C3.12496 10.3415 2.84163 10.6248 2.49996 10.6248H1.66663V11.6665H10.8333C11.75 11.6665 12.5 10.9165 12.5 9.99984V2.49984C12.5 2.0415 12.125 1.6665 11.6666 1.6665Z"
                                fill="#00A6F4" />
                              <path
                                d="M1.73333 4.375H0.833313C0.491646 4.375 0.208313 4.65833 0.208313 5C0.208313 5.34167 0.491646 5.625 0.833313 5.625H1.66665V5C1.66665 4.78333 1.69166 4.575 1.73333 4.375Z"
                                fill="#00A6F4" />
                              <path
                                d="M0.833313 6.875C0.491646 6.875 0.208313 7.15833 0.208313 7.5C0.208313 7.84167 0.491646 8.125 0.833313 8.125H1.66665V6.875H0.833313Z"
                                fill="#00A6F4" />
                              <path
                                d="M0.833313 9.375C0.491646 9.375 0.208313 9.65833 0.208313 10C0.208313 10.3417 0.491646 10.625 0.833313 10.625H1.66665V9.375H0.833313Z"
                                fill="#00A6F4" />
                            </svg>
                          </span>
                        </div>
                        <select :class="[
                          'block w-full appearance-none rounded-sm py-2 pl-14 pr-10 text-sm font-semibold transition focus:outline-none focus:ring-2',
                          state.validationTriggered && !state.itemStates?.[groupIndex]?.[itemIndex]?.shipping_method
                            ? 'border-2 border-red-200 bg-red-50 text-slate-700 focus:border-red-200 focus:ring-red-100'
                            : 'border border-slate-200 bg-slate-50 text-slate-700 focus:border-sky-500 focus:bg-white focus:ring-sky-100'
                        ]" @change="selectShipping(groupIndex, itemIndex, $event.target.value, item.id)">
                          <option value="" :selected="!state.itemStates?.[groupIndex]?.[itemIndex]?.shipping_method">
                            Pilih Metode Pengiriman
                          </option>
                          <option v-if="item.shipping_pickup" value="pickup"
                            :selected="state.itemStates?.[groupIndex]?.[itemIndex]?.shipping_method === 'pickup'">Ambil
                            di
                            Toko</option>
                          <option v-if="item.shipping_delivery" value="delivery"
                            :selected="state.itemStates?.[groupIndex]?.[itemIndex]?.shipping_method === 'delivery'">
                            Diantar
                            ke Tempat</option>
                        </select>
                        <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-500">
                          <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M5 7.5 10 12l5-4.5" stroke-linecap="round" stroke-linejoin="round" />
                          </svg>
                        </div>
                      </div>
                    </div>

                    <div class="flex flex-col items-end gap-3">
                      <p class="text-base font-bold text-slate-900">
                        {{ formatCurrency(item.price * (state.itemStates?.[groupIndex]?.[itemIndex]?.qty ?? item.qty ??
                          1)) }}
                      </p>
                    </div>
                  </div>

                  <div class="pl-[100px]">
                    <div class="space-y-2 border-t border-b border-slate-100 py-3">
                      <button type="button"
                        class="flex w-full items-center justify-between text-sm text-slate-600 hover:text-slate-800"
                        @click="toggleNote(groupIndex, itemIndex)">
                        <div class="flex items-center gap-2">
                          <svg class="nest-icon " width="20" height="20" fill="rgb(var(--NN600,109,117,136))"
                            viewBox="0 0 24 24">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                              d="M11.12 19.23H6.59c-1 0-1.8-.81-1.8-1.8V5.56c0-1 .81-1.8 1.8-1.8h9.14c.99 0 1.8.81 1.8 1.8v4.7c0 .41.34.75.75.75s.75-.34.75-.75v-4.7c0-1.82-1.48-3.3-3.3-3.3H6.59c-1.82 0-3.3 1.48-3.3 3.3v11.86c0 1.82 1.48 3.3 3.3 3.3h4.53c.41 0 .75-.34.75-.75s-.34-.75-.75-.75v.01ZM15.75 5.9h-9.6c-.41 0-.75.34-.75.75s.34.75.75.75h9.6c.41 0 .75-.34.75-.75s-.34-.75-.75-.75Zm-9.6 3.33h9.6c.41 0 .75.34.75.75s-.34.75-.75.75h-9.6c-.41 0-.75-.34-.75-.75s.34-.75.75-.75Zm6.5 3.32h-6.5c-.41 0-.75.34-.75.75s.34.75.75.75h6.5c.41 0 .75-.34.75-.75s-.34-.75-.75-.75Zm7.7-.98 1.4 1.05v.01c.26.2.43.48.48.81.05.32-.03.65-.23.91l-4.88 6.53c-.16.22-.4.38-.67.45l-1.65.43a.977.977 0 0 1-.84-.17.957.957 0 0 1-.39-.76l-.06-1.7c-.01-.28.07-.56.24-.78l4.88-6.53a1.23 1.23 0 0 1 1.72-.25Zm-5.32 8.57.94-.24v-.01l2.64-3.53-.97-.72L15 19.17l.03.97Zm3.51-5.69.97.72 1.13-1.51-.97-.72-1.13 1.51Z">
                            </path>
                          </svg>
                          <span>Kasih Catatan</span>
                        </div>
                        <div class="flex items-center gap-2">
                          <span class="text-xs text-slate-400">{{ (state.itemStates?.[groupIndex]?.[itemIndex]?.note ||
                            '').length }}/{{ noteLimit }}</span>
                          <svg class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="none" stroke="currentColor"
                            stroke-width="1.8">
                            <path d="M7 4.5 12 10l-5 5.5" stroke-linecap="round" stroke-linejoin="round" />
                          </svg>
                        </div>
                      </button>

                      <div v-if="state.itemStates?.[groupIndex]?.[itemIndex]?.noteOpen" class="relative">
                        <textarea rows="3" :maxlength="noteLimit"
                          class="w-full rounded-sm border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 placeholder:font-normal placeholder:text-slate-400 focus:border-sky-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-100"
                          placeholder="Tuliskan catatan untuk Penjual"
                          v-model="state.itemStates[groupIndex][itemIndex].note" :id="`note-${groupIndex}-${itemIndex}`"
                          @input="limitNoteLength(groupIndex, itemIndex)" @keydown="handleNoteKeydown"
                          @blur="saveNote(groupIndex, itemIndex, item.id)" />
                        <span
                          class="pointer-events-none absolute bottom-2 right-3 text-xs font-semibold text-slate-400">
                          {{ (state.itemStates?.[groupIndex]?.[itemIndex]?.note || '').length }}/{{ noteLimit }}
                        </span>
                      </div>

                      <div v-else-if="state.itemStates?.[groupIndex]?.[itemIndex]?.note"
                        class="rounded-md border border-slate-100 bg-slate-50/80 px-4 py-2 text-xs text-slate-500 cursor-pointer transition hover:border-slate-200 hover:bg-slate-50"
                        @click="openNoteEditor(groupIndex, itemIndex)">
                        <p class="whitespace-pre-line text-sm font-semibold text-slate-700">
                          {{ state.itemStates[groupIndex][itemIndex].note }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <aside class="sticky top-32 space-y-3">
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
              <p class="text-lg font-semibold text-slate-900">Ringkasan Belanja</p>

              <div class="mt-4 space-y-4 text-sm text-slate-600">
                <div v-for="(order, idx) in orderSummaries" :key="`order-${idx}`"
                  class="space-y-2 border-b border-slate-100 pb-4 last:border-0 last:pb-0">
                  <div class="flex items-center justify-between">
                    <p class="text-base font-semibold text-slate-800">Pesanan {{ idx + 1 }}</p>
                    <span class="max-w-[60%] text-right text-sm font-semibold text-slate-800">{{ order.vendor }}</span>
                  </div>

                  <div class="flex items-center justify-between">
                    <span class="text-slate-500">{{ order.typeLabel }}</span>
                    <span class="font-semibold text-slate-800">{{ formatCurrency(order.total) }}</span>
                  </div>

                  <div v-if="order.benefit" class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-emerald-600">{{ order.benefit }}</span>
                  </div>

                  <div class="flex items-center justify-between font-semibold text-slate-800">
                    <span>Total Pesanan</span>
                    <span>{{ formatCurrency(order.total) }}</span>
                  </div>
                </div>

                <div
                  class="flex items-center justify-between border-t border-slate-200 pt-3 text-base font-semibold text-slate-800">
                  <span>Total Harga</span>
                  <span class="text-base text-slate-900">{{ formatCurrency(selectedTotal) }}</span>
                </div>
              </div>

              <button type="button"
                class="mt-5 flex w-full items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-semibold shadow-sm transition"
                :class="(!hasSelection || state.paymentProcessing)
                  ? 'bg-slate-200 text-slate-500 cursor-not-allowed'
                  : 'bg-sky-600 text-white hover:bg-sky-700'" :disabled="!hasSelection || state.paymentProcessing"
                @click="proceedToPayment">
                <svg v-if="state.paymentProcessing" class="h-4 w-4 animate-spin text-white" viewBox="0 0 24 24"
                  fill="none" stroke="currentColor" stroke-width="2">
                  <circle class="opacity-25" cx="12" cy="12" r="10" />
                  <path class="opacity-75" d="M4 12a8 8 0 0 1 8-8" stroke-linecap="round" />
                </svg>
                <span>{{ state.paymentProcessing ? 'Loading...' : 'Pilih Pembayaran' }}</span>
              </button>
            </div>
          </aside>
        </div>

        <AddressSelectModal :open="state.addressModalOpen" :addresses="addressOptions" :selected-id="selectedAddressId"
          show-edit-button @update:open="(val) => (state.addressModalOpen = val)" @add="openNewAddress"
          @select="handleAddressSelect" @edit="handleAddressEdit" />
      </section>

      <AddressFormModal :open="state.addressFormOpen" @update:open="handleAddressFormToggle"
        :title="editingAddressId ? 'Ubah Alamat Pengiriman' : 'Tambah Alamat Pengiriman'" :description="editingAddressId
          ? 'Perbarui data alamat pengiriman Anda.'
          : 'Lengkapi data alamat pengiriman Anda untuk melanjutkan pembayaran.'"
        :submit-label="editingAddressId ? 'Simpan Perubahan' : 'Simpan Alamat'" :form="addressForm"
        :initial-region-names="regionInitialNames" @submit="submitAddressForm" />

      <Teleport to="body">
        <div v-if="addressLoading || state.paymentProcessing"
          class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-900/45 px-4">
          <div
            class="flex items-center gap-3 rounded-md bg-white px-5 py-3 text-slate-800 shadow-xl ring-1 ring-slate-200">
            <svg class="h-5 w-5 animate-spin text-sky-600" viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2">
              <circle class="opacity-25" cx="12" cy="12" r="10" />
              <path class="opacity-75" d="M4 12a8 8 0 0 1 8-8" stroke-linecap="round" />
            </svg>
            <span class="text-sm font-semibold">{{ globalLoadingMessage }}</span>
          </div>
        </div>
      </Teleport>

      <Teleport to="body">
        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="-translate-y-2 opacity-0"
          enter-to-class="translate-y-0 opacity-100" leave-active-class="transition duration-150 ease-in"
          leave-from-class="translate-y-0 opacity-100" leave-to-class="-translate-y-2 opacity-0">
          <div v-if="addressSuccessVisible"
            class="fixed top-0 left-0 z-[80] w-full bg-emerald-600 px-4 py-3 text-center text-sm font-semibold text-white shadow-md">
            {{ addressSuccessMessage || 'Alamat berhasil disimpan.' }}
          </div>
        </Transition>
      </Teleport>

      <!-- Notification Toast -->
      <Transition enter-active-class="transition duration-300 ease-out" enter-from-class="transform scale-95 opacity-0"
        enter-to-class="transform scale-100 opacity-100" leave-active-class="transition duration-200 ease-in"
        leave-from-class="transform scale-100 opacity-100" leave-to-class="transform scale-95 opacity-0">
        <div v-if="state.notification.show"
          class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
          <div
            class="flex w-full max-w-md items-center gap-3 rounded-lg border border-red-200 bg-white px-4 py-3 shadow-xl">
            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-red-100">
              <svg class="h-5 w-5 text-red-600" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                  d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                  clip-rule="evenodd" />
              </svg>
            </div>
            <div class="flex-1">
              <p class="text-sm font-semibold text-red-900">Metode Pengiriman Diperlukan</p>
              <p class="mt-0.5 text-sm text-red-700">{{ state.notification.message }}</p>
            </div>
            <button type="button" @click="state.notification.show = false"
              class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full text-red-400 transition hover:bg-red-100 hover:text-red-600">
              <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                  d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                  clip-rule="evenodd" />
              </svg>
            </button>
          </div>
        </div>
      </Transition>
    </main>
  </div>

  <!-- Normal Cart Checkout Mode: Full layout with header/footer -->
  <LandingLayout v-else>

    <Head :title="`Checkout - ${appName}`" />

    <section class="space-y-6">
      <div class="flex items-center gap-2">
        <h1 class="text-3xl font-bold text-slate-900">Checkout</h1>
      </div>

      <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,2.4fr)_minmax(320px,1fr)]">
        <div class="space-y-4">
          <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-base font-semibold text-slate-800">Alamat Pengiriman</p>
            <div class="mt-2 text-sm text-slate-600" v-if="addresses.length">
              <p class="font-semibold text-slate-900">{{ selectedAddress.tag }} - {{ selectedAddress.name }}</p>
              <p class="text-slate-700">{{ selectedAddress.phone }}</p>
              <p class="text-slate-600">{{ selectedAddress.detail }}</p>
            </div>
            <p v-else class="mt-2 text-sm text-slate-500">Belum ada alamat tersimpan.</p>
            <button type="button"
              class="mt-3 inline-flex w-full items-center justify-center rounded-md border border-sky-600 px-4 py-2 text-sm font-semibold text-sky-600 transition hover:bg-sky-50"
              @click="state.addressModalOpen = true">
              Pilih Alamat Lain
            </button>
          </div>

          <div v-if="!groups.length"
            class="rounded-lg border border-dashed border-slate-200 bg-white p-6 text-sm text-slate-600">
            Tidak ada item untuk checkout.
          </div>

          <div v-for="(group, groupIndex) in groups" :key="group.storeId ?? groupIndex"
            class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center gap-3 border-b border-slate-100 px-5 py-4">
              <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100">
                <svg class="h-6 w-6 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="1.5">
                  <circle cx="12" cy="8" r="4" />
                  <path d="M5 21c0-4 3-7 7-7s7 3 7 7" />
                </svg>
              </div>
              <div class="flex-1">
                <p class="text-sm font-semibold text-slate-900">{{ group.vendor }}</p>
                <p class="text-xs text-slate-500">{{ group.location || 'Lokasi tidak tersedia' }}</p>
              </div>
              <span v-if="group.benefit"
                class="inline-flex items-center gap-1 rounded-md bg-sky-50 px-3 py-1 text-[11px] font-semibold text-sky-700">
                {{ group.benefit }}
              </span>
            </div>

            <div class="divide-y divide-slate-100">
              <div v-for="(item, itemIndex) in group.items" :key="item.id ?? itemIndex" class="space-y-3 px-5 py-4">
                <div class="flex items-start gap-4">
                  <div class="h-20 w-20 overflow-hidden rounded-lg border border-slate-100"
                    :class="isValidImage(item.img) ? 'bg-slate-50' : ''">
                    <img v-if="isValidImage(item.img)" :src="item.img" :alt="item.name"
                      class="h-full w-full object-cover" />
                    <div v-else
                      class="flex h-full w-full items-center justify-center text-xs font-medium text-slate-400">
                      No Image
                    </div>
                  </div>

                  <div class="flex-1 space-y-2">

                    <p class="text-sm font-semibold leading-snug text-slate-900">
                      <Link v-if="item.url" :href="item.url" class="hover:text-sky-600">{{ item.name }}</Link>
                      <span v-else>{{ item.name }}</span>
                    </p>

                    <div class="flex flex-wrap items-center gap-2 text-[11px] font-semibold text-slate-600">
                      <span v-for="tag in item.tags" :key="tag" class="rounded-sm bg-slate-100 px-2.5 py-1">{{ tag
                        }}</span>
                      <span v-if="state.itemStates?.[groupIndex]?.[itemIndex]?.shipping_method === 'delivery'"
                        class="inline-flex items-center gap-1 rounded-sm bg-blue-50 px-2.5 py-1 text-blue-700">
                        Diantar ke Tempat
                      </span>
                      <span v-else-if="state.itemStates?.[groupIndex]?.[itemIndex]?.shipping_method === 'pickup'"
                        class="inline-flex items-center gap-1 rounded-sm bg-amber-50 px-2.5 py-1 text-amber-700">
                        Ambil di Toko
                      </span>
                    </div>

                    <div class="relative">
                      <div
                        class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 flex items-center justify-center">
                        <span class="flex h-7 w-7 items-center justify-center text-sky-600">
                          <svg width="16" height="16" viewBox="0 0 20 20" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path opacity="0.4"
                              d="M18.3334 11.6665V14.1665C18.3334 15.5498 17.2167 16.6665 15.8334 16.6665H15C15 15.7498 14.25 14.9998 13.3334 14.9998C12.4167 14.9998 11.6667 15.7498 11.6667 16.6665H8.33335C8.33335 15.7498 7.58335 14.9998 6.66669 14.9998C5.75002 14.9998 5.00002 15.7498 5.00002 16.6665H4.16669C2.78335 16.6665 1.66669 15.5498 1.66669 14.1665V11.6665H10.8334C11.75 11.6665 12.5 10.9165 12.5 9.99984V4.1665H14.0334C14.6334 4.1665 15.1834 4.49151 15.4834 5.00818L16.9083 7.49984H15.8334C15.375 7.49984 15 7.87484 15 8.33317V10.8332C15 11.2915 15.375 11.6665 15.8334 11.6665H18.3334Z"
                              fill="#00A6F4" />
                            <path
                              d="M6.66667 18.3333C7.58714 18.3333 8.33333 17.5871 8.33333 16.6667C8.33333 15.7462 7.58714 15 6.66667 15C5.74619 15 5 15.7462 5 16.6667C5 17.5871 5.74619 18.3333 6.66667 18.3333Z"
                              fill="#00A6F4" />
                            <path
                              d="M13.3334 18.3333C14.2538 18.3333 15 17.5871 15 16.6667C15 15.7462 14.2538 15 13.3334 15C12.4129 15 11.6667 15.7462 11.6667 16.6667C11.6667 17.5871 12.4129 18.3333 13.3334 18.3333Z"
                              fill="#00A6F4" />
                            <path
                              d="M18.3333 10.4417V11.6667H15.8333C15.375 11.6667 15 11.2917 15 10.8333V8.33333C15 7.875 15.375 7.5 15.8333 7.5H16.9083L18.1167 9.61665C18.2583 9.86665 18.3333 10.15 18.3333 10.4417Z"
                              fill="#00A6F4" />
                            <path
                              d="M11.6666 1.6665H4.99996C3.37496 1.6665 2.02497 2.83317 1.73331 4.37484H5.83329C6.17496 4.37484 6.45829 4.65817 6.45829 4.99984C6.45829 5.3415 6.17496 5.62484 5.83329 5.62484H1.66663V6.87484H4.16663C4.50829 6.87484 4.79163 7.15817 4.79163 7.49984C4.79163 7.8415 4.50829 8.12484 4.16663 8.12484H1.66663V9.37484H2.49996C2.84163 9.37484 3.12496 9.65817 3.12496 9.99984C3.12496 10.3415 2.84163 10.6248 2.49996 10.6248H1.66663V11.6665H10.8333C11.75 11.6665 12.5 10.9165 12.5 9.99984V2.49984C12.5 2.0415 12.125 1.6665 11.6666 1.6665Z"
                              fill="#00A6F4" />
                            <path
                              d="M1.73333 4.375H0.833313C0.491646 4.375 0.208313 4.65833 0.208313 5C0.208313 5.34167 0.491646 5.625 0.833313 5.625H1.66665V5C1.66665 4.78333 1.69166 4.575 1.73333 4.375Z"
                              fill="#00A6F4" />
                            <path
                              d="M0.833313 6.875C0.491646 6.875 0.208313 7.15833 0.208313 7.5C0.208313 7.84167 0.491646 8.125 0.833313 8.125H1.66665V6.875H0.833313Z"
                              fill="#00A6F4" />
                            <path
                              d="M0.833313 9.375C0.491646 9.375 0.208313 9.65833 0.208313 10C0.208313 10.3417 0.491646 10.625 0.833313 10.625H1.66665V9.375H0.833313Z"
                              fill="#00A6F4" />
                          </svg>
                        </span>
                      </div>
                      <select :class="[
                        'block w-full appearance-none rounded-sm py-2 pl-14 pr-10 text-sm font-semibold transition focus:outline-none focus:ring-2',
                        state.validationTriggered && !state.itemStates?.[groupIndex]?.[itemIndex]?.shipping_method
                          ? 'border-2 border-red-200 bg-red-50 text-slate-700 focus:border-red-200 focus:ring-red-100'
                          : 'border border-slate-200 bg-slate-50 text-slate-700 focus:border-sky-500 focus:bg-white focus:ring-sky-100'
                      ]" @change="selectShipping(groupIndex, itemIndex, $event.target.value, item.id)">
                        <option value="" :selected="!state.itemStates?.[groupIndex]?.[itemIndex]?.shipping_method">
                          Pilih Metode Pengiriman
                        </option>
                        <option v-if="item.shipping_pickup" value="pickup"
                          :selected="state.itemStates?.[groupIndex]?.[itemIndex]?.shipping_method === 'pickup'">Ambil di
                          Toko</option>
                        <option v-if="item.shipping_delivery" value="delivery"
                          :selected="state.itemStates?.[groupIndex]?.[itemIndex]?.shipping_method === 'delivery'">
                          Diantar
                          ke Tempat</option>
                      </select>
                      <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-500">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                          <path d="M5 7.5 10 12l5-4.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                      </div>
                    </div>
                  </div>

                  <div class="flex flex-col items-end gap-3">
                    <p class="text-base font-bold text-slate-900">
                      {{ formatCurrency(item.price * (state.itemStates?.[groupIndex]?.[itemIndex]?.qty ?? item.qty ??
                        1)) }}
                    </p>
                  </div>
                </div>

                <div class="pl-[100px]">
                  <div class="space-y-2 border-t border-b border-slate-100 py-3">
                    <button type="button"
                      class="flex w-full items-center justify-between text-sm text-slate-600 hover:text-slate-800"
                      @click="toggleNote(groupIndex, itemIndex)">
                      <div class="flex items-center gap-2">
                        <svg class="nest-icon " width="20" height="20" fill="rgb(var(--NN600,109,117,136))"
                          viewBox="0 0 24 24">
                          <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M11.12 19.23H6.59c-1 0-1.8-.81-1.8-1.8V5.56c0-1 .81-1.8 1.8-1.8h9.14c.99 0 1.8.81 1.8 1.8v4.7c0 .41.34.75.75.75s.75-.34.75-.75v-4.7c0-1.82-1.48-3.3-3.3-3.3H6.59c-1.82 0-3.3 1.48-3.3 3.3v11.86c0 1.82 1.48 3.3 3.3 3.3h4.53c.41 0 .75-.34.75-.75s-.34-.75-.75-.75v.01ZM15.75 5.9h-9.6c-.41 0-.75.34-.75.75s.34.75.75.75h9.6c.41 0 .75-.34.75-.75s-.34-.75-.75-.75Zm-9.6 3.33h9.6c.41 0 .75.34.75.75s-.34.75-.75.75h-9.6c-.41 0-.75-.34-.75-.75s.34-.75.75-.75Zm6.5 3.32h-6.5c-.41 0-.75.34-.75.75s.34.75.75.75h6.5c.41 0 .75-.34.75-.75s-.34-.75-.75-.75Zm7.7-.98 1.4 1.05v.01c.26.2.43.48.48.81.05.32-.03.65-.23.91l-4.88 6.53c-.16.22-.4.38-.67.45l-1.65.43a.977.977 0 0 1-.84-.17.957.957 0 0 1-.39-.76l-.06-1.7c-.01-.28.07-.56.24-.78l4.88-6.53a1.23 1.23 0 0 1 1.72-.25Zm-5.32 8.57.94-.24v-.01l2.64-3.53-.97-.72L15 19.17l.03.97Zm3.51-5.69.97.72 1.13-1.51-.97-.72-1.13 1.51Z">
                          </path>
                        </svg>
                        <span>Kasih Catatan</span>
                      </div>
                      <div class="flex items-center gap-2">
                        <span class="text-xs text-slate-400">{{ (state.itemStates?.[groupIndex]?.[itemIndex]?.note ||
                          '').length }}/{{ noteLimit }}</span>
                        <svg class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="none" stroke="currentColor"
                          stroke-width="1.8">
                          <path d="M7 4.5 12 10l-5 5.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                      </div>
                    </button>

                    <div v-if="state.itemStates?.[groupIndex]?.[itemIndex]?.noteOpen" class="relative">
                      <textarea rows="3" :maxlength="noteLimit"
                        class="w-full rounded-sm border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 placeholder:font-normal placeholder:text-slate-400 focus:border-sky-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-100"
                        placeholder="Tuliskan catatan untuk Penjual"
                        v-model="state.itemStates[groupIndex][itemIndex].note" :id="`note-${groupIndex}-${itemIndex}`"
                        @input="limitNoteLength(groupIndex, itemIndex)" @keydown="handleNoteKeydown"
                        @blur="saveNote(groupIndex, itemIndex, item.id)" />
                      <span class="pointer-events-none absolute bottom-2 right-3 text-xs font-semibold text-slate-400">
                        {{ (state.itemStates?.[groupIndex]?.[itemIndex]?.note || '').length }}/{{ noteLimit }}
                      </span>
                    </div>

                    <div v-else-if="state.itemStates?.[groupIndex]?.[itemIndex]?.note"
                      class="rounded-md border border-slate-100 bg-slate-50/80 px-4 py-2 text-xs text-slate-500 cursor-pointer transition hover:border-slate-200 hover:bg-slate-50"
                      @click="openNoteEditor(groupIndex, itemIndex)">
                      <p class="whitespace-pre-line text-sm font-semibold text-slate-700">
                        {{ state.itemStates[groupIndex][itemIndex].note }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <aside class="sticky top-32 space-y-3">
          <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-lg font-semibold text-slate-900">Ringkasan Belanja</p>

            <div class="mt-4 space-y-4 text-sm text-slate-600">
              <div v-for="(order, idx) in orderSummaries" :key="`order-${idx}`"
                class="space-y-2 border-b border-slate-100 pb-4 last:border-0 last:pb-0">
                <div class="flex items-center justify-between">
                  <p class="text-base font-semibold text-slate-800">Pesanan {{ idx + 1 }}</p>
                  <span class="max-w-[60%] text-right text-sm font-semibold text-slate-800">{{ order.vendor }}</span>
                </div>

                <div class="flex items-center justify-between">
                  <span class="text-slate-500">{{ order.typeLabel }}</span>
                  <span class="font-semibold text-slate-800">{{ formatCurrency(order.total) }}</span>
                </div>

                <div v-if="order.benefit" class="flex items-center justify-between">
                  <span class="text-xs font-semibold text-emerald-600">{{ order.benefit }}</span>
                </div>

                <div class="flex items-center justify-between font-semibold text-slate-800">
                  <span>Total Pesanan</span>
                  <span>{{ formatCurrency(order.total) }}</span>
                </div>
              </div>

              <div
                class="flex items-center justify-between border-t border-slate-200 pt-3 text-base font-semibold text-slate-800">
                <span>Total Harga</span>
                <span class="text-base text-slate-900">{{ formatCurrency(selectedTotal) }}</span>
              </div>
            </div>

            <button type="button"
              class="mt-5 flex w-full items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-semibold shadow-sm transition"
              :class="(!hasSelection || state.paymentProcessing)
                ? 'bg-slate-200 text-slate-500 cursor-not-allowed'
                : 'bg-sky-600 text-white hover:bg-sky-700'" :disabled="!hasSelection || state.paymentProcessing"
              @click="proceedToPayment">
              <svg v-if="state.paymentProcessing" class="h-4 w-4 animate-spin text-white" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2">
                <circle class="opacity-25" cx="12" cy="12" r="10" />
                <path class="opacity-75" d="M4 12a8 8 0 0 1 8-8" stroke-linecap="round" />
              </svg>
              <span>{{ state.paymentProcessing ? 'Loading...' : 'Pilih Pembayaran' }}</span>
            </button>
          </div>
        </aside>
      </div>

      <AddressSelectModal :open="state.addressModalOpen" :addresses="addressOptions" :selected-id="selectedAddressId"
        show-edit-button @update:open="(val) => (state.addressModalOpen = val)" @add="openNewAddress"
        @select="handleAddressSelect" @edit="handleAddressEdit" />
    </section>

    <AddressFormModal :open="state.addressFormOpen" @update:open="handleAddressFormToggle"
      :title="editingAddressId ? 'Ubah Alamat Pengiriman' : 'Tambah Alamat Pengiriman'" :description="editingAddressId
        ? 'Perbarui data alamat pengiriman Anda.'
        : 'Lengkapi data alamat pengiriman Anda untuk melanjutkan pembayaran.'"
      :submit-label="editingAddressId ? 'Simpan Perubahan' : 'Simpan Alamat'" :form="addressForm"
      :initial-region-names="regionInitialNames" @submit="submitAddressForm" />

    <Teleport to="body">
      <div v-if="addressLoading || state.paymentProcessing"
        class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-900/45 px-4">
        <div
          class="flex items-center gap-3 rounded-md bg-white px-5 py-3 text-slate-800 shadow-xl ring-1 ring-slate-200">
          <svg class="h-5 w-5 animate-spin text-sky-600" viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2">
            <circle class="opacity-25" cx="12" cy="12" r="10" />
            <path class="opacity-75" d="M4 12a8 8 0 0 1 8-8" stroke-linecap="round" />
          </svg>
          <span class="text-sm font-semibold">{{ globalLoadingMessage }}</span>
        </div>
      </div>
    </Teleport>

    <Teleport to="body">
      <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="-translate-y-2 opacity-0"
        enter-to-class="translate-y-0 opacity-100" leave-active-class="transition duration-150 ease-in"
        leave-from-class="translate-y-0 opacity-100" leave-to-class="-translate-y-2 opacity-0">
        <div v-if="addressSuccessVisible"
          class="fixed top-0 left-0 z-[80] w-full bg-emerald-600 px-4 py-3 text-center text-sm font-semibold text-white shadow-md">
          {{ addressSuccessMessage || 'Alamat berhasil disimpan.' }}
        </div>
      </Transition>
    </Teleport>

    <!-- Notification Toast -->
    <Transition enter-active-class="transition duration-300 ease-out" enter-from-class="transform scale-95 opacity-0"
      enter-to-class="transform scale-100 opacity-100" leave-active-class="transition duration-200 ease-in"
      leave-from-class="transform scale-100 opacity-100" leave-to-class="transform scale-95 opacity-0">
      <div v-if="state.notification.show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
        <div
          class="flex w-full max-w-md items-center gap-3 rounded-lg border border-red-200 bg-white px-4 py-3 shadow-xl">
          <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-red-100">
            <svg class="h-5 w-5 text-red-600" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd"
                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                clip-rule="evenodd" />
            </svg>
          </div>
          <div class="flex-1">
            <p class="text-sm font-semibold text-red-900">Metode Pengiriman Diperlukan</p>
            <p class="mt-0.5 text-sm text-red-700">{{ state.notification.message }}</p>
          </div>
          <button type="button" @click="state.notification.show = false"
            class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full text-red-400 transition hover:bg-red-100 hover:text-red-600">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd"
                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                clip-rule="evenodd" />
            </svg>
          </button>
        </div>
      </div>
    </Transition>
  </LandingLayout>
</template>
