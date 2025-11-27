<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $stats = [
            [
                'title' => 'Vendor Terverifikasi',
                'value' => 84,
                'trend' => '+8%',
            ],
            [
                'title' => 'PO Aktif',
                'value' => 27,
                'trend' => '+2%',
            ],
            [
                'title' => 'Pembayaran Menunggu',
                'value' => 5,
                'trend' => '-3%',
            ],
            [
                'title' => 'Tiket Bantuan',
                'value' => 3,
                'trend' => '0%',
            ],
        ];

        $shortcuts = [
            [
                'title' => 'Kelola Produk',
                'description' => 'Tambah atau perbarui katalog produk UMKM',
                'href' => '#products',
            ],
            [
                'title' => 'Proses Pesanan',
                'description' => 'Pantau status pemesanan dan pembayaran',
                'href' => '#orders',
            ],
            [
                'title' => 'Kelola Vendor',
                'description' => 'Verifikasi vendor baru dan kelola dokumen',
                'href' => '#vendors',
            ],
        ];

        return Inertia::render('Dashboard/Index', [
            'stats' => $stats,
            'shortcuts' => $shortcuts,
        ]);
    }
}
