<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Switch } from '@/components/ui/switch';
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

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Masuk" />

    <div class="flex min-h-screen flex-col items-center justify-center bg-slate-50 px-4 py-10">
        <div class="mb-8 text-center">
            <Link href="/" class="text-sm font-semibold text-sky-600 hover:text-sky-700">Kembali ke beranda</Link>
            <h1 class="mt-3 text-3xl font-bold text-slate-900">Masuk ke Dashboard</h1>
            <p class="text-sm text-slate-500">Gunakan akun Super Admin untuk mengelola marketplace.</p>
        </div>

        <Card class="w-full max-w-md border-slate-200 shadow-md">
            <CardHeader>
                <CardTitle>Selamat datang kembali</CardTitle>
                <CardDescription>Masuk dengan email dan kata sandi terdaftar.</CardDescription>
            </CardHeader>
            <CardContent>
                <form class="space-y-5" @submit.prevent="submit">
                    <div class="space-y-2">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            autocomplete="email"
                            placeholder="superadmin@example.com"
                            type="email"
                            :disabled="form.processing"
                        />
                        <p v-if="form.errors.email" class="text-sm text-red-500">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="password">Kata Sandi</Label>
                        <Input
                            id="password"
                            v-model="form.password"
                            autocomplete="current-password"
                            type="password"
                            placeholder="••••••••"
                            :disabled="form.processing"
                        />
                        <p v-if="form.errors.password" class="text-sm text-red-500">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-800">Ingat saya</p>
                            <p class="text-xs text-slate-500">Tetap login di perangkat ini.</p>
                        </div>
                        <Switch v-model:checked="form.remember" :disabled="form.processing" />
                    </div>

                    <Button class="w-full" type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Memproses...' : 'Masuk' }}
                    </Button>

                    <p v-if="form.errors.email && !form.errors.password" class="text-center text-sm text-red-500">
                        {{ form.errors.email }}
                    </p>
                </form>
            </CardContent>
            <CardFooter class="flex flex-col gap-2 text-center text-xs text-slate-500">
                <p>Gunakan akun Super Admin yang dibuat melalui seeder.</p>
                <p>
                    Belum punya akses? Hubungi tim administrator untuk mendapatkan kredensial.
                </p>
            </CardFooter>
        </Card>
    </div>
</template>
