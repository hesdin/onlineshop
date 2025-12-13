<script setup lang="ts">
import LandingLayout from '@/Layouts/LandingLayout.vue';
import CustomerSidebarMenu from '@/Components/Customer/SidebarMenu.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
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
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { CheckCircle2, HomeIcon, MapPin } from 'lucide-vue-next';
import RegionSelector from '@/Components/RegionSelector.vue';

const props = defineProps({
  addresses: {
    type: Array,
    default: () => [],
  },
});

const page = usePage();
const successMessage = computed(() => (page.props.flash as any)?.success ?? null);

const showDialog = ref(false);
const editing = ref<any | null>(null);

const form = useForm({
  id: null as number | null,
  label: '',
  recipient_name: '',
  phone: '',
  province_id: null as number | null,
  city_id: null as number | null,
  district_id: null as number | null,
  postal_code: '',
  address_line: '',
  is_default: false,
  note: '',
});

const openCreate = () => {
  editing.value = null;
  form.reset();
  form.clearErrors();
  form.is_default = props.addresses.length === 0;
  showDialog.value = true;
};

const openEdit = (address: any) => {
  editing.value = address;
  form.reset();
  form.clearErrors();
  form.id = address.id;
  form.label = address.label;
  form.recipient_name = address.recipient_name;
  form.phone = address.phone;
  form.province_id = address.province_id ?? null;
  form.city_id = address.city_id ?? null;
  form.district_id = address.district_id ?? null;
  form.postal_code = address.postal_code ?? '';
  form.address_line = address.address_line;
  form.is_default = address.is_default ?? false;
  form.note = address.note ?? '';
  showDialog.value = true;
};

const submit = () => {
  if (editing.value) {
    form.put(`/customer/dashboard/address/${editing.value.id}`, {
      onSuccess: () => {
        showDialog.value = false;
      },
    });
  } else {
    form.post('/customer/dashboard/address', {
      onSuccess: () => {
        showDialog.value = false;
      },
    });
  }
};

const makeDefault = (address: any) => {
  form.reset();
  form.is_default = true;
  form.postal_code = address.postal_code;
  form.address_line = address.address_line;
  form.label = address.label;
  form.recipient_name = address.recipient_name;
  form.phone = address.phone;
  form.province_id = address.province_id;
  form.city_id = address.city_id;
  form.district_id = address.district_id;
  form.note = address.note;
  form.put(`/customer/dashboard/address/${address.id}`, {
    onSuccess: () => form.reset(),
  });
};

const deleteAddress = (address: any) => {
  router.delete(`/customer/dashboard/address/${address.id}`);
};

const badge = (address: any) => address.is_default ? 'Alamat Utama' : '';

defineOptions({
  layout: LandingLayout,
});
</script>

<template>
  <div class="bg-slate-50">

    <Head title="Alamat Pengiriman" />
    <div class="mx-auto flex max-w-screen-2xl flex-col gap-6 px-6 py-10">
      <nav class="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
        <a href="/" class="text-sky-600 hover:underline">Beranda</a>
        <span>/</span>
        <span class="text-slate-900">Alamat Pengiriman</span>
      </nav>

      <div class="grid gap-6 lg:grid-cols-[260px_minmax(0,1fr)]">
        <CustomerSidebarMenu active-key="alamat" />

        <main class="space-y-6">
          <Alert v-if="successMessage" variant="default" class="flex items-start gap-3 border-green-200 bg-green-50">
            <CheckCircle2 class="h-5 w-5 text-emerald-600" />
            <div>
              <AlertTitle class="text-green-800">Berhasil</AlertTitle>
              <AlertDescription class="text-green-700">{{ successMessage }}</AlertDescription>
            </div>
          </Alert>

          <Card class="border-slate-200 bg-white shadow-sm">
            <CardHeader class="flex flex-wrap items-center justify-between gap-3">
              <div>
                <CardTitle>Alamat Pengiriman</CardTitle>
                <CardDescription>Kelola alamat untuk mempermudah pengiriman pesanan.</CardDescription>
              </div>
              <Button class="bg-sky-600 hover:bg-sky-700" @click="openCreate">
                Tambah Alamat
              </Button>
            </CardHeader>
            <CardContent class="space-y-3">
              <div v-if="!addresses.length"
                class="rounded-lg border border-dashed border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                Belum ada alamat tersimpan.
              </div>

              <div v-for="address in addresses" :key="address.id"
                class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                  <div class="space-y-1">
                    <div class="flex items-center gap-2">
                      <h3 class="text-sm font-semibold text-slate-900">{{ address.label }}</h3>
                      <span v-if="badge(address)"
                        class="rounded-full bg-sky-100 px-2 py-0.5 text-[11px] font-semibold text-sky-700">{{
                        badge(address) }}</span>
                    </div>
                    <p class="text-sm text-slate-600">{{ address.recipient_name }} • {{ address.phone }}</p>
                    <p class="text-sm text-slate-600 flex items-center gap-2">
                      <MapPin class="h-4 w-4 text-slate-400" />
                      <span>
                        {{ address.address_line }}
                        <template v-if="address.district">, {{ address.district }}</template>
                        <template v-if="address.city">, {{ address.city }}</template>
                        <template v-if="address.province">, {{ address.province }}</template>
                        <template v-if="address.postal_code">, {{ address.postal_code }}</template>
                      </span>
                    </p>
                  </div>
                  <div class="flex flex-wrap gap-2 text-xs">
                    <Button variant="outline" size="sm" @click="openEdit(address)">Ubah</Button>
                    <Button variant="outline" size="sm" @click="makeDefault(address)" :disabled="address.is_default">
                      Jadikan Utama
                    </Button>
                    <Button variant="destructive" size="sm" @click="deleteAddress(address)">
                      Hapus
                    </Button>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <Dialog :open="showDialog" @update:open="(val) => (showDialog.value = val)">
            <DialogContent class="max-w-3xl">
              <DialogHeader>
                <DialogTitle>{{ editing ? 'Ubah Alamat' : 'Tambah Alamat' }}</DialogTitle>
              </DialogHeader>

              <form class="space-y-4" @submit.prevent="submit">
                <div class="grid gap-4 md:grid-cols-2">
                  <div class="space-y-2">
                    <Label>Label</Label>
                    <Input v-model="form.label" placeholder="Rumah / Kantor" :disabled="form.processing" />
                    <p v-if="form.errors.label" class="text-sm text-red-500">{{ form.errors.label }}</p>
                  </div>
                  <div class="space-y-2">
                    <Label>Nama Penerima</Label>
                    <Input v-model="form.recipient_name" placeholder="Nama" :disabled="form.processing" />
                    <p v-if="form.errors.recipient_name" class="text-sm text-red-500">{{ form.errors.recipient_name }}
                    </p>
                  </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                  <div class="space-y-2">
                    <Label>No. Telepon</Label>
                    <Input v-model="form.phone" placeholder="08xxxxxxxxxx" :disabled="form.processing" />
                    <p v-if="form.errors.phone" class="text-sm text-red-500">{{ form.errors.phone }}</p>
                  </div>
                  <div class="flex items-center gap-2">
                    <input id="is_default" type="checkbox" v-model="form.is_default" :disabled="form.processing" />
                    <Label for="is_default" class="text-sm">Jadikan alamat utama</Label>
                  </div>
                </div>

                <RegionSelector v-model:provinceId="form.province_id" v-model:cityId="form.city_id"
                  v-model:districtId="form.district_id" :show-district="true" :disabled="form.processing" :errors="{
                    provinceId: form.errors.province_id,
                    cityId: form.errors.city_id,
                    districtId: form.errors.district_id,
                  }" />

                <div class="space-y-2">
                  <Label>Kode Pos</Label>
                  <Input v-model="form.postal_code" placeholder="Kode Pos" :disabled="form.processing" />
                  <p v-if="form.errors.postal_code" class="text-sm text-red-500">{{ form.errors.postal_code }}</p>
                </div>

                <div class="space-y-2">
                  <Label>Alamat Lengkap</Label>
                  <Input v-model="form.address_line" placeholder="Jalan, RT/RW, nomor rumah"
                    :disabled="form.processing" />
                  <p v-if="form.errors.address_line" class="text-sm text-red-500">{{ form.errors.address_line }}</p>
                </div>

                <div class="space-y-2">
                  <Label>Catatan</Label>
                  <Input v-model="form.note" placeholder="Patokan atau catatan kurir" :disabled="form.processing" />
                  <p v-if="form.errors.note" class="text-sm text-red-500">{{ form.errors.note }}</p>
                </div>

                <DialogFooter class="flex items-center justify-end gap-2">
                  <Button type="button" variant="outline" @click="showDialog = false" :disabled="form.processing">
                    Batal
                  </Button>
                  <Button type="submit" :disabled="form.processing">
                    {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                  </Button>
                </DialogFooter>
              </form>
            </DialogContent>
          </Dialog>
        </main>
      </div>
    </div>
  </div>
</template>
