<script setup lang="ts">
import SellerDashboardLayout from '@/Layouts/SellerDashboardLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Separator } from '@/components/ui/separator';
import { CheckCircle2, User, Lock, Camera, X, Eye, EyeOff } from 'lucide-vue-next';

type ProfileData = {
  name: string;
  email: string;
  phone: string | null;
  avatar_url: string | null;
};

const props = defineProps<{
  profile: ProfileData;
}>();

defineOptions({
  layout: SellerDashboardLayout,
});

const page = usePage();
const flash = computed(() => (page.props.flash ?? {}) as Record<string, string>);
const flashSuccess = computed(() => flash.value.success ?? '');
const showSuccess = ref(!!flashSuccess.value);

watch(flashSuccess, (value) => {
  showSuccess.value = !!value;
  if (value) {
    setTimeout(() => {
      showSuccess.value = false;
    }, 3000);
  }
});

// Profile form
const profileForm = useForm({
  name: props.profile.name ?? '',
  phone: props.profile.phone ?? '',
  avatar: null as File | null,
});

const avatarPreview = ref<string | null>(props.profile.avatar_url);
const avatarInput = ref<HTMLInputElement | null>(null);

const handleAvatarChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0];
  if (file) {
    profileForm.avatar = file;
    avatarPreview.value = URL.createObjectURL(file);
  }
};

const removeAvatar = () => {
  profileForm.avatar = null;
  avatarPreview.value = props.profile.avatar_url;
  if (avatarInput.value) {
    avatarInput.value.value = '';
  }
};

const submitProfile = () => {
  profileForm.transform((data) => {
    const formData: Record<string, unknown> = {
      ...data,
      _method: 'PUT',
    };
    if (!formData.avatar) delete formData.avatar;
    return formData;
  }).post('/seller/profile', {
    preserveScroll: true,
    forceFormData: true,
  });
};

// Password form
const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const submitPassword = () => {
  passwordForm.put('/seller/profile/password', {
    preserveScroll: true,
    onSuccess: () => {
      passwordForm.reset();
    },
  });
};
</script>

<template>
  <div class="space-y-6 w-full px-2 sm:px-4 lg:px-6">

    <Head title="Profil Saya" />

    <div>
      <h1 class="text-2xl font-semibold text-slate-900">Profil Saya</h1>
      <p class="text-sm text-slate-500">Kelola informasi pribadi dan keamanan akun Anda.</p>
    </div>

    <Alert v-if="showSuccess && flashSuccess" variant="default"
      class="flex items-center gap-2 border-green-200 bg-green-50 text-green-700">
      <CheckCircle2 class="h-5 w-5 text-green-600" />
      <div class="flex flex-wrap gap-1 text-sm">
        <span class="font-semibold">Berhasil.</span>
        <span>{{ flashSuccess }}</span>
      </div>
    </Alert>

    <div class="grid gap-6 lg:grid-cols-2">
      <!-- Profile Information Card -->
      <Card>
        <CardHeader>
          <div class="flex items-center gap-2">
            <User class="h-5 w-5 text-indigo-600" />
            <CardTitle>Informasi Pribadi</CardTitle>
          </div>
          <CardDescription>Perbarui nama dan foto profil Anda.</CardDescription>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="submitProfile" class="space-y-4">
            <!-- Avatar Upload -->
            <div class="space-y-2">
              <Label>Foto Profil</Label>
              <div class="flex items-center gap-4">
                <div
                  class="relative h-16 w-16 overflow-hidden rounded-full border-2 border-dashed border-slate-200 bg-slate-50 hover:border-indigo-400 hover:bg-slate-100 transition-colors cursor-pointer flex-shrink-0"
                  @click="avatarInput?.click()">
                  <img v-if="avatarPreview" :src="avatarPreview" alt="Avatar" class="h-full w-full object-cover" />
                  <div v-else class="flex h-full flex-col items-center justify-center text-slate-400">
                    <Camera class="h-5 w-5" />
                  </div>
                  <button v-if="avatarPreview && profileForm.avatar" type="button" @click.stop="removeAvatar"
                    class="absolute -top-1 -right-1 rounded-full bg-red-500 p-0.5 text-white shadow-md hover:bg-red-600 transition-colors">
                    <X class="h-3 w-3" />
                  </button>
                </div>
                <div class="text-sm text-slate-500">
                  <p>JPG, PNG, WEBP. Max 2MB.</p>
                </div>
              </div>
              <input ref="avatarInput" type="file" accept="image/jpeg,image/png,image/webp" class="hidden"
                @change="handleAvatarChange" />
              <p v-if="profileForm.errors.avatar" class="text-xs text-red-600">{{ profileForm.errors.avatar }}</p>
            </div>

            <div class="space-y-2">
              <Label for="name">Nama Lengkap</Label>
              <Input id="name" v-model="profileForm.name" placeholder="Nama lengkap"
                :class="profileForm.errors.name ? 'border-red-500' : ''" />
              <p v-if="profileForm.errors.name" class="text-xs text-red-600">{{ profileForm.errors.name }}</p>
            </div>

            <div class="space-y-2">
              <Label for="email">Email</Label>
              <Input id="email" :value="profile.email" disabled class="bg-slate-50 text-slate-500" />
              <p class="text-xs text-slate-500">Email tidak dapat diubah.</p>
            </div>

            <div class="space-y-2">
              <Label for="phone">Nomor Telepon</Label>
              <Input id="phone" v-model="profileForm.phone" type="tel" placeholder="081234567890"
                :class="profileForm.errors.phone ? 'border-red-500' : ''" />
              <p v-if="profileForm.errors.phone" class="text-xs text-red-600">{{ profileForm.errors.phone }}</p>
            </div>

            <div class="pt-2">
              <Button type="submit" :disabled="profileForm.processing">
                {{ profileForm.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>

      <!-- Change Password Card -->
      <Card>
        <CardHeader>
          <div class="flex items-center gap-2">
            <Lock class="h-5 w-5 text-indigo-600" />
            <CardTitle>Ubah Kata Sandi</CardTitle>
          </div>
          <CardDescription>Pastikan menggunakan kata sandi yang kuat dan unik.</CardDescription>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="submitPassword" class="space-y-4">
            <div class="space-y-2">
              <Label for="current_password">Kata Sandi Saat Ini</Label>
              <div class="relative">
                <Input id="current_password" v-model="passwordForm.current_password"
                  :type="showCurrentPassword ? 'text' : 'password'" placeholder="Masukkan kata sandi saat ini"
                  :class="passwordForm.errors.current_password ? 'border-red-500 pr-10' : 'pr-10'" />
                <button type="button" @click="showCurrentPassword = !showCurrentPassword"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                  <Eye v-if="!showCurrentPassword" class="h-4 w-4" />
                  <EyeOff v-else class="h-4 w-4" />
                </button>
              </div>
              <p v-if="passwordForm.errors.current_password" class="text-xs text-red-600">
                {{ passwordForm.errors.current_password }}
              </p>
            </div>

            <Separator />

            <div class="space-y-2">
              <Label for="password">Kata Sandi Baru</Label>
              <div class="relative">
                <Input id="password" v-model="passwordForm.password" :type="showNewPassword ? 'text' : 'password'"
                  placeholder="Masukkan kata sandi baru"
                  :class="passwordForm.errors.password ? 'border-red-500 pr-10' : 'pr-10'" />
                <button type="button" @click="showNewPassword = !showNewPassword"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                  <Eye v-if="!showNewPassword" class="h-4 w-4" />
                  <EyeOff v-else class="h-4 w-4" />
                </button>
              </div>
              <p v-if="passwordForm.errors.password" class="text-xs text-red-600">{{ passwordForm.errors.password }}</p>
            </div>

            <div class="space-y-2">
              <Label for="password_confirmation">Konfirmasi Kata Sandi Baru</Label>
              <div class="relative">
                <Input id="password_confirmation" v-model="passwordForm.password_confirmation"
                  :type="showConfirmPassword ? 'text' : 'password'" placeholder="Ulangi kata sandi baru"
                  class="pr-10" />
                <button type="button" @click="showConfirmPassword = !showConfirmPassword"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                  <Eye v-if="!showConfirmPassword" class="h-4 w-4" />
                  <EyeOff v-else class="h-4 w-4" />
                </button>
              </div>
            </div>

            <div class="pt-2">
              <Button type="submit" :disabled="passwordForm.processing" variant="outline">
                {{ passwordForm.processing ? 'Memperbarui...' : 'Ubah Kata Sandi' }}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </div>
</template>
