<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    stats: {
        type: Array,
        default: () => [],
    },
    shortcuts: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <DashboardLayout>
        <Head title="Dashboard" />

        <section class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <Card v-for="stat in props.stats" :key="stat.title" class="border-slate-200 bg-white shadow-sm">
                <CardHeader class="space-y-1">
                    <CardDescription class="text-xs uppercase tracking-wide text-slate-500">
                        {{ stat.title }}
                    </CardDescription>
                    <CardTitle class="text-3xl font-semibold text-slate-900">
                        {{ stat.value }}
                    </CardTitle>
                </CardHeader>
                <CardFooter>
                    <span class="text-sm font-medium text-emerald-600">
                        {{ stat.trend }}
                    </span>
                </CardFooter>
            </Card>
        </section>

        <section class="mt-8 grid gap-6 lg:grid-cols-3">
            <Card class="lg:col-span-2 border-slate-200 bg-white shadow-sm">
                <CardHeader>
                    <CardTitle>Aktivitas terbaru</CardTitle>
                    <CardDescription>Ringkasan aktivitas penting dalam 7 hari terakhir.</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="rounded-lg border border-slate-100 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                        3 vendor baru berhasil diverifikasi dan siap menerima PO.
                    </div>
                    <div class="rounded-lg border border-slate-100 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                        27 permintaan pengadaan sedang diproses dengan SLA di bawah 3 hari.
                    </div>
                    <div class="rounded-lg border border-slate-100 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                        5 pembayaran menunggu konfirmasi bank. Segera tindak lanjuti.
                    </div>
                </CardContent>
                <CardFooter>
                    <Button variant="ghost" class="text-slate-600">
                        Lihat log aktivitas
                    </Button>
                </CardFooter>
            </Card>

            <Card class="border-slate-200 bg-white shadow-sm">
                <CardHeader>
                    <CardTitle>Shortcut</CardTitle>
                    <CardDescription>Akses cepat tugas rutin harian.</CardDescription>
                </CardHeader>
                <CardContent class="space-y-3">
                    <div
                        v-for="shortcut in props.shortcuts"
                        :key="shortcut.title"
                        class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3"
                    >
                        <p class="text-sm font-semibold text-slate-800">{{ shortcut.title }}</p>
                        <p class="text-xs text-slate-500">{{ shortcut.description }}</p>
                        <Button variant="link" class="mt-1 px-0 text-sm" as-child>
                            <a :href="shortcut.href">Buka</a>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </section>
    </DashboardLayout>
</template>
