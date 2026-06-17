<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/shared/PageHeader.vue';
import { FormField } from '@/components/ui/FormField';
import { FormSection } from '@/components/ui/FormSection';
import { FormActions } from '@/components/ui/FormActions';
import { Input } from '@/components/ui/Input';
import { InputPassword } from '@/components/ui/InputPassword';
import { Button } from '@/components/ui/Button';
import { Card, CardContent } from '@/components/ui/Card';
import { Avatar } from '@/components/ui/Avatar';
import { useConfirm } from '@/composables/useConfirm';
import type { User } from '@/types';
import { computed, ref } from 'vue';

const props = defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
    sessions?: Array<{ id: string; ip_address: string; user_agent: string; last_activity: number; current: boolean }>;
}>();

const { t } = useI18n();

const page = usePage();
const user = computed<User | null>(() => (page.props.auth as { user: User | null }).user);
const avatarFile = ref<File | null>(null);

const profileForm = useForm({
    name: user.value?.name ?? '',
    username: user.value?.username ?? '',
    email: user.value?.email ?? '',
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const sessionsForm = useForm({ password: '' });
const deleteForm = useForm({ password: '' });

function saveProfile(): void {
    profileForm.patch('/profile', { preserveScroll: true });
}

function savePassword(): void {
    passwordForm.put('/profile/password', {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    });
}

function uploadAvatar(): void {
    if (!avatarFile.value) return;
    const f = useForm({ avatar: avatarFile.value });
    f.post('/profile/avatar', { forceFormData: true, preserveScroll: true });
}

function logoutOthers(): void {
    sessionsForm.delete('/profile/sessions', {
        preserveScroll: true,
        onSuccess: () => sessionsForm.reset(),
    });
}

const { confirm } = useConfirm();

async function deleteAccount(): Promise<void> {
    const ok = await confirm({
        title: t('profile.deleteAccountTitle'),
        message: t('profile.deleteAccountMessage'),
        variant: 'destructive',
        confirmLabel: t('common.delete'),
    });
    if (!ok) return;
    deleteForm.delete('/profile', {
        preserveScroll: true,
        onSuccess: () => deleteForm.reset(),
    });
}
</script>

<template>
    <Head :title="t('profile.title')" />
    <AppLayout>
        <PageHeader :title="t('profile.title')" :description="t('profile.description')" />

        <Card>
            <CardContent>
                <form @submit.prevent="saveProfile">
                    <FormSection :title="t('profile.section.account')" :description="t('profile.section.accountDesc')">
                        <div class="flex items-center gap-4">
                            <Avatar :src="user?.avatar_url" :name="user?.name" size="lg" />
                            <div class="flex-1">
                                <input
                                    type="file"
                                    accept="image/*"
                                    class="block w-full text-sm"
                                    @change="(e) => (avatarFile = (e.target as HTMLInputElement).files?.[0] ?? null)"
                                />
                                <Button v-if="avatarFile" size="sm" class="mt-2" @click="uploadAvatar">
                                    {{ t('profile.uploadAvatar') }}
                                </Button>
                            </div>
                        </div>
                        <FormField :label="t('profile.name')" :error="profileForm.errors.name" required>
                            <Input v-model="profileForm.name" />
                        </FormField>
                        <FormField :label="t('profile.username')" :error="profileForm.errors.username" required>
                            <Input v-model="profileForm.username" autocomplete="off" />
                        </FormField>
                        <FormField :label="t('profile.email')" :error="profileForm.errors.email" required>
                            <Input v-model="profileForm.email" type="email" />
                        </FormField>
                        <p v-if="mustVerifyEmail && !user?.email_verified_at" class="text-xs text-warning">
                            {{ t('profile.emailNotVerified') }}
                        </p>
                    </FormSection>
                    <FormActions>
                        <Button type="submit" :loading="profileForm.processing">{{ t('profile.save') }}</Button>
                    </FormActions>
                </form>

                <form @submit.prevent="savePassword">
                    <FormSection :title="t('profile.section.password')" :description="t('profile.section.passwordDesc')">
                        <FormField :label="t('profile.currentPassword')" :error="passwordForm.errors.current_password" required>
                            <InputPassword v-model="passwordForm.current_password" autocomplete="current-password" />
                        </FormField>
                        <FormField :label="t('profile.newPassword')" :error="passwordForm.errors.password" required>
                            <InputPassword v-model="passwordForm.password" autocomplete="new-password" />
                        </FormField>
                        <FormField :label="t('profile.passwordConfirmation')" required>
                            <InputPassword v-model="passwordForm.password_confirmation" autocomplete="new-password" />
                        </FormField>
                    </FormSection>
                    <FormActions>
                        <Button type="submit" :loading="passwordForm.processing">{{ t('profile.changePassword') }}</Button>
                    </FormActions>
                </form>

                <form @submit.prevent="logoutOthers">
                    <FormSection :title="t('profile.section.sessions')" :description="t('profile.section.sessionsDesc')">
                        <ul class="divide-y divide-border text-sm">
                            <li v-for="s in sessions" :key="s.id" class="py-2 flex justify-between">
                                <span>{{ s.ip_address }} <span class="text-xs text-muted-foreground">{{ s.user_agent }}</span></span>
                                <span v-if="s.current" class="text-xs text-success">{{ t('profile.sessionCurrent') }}</span>
                            </li>
                        </ul>
                        <FormField :label="t('profile.password')" :error="sessionsForm.errors.password" required>
                            <InputPassword v-model="sessionsForm.password" autocomplete="current-password" />
                        </FormField>
                    </FormSection>
                    <FormActions>
                        <Button type="submit" variant="outline" :loading="sessionsForm.processing">{{ t('profile.logoutOthers') }}</Button>
                    </FormActions>
                </form>

                <FormSection :title="t('profile.section.deleteAccount')" :description="t('profile.section.deleteAccountDesc')">
                    <FormField :label="t('profile.password')" :error="deleteForm.errors.password" required>
                        <InputPassword v-model="deleteForm.password" autocomplete="current-password" />
                    </FormField>
                    <FormActions>
                        <Button variant="destructive" :loading="deleteForm.processing" @click="deleteAccount">
                            {{ t('profile.deleteAccount') }}
                        </Button>
                    </FormActions>
                </FormSection>
            </CardContent>
        </Card>
    </AppLayout>
</template>
