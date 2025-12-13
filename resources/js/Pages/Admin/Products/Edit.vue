<script setup lang="ts">
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, watch, ref, onBeforeUnmount } from 'vue';
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
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
import { ChevronsUpDown, Check, ImagePlus, X } from 'lucide-vue-next';

type Option = {
    id: number;
    name: string;
    city?: string | null;
    province?: string | null;
    district?: string | null;
    postal_code?: string | null;
};

type SelectOption = {
    value: string;
    label: string;
};

type ProductPayload = {
    id: number;
    store_id: number | null;
    category_id: number | null;
    name: string;
    slug: string;
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
    location_province_id: number | null;
    location_city_id: number | null;
    location_district_id: number | null;
    location_postal_code: string | null;
    is_pdn: boolean;
    is_pkp: boolean;
    is_tkdn: boolean;
};

const props = defineProps<{
    product: ProductPayload;
    storeOptions: Option[];
    categoryOptions: Option[];
    statuses: SelectOption[];
    itemTypes: SelectOption[];
}>();

defineOptions({
    layout: AdminDashboardLayout,
});

const form = useForm({
    store_id: props.product.store_id ? props.product.store_id.toString() : '',
    category_id: props.product.category_id ? props.product.category_id.toString() : '',
    name: props.product.name,
    slug: props.product.slug,
    brand: props.product.brand ?? '',
    description: props.product.description ?? '',
    // pakai string agar konsisten dengan Create.vue
    price: props.product.price.toString(),
    sale_price: props.product.sale_price !== null ? props.product.sale_price.toString() : '',
    min_order: props.product.min_order,
    stock: props.product.stock,
    weight: props.product.weight !== null ? props.product.weight.toString() : '',
    length: props.product.length !== null ? props.product.length.toString() : '',
    width: props.product.width !== null ? props.product.width.toString() : '',
    height: props.product.height !== null ? props.product.height.toString() : '',
    item_type: props.product.item_type,
    status: props.product.status,
    is_pdn: props.product.is_pdn,
    is_pkp: props.product.is_pkp,
    is_tkdn: props.product.is_tkdn,
    images: [] as File[],
});

const generateSlug = (value?: string | number | null) =>
    value
        ?.toString()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '') ?? '';

watch(
    () => form.name,
    (value) => {
        form.slug = generateSlug(value);
    },
);

const submit = () => {
    form.put(`/admin/products/${props.product.id}`, {
        preserveScroll: true,
        forceFormData: true,
    });
};

const hasDimensions = computed(() => form.length || form.width || form.height);
const hasErrors = computed(() => Object.keys(form.errors).length > 0);
const storeLocationText = computed(() => {
    const store = props.storeOptions.find((s) => s.id.toString() === form.store_id);
    if (!store) return 'Lokasi mengikuti profil toko';
    const parts = [store.district, store.city, store.province, store.postal_code].filter(Boolean);
    return parts.join(', ') || 'Lokasi mengikuti profil toko';
});

const storePopoverOpen = ref(false);
const categoryPopoverOpen = ref(false);

const selectedStore = computed(
    () => props.storeOptions.find((s) => s.id.toString() === form.store_id) ?? null,
);

const selectedCategory = computed(
    () => props.categoryOptions.find((c) => c.id.toString() === form.category_id) ?? null,
);

const discountPercentage = computed(() => {
    if (!form.price || !form.sale_price) return null;
    const price = parseFloat(form.price as unknown as string);
    const salePrice = parseFloat(form.sale_price as unknown as string);
    if (price > 0 && salePrice > 0 && salePrice < price) {
        return Math.round(((price - salePrice) / price) * 100);
    }
    return null;
});

const storeSearch = ref('');
const categorySearch = ref('');

const filteredStoreOptions = computed(() => {
    if (!storeSearch.value) {
        return props.storeOptions.slice(0, 5);
    }
    const term = storeSearch.value.toLowerCase();
    return props.storeOptions.filter((option) =>
        option.name.toLowerCase().includes(term),
    );
});

const filteredCategoryOptions = computed(() => {
    if (!categorySearch.value) {
        return props.categoryOptions.slice(0, 5);
    }
    const term = categorySearch.value.toLowerCase();
    return props.categoryOptions.filter((option) =>
        option.name.toLowerCase().includes(term),
    );
});

const objectUrls = ref<string[]>([]);

const imagePreviews = computed(() => {
    objectUrls.value.forEach((url) => URL.revokeObjectURL(url));
    objectUrls.value = [];

    if (!form.images || form.images.length === 0) return [];

    return form.images.map((file) => {
        const url = URL.createObjectURL(file);
        objectUrls.value.push(url);
        return url;
    });
});

const handleImagesChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const files = target.files ? Array.from(target.files) : [];
    form.images = files;
};

onBeforeUnmount(() => {
    objectUrls.value.forEach((url) => URL.revokeObjectURL(url));
    objectUrls.value = [];
});
</script>

<template>
    <div class="mx-auto space-y-6">

        <Head title="Edit Produk" />

        <!-- Header -->
        <div class="space-y-1">
            <div class="flex items-center justify-between gap-2">
                <h1 class="text-xl font-bold tracking-tight text-slate-900">
                    Edit Produk
                </h1>
                <Button variant="outline" size="sm" @click="router.visit('/admin/products')" :disabled="form.processing">
                    Kembali
                </Button>
            </div>
        </div>

        <!-- Error Alert -->
        <div v-if="hasErrors"
            class="flex items-start gap-3 rounded-sm border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="mt-0.5 shrink-0">
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

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Store & Category -->
            <Card>
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <div class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="text-blue-600">
                                <path d="M3 9h18v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9Z" />
                                <path d="m3 9 2.45-4.9A2 2 0 0 1 7.24 3h9.52a2 2 0 0 1 1.8 1.1L21 9" />
                                <path d="M12 3v6" />
                            </svg>
                        </div>
                        <div>
                            <CardTitle class="text-md">Toko & Kategori</CardTitle>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="store" class="flex items-center gap-1">
                                Toko
                                <span class="text-red-500">*</span>
                            </Label>

                            <Popover v-model:open="storePopoverOpen">
                                <PopoverTrigger as-child>
                                    <Button id="store" variant="outline" role="combobox" class="w-full justify-between"
                                        :class="form.errors.store_id ? 'border-red-500' : ''">
                                        <span class="truncate text-sm">
                                            {{ selectedStore?.name ?? 'Pilih toko' }}
                                        </span>
                                        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent align="start" class="w-[280px] p-0 text-left">
                                    <Command>
                                        <CommandInput v-model="storeSearch" placeholder="Cari toko..." />
                                        <CommandEmpty>
                                            Toko tidak ditemukan.
                                        </CommandEmpty>
                                        <CommandGroup>
                                            <CommandItem v-for="option in filteredStoreOptions" :key="option.id"
                                                :value="option.name" @select="() => {
                                                    form.store_id = option.id.toString();
                                                    storePopoverOpen = false;
                                                }">
                                                <Check class="mr-2 h-4 w-4" :class="form.store_id === option.id.toString()
                                                    ? 'opacity-100'
                                                    : 'opacity-0'" />
                                                <span>{{ option.name }}</span>
                                            </CommandItem>
                                        </CommandGroup>
                                    </Command>
                                </PopoverContent>
                            </Popover>

                            <p v-if="form.errors.store_id" class="text-xs text-red-600">
                                {{ form.errors.store_id }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="category">Kategori</Label>

                            <Popover v-model:open="categoryPopoverOpen">
                                <PopoverTrigger as-child>
                                    <Button id="category" variant="outline" role="combobox"
                                        class="w-full justify-between"
                                        :class="form.errors.category_id ? 'border-red-500' : ''">
                                        <span class="truncate text-sm">
                                            {{ selectedCategory?.name ?? 'Tanpa kategori' }}
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
                                            <!-- opsi kosong -->
                                            <CommandItem value="Tanpa kategori" @select="() => {
                                                form.category_id = '';
                                                categoryPopoverOpen = false;
                                            }">
                                                <Check class="mr-2 h-4 w-4" :class="!form.category_id
                                                    ? 'opacity-100'
                                                    : 'opacity-0'" />
                                                <span>Tanpa kategori</span>
                                            </CommandItem>

                                            <CommandItem v-for="option in filteredCategoryOptions" :key="option.id"
                                                :value="option.name" @select="() => {
                                                    form.category_id = option.id.toString();
                                                    categoryPopoverOpen = false;
                                                }">
                                                <Check class="mr-2 h-4 w-4" :class="form.category_id === option.id.toString()
                                                    ? 'opacity-100'
                                                    : 'opacity-0'" />
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
                    </div>
                </CardContent>
            </Card>

            <!-- Product Information -->
            <Card>
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <div class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="text-blue-600">
                                <path d="M16 20V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
                                <rect width="20" height="14" x="2" y="6" rx="2" />
                            </svg>
                        </div>
                        <div>
                            <CardTitle class="text-md">Informasi Produk</CardTitle>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2 sm:col-span-2">
                            <Label for="name" class="flex items-center gap-1">
                                Nama Produk
                                <span class="text-red-500">*</span>
                            </Label>
                            <Input id="name" v-model="form.name" placeholder="Contoh: Paket Bingkisan Premium"
                                :class="form.errors.name ? 'border-red-500' : ''" />
                            <p v-if="form.errors.name" class="text-xs text-red-600">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <div class="space-y-2 sm:col-span-2">
                            <Label for="slug">URL Slug</Label>
                            <div class="relative">
                                <Input id="slug" v-model="form.slug" disabled class="bg-slate-50 pr-20" />
                                <Badge variant="secondary" class="absolute right-2 top-1/2 -translate-y-1/2 text-xs">
                                    Auto
                                </Badge>
                            </div>
                            <p class="text-xs text-slate-500">
                                Slug otomatis dibuat dari nama produk
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

                        <div class="space-y-2">
                            <Label for="item_type">Jenis Item</Label>
                            <Select v-model="form.item_type">
                                <SelectTrigger class="w-full" id="item_type"
                                    :class="form.errors.item_type ? 'border-red-500' : ''">
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

                    <div class="space-y-2">
                        <Label for="description">Deskripsi Produk</Label>
                        <Textarea id="description" v-model="form.description" rows="5"
                            placeholder="Jelaskan detail produk, keunggulan, bahan, cara penggunaan, dan informasi penting lainnya..."
                            :class="form.errors.description ? 'border-red-500' : ''" />
                        <p class="text-xs text-slate-500">
                            Deskripsi yang lengkap membantu pembeli membuat keputusan
                        </p>
                        <p v-if="form.errors.description" class="text-xs text-red-600">
                            {{ form.errors.description }}
                        </p>
                    </div>
                </CardContent>
            </Card>

            <!-- Pricing & Status -->
            <Card>
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <div class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="text-blue-600">
                                <line x1="12" x2="12" y1="2" y2="22" />
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                            </svg>
                        </div>
                        <div>
                            <CardTitle class="text-md">Harga & Status</CardTitle>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="space-y-2">
                            <Label for="price" class="flex items-center gap-1">
                                Harga Normal
                                <span class="text-red-500">*</span>
                            </Label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-500">Rp</span>
                                <Input id="price" type="number" min="0" step="1" v-model="form.price"
                                    placeholder="100000" class="pl-10"
                                    :class="form.errors.price ? 'border-red-500' : ''" />
                            </div>
                            <p v-if="form.errors.price" class="text-xs text-red-600">
                                {{ form.errors.price }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <Label for="sale_price" class="flex items-center gap-1">
                                    Harga Promo
                                </Label>

                                <!-- Badge rounded full + sedikit dinaikkan -->
                                <Badge v-if="discountPercentage" variant="destructive"
                                    class="text-[10px] -mt-1 rounded-sm px-2.5 py-0.5">
                                    -{{ discountPercentage }}%
                                </Badge>
                            </div>

                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-500">
                                    Rp
                                </span>
                                <Input id="sale_price" type="number" min="0" step="1" v-model="form.sale_price"
                                    placeholder="85000" class="pl-10"
                                    :class="form.errors.sale_price ? 'border-red-500' : ''" />
                            </div>

                            <p v-if="form.errors.sale_price" class="text-xs text-red-600">
                                {{ form.errors.sale_price }}
                            </p>
                            <p v-else class="text-xs text-slate-500">Opsional</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="status">Status Produk</Label>
                            <Select v-model="form.status">
                                <SelectTrigger class="w-full" id="status"
                                    :class="form.errors.status ? 'border-red-500' : ''">
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
                </CardContent>
            </Card>

            <!-- Stock & Dimensions -->
            <Card>
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <div class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="text-blue-600">
                                <path
                                    d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
                                <polyline points="3.29 7 12 12 20.71 7" />
                                <line x1="12" x2="12" y1="22" y2="12" />
                            </svg>
                        </div>
                        <div>
                            <CardTitle class="text-md">Stok & Dimensi</CardTitle>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="space-y-2">
                            <Label for="min_order">Minimal Order</Label>
                            <Input id="min_order" type="number" min="1" step="1" v-model="form.min_order"
                                :class="form.errors.min_order ? 'border-red-500' : ''" />
                            <p v-if="form.errors.min_order" class="text-xs text-red-600">
                                {{ form.errors.min_order }}
                            </p>
                            <p v-else class="text-xs text-slate-500">
                                Minimum pembelian
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="stock">Stok Tersedia</Label>
                            <Input id="stock" type="number" min="0" step="1" v-model="form.stock"
                                :class="form.errors.stock ? 'border-red-500' : ''" />
                            <p v-if="form.errors.stock" class="text-xs text-red-600">
                                {{ form.errors.stock }}
                            </p>
                            <p v-else class="text-xs text-slate-500">
                                Jumlah unit tersedia
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="weight">Berat (gram)</Label>
                            <Input id="weight" type="number" min="0" step="1" v-model="form.weight" placeholder="500"
                                :class="form.errors.weight ? 'border-red-500' : ''" />
                            <p v-if="form.errors.weight" class="text-xs text-red-600">
                                {{ form.errors.weight }}
                            </p>
                            <p v-else class="text-xs text-slate-500">
                                Untuk hitung ongkir
                            </p>
                        </div>
                    </div>

                    <Separator />

                    <div>
                        <h4 class="mb-3 text-sm font-medium text-slate-900">
                            Dimensi Paket
                        </h4>
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div class="space-y-2">
                                <Label for="length">Panjang (cm)</Label>
                                <Input id="length" type="number" min="0" step="0.1" v-model="form.length"
                                    placeholder="30" :class="form.errors.length ? 'border-red-500' : ''" />
                                <p v-if="form.errors.length" class="text-xs text-red-600">
                                    {{ form.errors.length }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="width">Lebar (cm)</Label>
                                <Input id="width" type="number" min="0" step="0.1" v-model="form.width" placeholder="20"
                                    :class="form.errors.width ? 'border-red-500' : ''" />
                                <p v-if="form.errors.width" class="text-xs text-red-600">
                                    {{ form.errors.width }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="height">Tinggi (cm)</Label>
                                <Input id="height" type="number" min="0" step="0.1" v-model="form.height"
                                    placeholder="10" :class="form.errors.height ? 'border-red-500' : ''" />
                                <p v-if="form.errors.height" class="text-xs text-red-600">
                                    {{ form.errors.height }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div v-if="hasDimensions"
                        class="flex items-start gap-3 rounded-sm border border-blue-200 bg-blue-50 p-3 text-sm text-blue-900">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="mt-0.5 shrink-0">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M12 16v-4" />
                            <path d="M12 8h.01" />
                        </svg>
                        <p class="text-xs">
                            Dimensi membantu kurir menghitung volume dan biaya
                            pengiriman dengan akurat.
                        </p>
                    </div>
                </CardContent>
            </Card>

            <!-- Product Images (Multiple) -->
            <Card>
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <div class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100">
                            <ImagePlus class="h-4 w-4 text-blue-600" />
                        </div>
                        <div>
                            <CardTitle class="text-md">Gambar Produk</CardTitle>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-2">
                        <Label for="images">Upload Gambar</Label>
                        <Input id="images" type="file" accept="image/*" multiple @change="handleImagesChange"
                            :disabled="form.processing" />
                        <p v-if="form.errors.images" class="text-xs text-red-600">
                            {{ form.errors.images }}
                        </p>
                        <p class="text-xs text-slate-500">
                            Tambahkan satu atau beberapa gambar. Gambar pertama menjadi cover utama.
                        </p>
                    </div>

                    <div class="rounded-sm border border-dashed border-slate-200 bg-slate-50/80 p-4">
                        <div v-if="imagePreviews.length" class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                            <div v-for="(src, index) in imagePreviews" :key="index" class="relative flex flex-col items-center gap-1">
                                <img :src="src" :alt="`Preview gambar ${index + 1}`"
                                    class="h-24 w-24 rounded-md object-cover shadow-inner" />
                                <span class="rounded-full bg-slate-900/70 px-2 py-0.5 text-[10px] text-white">
                                    {{ index === 0 ? 'Utama' : `Gambar ${index + 1}` }}
                                </span>
                            </div>
                        </div>
                        <div v-else class="text-center text-xs text-slate-500">
                            Preview akan muncul setelah gambar dipilih
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Location & Certifications -->
            <Card>
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <div class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="text-blue-600">
                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                                <circle cx="12" cy="10" r="3" />
                            </svg>
                        </div>
                        <div>
                            <CardTitle class="text-md">Lokasi & Sertifikasi</CardTitle>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="rounded-md border border-dashed border-slate-200 bg-slate-50/70 p-4 text-sm text-slate-700">
                        <p class="font-medium text-slate-900">Lokasi pengiriman mengikuti toko</p>
                        <p class="mt-1 text-slate-600">
                            Lokasi produk otomatis disalin dari profil toko terkait.
                        </p>
                        <p class="mt-2 text-xs text-slate-500">
                            Lokasi saat ini: {{ storeLocationText }}
                        </p>
                    </div>

                    <Separator />

                    <div>
                        <h4 class="mb-3 text-sm font-medium text-slate-900">
                            Sertifikasi & Label
                        </h4>
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div
                                class="flex items-start justify-between gap-3 rounded-sm border border-slate-200 bg-white p-4 transition-colors hover:bg-slate-50">
                                <div class="space-y-1">
                                    <p class="text-sm font-medium text-slate-900">
                                        PDN
                                    </p>
                                    <p class="text-xs leading-relaxed text-slate-600">
                                        Produk Dalam Negeri
                                    </p>
                                </div>
                                <Switch v-model:checked="form.is_pdn" />
                            </div>

                            <div
                                class="flex items-start justify-between gap-3 rounded-sm border border-slate-200 bg-white p-4 transition-colors hover:bg-slate-50">
                                <div class="space-y-1">
                                    <p class="text-sm font-medium text-slate-900">
                                        PKP
                                    </p>
                                    <p class="text-xs leading-relaxed text-slate-600">
                                        Pengusaha Kena Pajak
                                    </p>
                                </div>
                                <Switch v-model:checked="form.is_pkp" />
                            </div>

                            <div
                                class="flex items-start justify-between gap-3 rounded-sm border border-slate-200 bg-white p-4 transition-colors hover:bg-slate-50">
                                <div class="space-y-1">
                                    <p class="text-sm font-medium text-slate-900">
                                        TKDN
                                    </p>
                                    <p class="text-xs leading-relaxed text-slate-600">
                                        Tingkat Komponen DN
                                    </p>
                                </div>
                                <Switch v-model:checked="form.is_tkdn" />
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Actions -->
            <Card class="border-0">
                <CardFooter class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <Button type="button" variant="outline" @click="router.visit('/admin/products')"
                        :disabled="form.processing">
                        Batal
                    </Button>
                    <Button type="submit" :disabled="form.processing" class="min-w-[160px]">
                        <svg v-if="form.processing" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" class="mr-2 animate-spin">
                            <path d="M21 12a9 9 0 1 1-6.219-8.56" />
                        </svg>
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                    </Button>
                </CardFooter>
            </Card>
        </form>
    </div>
</template>
