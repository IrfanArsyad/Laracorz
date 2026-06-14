<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
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

defineProps<{ matrix: Array<{ id: number; name: string; label: string; modules: unknown[] }> }>();

const form = useForm({
    name: '',
    display_name: '',
    description: '',
    is_active: true,
    read: [] as Array<number | string>,
    create: [] as Array<number | string>,
    update: [] as Array<number | string>,
    delete: [] as Array<number | string>,
    extra: {} as Record<string, string[]>,
});

function submit(): void {
    form.post('/roles');
}
</script>

<template>
    <Head title="Tambah Role" />
    <AppLayout>
        <PageHeader
            title="Tambah Role"
            :breadcrumbs="[{ label: 'Role', href: '/roles' }, { label: 'Tambah' }]"
        />

        <Card>
            <CardContent>
                <form @submit.prevent="submit">
                    <FormSection title="Informasi Role" description="Detail dasar role.">
                        <FormField label="Slug" hint="huruf kecil, tanpa spasi" :error="form.errors.name" required>
                            <Input v-model="form.name" placeholder="contoh: editor" />
                        </FormField>
                        <FormField label="Nama Tampilan" :error="form.errors.display_name" required>
                            <Input v-model="form.display_name" placeholder="contoh: Editor Konten" />
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

                    <FormSection title="Matriks Izin" description="Tentukan modul mana saja yang boleh diakses role ini.">
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
