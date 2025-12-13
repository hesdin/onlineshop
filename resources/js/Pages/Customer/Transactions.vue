<script setup>
import LandingLayout from '@/Layouts/LandingLayout.vue';
import CustomerSidebarMenu from '@/Components/Customer/SidebarMenu.vue';
import { Head } from '@inertiajs/vue3';

defineOptions({
    layout: LandingLayout,
});

const props = defineProps({
    orders: {
        type: Array,
        default: () => [],
    },
    statusOptions: {
        type: Array,
        default: () => [],
    },
    paymentStatusOptions: {
        type: Array,
        default: () => [],
    },
});

const statusLabel = (value) => props.statusOptions.find((item) => item.value === value)?.label ?? value ?? '-';
const paymentLabel = (value) => props.paymentStatusOptions.find((item) => item.value === value)?.label ?? value ?? '-';

const statusBadgeClass = (value) => {
    switch (value) {
        case 'pending_payment':
            return 'bg-amber-100 text-amber-700';
        case 'processing':
        case 'shipped':
            return 'bg-sky-100 text-sky-700';
        case 'delivered':
        case 'completed':
            return 'bg-emerald-100 text-emerald-700';
        case 'cancelled':
            return 'bg-rose-100 text-rose-700';
        default:
            return 'bg-slate-100 text-slate-700';
    }
};

const formatPrice = (value) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(
        Number(value ?? 0),
    );

const formatDate = (value) => {
    if (!value) return '-';
    const date = new Date(value);
    return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};
</script>

<template>
    <div class="bg-slate-50">
        <Head title="Daftar Transaksi" />

        <div class="mx-auto flex max-w-screen-2xl flex-col gap-6 px-6 py-10">
            <nav class="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
                <a href="/" class="text-sky-600 hover:underline">Beranda</a>
                <span>/</span>
                <span class="text-slate-900">Daftar Transaksi</span>
            </nav>

            <div class="grid gap-6 lg:grid-cols-[260px_minmax(0,1fr)]">
                <CustomerSidebarMenu active-key="transaksi" />

                <main class="space-y-6">
                    <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h2 class="text-xl font-semibold text-slate-900">Daftar Transaksi</h2>
                                <p class="text-sm text-slate-500">Lihat status pesanan dan riwayat transaksi.</p>
                            </div>
                            <div class="flex gap-2">
                                <button
                                    class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                                    Filter
                                </button>
                                <button
                                    class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                                    Ekspor
                                </button>
                            </div>
                        </div>

                        <div v-if="props.orders.length" class="mt-6 space-y-3">
                            <div v-for="order in props.orders" :key="order.id"
                                class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">No. Pesanan {{ order.order_number }}</p>
                                        <p class="text-xs text-slate-500">Tanggal: {{ formatDate(order.created_at) }}</p>
                                        <p v-if="order.store" class="text-xs text-slate-500">Toko: {{ order.store }}</p>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <span
                                            class="rounded-full px-3 py-1 text-xs font-semibold"
                                            :class="statusBadgeClass(order.status)">
                                            {{ statusLabel(order.status) }}
                                        </span>
                                        <span
                                            class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                            {{ paymentLabel(order.payment_status) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-3 flex flex-wrap items-center gap-4 text-sm text-slate-600">
                                    <span>Total: <strong class="text-slate-900">{{ formatPrice(order.grand_total) }}</strong></span>
                                    <span>Jumlah Barang: {{ order.items_count ?? 0 }}</span>
                                </div>
                                <div class="mt-3 flex gap-2 text-sm">
                                    <button
                                        class="rounded-lg border border-slate-200 px-3 py-2 text-slate-600 hover:bg-slate-50">
                                        Detail
                                    </button>
                                    <button v-if="order.status === 'pending_payment'"
                                        class="rounded-lg border border-sky-500 bg-sky-500 px-3 py-2 text-white hover:bg-sky-600">
                                        Bayar Sekarang
                                    </button>
                                    <button v-else
                                        class="rounded-lg border border-slate-200 px-3 py-2 text-slate-600 hover:bg-slate-50">
                                        Beli Lagi
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div v-else class="mt-6 rounded-lg border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-600">
                            Belum ada transaksi.
                        </div>
                    </section>
                </main>
            </div>
        </div>
    </div>
</template>
