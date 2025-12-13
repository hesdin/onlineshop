<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { onBeforeUnmount, ref, watch } from 'vue';
import LandingLayout from '@/Layouts/LandingLayout.vue';
import CustomerSidebarMenu from '@/Components/Customer/SidebarMenu.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { CheckCircle2 } from 'lucide-vue-next';

const page = usePage<{
    flash?: { success?: string | null };
    profile?: { avatar_url?: string | null; name?: string; referral_code?: string; phone?: string; email?: string };
}>();
const successMessage = ref<string | null>(page.props.flash?.success ?? null);
watch(
    () => page.props.flash?.success,
    (val) => {
        successMessage.value = val ?? null;
        if (successMessage.value) {
            setTimeout(() => {
                successMessage.value = null;
            }, 3000);
        }
    },
    { immediate: true },
);

const props = defineProps({
    profile: {
        type: Object,
        default: () => ({}),
    },
});

const form = useForm({
    name: props.profile.name ?? '',
    referral_code: props.profile.referral_code ?? '',
    phone: props.profile.phone ?? '',
    avatar: null as File | null,
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const serverAvatarUrl = ref<string | null>(props.profile.avatar_url ?? null);
const avatarPreview = ref<string | null>(serverAvatarUrl.value);
let avatarObjectUrl: string | null = null;

const resetPreviewToServer = () => {
    avatarPreview.value = serverAvatarUrl.value;
};

const revokeAvatarObjectUrl = () => {
    if (avatarObjectUrl) {
        URL.revokeObjectURL(avatarObjectUrl);
        avatarObjectUrl = null;
    }
};

watch(
    () => props.profile.avatar_url,
    (val) => {
        serverAvatarUrl.value = val ?? null;
        if (!form.avatar) {
            resetPreviewToServer();
        }
    },
);

const handleAvatarChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0] ?? null;
    form.avatar = file;
    if (file) {
        revokeAvatarObjectUrl();
        avatarObjectUrl = URL.createObjectURL(file);
        avatarPreview.value = avatarObjectUrl;
    } else {
        resetPreviewToServer();
    }
};

const submitProfile = () => {
    form.post('/customer/dashboard/profile', {
        forceFormData: true,
        onSuccess: () => {
            revokeAvatarObjectUrl();
            const latestAvatar = (page.props.profile?.avatar_url as string | null) ?? serverAvatarUrl.value;
            serverAvatarUrl.value = latestAvatar;
            avatarPreview.value = latestAvatar;
        },
        onFinish: () => form.reset('avatar'),
    });
};

const resetProfileForm = () => {
    form.reset();
    form.name = props.profile.name ?? '';
    form.referral_code = props.profile.referral_code ?? '';
    form.phone = props.profile.phone ?? '';
    form.avatar = null;
    resetPreviewToServer();
};

const submitPassword = () => {
    passwordForm.post('/customer/dashboard/profile/password', {
        onSuccess: () => passwordForm.reset(),
        preserveScroll: true,
    });
};

onBeforeUnmount(() => {
    revokeAvatarObjectUrl();
});

defineOptions({
    layout: LandingLayout,
});
</script>

<template>
    <div class="bg-slate-50">
        <Head title="Profil" />
        <div class="mx-auto flex max-w-screen-2xl flex-col gap-6 px-6 py-10">
            <nav class="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
                <a href="/" class="text-sky-600 hover:underline">Beranda</a>
                <span>/</span>
                <span>Ubah Profil</span>
                <span>/</span>
                <span class="text-slate-900">Profil</span>
            </nav>

            <div class="grid gap-6 lg:grid-cols-[260px_minmax(0,1fr)]">
                <CustomerSidebarMenu active-key="profil" />

                <main class="space-y-6">
                    <Alert v-if="successMessage" variant="default" class="flex items-start gap-3 border-green-200 bg-green-50">
                        <CheckCircle2 class="h-5 w-5 text-emerald-600" />
                        <div>
                            <AlertTitle class="text-green-800">Berhasil</AlertTitle>
                            <AlertDescription class="text-green-700">{{ successMessage }}</AlertDescription>
                        </div>
                    </Alert>

                    <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                        <header class="mb-6">
                            <h2 class="text-xl font-semibold text-slate-900">Informasi Umum</h2>
                        </header>

                        <div
                            class="flex flex-col items-center gap-6 rounded-lg border border-dashed border-slate-200 bg-slate-50/70 p-6 sm:flex-row sm:items-start sm:gap-10">
                            <div class="flex flex-col items-center gap-3">
                                <div class="grid h-28 w-28 place-items-center rounded-full border-2 border-sky-100 bg-white overflow-hidden">
                                    <img v-if="avatarPreview" :src="avatarPreview" alt="Avatar" class="h-24 w-24 rounded-full object-cover" />
                                    <div v-else class="grid h-24 w-24 place-items-center rounded-full bg-slate-100">
                                        <svg class="h-10 w-10 text-slate-400" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="1.8">
                                            <circle cx="12" cy="8" r="4" />
                                            <path d="M6 20c0-3.3 2.7-6 6-6s6 2.7 6 6" />
                                        </svg>
                                    </div>
                                </div>
                                <label
                                    class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-lg border border-sky-500 px-4 py-2 text-sm font-semibold text-sky-600 transition hover:bg-sky-50">
                                    <input type="file" accept="image/*" class="hidden" @change="handleAvatarChange" />
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path d="m7 10 5-5 5 5" />
                                        <path d="M12 5v14" />
                                    </svg>
                                    Pilih Foto
                                </label>
                                <p class="text-center text-[11px] text-slate-500">Format gambar .jpg .jpeg .png dan
                                    ukuran file maksimum 300KB</p>
                            </div>

                            <div class="grid flex-1 gap-4">
                                <div class="space-y-1">
                                    <label class="text-xs font-semibold text-slate-600">Nama</label>
                                    <Input v-model="form.name"
                                        class="w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-100" />
                                    <p v-if="form.errors.name" class="text-xs text-red-500">{{ form.errors.name }}</p>
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs font-semibold text-slate-600">Kode Referal</label>
                                    <Input v-model="form.referral_code" placeholder="Masukkan Kode Referal"
                                        class="w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-100" />
                                    <p v-if="form.errors.referral_code" class="text-xs text-red-500">{{ form.errors.referral_code }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end gap-3">
                            <Button type="button" variant="outline" @click="resetProfileForm"
                                class="px-5 py-2.5 text-sm font-semibold text-slate-600 transition">
                                Batal
                            </Button>
                            <Button type="button" @click="submitProfile"
                                class="bg-sky-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-600"
                                :disabled="form.processing">
                                {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                            </Button>
                        </div>
                    </section>

                    <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-slate-900">Daftar Perangkat Anda</h3>
                        <p class="mt-2 text-sm text-slate-600">
                            Daftar ini menampilkan berbagai perangkat yang sedang menggunakan akun Anda.
                            Apabila ada aktivitas yang tidak dikenal, segera tekan Logout Semua Perangkat dan atur ulang kata sandi
                            akun Anda.
                        </p>
                        <div
                            class="mt-4 rounded-lg border border-slate-100 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-800">
                            Perangkat Sedang Dipakai
                        </div>
                    </section>

                    <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-slate-900">Email dan Telepon</h3>
                        <div class="mt-4 space-y-4 text-sm">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="min-w-[80px] text-slate-500">Email</span>
                                <span class="font-semibold text-slate-900">{{ props.profile.email }}</span>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="min-w-[80px] text-slate-500">Telepon</span>
                                <Input v-model="form.phone"
                                    class="w-full max-w-xs rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm text-slate-800 outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-100" />
                                <span v-if="form.errors.phone" class="text-xs text-red-500">{{ form.errors.phone }}</span>
                                <span
                                    class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-600">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path d="m5 13 4 4L19 7" />
                                    </svg>
                                    Terverifikasi
                                </span>
                                <Button variant="outline" size="sm" @click="submitProfile" :disabled="form.processing">
                                    {{ form.processing ? 'Menyimpan...' : 'Ubah' }}
                                </Button>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <h3 class="text-lg font-semibold text-slate-900">Kata Sandi</h3>
                            <div class="inline-flex rounded-lg bg-slate-100 px-3 py-1 text-[11px] font-semibold text-slate-600">
                                Terakhir diubah 01 Maret 2022
                            </div>
                        </div>

                        <div class="mt-4 grid gap-4">
                            <div class="space-y-1">
                                <label class="text-xs font-semibold text-slate-600">Kata Sandi Lama</label>
                                <Input v-model="passwordForm.current_password" type="password" placeholder="Kata sandi lama"
                                    class="w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-100" />
                                <p v-if="passwordForm.errors.current_password" class="text-xs text-red-500">
                                    {{ passwordForm.errors.current_password }}
                                </p>
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-semibold text-slate-600">Kata Sandi Baru</label>
                                <Input v-model="passwordForm.password" type="password" placeholder="Kata sandi baru"
                                    class="w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-100" />
                                <p v-if="passwordForm.errors.password" class="text-xs text-red-500">
                                    {{ passwordForm.errors.password }}
                                </p>
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-semibold text-slate-600">Konfirmasi Kata Sandi Baru</label>
                                <Input v-model="passwordForm.password_confirmation" type="password"
                                    placeholder="Ketik ulang kata sandi baru"
                                    class="w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-100" />
                                <p v-if="passwordForm.errors.password_confirmation" class="text-xs text-red-500">
                                    {{ passwordForm.errors.password_confirmation }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="mt-4 grid gap-2 rounded-lg border border-slate-100 bg-slate-50 px-4 py-3 text-sm text-slate-700 md:grid-cols-2">
                            <div class="space-y-1">
                                <p class="font-semibold text-slate-900">Kata sandi harus memiliki:</p>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="grid h-5 w-5 place-items-center rounded-full border border-slate-200 bg-white text-[10px] font-bold text-slate-600">✓</span>
                                    Minimal 8 karakter
                                </div>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="grid h-5 w-5 place-items-center rounded-full border border-slate-200 bg-white text-[10px] font-bold text-slate-600">✓</span>
                                    Huruf besar
                                </div>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="grid h-5 w-5 place-items-center rounded-full border border-slate-200 bg-white text-[10px] font-bold text-slate-600">✓</span>
                                    Huruf kecil
                                </div>
                            </div>
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="grid h-5 w-5 place-items-center rounded-full border border-slate-200 bg-white text-[10px] font-bold text-slate-600">✓</span>
                                    Angka
                                </div>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="grid h-5 w-5 place-items-center rounded-full border border-slate-200 bg-white text-[10px] font-bold text-slate-600">✓</span>
                                    Simbol (contoh: @, #, $, &, ! atau simbol lainnya.)
                                </div>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="grid h-5 w-5 place-items-center rounded-full border border-slate-200 bg-white text-[10px] font-bold text-slate-600">✓</span>
                                    Tidak boleh sama dengan kata sandi terakhir
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end gap-3">
                            <Button type="button" variant="outline" @click="passwordForm.reset()"
                                class="px-5 py-2.5 text-sm font-semibold text-slate-600 transition">
                                Batal
                            </Button>
                            <Button type="button" @click="submitPassword"
                                class="bg-sky-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-600"
                                :disabled="passwordForm.processing">
                                {{ passwordForm.processing ? 'Menyimpan...' : 'Simpan' }}
                            </Button>
                        </div>
                    </section>
                </main>
            </div>
        </div>
    </div>
</template>
