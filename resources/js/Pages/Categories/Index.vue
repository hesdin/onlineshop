<script setup lang="ts">
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
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
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
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
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { DataTable } from '@/components/ui/data-table';
import { ref, watch, computed, h, onUnmounted } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { Trash2, Edit2, ImagePlus, SlidersHorizontal, CheckCircle2 } from 'lucide-vue-next';
import type { ColumnDef } from '@tanstack/vue-table';

const props = defineProps({
    categories: {
        type: Object,
        required: true,
    },
    parentOptions: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const search = ref(props.filters.search ?? '');

const createDialogOpen = ref(false);
const editDialogOpen = ref(false);
const editingCategory = ref(null);
const deleteDialogOpen = ref(false);
const deletingCategory = ref<any | null>(null);
const showSuccessAlert = ref(false);
const successMessage = ref('');
const successTimeout = ref<ReturnType<typeof setTimeout> | null>(null);

const createForm = useForm({
    name: '',
    slug: '',
    parent_id: '',
    image: null,
});

const editForm = useForm({
    name: '',
    slug: '',
    parent_id: '',
    image: null,
});

const generateSlug = (value) =>
    value
        ?.toString()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '') ?? '';

const debouncedSearch = useDebounceFn((value) => {
    router.get(
        '/categories',
        { search: value },
        { preserveState: true, replace: true, preserveScroll: true },
    );
}, 400);

watch(search, (value) => {
    debouncedSearch(value);
});

watch(
    () => createForm.name,
    (value) => {
        createForm.slug = generateSlug(value);
    },
);

watch(
    () => editForm.name,
    (value) => {
        editForm.slug = generateSlug(value);
    },
);

const handleFileChange = (event, form) => {
    const file = event.target.files?.[0];
    form.image = file ?? null;
};

const resetCreateForm = () => {
    createForm.reset();
    createForm.clearErrors();
    createForm.image = null;
};

const triggerSuccessAlert = (message: string) => {
    successMessage.value = message;
    showSuccessAlert.value = true;

    if (successTimeout.value) {
        clearTimeout(successTimeout.value);
    }

    successTimeout.value = window.setTimeout(() => {
        showSuccessAlert.value = false;
    }, 4000);
};

const submitCreate = () => {
    createForm.post('/categories', {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            createDialogOpen.value = false;
            resetCreateForm();
            triggerSuccessAlert('Kategori baru berhasil disimpan.');
        },
    });
};

const openEdit = (category) => {
    editingCategory.value = { ...category };
    editForm.reset();
    editForm.name = category.name;
    editForm.slug = category.slug;
    editForm.parent_id = category.parent?.id ?? '';
    editForm.image = null;
    editForm.clearErrors();
    editDialogOpen.value = true;
};

const submitEdit = () => {
    if (!editingCategory.value) return;

    editForm
        .transform((data) => ({
            ...data,
            _method: 'put',
        }))
        .post(`/categories/${editingCategory.value.id}`, {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                editDialogOpen.value = false;
                editingCategory.value = null;
                triggerSuccessAlert('Perubahan kategori berhasil disimpan.');
            },
        });
};

const requestDelete = (category) => {
    deletingCategory.value = category;
    deleteDialogOpen.value = true;
};

const deleteCategory = () => {
    if (!deletingCategory.value) return;

    router.delete(`/categories/${deletingCategory.value.id}`, {
        preserveScroll: true,
        onFinish: () => {
            deleteDialogOpen.value = false;
            deletingCategory.value = null;
        },
    });
};

const categoryImage = (category) =>
    category.image_url && category.image_url.length > 0
        ? category.image_url
        : 'https://placehold.co/80x80?text=No+Image';

defineOptions({
    layout: DashboardLayout,
});

const createPreview = computed(() =>
    createForm.image ? URL.createObjectURL(createForm.image) : null,
);

const editPreview = computed(() => {
    if (editForm.image) {
        return URL.createObjectURL(editForm.image);
    }

    return editingCategory.value?.image_url ?? null;
});

const allColumnIds = ['image', 'name', 'slug', 'parent', 'actions'];
const visibleColumns = ref<string[]>([...allColumnIds]);

const baseColumns = computed<ColumnDef<any>[]>(() => [
    {
        id: 'image',
        header: () => 'Icon',
        cell: ({ row }) =>
            h('div', { class: 'flex items-center justify-start' }, [
                h('img', {
                    src: categoryImage(row.original),
                    alt: row.original.name,
                    class: 'h-14 w-14 rounded-md border border-slate-200 object-cover shadow-inner',
                }),
            ]),
        enableSorting: false,
        meta: { class: 'w-32' },
    },
    {
        id: 'name',
        accessorKey: 'name',
        header: () => 'Nama',
        cell: ({ row }) => h('div', { class: 'font-semibold text-slate-900' }, row.original.name),
    },
    {
        id: 'slug',
        accessorKey: 'slug',
        header: () => 'Slug',
        cell: ({ row }) => h('span', { class: 'text-xs text-slate-500' }, row.original.slug),
    },
    {
        id: 'parent',
        header: () => 'Sub dari',
        cell: ({ row }) =>
            h('span', { class: 'text-sm text-slate-600' }, row.original.parent?.name ?? '-'),
    },
    {
        id: 'actions',
        header: () => 'Aksi',
        cell: ({ row }) =>
            h('div', { class: 'flex justify-end gap-2' }, [
                h(
                    Button,
                    {
                        variant: 'outline',
                        size: 'icon-sm',
                        onClick: () => openEdit(row.original),
                    },
                    {
                        default: () => h(Edit2, { class: 'h-3 w-3' }),
                    },
                ),
                h(
                    Button,
                    {
                        variant: 'destructive',
                        size: 'icon-sm',
                        onClick: () => requestDelete(row.original),
                    },
                    {
                        default: () => h(Trash2, { class: 'h-3 w-3' }),
                    },
                ),
            ]),
        meta: { class: 'text-right w-32' },
        enableHiding: false,
    },
]);

const tableColumns = computed(() => {
    return baseColumns.value.filter((column) => {
        const columnId = column.id ?? (column as any).accessorKey;
        if (!columnId) return true;
        return visibleColumns.value.includes(columnId);
    });
});

const toggleColumn = (columnId: string) => {
    if (columnId === 'actions') return;
    if (visibleColumns.value.includes(columnId)) {
        visibleColumns.value = visibleColumns.value.filter((id) => id !== columnId);
    } else {
        visibleColumns.value = [...visibleColumns.value, columnId];
    }
};

const columnOptions = [
    { id: 'image', label: 'Icon' },
    { id: 'name', label: 'Nama' },
    { id: 'slug', label: 'Slug' },
    { id: 'parent', label: 'Sub dari' },
    { id: 'actions', label: 'Aksi', disabled: true },
];

const numberedPaginationLinks = computed(() =>
    (props.categories.links ?? []).filter((link) => Number.isInteger(Number(link.label))),
);

const paginateTo = (url?: string | null) => {
    if (!url) return;
    router.visit(url, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
};

onUnmounted(() => {
    if (successTimeout.value) {
        clearTimeout(successTimeout.value);
    }
    showSuccessAlert.value = false;
});
</script>

<template>
    <div class="space-y-6">

        <Head title="Kategori" />

        <Alert v-if="showSuccessAlert" variant="default" class="flex items-start gap-3 border-green-200 bg-green-50">
            <CheckCircle2 class="h-5 w-5 text-green-600" />
            <div class="space-y-1">
                <AlertTitle class="text-green-800">Berhasil</AlertTitle>
                <AlertDescription class="text-green-700">
                    {{ successMessage }}
                </AlertDescription>
            </div>
        </Alert>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Kategori Produk</h1>
                <p class="text-sm text-slate-500">Kelola daftar kategori dan ikon visualnya.</p>
            </div>
            <Button @click="createDialogOpen = true">
                <ImagePlus class="mr-2 h-4 w-4" />
                Tambah Kategori
            </Button>
        </div>

        <Card class="shadow-none rounded-md">
            <CardHeader class="space-y-4">
                <div>
                    <CardTitle>Daftar Kategori</CardTitle>
                    <CardDescription>Total {{ categories.total }} kategori.</CardDescription>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <Input v-model="search" placeholder="Filter nama kategori..." class="w-full md:w-64" />
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" class="gap-2">
                                <SlidersHorizontal class="h-4 w-4" />
                                Kolom
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-48">
                            <DropdownMenuCheckboxItem v-for="option in columnOptions" :key="option.id"
                                :model-value="visibleColumns.includes(option.id)" :disabled="option.disabled"
                                @update:modelValue="() => toggleColumn(option.id)">
                                {{ option.label }}
                            </DropdownMenuCheckboxItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </CardHeader>
            <CardContent class="space-y-4">
                <DataTable :columns="tableColumns" :data="categories.data" />
            </CardContent>
            <CardFooter class="flex flex-wrap items-center justify-between gap-2 text-sm text-slate-500">
                <p>Menampilkan {{ categories.data.length }} dari {{ categories.total }} kategori.</p>
                <div class="flex flex-wrap items-center gap-1">
                    <Button variant="outline" size="sm" :disabled="!categories.prev_page_url"
                        @click="paginateTo(categories.prev_page_url)">
                        Sebelumnya
                    </Button>
                    <Button v-for="link in numberedPaginationLinks" :key="link.label" size="sm"
                        :variant="link.active ? 'default' : 'outline'" :aria-current="link.active ? 'page' : undefined"
                        :disabled="!link.url" @click="paginateTo(link.url)">
                        {{ link.label }}
                    </Button>
                    <Button variant="outline" size="sm" :disabled="!categories.next_page_url"
                        @click="paginateTo(categories.next_page_url)">
                        Selanjutnya
                    </Button>
                </div>
            </CardFooter>
        </Card>

        <!-- Create Dialog -->
        <Dialog :open="createDialogOpen" @update:open="(value) => (createDialogOpen = value)">
            <DialogContent class="max-w-3xl">
                <DialogHeader>
                    <DialogTitle>Tambah Kategori Baru</DialogTitle>
                    <DialogDescription>
                        Lengkapi detail kategori dan gambar untuk tampil di landing page.
                    </DialogDescription>
                </DialogHeader>

                <form class="space-y-6" @submit.prevent="submitCreate">
                    <div class="space-y-2">
                        <Label>Nama</Label>
                        <Input v-model="createForm.name" placeholder="Contoh: Perlengkapan Kantor" />
                        <p v-if="createForm.errors.name" class="text-sm text-red-500">{{ createForm.errors.name }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label>Slug</Label>
                        <Input v-model="createForm.slug" placeholder="otomatis" disabled />
                        <p v-if="createForm.errors.slug" class="text-sm text-red-500">{{ createForm.errors.slug }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label>Parent</Label>
                        <select v-model="createForm.parent_id"
                            class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm">
                            <option value="">Tanpa parent</option>
                            <option v-for="option in parentOptions" :key="option.id" :value="option.id">
                                {{ option.name }}
                            </option>
                        </select>
                        <p v-if="createForm.errors.parent_id" class="text-sm text-red-500">
                            {{ createForm.errors.parent_id }}
                        </p>
                    </div>
                    <div class="space-y-2">
                        <Label>Upload Gambar</Label>
                        <Input type="file" accept="image/*" @change="(e) => handleFileChange(e, createForm)" />
                        <p v-if="createForm.errors.image" class="text-sm text-red-500">
                            {{ createForm.errors.image }}
                        </p>
                    </div>

                    <div
                        class="grid place-items-center rounded-md border border-dashed border-slate-200 bg-slate-50/80 p-6">
                        <img v-if="createPreview" :src="createPreview" alt="preview"
                            class="h-32 w-32 rounded-md object-cover shadow-inner" />
                        <div v-else class="text-center text-sm text-slate-500">
                            Preview akan muncul setelah gambar dipilih
                        </div>
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="outline" @click="createDialogOpen = false">Batal</Button>
                        <Button type="submit" :disabled="createForm.processing">
                            {{ createForm.processing ? 'Menyimpan...' : 'Simpan Kategori' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Edit Dialog -->
        <Dialog :open="editDialogOpen" @update:open="(value) => (editDialogOpen = value)">
            <DialogContent class="max-w-3xl">
                <DialogHeader>
                    <DialogTitle>Edit Kategori</DialogTitle>
                    <DialogDescription>
                        Perbarui informasi kategori dan unggah ikon baru bila diperlukan.
                    </DialogDescription>
                </DialogHeader>

                <form class="space-y-6" @submit.prevent="submitEdit">
                    <div class="space-y-2">
                        <Label>Nama</Label>
                        <Input v-model="editForm.name" placeholder="Nama kategori" />
                        <p v-if="editForm.errors.name" class="text-sm text-red-500">{{ editForm.errors.name }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label>Slug</Label>
                        <Input v-model="editForm.slug" placeholder="otomatis" disabled />
                        <p v-if="editForm.errors.slug" class="text-sm text-red-500">{{ editForm.errors.slug }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label>Parent</Label>
                        <select v-model="editForm.parent_id"
                            class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm">
                            <option value="">Tanpa parent</option>
                            <option v-for="option in parentOptions" :key="option.id" :value="option.id"
                                :disabled="editingCategory?.id === option.id">
                                {{ option.name }}
                            </option>
                        </select>
                        <p v-if="editForm.errors.parent_id" class="text-sm text-red-500">
                            {{ editForm.errors.parent_id }}
                        </p>
                    </div>
                    <div class="space-y-2">
                        <Label>Upload Gambar</Label>
                        <Input type="file" accept="image/*" @change="(e) => handleFileChange(e, editForm)" />
                        <p v-if="editForm.errors.image" class="text-sm text-red-500">
                            {{ editForm.errors.image }}
                        </p>
                    </div>

                    <div
                        class="grid place-items-center rounded-md border border-dashed border-slate-200 bg-slate-50/80 p-6">
                        <img v-if="editPreview" :src="editPreview" alt="preview"
                            class="h-32 w-32 rounded-md object-cover shadow-inner" />
                        <div v-else class="text-center text-sm text-slate-500">
                            Preview akan muncul setelah gambar dipilih
                        </div>
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="outline" @click="editDialogOpen = false">Batal</Button>
                        <Button type="submit" :disabled="editForm.processing">
                            {{ editForm.processing ? 'Menyimpan...' : 'Perbarui Kategori' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Delete Alert Dialog -->
        <AlertDialog :open="deleteDialogOpen" @update:open="(value) => (deleteDialogOpen = value)">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Hapus kategori?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Kategori <strong>{{ deletingCategory?.name }}</strong> akan dihapus dan gambar terkait akan
                        dihapus
                        permanen. Tindakan ini tidak dapat dibatalkan.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="deleteDialogOpen = false">Batal</AlertDialogCancel>
                    <AlertDialogAction class="bg-red-600 hover:bg-red-700" @click="deleteCategory">
                        Hapus
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </div>
</template>
