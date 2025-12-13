<script setup lang="ts">
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ArrowLeft } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
  roles: string[];
}>();

defineOptions({
  layout: AdminDashboardLayout,
});

const form = useForm({
  name: '',
  email: '',
  roles: [] as string[],
});

const allowedRoles = computed(() => props.roles.filter((role) => ['seller', 'superadmin'].includes(role)));
const selectedRole = ref<string>('');

watch(selectedRole, (val) => {
  form.roles = val ? [val] : [];
});

const submit = () => {
  form.post('/admin/users', {
    preserveScroll: true,
  });
};

</script>

<template>
  <div class="space-y-6">

    <Head title="Tambah User" />

    <!-- Header -->
    <div class="flex items-center gap-3">
      <Button variant="ghost" size="icon" as-child>
        <Link href="/admin/users">
        <ArrowLeft class="h-4 w-4" />
        </Link>
      </Button>
      <h1 class="text-2xl font-semibold text-slate-900">Tambah User Baru</h1>
    </div>

    <Card>
      <CardHeader>
        <CardTitle>Informasi User</CardTitle>
        <CardDescription>Tambahkan user baru ke sistem</CardDescription>
      </CardHeader>
      <CardContent>
        <form @submit.prevent="submit" class="space-y-4">
          <div class="space-y-2">
            <Label for="name">Nama</Label>
            <Input id="name" v-model="form.name" :class="{ 'border-red-500': form.errors.name }" />
            <p v-if="form.errors.name" class="text-xs text-red-600">{{ form.errors.name }}</p>
          </div>

          <div class="space-y-2">
            <Label for="email">Email</Label>
            <Input id="email" type="email" v-model="form.email" :class="{ 'border-red-500': form.errors.email }" />
            <p v-if="form.errors.email" class="text-xs text-red-600">{{ form.errors.email }}</p>
          </div>

          <p class="text-xs text-slate-500 bg-slate-50 border border-slate-200 rounded-md px-3 py-2">
            Password awal di-set otomatis (password123) dan user akan diminta membuat password baru via email verifikasi.
          </p>

          <div class="space-y-2">
            <Label>Role</Label>
            <select v-model="selectedRole" class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm">
              <option value="">Pilih role</option>
              <option v-for="role in allowedRoles" :key="role" :value="role">
                {{ role }}
              </option>
            </select>
            <p class="text-xs text-slate-500">Hanya dapat menambahkan role seller atau superadmin (customer daftar sendiri).</p>
            <p v-if="form.errors.roles" class="text-xs text-red-600">{{ form.errors.roles }}</p>
          </div>

          <div class="flex gap-3">
            <Button type="submit" :disabled="form.processing">
              Simpan
            </Button>
            <Button variant="outline" type="button" as-child>
              <Link href="/admin/users">
              Batal
              </Link>
            </Button>
          </div>
        </form>
      </CardContent>
    </Card>
  </div>
</template>
