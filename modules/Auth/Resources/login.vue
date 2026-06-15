<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { AtSign } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { FormField } from '@/components/ui/FormField';
import { Input } from '@/components/ui/Input';
import { InputPassword } from '@/components/ui/InputPassword';
import { Checkbox } from '@/components/ui/Checkbox';
import { Button } from '@/components/ui/Button';

defineProps<{ canResetPassword?: boolean; status?: string }>();

const { t } = useI18n();

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
    <Head :title="t('auth.login')" />
    <AuthLayout :title="t('auth.loginTitle')">
        <p v-if="status" class="mb-4 text-sm font-medium text-[var(--status-success-fg)]">{{ status }}</p>

        <form class="space-y-4" @submit.prevent="submit">
            <FormField
                :label="t('auth.emailOrUsername')"
                :error="form.errors.login"
                :hint="t('auth.emailOrUsernameHint')"
                required
            >
                <Input
                    v-model="form.login"
                    type="text"
                    required
                    autocomplete="username"
                    :placeholder="t('auth.emailOrUsernamePlaceholder')"
                >
                    <template #prefix>
                        <AtSign class="h-4 w-4" />
                    </template>
                </Input>
            </FormField>

            <FormField :label="t('auth.password')" :error="form.errors.password" required>
                <InputPassword v-model="form.password" autocomplete="current-password" />
            </FormField>

            <div class="flex items-center justify-between">
                <label class="inline-flex items-center gap-2 text-sm cursor-pointer">
                    <Checkbox v-model="form.remember" />
                    <span>{{ t('auth.rememberMe') }}</span>
                </label>
                <a v-if="canResetPassword" href="/forgot-password" class="text-sm font-medium text-[var(--text-link)] hover:text-[var(--text-link-hover)] hover:underline">
                    {{ t('auth.forgot') }}
                </a>
            </div>

            <Button type="submit" :loading="form.processing" class="w-full">{{ t('auth.login') }}</Button>
        </form>
    </AuthLayout>
</template>
