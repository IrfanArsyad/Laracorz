<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
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
        title: 'Hapus Akun?',
        message: 'Akun Anda akan dihapus permanen. Lanjutkan?',
        variant: 'destructive',
        confirmLabel: 'Hapus',
    });
    if (!ok) return;
    deleteForm.delete('/profile', {
        preserveScroll: true,
        onSuccess: () => deleteForm.reset(),
    });
}
</script>

<template>
    <Head title="Profil" />
    <AppLayout>
        <PageHeader title="Profil" description="Kelola informasi akun, kata sandi, dan sesi Anda." />

        <Card>
            <CardContent>
                <form @submit.prevent="saveProfile">
                    <FormSection title="Informasi Akun" description="Perbarui nama dan email akun Anda.">
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
                                    Unggah foto
                                </Button>
                            </div>
                        </div>
                        <FormField label="Nama" :error="profileForm.errors.name" required>
                            <Input v-model="profileForm.name" />
                        </FormField>
                        <FormField label="Username" :error="profileForm.errors.username" required>
                            <Input v-model="profileForm.username" autocomplete="off" />
                        </FormField>
                        <FormField label="Email" :error="profileForm.errors.email" required>
                            <Input v-model="profileForm.email" type="email" />
                        </FormField>
                        <p v-if="mustVerifyEmail && !user?.email_verified_at" class="text-xs text-warning">
                            Email Anda belum terverifikasi.
                        </p>
                    </FormSection>
                    <FormActions>
                        <Button type="submit" :loading="profileForm.processing">Simpan</Button>
                    </FormActions>
                </form>

                <form @submit.prevent="savePassword">
                    <FormSection title="Kata Sandi" description="Ubah kata sandi akun.">
                        <FormField label="Kata Sandi Saat Ini" :error="passwordForm.errors.current_password" required>
                            <InputPassword v-model="passwordForm.current_password" />
                        </FormField>
                        <FormField label="Kata Sandi Baru" :error="passwordForm.errors.password" required>
                            <InputPassword v-model="passwordForm.password" />
                        </FormField>
                        <FormField label="Konfirmasi Kata Sandi" required>
                            <InputPassword v-model="passwordForm.password_confirmation" />
                        </FormField>
                    </FormSection>
                    <FormActions>
                        <Button type="submit" :loading="passwordForm.processing">Ubah Kata Sandi</Button>
                    </FormActions>
                </form>

                <form @submit.prevent="logoutOthers">
                    <FormSection title="Sesi Browser" description="Lihat dan keluarkan sesi browser lain.">
                        <ul class="divide-y divide-border text-sm">
                            <li v-for="s in sessions" :key="s.id" class="py-2 flex justify-between">
                                <span>{{ s.ip_address }} <span class="text-xs text-muted-foreground">{{ s.user_agent }}</span></span>
                                <span v-if="s.current" class="text-xs text-success">Aktif</span>
                            </li>
                        </ul>
                        <FormField label="Kata Sandi" :error="sessionsForm.errors.password" required>
                            <InputPassword v-model="sessionsForm.password" />
                        </FormField>
                    </FormSection>
                    <FormActions>
                        <Button type="submit" variant="outline" :loading="sessionsForm.processing">Keluarkan Sesi Lain</Button>
                    </FormActions>
                </form>

                <FormSection title="Hapus Akun" description="Tindakan ini permanen dan tidak dapat dibatalkan.">
                    <FormField label="Kata Sandi" :error="deleteForm.errors.password" required>
                        <InputPassword v-model="deleteForm.password" />
                    </FormField>
                    <FormActions>
                        <Button variant="destructive" :loading="deleteForm.processing" @click="deleteAccount">
                            Hapus Akun Saya
                        </Button>
                    </FormActions>
                </FormSection>
            </CardContent>
        </Card>
    </AppLayout>
</template>
