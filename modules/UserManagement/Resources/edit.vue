<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/shared/PageHeader.vue';
import UserForm from './components/UserForm.vue';
import { FormActions } from '@/components/ui/FormActions';
import { Button } from '@/components/ui/Button';
import { Card, CardContent } from '@/components/ui/Card';

const props = defineProps<{
    user: {
        id: number;
        role_id: number | null;
        name: string;
        username: string | null;
        email: string;
        status: string;
        avatar: string | null;
        avatar_url?: string | null;
    };
    roles: Array<{ id: number; name: string; display_name: string }>;
}>();

const form = useForm({
    _method: 'put',
    role_id: props.user.role_id,
    name: props.user.name,
    username: props.user.username ?? '',
    email: props.user.email,
    password: '',
    password_confirmation: '',
    status: props.user.status,
    avatar: null as File | null,
});

function submit(): void {
    form.post(`/users/${props.user.id}`, { forceFormData: true });
}
</script>

<template>
    <Head title="Ubah Pengguna" />
    <AppLayout>
        <PageHeader
            :title="`Ubah Pengguna: ${user.name}`"
            :breadcrumbs="[{ label: 'Pengguna', href: '/users' }, { label: 'Ubah' }]"
        />

        <Card>
            <CardContent>
                <form @submit.prevent="submit">
                    <UserForm :form="form" :roles="roles" :is-edit="true" :avatar-url="user.avatar_url" />
                    <FormActions>
                        <Button as="link" href="/users" variant="ghost">Batal</Button>
                        <Button type="submit" :loading="form.processing">Simpan</Button>
                    </FormActions>
                </form>
            </CardContent>
        </Card>
    </AppLayout>
</template>
