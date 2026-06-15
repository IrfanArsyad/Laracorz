<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
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

const { t } = useI18n();

const groupOptions = computed(() => props.groups.map((g) => ({ label: g.label, value: g.id })));
const parentOptions = computed(() => [{ label: '— Root —', value: null }, ...props.modules.map((m) => ({ label: m.label, value: m.id }))]);
</script>

<template>
    <FormSection :title="t('modules.section.identity')">
        <FormField :label="t('modules.slug')" :error="form.errors.name" required>
            <Input v-model="form.name" :placeholder="t('modules.slugPlaceholder')" />
        </FormField>
        <FormField :label="t('common.label')" :error="form.errors.label" required>
            <Input v-model="form.label" :placeholder="t('modules.labelPlaceholder')" />
        </FormField>
        <FormField :label="t('common.icon')" :hint="t('modules.iconHint')">
            <IconPicker v-model="form.icon" />
        </FormField>
    </FormSection>

    <FormSection :title="t('modules.section.tree')">
        <FormField :label="t('modules.groupRootOnly')">
            <Select v-model="form.module_group_id" :options="groupOptions" clearable searchable />
        </FormField>
        <FormField :label="t('modules.parentForChild')">
            <Select v-model="form.parent_id" :options="parentOptions" clearable searchable />
        </FormField>
        <FormField :label="t('modules.urlLeafOnly')" :hint="t('modules.urlLeafHint')">
            <Input v-model="form.url" placeholder="/users" />
        </FormField>
        <FormField :label="t('modules.routeLeafOnly')">
            <Input v-model="form.route_name" placeholder="users.index" />
        </FormField>
        <FormField :label="t('modules.badgeSource')">
            <Input v-model="form.badge_source" :placeholder="t('modules.badgeSourceHint')" />
        </FormField>
        <FormField :label="t('common.order')">
            <InputNumber v-model="form.order" :min="0" :format="false" />
        </FormField>
        <FormField :label="t('common.status')">
            <div class="flex items-center gap-2">
                <Switch v-model="form.active" /> <span class="text-sm">{{ t('common.active') }}</span>
            </div>
        </FormField>
    </FormSection>
</template>
