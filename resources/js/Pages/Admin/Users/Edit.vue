<script setup lang="ts">
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

const props = defineProps<{
  user: {
    id: number;
    name: string;
    email: string;
    roles: string[];
  };
  roles: string[];
}>();

defineOptions({
  layout: AdminDashboardLayout,
});

const form = useForm({
  name: props.user.name,
  email: props.user.email,
  password: '',
  password_confirmation: '',
  roles: props.user.roles,
});

const submit = () => {
  form.put(`/admin/users/${props.user.id}`, {
    preserveScroll: true,
  });
};

const toggleRole = (role: string) => {
  if (form.roles.includes(role)) {
    form.roles = form.roles.filter((r) => r !== role);
  } else {
    form.roles.push(role);
  }
};
</script>

<template>
  <div class="space-y-6">

    <Head title="Edit User" />

    <!-- Header -->
    <div class="flex items-center justify-between gap-3">
      <h1 class="text-xl font-bold tracking-tight text-slate-900">Edit User</h1>
      <Button variant="outline" size="sm" @click="router.visit('/admin/users')" :disabled="form.processing">
        Kembali
      </Button>
    </div>

    <Card>
      <CardHeader>
        <CardTitle>Informasi User</CardTitle>
        <CardDescription>Edit informasi user {{ user.name }}</CardDescription>
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

          <div class="space-y-2">
            <Label for="password">Password (Kosongkan jika tidak ingin mengubah)</Label>
            <Input id="password" type="password" v-model="form.password"
              :class="{ 'border-red-500': form.errors.password }" />
            <p v-if="form.errors.password" class="text-xs text-red-600">{{ form.errors.password }}</p>
          </div>

          <div class="space-y-2">
            <Label for="password_confirmation">Konfirmasi Password</Label>
            <Input id="password_confirmation" type="password" v-model="form.password_confirmation" />
          </div>

          <div class="space-y-2">
            <Label>Roles</Label>
            <div class="space-y-2">
              <div v-for="role in roles" :key="role" class="flex items-center gap-2">
                <Checkbox :id="`role-${role}`" :checked="form.roles.includes(role)"
                  @update:checked="() => toggleRole(role)" />
                <Label :for="`role-${role}`" class="font-normal cursor-pointer">{{ role }}</Label>
              </div>
            </div>
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
