<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { AtSign } from 'lucide-vue-next';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { FormField } from '@/components/ui/FormField';
import { Input } from '@/components/ui/Input';
import { InputPassword } from '@/components/ui/InputPassword';
import { Checkbox } from '@/components/ui/Checkbox';
import { Button } from '@/components/ui/Button';

defineProps<{ canResetPassword?: boolean; status?: string }>();

const form = useForm({
    login: '',
    password: '',
    remember: false,
});

function submit(): void {
    form.post('/login', {
        preserveScroll: true,
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <Head title="Masuk" />
    <AuthLayout title="Masuk ke Akun Anda">
        <p v-if="status" class="mb-4 text-sm font-medium text-[var(--status-success-fg)]">{{ status }}</p>

        <form class="space-y-4" @submit.prevent="submit">
            <FormField
                label="Email atau Username"
                :error="form.errors.login"
                hint="Bisa pakai alamat email atau username Anda."
                required
            >
                <Input
                    v-model="form.login"
                    type="text"
                    required
                    autocomplete="username"
                    placeholder="admin@example.com atau admin"
                >
                    <template #prefix>
                        <AtSign class="h-4 w-4" />
                    </template>
                </Input>
            </FormField>

            <FormField label="Kata Sandi" :error="form.errors.password" required>
                <InputPassword v-model="form.password" autocomplete="current-password" />
            </FormField>

            <div class="flex items-center justify-between">
                <label class="inline-flex items-center gap-2 text-sm cursor-pointer">
                    <Checkbox v-model="form.remember" />
                    <span>Ingat saya</span>
                </label>
                <a v-if="canResetPassword" href="/forgot-password" class="text-sm font-medium text-[var(--text-link)] hover:text-[var(--text-link-hover)] hover:underline">
                    Lupa kata sandi?
                </a>
            </div>

            <Button type="submit" :loading="form.processing" class="w-full">Masuk</Button>
        </form>
    </AuthLayout>
</template>
