<script setup lang="ts">
import { ref } from 'vue';

const showModal = ref(false);
</script>

<template>
  <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
    <div class="flex items-start gap-4">
      <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-amber-100">
        <svg class="h-6 w-6 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
      </div>
      <div class="flex-1 min-w-0">
        <h3 class="font-semibold text-slate-900">Perjanjian Penjual</h3>
        <p class="mt-1 text-sm text-slate-600">Platform tidak bertanggung jawab atas transaksi antara pembeli dan toko.</p>
        <button @click="showModal = true" class="mt-3 inline-flex items-center gap-1 text-sm font-medium text-sky-600 hover:text-sky-700">
          Lihat Detail
          <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Agreement Modal -->
    <Teleport to="body">
      <div v-if="showModal"
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 px-4 backdrop-blur-sm"
        @click.self="showModal = false">
        <div class="relative w-full max-w-2xl transform rounded-lg bg-white p-8 shadow-2xl">
          <button @click="showModal = false"
            class="absolute right-4 top-4 rounded-full p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/>
            </svg>
          </button>

          <h2 class="text-2xl font-bold text-slate-900 mb-4">Perjanjian Penjual</h2>

          <div class="max-h-96 overflow-y-auto pr-2 space-y-4 text-sm text-slate-700">
            <p class="font-semibold text-slate-900">Dengan mendaftar sebagai penjual di PKK Sulsel Mart, Anda menyetujui hal-hal berikut:</p>

            <div class="space-y-4">
              <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                <h3 class="font-semibold text-amber-900 mb-2 flex items-center gap-2">
                  <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                  </svg>
                  1. Tanggung Jawab Transaksi
                </h3>
                <p class="text-amber-900"><strong>Segala bentuk transaksi antara pembeli dan toko merupakan tanggung jawab penuh kedua belah pihak.</strong> Platform pkksulselmart.com <strong>tidak bertanggung jawab</strong> atas sengketa, kerugian, atau masalah yang timbul dari transaksi tersebut.</p>
              </div>

              <div>
                <h3 class="font-semibold text-slate-900 mb-2">2. Kewajiban Penjual</h3>
                <ul class="list-disc list-inside space-y-1.5 ml-2 text-slate-700">
                  <li>Memberikan informasi produk yang akurat dan jujur</li>
                  <li>Memenuhi pesanan sesuai dengan deskripsi produk</li>
                  <li>Berkomunikasi dengan pembeli secara profesional</li>
                  <li>Mematuhi semua peraturan yang berlaku</li>
                  <li>Menjaga reputasi platform dengan layanan terbaik</li>
                </ul>
              </div>

              <div>
                <h3 class="font-semibold text-slate-900 mb-2">3. Penyelesaian Sengketa</h3>
                <p>Penjual dan pembeli bertanggung jawab untuk menyelesaikan sengketa secara langsung. Platform hanya menyediakan sarana komunikasi dan tidak ikut serta dalam penyelesaian perselisihan.</p>
              </div>

              <div>
                <h3 class="font-semibold text-slate-900 mb-2">4. Ketentuan Lainnya</h3>
                <p>Penjual wajib mematuhi semua kebijakan platform dan undang-undang yang berlaku. Pelanggaran dapat mengakibatkan penangguhan atau penutupan akun.</p>
              </div>
            </div>
          </div>

          <div class="mt-6 flex justify-end">
            <button @click="showModal = false"
              class="rounded-lg bg-sky-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-sky-700">
              Tutup
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>
