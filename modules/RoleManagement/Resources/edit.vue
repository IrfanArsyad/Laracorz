<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/shared/PageHeader.vue';
import PermissionMatrix from './components/PermissionMatrix.vue';
import { FormField } from '@/components/ui/FormField';
import { FormSection } from '@/components/ui/FormSection';
import { FormActions } from '@/components/ui/FormActions';
import { Input } from '@/components/ui/Input';
import { Textarea } from '@/components/ui/Textarea';
import { Switch } from '@/components/ui/Switch';
import { Button } from '@/components/ui/Button';
import { Card, CardContent } from '@/components/ui/Card';

const props = defineProps<{
    role: {
        id: number;
        name: string;
        display_name: string;
        description: string | null;
        is_active: boolean;
        read: Array<number | string> | null;
        create: Array<number | string> | null;
        update: Array<number | string> | null;
        delete: Array<number | string> | null;
        extra: Record<string, string[]> | null;
    };
    matrix: Array<{ id: number; name: string; label: string; modules: unknown[] }>;
}>();

const form = useForm({
    name: props.role.name,
    display_name: props.role.display_name,
    description: props.role.description ?? '',
    is_active: props.role.is_active,
    read: props.role.read ?? [],
    create: props.role.create ?? [],
    update: props.role.update ?? [],
    delete: props.role.delete ?? [],
    extra: props.role.extra ?? {},
});

function submit(): void {
    form.put(`/roles/${props.role.id}`);
}
</script>

<template>
    <Head title="Ubah Role" />
    <AppLayout>
        <PageHeader
            :title="`Ubah Role: ${role.display_name}`"
            :breadcrumbs="[{ label: 'Role', href: '/roles' }, { label: 'Ubah' }]"
        />

        <Card>
            <CardContent>
                <form @submit.prevent="submit">
                    <FormSection title="Informasi Role">
                        <FormField label="Slug" :error="form.errors.name" required>
                            <Input v-model="form.name" />
                        </FormField>
                        <FormField label="Nama Tampilan" :error="form.errors.display_name" required>
                            <Input v-model="form.display_name" />
                        </FormField>
                        <FormField label="Deskripsi">
                            <Textarea v-model="form.description" :rows="2" />
                        </FormField>
                        <FormField label="Status">
                            <div class="flex items-center gap-2">
                                <Switch v-model="form.is_active" /> <span class="text-sm">Aktif</span>
                            </div>
                        </FormField>
                    </FormSection>

                    <FormSection title="Matriks Izin">
                        <PermissionMatrix
                            :matrix="matrix as never"
                            v-model:model-read="form.read"
                            v-model:model-create="form.create"
                            v-model:model-update="form.update"
                            v-model:model-delete="form.delete"
                            v-model:model-extra="form.extra"
                        />
                    </FormSection>

                    <FormActions>
                        <Button as="link" href="/roles" variant="ghost">Batal</Button>
                        <Button type="submit" :loading="form.processing">Simpan</Button>
                    </FormActions>
                </form>
            </CardContent>
        </Card>
    </AppLayout>
</template>
