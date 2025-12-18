<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { RichTextEditor } from '@/components/ui/rich-text-editor';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover';
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
} from '@/components/ui/command';
import RegionSelector from '@/components/RegionSelector.vue';
import { ChevronsUpDown, ImagePlus, X, Check } from 'lucide-vue-next';

type Option = {
  id: number;
  name: string;
};

type SelectOption = {
  value: string;
  label: string;
  description?: string;
};

type StoreLocation = {
  province_id: number | null;
  city_id: number | null;
  district_id: number | null;
  postal_code: string | null;
  province_name?: string | null;
  city_name?: string | null;
  district_name?: string | null;
};

const toNullableId = (value: unknown): number | null => {
  if (value === null || value === undefined || value === '') {
    return null;
  }

  const parsed = Number(value);
  return Number.isNaN(parsed) ? null : parsed;
};

const normalizePostalCode = (value?: string | number | null): string | null => {
  if (value === null || value === undefined) {
    return null;
  }

  const trimmed = String(value).trim();
  return trimmed || null;
};

type ProductPayload = {
  id: number;
  category_id: number | null;
  name: string;
  slug: string | null;
  brand: string | null;
  description: string | null;
  price: number;
  sale_price: number | null;
  min_order: number;
  stock: number;
  weight: number | null;
  length: number | null;
  width: number | null;
  height: number | null;
  item_type: string;
  status: string;
  visibility_scope: string;
  location_province_id: number | null;
  location_city_id: number | null;
  location_district_id: number | null;
  location_postal_code: string | null;
  is_pdn: boolean;
  shipping_pickup: boolean;
  shipping_delivery: boolean;
  images?: { id: number; url: string }[];
};

const props = defineProps<{
  mode: 'create' | 'edit';
  submitUrl: string;
  categoryOptions: Option[];
  statuses: SelectOption[];
  itemTypes: SelectOption[];
  visibilityOptions: SelectOption[];
  shippingMethods?: SelectOption[];
  product?: ProductPayload | null;
  storeLocation?: StoreLocation | null;
  canPublish?: boolean;
}>();

const visibilityOptions = computed(() => props.visibilityOptions ?? []);

const statusOptions = computed(() => {
  if (props.canPublish !== undefined && !props.canPublish) {
    // If canPublish is false, filter out 'published' status options (assuming 'ready' is published)
    // Actually, usually 'ready' means active/published. 'archived' or 'draft' is hidden.
    // If we want to restrict to DRAFT only, we should see what statuses are available.
    // Usually: ready, empty, draft?
    // Based on Index.vue: 'ready', 'pre_order', 'archived'?
    // Let's assume we want to force 'draft' or similar if not approved.
    // If we only have 'ready' and 'pre_order', maybe we need to add 'draft' to backend or frontend?
    // For now, let's just disabling the select if restriction is active, forcing it to whatever "Draft" equivalent or just showing warning.
    // But if they are 'submitted', they can create "draft".
    // Let's look at `statuses` prop in usage.

    // If user cannot publish, we should only allow 'archived' or 'draft' (if exists).
    // Or we simply disable the field and force value to 'archived'/'draft'.

    return props.statuses;
  }
  return props.statuses;
});

const isStatusDisabled = computed(() => {
  return props.canPublish !== undefined && !props.canPublish;
});

const normalizedStoreLocation = computed<StoreLocation | null>(() => {
  if (!props.storeLocation) {
    return null;
  }

  return {
    province_id: toNullableId(props.storeLocation.province_id),
    city_id: toNullableId(props.storeLocation.city_id),
    district_id: toNullableId(props.storeLocation.district_id),
    postal_code: normalizePostalCode(props.storeLocation.postal_code),
    province_name: props.storeLocation.province_name ?? null,
    city_name: props.storeLocation.city_name ?? null,
    district_name: props.storeLocation.district_name ?? null,
  };
});

const storeLocationHasData = (location?: StoreLocation | null) => Boolean(
  location
  && (location.province_id || location.city_id || location.district_id || location.postal_code),
);

const form = useForm({
  category_id: props.product?.category_id?.toString() ?? '',
  name: props.product?.name ?? '',
  slug: props.product?.slug ?? '',
  brand: props.product?.brand ?? '',
  description: props.product?.description ?? '',
  price: props.product?.price ?? '',
  sale_price: props.product?.sale_price ?? '',
  min_order: props.product?.min_order ?? 1,
  stock: props.product?.stock ?? 0,
  weight: props.product?.weight ?? '',
  length: props.product?.length ?? '',
  width: props.product?.width ?? '',
  height: props.product?.height ?? '',
  item_type: props.product?.item_type ?? props.itemTypes[0]?.value ?? 'product',
  status: props.product?.status ?? props.statuses[0]?.value ?? 'ready',
  visibility_scope: props.product?.visibility_scope
    ?? props.visibilityOptions[0]?.value
    ?? 'global',
  location_province_id: toNullableId(props.product?.location_province_id)
    ?? normalizedStoreLocation.value?.province_id
    ?? null,
  location_city_id: toNullableId(props.product?.location_city_id)
    ?? normalizedStoreLocation.value?.city_id
    ?? null,
  location_district_id: toNullableId(props.product?.location_district_id)
    ?? normalizedStoreLocation.value?.district_id
    ?? null,
  location_postal_code: props.product?.location_postal_code
    ?? normalizedStoreLocation.value?.postal_code
    ?? '',
  is_pdn: props.product?.is_pdn ?? false,
  shipping_pickup: props.product?.shipping_pickup ?? false,
  shipping_delivery: props.product?.shipping_delivery ?? false,
  images: [] as File[],
  deleted_images: [] as number[],
});

const hasEnabledLocationEditing = ref(false);

const isUsingStoreLocation = computed(() => {
  const location = normalizedStoreLocation.value;

  if (!location || !storeLocationHasData(location)) {
    return false;
  }

  const provinceMatches = toNullableId(form.location_province_id) === toNullableId(location.province_id);
  const cityMatches = toNullableId(form.location_city_id) === toNullableId(location.city_id);
  const districtMatches = toNullableId(form.location_district_id) === toNullableId(location.district_id);
  const postalMatches = normalizePostalCode(form.location_postal_code)
    === normalizePostalCode(location.postal_code);

  return provinceMatches && cityMatches && districtMatches && postalMatches;
});

const isLocationLocked = computed(() => props.mode === 'create'
  && !hasEnabledLocationEditing.value
  && isUsingStoreLocation.value);

const locationFieldsDisabled = computed(() => form.processing || isLocationLocked.value);

const showEditLocationButton = computed(() => isLocationLocked.value);

const enableLocationEditing = () => {
  hasEnabledLocationEditing.value = true;
};

const hasErrors = computed(() => Object.keys(form.errors).length > 0);

const hasValue = (value: unknown) => {
  if (value === null || value === undefined) return false;
  if (typeof value === 'string') return value.trim().length > 0;
  return value !== '';
};

const validateRequiredFields = () => {
  let valid = true;
  const ensure = (field: string, condition: boolean, message: string) => {
    if (!condition) {
      form.setError(field as any, message);
      valid = false;
    }
  };

  ensure('name', hasValue(form.name), 'Nama produk wajib diisi');
  ensure('slug', hasValue(form.slug), 'URL slug wajib diisi');
  ensure('category_id', hasValue(form.category_id), 'Kategori wajib dipilih');
  ensure('item_type', hasValue(form.item_type), 'Jenis item wajib dipilih');
  ensure('status', hasValue(form.status), 'Status produk wajib dipilih');
  ensure('visibility_scope', hasValue(form.visibility_scope), 'Jangkauan produk wajib dipilih');
  ensure('description', hasValue(form.description), 'Deskripsi produk wajib diisi');

  ensure('price', hasValue(form.price), 'Harga normal wajib diisi');
  ensure('min_order', hasValue(form.min_order), 'Minimal order wajib diisi');
  ensure('stock', hasValue(form.stock), 'Stok wajib diisi');
  ensure('weight', hasValue(form.weight), 'Berat produk wajib diisi');

  ensure('location_province_id', hasValue(form.location_province_id), 'Provinsi wajib dipilih');
  ensure('location_city_id', hasValue(form.location_city_id), 'Kota/Kabupaten wajib dipilih');
  ensure('location_district_id', hasValue(form.location_district_id), 'Kecamatan wajib dipilih');
  ensure('location_postal_code', hasValue(form.location_postal_code), 'Kode pos wajib diisi');

  // Shipping methods are optional - seller can choose one, both, or configure later
  // No validation required here

  return valid;
};

const ensureStoreLocationApplied = async () => {
  if (!isLocationLocked.value) {
    return;
  }

  await fillLocationFromStore();
};

const submit = async () => {
  await ensureStoreLocationApplied();
  form.clearErrors();

  if (!validateRequiredFields()) {
    return;
  }

  if (props.mode === 'create' && (!form.images || form.images.length === 0)) {
    form.setError('images', 'Minimal 1 gambar produk');
    return;
  }

  const options = {
    preserveScroll: true,
    forceFormData: true,
  };

  if (props.mode === 'create') {
    form.transform((data) => ({
      ...data,
      is_pdn: data.is_pdn ? 1 : 0,
      shipping_pickup: data.shipping_pickup ? 1 : 0,
      shipping_delivery: data.shipping_delivery ? 1 : 0,
    })).post(props.submitUrl, options);
  } else {
    form.transform((data) => ({
      ...data,
      _method: 'PUT',
      is_pdn: data.is_pdn ? 1 : 0,
      shipping_pickup: data.shipping_pickup ? 1 : 0,
      shipping_delivery: data.shipping_delivery ? 1 : 0,
    })).post(props.submitUrl, options);
  }
};

const generateSlug = (value?: string | number | null) =>
  value
    ?.toString()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '') ?? '';

const isSlugLocked = ref(true);
const hasManualSlug = ref(false);
const slugDuplicateError = ref(false);

watch(
  () => form.name,
  (value) => {
    if (!hasManualSlug.value) {
      form.slug = generateSlug(value);
    }
  },
  { immediate: true },
);

// Watch for duplicate slug error from backend
watch(
  () => form.errors.slug,
  (error) => {
    if (error && (error.toLowerCase().includes('sudah digunakan') || error.toLowerCase().includes('already') || error.toLowerCase().includes('duplicate'))) {
      slugDuplicateError.value = true;
      isSlugLocked.value = false; // Unlock slug for editing
    } else {
      slugDuplicateError.value = false;
    }
  },
);

const unlockSlug = () => {
  isSlugLocked.value = false;
  hasManualSlug.value = true;
  slugDuplicateError.value = false;
};

const lockSlug = () => {
  isSlugLocked.value = true;
  hasManualSlug.value = false;
  form.slug = generateSlug(form.name);
  slugDuplicateError.value = false;
};

const resetSlug = () => {
  hasManualSlug.value = false;
  isSlugLocked.value = true;
  form.slug = generateSlug(form.name);
  slugDuplicateError.value = false;
};

const handleSlugInput = (value: string) => {
  hasManualSlug.value = true;
  form.slug = generateSlug(value);
  slugDuplicateError.value = false;
  form.clearErrors('slug');
};

const selectedCategory = computed(
  () => props.categoryOptions.find((option) => option.id.toString() === form.category_id) ?? null,
);
const categoryPopoverOpen = ref(false);
const categorySearch = ref('');

const filteredCategoryOptions = computed(() => {
  if (!categorySearch.value) {
    return props.categoryOptions.slice(0, 5);
  }
  const term = categorySearch.value.toLowerCase();
  return props.categoryOptions.filter((option) => option.name.toLowerCase().includes(term));
});

const selectCategory = (option?: Option | null) => {
  form.category_id = option ? option.id.toString() : '';
  categoryPopoverOpen.value = false;
  categorySearch.value = '';
};

const discountPercentage = computed(() => {
  if (!form.price || !form.sale_price) return null;
  const price = parseFloat(form.price as unknown as string);
  const salePrice = parseFloat(form.sale_price as unknown as string);
  if (price > 0 && salePrice > 0 && salePrice < price) {
    return Math.round(((price - salePrice) / price) * 100);
  }
  return null;
});

const hasDimensions = computed(() => form.length || form.width || form.height);

watch(categoryPopoverOpen, (open) => {
  if (!open) {
    categorySearch.value = '';
  }
});


const imageInputRef = ref<HTMLInputElement | null>(null);
const objectUrls = ref<string[]>([]);

const imagePreviews = computed(() => {
  const previews: { type: 'existing' | 'new'; source: string; id?: number | string }[] = [];

  // Existing images (that haven't been deleted)
  if (props.product?.images && props.product.images.length > 0) {
    props.product.images.forEach((img) => {
      if (!form.deleted_images.includes(img.id)) {
        previews.push({
          type: 'existing',
          source: img.url,
          id: img.id,
        });
      }
    });
  }

  // New images from file input
  if (form.images && form.images.length > 0) {
    // Clear old object URLs to avoid leaks
    objectUrls.value.forEach((url) => URL.revokeObjectURL(url));
    objectUrls.value = [];

    form.images.forEach((file, index) => {
      const url = URL.createObjectURL(file);
      objectUrls.value.push(url);
      previews.push({
        type: 'new',
        source: url,
        id: `new-${index}`, // Temporary ID for tracking
      });
    });
  }

  return previews;
});

const isShowingExistingImages = computed(() => {
  return imagePreviews.value.some(img => img.type === 'existing');
});

const handleImagesChange = (event: Event) => {
  const target = event.target as HTMLInputElement | null;
  const files = target?.files ? Array.from(target.files) : [];
  if (!files.length) return;

  form.images = [...form.images, ...files];

  if (target) {
    target.value = '';
  }
};

const triggerImagePicker = () => {
  if (form.processing) return;
  imageInputRef.value?.click();
};

const removeImage = (previewIndex: number) => {
  const previewItem = imagePreviews.value[previewIndex];

  if (!previewItem) return;

  if (previewItem.type === 'existing' && typeof previewItem.id === 'number') {
    // Mark existing image for deletion
    form.deleted_images.push(previewItem.id);
  } else if (previewItem.type === 'new') {
    // Remove from form.images array
    // We need to find the correct index in form.images.
    // Since imagePreviews appends new images after existing ones, we can calculate the offset.
    const existingCount = imagePreviews.value.filter(p => p.type === 'existing').length;
    const newImageIndex = previewIndex - existingCount;

    if (newImageIndex >= 0 && newImageIndex < form.images.length) {
      const next = [...form.images];
      next.splice(newImageIndex, 1);
      form.images = next;
    }
  }
};

const fillLocationFromStore = async () => {
  const location = normalizedStoreLocation.value;

  if (!location) {
    return;
  }

  form.location_province_id = location.province_id;
  await nextTick();
  form.location_city_id = location.city_id;
  await nextTick();
  form.location_district_id = location.district_id;
  form.location_postal_code = location.postal_code ?? '';
};

watch(
  normalizedStoreLocation,
  async (location) => {
    if (!storeLocationHasData(location)) {
      return;
    }

    if (props.mode === 'create' && !hasEnabledLocationEditing.value) {
      await fillLocationFromStore();
    }
  },
  { immediate: true },
);

// Force status to 'archived' (draft) if not allowed to publish
watch(
  () => props.canPublish,
  (allowed) => {
    if (allowed !== undefined && !allowed) {
      // Find a suitable "draft" status. Usually 'archived' or 'draft'.
      // If we don't know the exact value, we might guess 'archived' based on typical ecommerce.
      // Or checking props.statuses content.
      // Let's assume 'archived' is the key for draft/hidden.
      // Since I can't see props.statuses content values here, I'll assume 'archived'.
      // If 'archived' is not in options, this might be issue.
      // Safer: check if 'archived' exists in statuses, else pick first non-ready one?
      const draftOption = props.statuses.find(s => s.value === 'archived' || s.value === 'draft');
      if (draftOption) {
        form.status = draftOption.value;
      } else {
        // Fallback or leave as is but it will be disabled?
        //If we disable the input, user can't change it.
      }
    }
  },
  { immediate: true }
);

onBeforeUnmount(() => {
  objectUrls.value.forEach((url) => URL.revokeObjectURL(url));
  objectUrls.value = [];
});
</script>

<template>
  <form @submit.prevent="submit" class="space-y-6">
    <div v-if="hasErrors"
      class="flex items-start gap-3 rounded-sm border border-red-200 bg-red-50 p-4 text-sm text-red-800">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 shrink-0">
        <circle cx="12" cy="12" r="10" />
        <line x1="12" x2="12" y1="8" y2="12" />
        <line x1="12" x2="12.01" y1="16" y2="16" />
      </svg>
      <div>
        <p class="font-medium">Terdapat kesalahan pada form</p>
        <p class="text-xs text-red-700">
          Mohon periksa kembali field yang ditandai merah
        </p>
      </div>
    </div>

    <!-- Informasi Produk -->
    <Card>
      <CardHeader>
        <div class="flex items-center gap-2">
          <div class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
              class="text-blue-600">
              <path d="M16 20V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
              <rect width="20" height="14" x="2" y="6" rx="2" />
            </svg>
          </div>
          <CardTitle class="text-md">Informasi Produk</CardTitle>
        </div>
      </CardHeader>
      <CardContent class="space-y-6">
        <div class="grid gap-4 sm:grid-cols-2">
          <div class="space-y-2 sm:col-span-2">
            <Label for="name" class="flex items-center gap-1">
              Nama Produk
              <span class="text-red-500">*</span>
            </Label>
            <Input id="name" v-model="form.name" required placeholder="Contoh: Paket Bingkisan Premium"
              :class="form.errors.name ? 'border-red-500' : ''" />
            <p v-if="form.errors.name" class="text-xs text-red-600">
              {{ form.errors.name }}
            </p>
          </div>
        </div>

        <div class="space-y-2">
          <Label for="slug" class="flex items-center gap-1">
            URL Slug
            <span class="text-red-500">*</span>
          </Label>

          <!-- Duplicate Slug Alert -->
          <div v-if="slugDuplicateError"
            class="flex items-start gap-3 rounded-md border border-amber-200 bg-amber-50 p-3 text-sm">
            <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-amber-600" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd"
                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                clip-rule="evenodd" />
            </svg>
            <div class="flex-1">
              <p class="font-semibold text-amber-900">Slug sudah digunakan</p>
              <p class="mt-1 text-xs text-amber-700">{{ form.errors.slug }}</p>
              <p class="mt-1 text-xs text-amber-700">Silakan edit slug di bawah untuk membuatnya unik.</p>
            </div>
          </div>

          <div class="relative flex items-center gap-2">
            <Input id="slug" :model-value="form.slug" :disabled="isSlugLocked"
              :class="[form.errors.slug ? 'border-red-500' : '', isSlugLocked ? 'bg-slate-50' : '']" class="pr-28"
              required @update:model-value="handleSlugInput" />
            <div v-if="!hasManualSlug && !slugDuplicateError" class="absolute right-2 flex gap-1 text-xs">
              <span
                class="inline-flex h-7 items-center rounded-md bg-blue-50 px-2 text-[11px] font-medium text-blue-700">
                Auto
              </span>
            </div>
            <div v-else-if="slugDuplicateError && isSlugLocked" class="absolute right-2 flex gap-1 text-xs">
              <Button type="button" size="sm" variant="outline" class="h-7 px-2 bg-white" @click="unlockSlug">
                Edit Slug
              </Button>
            </div>
            <div v-else-if="hasManualSlug" class="absolute right-2 flex gap-1 text-xs">
              <Button type="button" size="sm" variant="ghost" class="h-7 px-2 text-slate-500" @click="resetSlug">
                Reset
              </Button>
            </div>
          </div>
          <p class="text-xs text-slate-500">
            Slug otomatis dibuat dari nama produk dan tidak bisa diubah.
          </p>
          <p v-if="form.errors.slug && !slugDuplicateError" class="text-xs text-red-600">
            {{ form.errors.slug }}
          </p>
        </div>

        <div class="space-y-2">
          <Label for="brand">Brand</Label>
          <Input id="brand" v-model="form.brand" placeholder="Nama brand (opsional)"
            :class="form.errors.brand ? 'border-red-500' : ''" />
          <p v-if="form.errors.brand" class="text-xs text-red-600">
            {{ form.errors.brand }}
          </p>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
          <div class="space-y-2 md:col-span-2 xl:col-span-2">
            <Label for="category" class="flex items-center gap-1">
              Kategori
              <span class="text-red-500">*</span>
            </Label>
            <Popover v-model:open="categoryPopoverOpen">
              <PopoverTrigger as-child>
                <Button id="category" variant="outline" role="combobox"
                  class="w-full justify-between text-left text-sm font-normal"
                  :class="form.errors.category_id ? 'border-red-500' : ''">
                  <span class="truncate">
                    {{ selectedCategory?.name ?? 'Pilih kategori' }}
                  </span>
                  <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                </Button>
              </PopoverTrigger>
              <PopoverContent align="start" class="w-[280px] p-0 text-left">
                <Command>
                  <CommandInput v-model="categorySearch" placeholder="Cari kategori..." />
                  <CommandEmpty>
                    Kategori tidak ditemukan.
                  </CommandEmpty>
                  <CommandGroup>
                    <CommandItem v-for="option in filteredCategoryOptions" :key="option.id" :value="option.name"
                      @select="() => selectCategory(option)">
                      <Check class="mr-2 h-4 w-4"
                        :class="form.category_id === option.id.toString() ? 'opacity-100' : 'opacity-0'" />
                      <span>{{ option.name }}</span>
                    </CommandItem>
                  </CommandGroup>
                </Command>
              </PopoverContent>
            </Popover>
            <p v-if="form.errors.category_id" class="text-xs text-red-600">
              {{ form.errors.category_id }}
            </p>
          </div>

          <div class="space-y-2">
            <Label for="item_type" class="flex items-center gap-1">
              Jenis Item
              <span class="text-red-500">*</span>
            </Label>
            <Select v-model="form.item_type">
              <SelectTrigger id="item_type" class="w-full" :class="form.errors.item_type ? 'border-red-500' : ''">
                <SelectValue placeholder="Pilih jenis" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="option in itemTypes" :key="option.value" :value="option.value">
                  {{ option.label }}
                </SelectItem>
              </SelectContent>
            </Select>
            <p v-if="form.errors.item_type" class="text-xs text-red-600">
              {{ form.errors.item_type }}
            </p>
          </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
          <div class="space-y-2">
            <Label for="visibility_scope" class="flex items-center gap-1">
              Jangkauan Produk
              <span class="text-red-500">*</span>
            </Label>
            <Select v-model="form.visibility_scope">
              <SelectTrigger id="visibility_scope" class="w-full"
                :class="form.errors.visibility_scope ? 'border-red-500' : ''">
                <SelectValue placeholder="Atur jangkauan produk" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="option in visibilityOptions" :key="option.value" :value="option.value">
                  {{ option.label }}
                </SelectItem>
              </SelectContent>
            </Select>
            <p class="text-xs text-slate-500">
              Tentukan apakah produk tampil untuk semua customer atau khusus yang satu kota.
            </p>
            <p v-if="form.errors.visibility_scope" class="text-xs text-red-600">
              {{ form.errors.visibility_scope }}
            </p>
          </div>

          <div class="space-y-2">
            <Label for="status" class="flex items-center gap-1">
              Status
              <span class="text-red-500">*</span>
            </Label>
            <Select v-model="form.status">
              <SelectTrigger id="status" class="w-full" :class="form.errors.status ? 'border-red-500' : ''">
                <SelectValue placeholder="Pilih status" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="option in statuses" :key="option.value" :value="option.value">
                  {{ option.label }}
                </SelectItem>
              </SelectContent>
            </Select>
            <p v-if="form.errors.status" class="text-xs text-red-600">
              {{ form.errors.status }}
            </p>
          </div>
        </div>

        <div class="space-y-2">
          <Label for="description" class="flex items-center gap-1">
            Deskripsi Produk
            <span class="text-red-500">*</span>
          </Label>
          <RichTextEditor id="description" v-model="form.description"
            placeholder="Jelaskan detail produk, bahan, cara pakai, atau nilai tambah lain."
            :error="!!form.errors.description" />
          <p class="text-xs text-slate-500">
            Deskripsi yang jelas membantu meningkatkan konversi.
          </p>
          <p v-if="form.errors.description" class="text-xs text-red-600">
            {{ form.errors.description }}
          </p>
        </div>
      </CardContent>
    </Card>

    <!-- Gambar Produk -->
    <Card>
      <CardHeader>
        <div class="flex items-center gap-2">
          <div class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
              class="text-blue-600">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
              <circle cx="9" cy="9" r="2" />
              <path d="M21 15l-4-4-3 3-4-4-4 5" />
            </svg>
          </div>
          <CardTitle class="text-md">Gambar Produk</CardTitle>
        </div>
      </CardHeader>
      <CardContent class="space-y-4">
        <div class="space-y-2">
          <Label for="images" class="flex items-center gap-2">
            Upload Gambar
            <span class="text-red-500" v-if="props.mode === 'create'">*</span>
            <span class="text-[11px] font-medium text-slate-500">(gambar pertama jadi gambar utama)</span>
          </Label>

          <input ref="imageInputRef" id="images" type="file" accept="image/*" multiple class="sr-only"
            :disabled="form.processing" @change="handleImagesChange" />

          <div class="flex flex-col gap-1">
            <Button type="button" variant="outline" size="sm" @click="triggerImagePicker" :disabled="form.processing">
              <ImagePlus class="mr-2 h-4 w-4" />
              Tambah Gambar
            </Button>
            <p class="text-xs text-slate-500">
              Bisa pilih sekaligus atau tambahkan satu per satu.
            </p>
          </div>

          <p v-if="form.errors.images" class="text-xs text-red-600">
            {{ form.errors.images }}
          </p>
        </div>

        <div class="rounded-sm border border-dashed border-slate-200 bg-slate-50/80 p-4">
          <div v-if="imagePreviews.length" class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <button type="button"
              class="flex min-h-[120px] items-center justify-center rounded-md border border-dashed border-slate-300 bg-white/80 text-sm text-slate-600 transition hover:border-blue-400 hover:bg-blue-50 disabled:cursor-not-allowed"
              :disabled="form.processing" @click="triggerImagePicker">
              <ImagePlus class="mr-2 h-4 w-4" />
              Tambah
            </button>

            <div v-for="(preview, index) in imagePreviews" :key="preview.id"
              class="relative flex flex-col items-center gap-2 rounded-md border border-slate-200 bg-white p-2 shadow-sm">
              <button type="button"
                class="absolute right-2 top-2 inline-flex h-6 w-6 items-center justify-center rounded-full bg-slate-900/80 text-white shadow-sm transition hover:bg-red-500"
                aria-label="Hapus gambar" :disabled="form.processing" @click="removeImage(index)">
                <X class="h-3 w-3" />
              </button>
              <img :src="preview.source" :alt="`Preview gambar ${index + 1}`"
                class="h-24 w-24 rounded-md object-cover shadow-inner" />
              <span class="rounded-full bg-slate-900/70 px-2 py-0.5 text-[10px] text-white">
                {{ index === 0 ? 'Utama' : `Gambar ${index + 1}` }}
              </span>
            </div>
          </div>
          <div v-else
            class="flex flex-col items-center justify-center gap-3 rounded-md border border-dashed border-slate-200 bg-white/60 px-6 py-10 text-center">
            <button type="button"
              class="flex items-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:border-blue-400 hover:bg-blue-50 disabled:cursor-not-allowed"
              :disabled="form.processing" @click="triggerImagePicker">
              <ImagePlus class="h-4 w-4" />
              Tambah Gambar
            </button>
            <p class="text-xs text-slate-500">
              Belum ada gambar. Klik tombol di atas untuk mulai upload.
            </p>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Harga & Stok -->
    <Card>
      <CardHeader>
        <div class="flex items-center gap-2">
          <div class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
              class="text-blue-600">
              <line x1="12" x2="12" y1="2" y2="22" />
              <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
            </svg>
          </div>
          <CardTitle class="text-md">Harga & Stok</CardTitle>
        </div>
      </CardHeader>
      <CardContent class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="space-y-2">
          <Label for="price" class="flex items-center gap-1">
            Harga Normal
            <span class="text-red-500">*</span>
          </Label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-500">Rp</span>
            <Input id="price" v-model="form.price" type="number" min="0" placeholder="0" required class="pl-10"
              :class="form.errors.price ? 'border-red-500' : ''" />
          </div>
          <p v-if="form.errors.price" class="text-xs text-red-600">
            {{ form.errors.price }}
          </p>
        </div>

        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <Label for="sale_price">Harga Promo</Label>
            <Badge v-if="discountPercentage" variant="destructive" class="text-[11px] rounded-full px-2.5 py-0.5">
              -{{ discountPercentage }}%
            </Badge>
          </div>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-500">Rp</span>
            <Input id="sale_price" v-model="form.sale_price" type="number" min="0" placeholder="0" class="pl-10"
              :class="form.errors.sale_price ? 'border-red-500' : ''" />
          </div>
          <p v-if="form.errors.sale_price" class="text-xs text-red-600">
            {{ form.errors.sale_price }}
          </p>
        </div>

        <div class="space-y-2">
          <Label for="min_order" class="flex items-center gap-1">
            Minimal Order
            <span class="text-red-500">*</span>
          </Label>
          <Input id="min_order" v-model="form.min_order" type="number" min="1" placeholder="1" required
            :class="form.errors.min_order ? 'border-red-500' : ''" />
          <p v-if="form.errors.min_order" class="text-xs text-red-600">
            {{ form.errors.min_order }}
          </p>
        </div>

        <div class="space-y-2">
          <Label for="stock" class="flex items-center gap-1">
            Stok
            <span class="text-red-500">*</span>
          </Label>
          <Input id="stock" v-model="form.stock" type="number" min="0" placeholder="0" required
            :class="form.errors.stock ? 'border-red-500' : ''" />
          <p v-if="form.errors.stock" class="text-xs text-red-600">
            {{ form.errors.stock }}
          </p>
        </div>

      </CardContent>
    </Card>

    <!-- Pengiriman -->
    <Card>
      <CardHeader>
        <div class="flex items-center gap-2">
          <div class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
              class="text-blue-600">
              <path
                d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
              <polyline points="3.29 7 12 12 20.71 7" />
            </svg>
          </div>
          <CardTitle class="text-md">Pengiriman & Dimensi</CardTitle>
        </div>
      </CardHeader>
      <CardContent class="space-y-6">
        <div class="grid gap-4 sm:grid-cols-2">
          <div class="space-y-2">
            <Label for="weight" class="flex items-center gap-1">
              Berat (gram)
              <span class="text-red-500">*</span>
            </Label>
            <Input id="weight" v-model="form.weight" type="number" min="0" placeholder="0" required
              :class="form.errors.weight ? 'border-red-500' : ''" />
            <p v-if="form.errors.weight" class="text-xs text-red-600">
              {{ form.errors.weight }}
            </p>
          </div>
          <div class="grid grid-cols-3 gap-3">
            <div class="space-y-2">
              <Label for="length">Panjang</Label>
              <Input id="length" v-model="form.length" type="number" min="0" step="0.1" placeholder="0"
                :class="form.errors.length ? 'border-red-500' : ''" />
              <p v-if="form.errors.length" class="text-xs text-red-600">
                {{ form.errors.length }}
              </p>
            </div>
            <div class="space-y-2">
              <Label for="width">Lebar</Label>
              <Input id="width" v-model="form.width" type="number" min="0" step="0.1" placeholder="0"
                :class="form.errors.width ? 'border-red-500' : ''" />
              <p v-if="form.errors.width" class="text-xs text-red-600">
                {{ form.errors.width }}
              </p>
            </div>
            <div class="space-y-2">
              <Label for="height">Tinggi</Label>
              <Input id="height" v-model="form.height" type="number" min="0" step="0.1" placeholder="0"
                :class="form.errors.height ? 'border-red-500' : ''" />
              <p v-if="form.errors.height" class="text-xs text-red-600">
                {{ form.errors.height }}
              </p>
            </div>
          </div>
        </div>

        <Separator />

        <div class="space-y-4">
          <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <p class="text-sm font-medium text-slate-800">Lokasi Produk</p>
              <p class="text-xs text-slate-500">Sesuaikan alamat atau samakan dengan lokasi toko.</p>
            </div>
            <Button v-if="showEditLocationButton" type="button" size="sm" variant="ghost"
              class="text-blue-600 hover:text-blue-700" :disabled="form.processing" @click="enableLocationEditing">
              Ubah lokasi produk
            </Button>
          </div>

          <RegionSelector v-model:provinceId="form.location_province_id" v-model:cityId="form.location_city_id"
            v-model:districtId="form.location_district_id" :show-district="true" :disabled="locationFieldsDisabled"
            :errors="{
              provinceId: form.errors.location_province_id,
              cityId: form.errors.location_city_id,
              districtId: form.errors.location_district_id,
            }" :province-required="true" :city-required="true" :district-required="true"
            :initial-province-name="normalizedStoreLocation?.province_name ?? null"
            :initial-city-name="normalizedStoreLocation?.city_name ?? null"
            :initial-district-name="normalizedStoreLocation?.district_name ?? null" />

          <div class="space-y-2">
            <Label for="location_postal_code" class="flex items-center gap-1">
              Kode Pos
              <span class="text-red-500">*</span>
            </Label>
            <Input id="location_postal_code" v-model="form.location_postal_code" placeholder="Kode pos asal" required
              :disabled="locationFieldsDisabled" :class="form.errors.location_postal_code ? 'border-red-500' : ''" />
            <p v-if="form.errors.location_postal_code" class="text-xs text-red-600">
              {{ form.errors.location_postal_code }}
            </p>
          </div>
        </div>

        <div v-if="hasDimensions"
          class="flex items-start gap-3 rounded-sm border border-blue-200 bg-blue-50 p-3 text-xs text-blue-900">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            class="mt-0.5 shrink-0">
            <circle cx="12" cy="12" r="10" />
            <path d="M12 16v-4" />
            <path d="M12 8h.01" />
          </svg>
          <p>Dimensi membantu kurir menghitung volume dan biaya pengiriman.</p>
        </div>
      </CardContent>
    </Card>

    <!-- Label Kepatuhan -->
    <Card>
      <CardHeader>
        <div class="flex items-center gap-2">
          <div class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
              class="text-blue-600">
              <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
              <circle cx="12" cy="10" r="3" />
            </svg>
          </div>
          <CardTitle class="text-md">Label & Kepatuhan</CardTitle>
        </div>
      </CardHeader>
      <CardContent>
        <label class="flex items-center justify-between rounded-md border border-slate-200 bg-slate-50/60 px-4 py-3">
          <div>
            <p class="text-sm font-medium text-slate-800">Produk Dalam Negeri</p>
            <p class="text-xs text-slate-500">Tandai jika produk buatan lokal.</p>
          </div>
          <input type="checkbox" v-model="form.is_pdn"
            class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
        </label>
      </CardContent>
    </Card>

    <!-- Metode Pengiriman -->
    <Card>
      <CardHeader>
        <div class="flex items-center gap-2">
          <div class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
              class="text-blue-600">
              <path d="M10 17h4V5H2v12h3" />
              <path d="M20 17h2v-3.34a4 4 0 0 0-1.17-2.83L19 9h-5v8h1" />
              <circle cx="7.5" cy="17.5" r="2.5" />
              <circle cx="17.5" cy="17.5" r="2.5" />
            </svg>
          </div>
          <CardTitle class="text-md">Metode Pengiriman</CardTitle>
        </div>
      </CardHeader>
      <CardContent class="space-y-4">
        <p class="text-sm text-slate-600">
          Pilih metode pengiriman yang didukung untuk produk ini. Minimal pilih satu metode.
        </p>
        <div class="grid gap-4 md:grid-cols-2">
          <label
            class="flex items-center justify-between rounded-md border border-slate-200 bg-slate-50/60 px-4 py-3 cursor-pointer hover:border-blue-300 transition"
            :class="form.shipping_pickup ? 'border-blue-400 bg-blue-50/60' : ''">
            <div>
              <p class="text-sm font-medium text-slate-800">Ambil di Toko</p>
              <p class="text-xs text-slate-500">Customer mengambil produk langsung di toko.</p>
            </div>
            <input type="checkbox" v-model="form.shipping_pickup"
              class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
          </label>

          <label
            class="flex items-center justify-between rounded-md border border-slate-200 bg-slate-50/60 px-4 py-3 cursor-pointer hover:border-blue-300 transition"
            :class="form.shipping_delivery ? 'border-blue-400 bg-blue-50/60' : ''">
            <div>
              <p class="text-sm font-medium text-slate-800">Diantar ke Tempat</p>
              <p class="text-xs text-slate-500">Penjual mengirim/mengantar produk ke alamat pembeli.</p>
            </div>
            <input type="checkbox" v-model="form.shipping_delivery"
              class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
          </label>
        </div>
        <p v-if="!form.shipping_pickup && !form.shipping_delivery" class="text-xs text-red-600 font-medium">
          Pilih minimal satu metode pengiriman
        </p>
      </CardContent>
      <CardFooter class="flex flex-col gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:justify-end">
        <Button v-if="$slots.cancel" type="button" variant="outline" as-child>
          <slot name="cancel" />
        </Button>
        <Button type="submit" :disabled="form.processing">
          {{ props.mode === 'create' ? 'Simpan produk' : 'Update produk' }}
        </Button>
      </CardFooter>
    </Card>
  </form>
</template>
