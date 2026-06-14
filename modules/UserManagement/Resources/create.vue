<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/shared/PageHeader.vue';
import UserForm from './components/UserForm.vue';
import { FormActions } from '@/components/ui/FormActions';
import { Button } from '@/components/ui/Button';
import { Card, CardContent } from '@/components/ui/Card';

defineProps<{ roles: Array<{ id: number; name: string; display_name: string }> }>();

const form = useForm({
    role_id: null as number | null,
    name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    status: 'active',
    avatar: null as File | null,
});

function submit(): void {
    form.post('/users', { forceFormData: true });
}
</script>

<template>
    <Head title="Tambah Pengguna" />
    <AppLayout>
        <PageHeader
            title="Tambah Pengguna"
            :breadcrumbs="[{ label: 'Pengguna', href: '/users' }, { label: 'Tambah' }]"
        />

        <Card>
            <CardContent>
                <form @submit.prevent="submit">
                    <UserForm :form="form" :roles="roles" />
                    <FormActions>
                        <Button as="link" href="/users" variant="ghost">Batal</Button>
                        <Button type="submit" :loading="form.processing">Simpan</Button>
                    </FormActions>
                </form>
            </CardContent>
        </Card>
    </AppLayout>
</template>
