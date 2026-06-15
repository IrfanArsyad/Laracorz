<script setup lang="ts">
import { computed } from 'vue';
import { FormField } from '@/components/ui/FormField';
import { FormSection } from '@/components/ui/FormSection';
import { Input } from '@/components/ui/Input';
import { Select } from '@/components/ui/Select';
import { Switch } from '@/components/ui/Switch';
import { InputNumber } from '@/components/ui/InputNumber';
import { IconPicker } from '@/components/ui/IconPicker';

const props = defineProps<{
    form: Record<string, any>;
    groups: Array<{ id: number; name: string; label: string }>;
    modules: Array<{ id: number; name: string; label: string }>;
}>();

const groupOptions = computed(() => props.groups.map((g) => ({ label: g.label, value: g.id })));
const parentOptions = computed(() => [{ label: '— Root —', value: null }, ...props.modules.map((m) => ({ label: m.label, value: m.id }))]);
</script>

<template>
    <FormSection title="Identitas">
        <FormField label="Slug" :error="form.errors.name" required>
            <Input v-model="form.name" placeholder="contoh: user-management" />
        </FormField>
        <FormField label="Label" :error="form.errors.label" required>
            <Input v-model="form.label" placeholder="contoh: Pengguna" />
        </FormField>
        <FormField label="Icon (lucide)" hint="cari & pilih ikon visual">
            <IconPicker v-model="form.icon" />
        </FormField>
    </FormSection>

    <FormSection title="Tree & Routing">
        <FormField label="Grup (root only)">
            <Select v-model="form.module_group_id" :options="groupOptions" clearable searchable />
        </FormField>
        <FormField label="Parent (untuk child)">
            <Select v-model="form.parent_id" :options="parentOptions" clearable searchable />
        </FormField>
        <FormField label="URL (leaf only)" hint="kosongkan jika container">
            <Input v-model="form.url" placeholder="/users" />
        </FormField>
        <FormField label="Route Name (leaf only)">
            <Input v-model="form.route_name" placeholder="users.index" />
        </FormField>
        <FormField label="Badge Source">
            <Input v-model="form.badge_source" placeholder="(opsional)" />
        </FormField>
        <FormField label="Urutan">
            <InputNumber v-model="form.order" :min="0" :format="false" />
        </FormField>
        <FormField label="Status">
            <div class="flex items-center gap-2">
                <Switch v-model="form.active" /> <span class="text-sm">Aktif</span>
            </div>
        </FormField>
    </FormSection>
</template>
