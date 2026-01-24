<script setup lang="ts">
import SellerDashboardLayout from '@/Layouts/SellerDashboardLayout.vue';
import { Head } from '@inertiajs/vue3';

const agreementSections = [
  {
    title: '1. Tanggung Jawab Transaksi',
    content: 'Segala bentuk transaksi antara pembeli dan toko merupakan tanggung jawab penuh kedua belah pihak. Platform pkksulselmart.com tidak bertanggung jawab atas sengketa, kerugian, atau masalah yang timbul dari transaksi tersebut.',
    isAlert: true
  },
  {
    title: '2. Kewajiban Penjual',
    items: [
      'Memberikan informasi produk yang akurat dan jujur',
      'Memenuhi pesanan sesuai dengan deskripsi produk',
      'Berkomunikasi dengan pembeli secara profesional',
      'Mematuhi semua peraturan yang berlaku',
      'Menjaga reputasi platform dengan layanan terbaik'
    ]
  },
  {
    title: '3. Penyelesaian Sengketa',
    content: 'Penjual dan pembeli bertanggung jawab untuk menyelesaikan sengketa secara langsung. Platform hanya menyediakan sarana komunikasi dan tidak ikut serta dalam penyelesaian perselisihan.'
  },
  {
    title: '4. Ketentuan Lainnya',
    content: 'Penjual wajib mematuhi semua kebijakan platform dan undang-undang yang berlaku. Pelanggaran dapat mengakibatkan penangguhan atau penutupan akun.'
  }
];

const privacySections = [
  {
    title: '1. Informasi yang Kami Kumpulkan',
    content: 'Kami mengumpulkan data yang Anda berikan langsung saat mendaftar, seperti nama, email, nomor telepon, dan data usaha lainnya yang diperlukan untuk operasional toko.'
  },
  {
    title: '2. Penggunaan Informasi',
    content: 'Data Anda digunakan untuk memproses pendaftaran, memverifikasi akun, memudahkan komunikasi antara penjual dan pembeli, serta meningkatkan kualitas layanan platform kami.'
  },
  {
    title: '3. Keamanan Data',
    content: 'Kami menerapkan standar keamanan teknis dan organisasi untuk melindungi data Anda dari akses yang tidak sah, kehilangan, atau penyalahgunaan.'
  },
  {
    title: '4. Berbagi Informasi',
    content: 'Kami tidak akan menjual atau menyewakan data pribadi Anda kepada pihak ketiga. Informasi hanya dibagikan untuk tujuan transaksi atau jika diwajibkan oleh hukum.'
  }
];
</script>

<template>

  <Head title="Agreement" />

  <SellerDashboardLayout>
    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
      <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900">Perjanjian & Kebijakan</h1>
        <p class="mt-2 text-sm text-slate-500">Silakan baca syarat dan ketentuan serta kebijakan privasi kami.</p>
      </div>

      <div class="space-y-12">
        <!-- Seller Agreement -->
        <section>
          <div class="flex items-center gap-3 mb-6">
            <div class="p-2 bg-sky-50 rounded-lg">
              <svg class="h-6 w-6 text-sky-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                <polyline points="14 2 14 8 20 8" />
                <line x1="16" y1="13" x2="8" y2="13" />
                <line x1="16" y1="17" x2="8" y2="17" />
                <polyline points="10 9 9 9 8 9" />
              </svg>
            </div>
            <h2 class="text-xl font-bold text-slate-900">Perjanjian Penjual</h2>
          </div>

          <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-6 sm:p-8 space-y-6">
              <p class="text-sm text-slate-600 font-medium">Dengan terdaftar sebagai penjual di PKK Sulsel Mart, Anda menyetujui hal-hal berikut:</p>

              <div v-for="section in agreementSections" :key="section.title" class="space-y-3">
                <h3 class="font-semibold text-slate-900 flex items-center gap-2">
                  <span v-if="section.isAlert" class="text-amber-500">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                  </span>
                  {{ section.title }}
                </h3>

                <div v-if="section.isAlert" class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                  <p class="text-sm text-amber-900 leading-relaxed" v-html="section.content.replace('tidak bertanggung jawab', '<strong>tidak bertanggung jawab</strong>').replace('tanggung jawab penuh', '<strong>tanggung jawab penuh</strong>')"></p>
                </div>

                <p v-else-if="section.content" class="text-sm text-slate-600 leading-relaxed">
                  {{ section.content }}
                </p>

                <ul v-if="section.items" class="list-disc list-inside space-y-2 ml-2 text-sm text-slate-600">
                  <li v-for="item in section.items" :key="item">{{ item }}</li>
                </ul>
              </div>
            </div>
          </div>
        </section>

        <!-- Privacy Policy -->
        <section>
          <div class="flex items-center gap-3 mb-6">
            <div class="p-2 bg-emerald-50 rounded-lg">
              <svg class="h-6 w-6 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
              </svg>
            </div>
            <h2 class="text-xl font-bold text-slate-900">Kebijakan Privasi</h2>
          </div>

          <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-6 sm:p-8 space-y-6">
              <p class="text-sm text-slate-600">Di PKK Sulsel Mart, kami sangat menjaga privasi dan data pribadi pengguna kami. Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, dan melindungi informasi Anda.</p>

              <div class="grid gap-6 sm:grid-cols-2">
                <div v-for="section in privacySections" :key="section.title" class="space-y-2">
                  <h3 class="font-semibold text-slate-900 text-sm">{{ section.title }}</h3>
                  <p class="text-sm text-slate-600 leading-relaxed">
                    {{ section.content }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </SellerDashboardLayout>
</template>
