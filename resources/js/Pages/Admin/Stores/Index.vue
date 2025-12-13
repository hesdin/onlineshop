<script setup lang="ts">
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { computed, ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { Building2, CheckCircle2, MapPin, Plus, Search, Shield, Store as StoreIcon, Trash2, Users } from 'lucide-vue-next';

const props = defineProps({
    stores: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    typeOptions: {
        type: Array,
        default: () => [],
    },
    taxStatusOptions: {
        type: Array,
        default: () => [],
    },
    metrics: {
        type: Object,
        default: () => ({
            total: 0,
            verified: 0,
            umkm: 0,
        }),
    },
});

const page = usePage();

const search = ref(props.filters.search ?? '');
const typeFilter = ref(props.filters.type ?? '');
const statusFilter = ref(props.filters.status ?? '');
const taxStatusFilter = ref(props.filters.tax_status ?? '');

const deleteDialogOpen = ref(false);
const deletingStore = ref<any | null>(null);
const deletingProcessing = ref(false);
const successMessage = ref<string | null>((page.props.flash as any)?.success ?? null);

watch(
    () => (page.props.flash as any)?.success,
    (value) => {
        successMessage.value = value ?? null;
    },
);

const typeLabelMap = computed<Record<string, string>>(() =>
    Object.fromEntries(props.typeOptions.map((option: any) => [option.value, option.label])),
);
const taxLabelMap = computed<Record<string, string>>(() =>
    Object.fromEntries(props.taxStatusOptions.map((option: any) => [option.value, option.label])),
);

const buildQuery = (override: Record<string, unknown> = {}) => ({
    search: search.value || undefined,
    type: typeFilter.value || undefined,
    status: statusFilter.value || undefined,
    tax_status: taxStatusFilter.value || undefined,
    ...override,
});

const debouncedSearch = useDebounceFn((value: string) => {
    router.get('/admin/stores', buildQuery({ search: value || undefined }), {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
}, 400);

watch(search, (value) => {
    debouncedSearch(value);
});

watch([typeFilter, statusFilter, taxStatusFilter], () => {
    router.get('/admin/stores', buildQuery(), {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
});

const requestDelete = (store: any) => {
    deletingStore.value = store;
    deleteDialogOpen.value = true;
};

const deleteStore = () => {
    if (!deletingStore.value) {
        return;
    }
    deletingProcessing.value = true;
    router.delete(`/admin/stores/${deletingStore.value.id}`, {
        preserveScroll: true,
        onFinish: () => {
            deleteDialogOpen.value = false;
            deletingStore.value = null;
            deletingProcessing.value = false;
        },
    });
};

const paginateTo = (url?: string | null) => {
    if (!url) return;
    router.visit(url, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
};

const numberedPaginationLinks = computed(() =>
    (props.stores.links ?? []).filter((link: any) => Number.isInteger(Number(link.label))),
);

defineOptions({
    layout: AdminDashboardLayout,
});
</script>

<template>
    <div class="space-y-6">
        <Head title="Manajemen Toko" />

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Vendor & Kemitraan</p>
                <h1 class="text-2xl font-bold text-slate-900">Manajemen Toko</h1>
                <p class="text-sm text-slate-500">Kelola status verifikasi, profil toko, dan performa penjualan.</p>
            </div>
            <Button as-child>
                <Link href="/admin/stores/create">
                <Plus class="h-4 w-4" />
                Tambah Toko
                </Link>
            </Button>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <Card class="border-slate-200 bg-white shadow-sm">
                <CardHeader class="pb-2">
                    <CardDescription>Total Toko Terdaftar</CardDescription>
                    <CardTitle class="text-3xl font-semibold text-slate-900">{{ metrics.total }}</CardTitle>
                </CardHeader>
                <CardContent class="text-sm text-slate-500">Semua vendor aktif di platform</CardContent>
            </Card>
            <Card class="border-slate-200 bg-white shadow-sm">
                <CardHeader class="pb-2">
                    <CardDescription>Terverifikasi</CardDescription>
                    <CardTitle class="text-3xl font-semibold text-emerald-600">{{ metrics.verified }}</CardTitle>
                </CardHeader>
                <CardContent class="text-sm text-slate-500">Sudah melalui proses verifikasi dokumen</CardContent>
            </Card>
            <Card class="border-slate-200 bg-white shadow-sm">
                <CardHeader class="pb-2">
                    <CardDescription>Toko UMKM</CardDescription>
                    <CardTitle class="text-3xl font-semibold text-sky-600">{{ metrics.umkm }}</CardTitle>
                </CardHeader>
                <CardContent class="text-sm text-slate-500">Mendapat prioritas dukungan & promosi</CardContent>
            </Card>
        </div>

        <Card class="border-slate-200 bg-white shadow-sm">
            <CardHeader class="pb-4">
                <CardTitle>Filter Cepat</CardTitle>
                <CardDescription>Sempurnakan pencarian berdasarkan jenis, status verifikasi, dan pajak.</CardDescription>
            </CardHeader>
            <CardContent class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div class="col-span-2">
                    <Label class="text-xs font-semibold uppercase text-slate-500">Cari Toko</Label>
                    <div class="relative mt-1">
                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                            <Search class="h-4 w-4" />
                        </span>
                        <Input v-model="search" placeholder="Nama, kota, atau provinsi" class="pl-9" />
                    </div>
                </div>
                <div>
                    <Label class="text-xs font-semibold uppercase text-slate-500">Jenis Toko</Label>
                    <select v-model="typeFilter"
                        class="mt-1 w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700">
                        <option value="">Semua</option>
                        <option v-for="option in typeOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                </div>
                <div>
                    <Label class="text-xs font-semibold uppercase text-slate-500">Status Verifikasi</Label>
                    <select v-model="statusFilter"
                        class="mt-1 w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700">
                        <option value="">Semua</option>
                        <option value="verified">Terverifikasi</option>
                        <option value="unverified">Belum Verifikasi</option>
                    </select>
                </div>
                <div>
                    <Label class="text-xs font-semibold uppercase text-slate-500">Status Pajak</Label>
                    <select v-model="taxStatusFilter"
                        class="mt-1 w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700">
                        <option value="">Semua</option>
                        <option v-for="option in taxStatusOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                </div>
            </CardContent>
        </Card>

        <Alert v-if="successMessage" variant="default" class="flex items-start gap-3 border-green-200 bg-green-50">
            <CheckCircle2 class="h-5 w-5 text-emerald-600" />
            <div>
                <AlertTitle class="text-green-800">Berhasil</AlertTitle>
                <AlertDescription class="text-green-700">
                    {{ successMessage }}
                </AlertDescription>
            </div>
        </Alert>

        <Card class="border-slate-200 bg-white shadow-sm">
            <CardHeader>
                <CardTitle>Daftar Toko</CardTitle>
                <CardDescription>Menampilkan {{ stores.data.length }} dari {{ stores.total }} toko yang memenuhi filter.
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
                <div v-if="stores.data.length === 0"
                    class="rounded-lg border border-dashed border-slate-200 bg-slate-50 p-6 text-center text-sm text-slate-500">
                    Belum ada data toko yang sesuai dengan filter.
                </div>

                <div v-else class="space-y-3">
                    <div v-for="store in stores.data" :key="store.id"
                        class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-slate-300 hover:shadow-md">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="flex items-start gap-4">
                                <div class="grid h-12 w-12 place-items-center rounded-xl bg-slate-100 text-slate-600">
                                    <StoreIcon class="h-5 w-5" />
                                </div>
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="text-base font-semibold text-slate-900">{{ store.name }}</p>
                                        <span v-if="store.is_verified"
                                            class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-0.5 text-xs font-semibold text-emerald-700">
                                            <Shield class="h-3.5 w-3.5" />
                                            Verified
                                        </span>
                                        <span v-if="store.is_umkm"
                                            class="inline-flex items-center gap-1 rounded-full bg-sky-100 px-3 py-0.5 text-xs font-semibold text-sky-700">
                                            <Users class="h-3.5 w-3.5" />
                                            UMKM
                                        </span>
                                    </div>
                                    <p class="text-sm text-slate-500">{{ store.tagline || 'Belum ada deskripsi singkat' }}</p>
                                    <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5">
                                            <Building2 class="h-3.5 w-3.5" />
                                            {{ typeLabelMap[store.type] ?? store.type }}
                                        </span>
                                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5">
                                            <Shield class="h-3.5 w-3.5" />
                                            {{ taxLabelMap[store.tax_status] ?? store.tax_status }}
                                        </span>
                                        <span v-if="store.city || store.province"
                                            class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5">
                                            <MapPin class="h-3.5 w-3.5" />
                                            {{ store.city ?? 'Kota tidak diketahui' }},
                                            {{ store.province ?? 'Provinsi tidak diketahui' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <Button variant="outline" size="sm" as-child>
                                    <Link :href="`/admin/stores/${store.id}/edit`">Kelola</Link>
                                </Button>
                                <Button variant="ghost" size="icon" class="text-red-500 hover:text-red-600"
                                    @click="requestDelete(store)">
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                        <div class="mt-4 grid gap-3 border-t border-slate-100 pt-4 text-sm text-slate-600 md:grid-cols-4">
                            <div>
                                <p class="text-xs uppercase text-slate-400">Rating</p>
                                <p class="text-base font-semibold text-slate-900">
                                    {{ store.rating ? Number(store.rating).toFixed(1) : 'Belum ada' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs uppercase text-slate-400">Transaksi</p>
                                <p class="text-base font-semibold text-slate-900">
                                    {{ store.transactions_count?.toLocaleString() ?? 0 }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs uppercase text-slate-400">Produk Aktif</p>
                                <p class="text-base font-semibold text-slate-900">{{ store.products_count }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase text-slate-400">Respons</p>
                                <p class="text-base font-semibold text-slate-900">
                                    {{ store.response_time_label || 'Belum diatur' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </CardContent>
            <CardFooter class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">
                <p>
                    Menampilkan {{ stores.data.length }} dari {{ stores.total }} toko.
                </p>
                <div class="flex flex-wrap items-center gap-1">
                    <Button variant="outline" size="sm" :disabled="!stores.prev_page_url"
                        @click="paginateTo(stores.prev_page_url)">
                        Sebelumnya
                    </Button>
                    <Button v-for="link in numberedPaginationLinks" :key="link.label" size="sm"
                        :variant="link.active ? 'default' : 'outline'" :aria-current="link.active ? 'page' : undefined"
                        :disabled="!link.url" @click="paginateTo(link.url)">
                        {{ link.label }}
                    </Button>
                    <Button variant="outline" size="sm" :disabled="!stores.next_page_url"
                        @click="paginateTo(stores.next_page_url)">
                        Selanjutnya
                    </Button>
                </div>
            </CardFooter>
        </Card>

        <AlertDialog :open="deleteDialogOpen" @update:open="(value) => (deleteDialogOpen = value)">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Hapus Toko?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Tindakan ini akan menghapus data toko {{ deletingStore?.name }} secara permanen.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel :disabled="deletingProcessing">Batal</AlertDialogCancel>
                    <AlertDialogAction class="bg-red-500 hover:bg-red-600" :disabled="deletingProcessing"
                        @click="deleteStore">
                        Hapus
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </div>
</template>
