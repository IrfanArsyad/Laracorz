<script setup lang="ts">
import { ref } from 'vue';
import { FormField } from '@/components/ui/FormField';
import { FormSection } from '@/components/ui/FormSection';
import { Input } from '@/components/ui/Input';
import { InputPassword } from '@/components/ui/InputPassword';
import { Select } from '@/components/ui/Select';
import { Avatar } from '@/components/ui/Avatar';
import { USER_STATUS } from '@/types/enums';

const props = defineProps<{
    form: Record<string, any>;
    roles: Array<{ id: number; name: string; display_name: string }>;
    isEdit?: boolean;
    avatarUrl?: string | null;
}>();

const roleOptions = props.roles.map((r) => ({ label: r.display_name, value: r.id }));
const statusOptions = Object.entries(USER_STATUS).map(([k, v]) => ({ label: v.label, value: k }));

const avatarPreview = ref<string | null>(props.avatarUrl ?? null);

function onAvatar(e: Event): void {
    const f = (e.target as HTMLInputElement).files?.[0] ?? null;
    props.form.avatar = f;
    avatarPreview.value = f ? URL.createObjectURL(f) : null;
}
</script>

<template>
    <FormSection title="Informasi Akun" description="Identitas dan akses pengguna.">
        <FormField label="Nama" :error="form.errors.name" required>
            <Input v-model="form.name" />
        </FormField>
        <FormField label="Username" hint="Huruf, angka, strip, garis bawah." :error="form.errors.username" required>
            <Input v-model="form.username" autocomplete="off" />
        </FormField>
        <FormField label="Email" :error="form.errors.email" required>
            <Input v-model="form.email" type="email" />
        </FormField>
        <FormField label="Role" :error="form.errors.role_id" required>
            <Select v-model="form.role_id" :options="roleOptions" searchable />
        </FormField>
        <FormField label="Status" :error="form.errors.status" required>
            <Select v-model="form.status" :options="statusOptions" />
        </FormField>
        <FormField :label="isEdit ? 'Kata Sandi Baru (opsional)' : 'Kata Sandi'" :error="form.errors.password" :required="!isEdit">
            <InputPassword v-model="form.password" />
        </FormField>
        <FormField label="Konfirmasi Kata Sandi" :required="!isEdit">
            <InputPassword v-model="form.password_confirmation" />
        </FormField>
    </FormSection>

    <FormSection title="Foto" description="Unggah foto profil.">
        <div class="flex items-center gap-4">
            <Avatar :src="avatarPreview" :name="form.name" size="lg" />
            <input type="file" accept="image/*" class="block text-sm" @change="onAvatar" />
        </div>
    </FormSection>
</template>
