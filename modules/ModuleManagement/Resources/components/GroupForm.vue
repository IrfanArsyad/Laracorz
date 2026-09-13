<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { FormField } from '@/components/ui/FormField';
import { Input } from '@/components/ui/Input';
import { InputNumber } from '@/components/ui/InputNumber';
import { Switch } from '@/components/ui/Switch';
import { IconPicker } from '@/components/ui/IconPicker';

const props = defineProps<{ form: Record<string, any> }>();
const { t } = useI18n();

// Inertia's useForm object is shared reactive state passed by reference from the
// parent; binding to a local alias lets child fields update it without tripping
// vue/no-mutating-props while preserving the exact same two-way behavior.
const model = props.form;
</script>

<template>
    <div class="space-y-4">
        <FormField :label="t('modules.slug')" :error="model.errors.name" required :hint="t('modules.slugHint')">
            <Input v-model="model.name" placeholder="main" />
        </FormField>

        <FormField :label="t('common.label')" :error="model.errors.label" required :hint="t('modules.labelHint')">
            <Input v-model="model.label" placeholder="Main" />
        </FormField>

        <FormField :label="t('common.icon')" :hint="t('modules.iconHint')">
            <IconPicker v-model="model.icon" />
        </FormField>

        <div class="grid gap-3.5 sm:grid-cols-2">
            <FormField :label="t('common.order')" :hint="t('modules.orderHint')">
                <InputNumber v-model="model.order" :min="0" :format="false" />
            </FormField>
            <FormField :label="t('common.status')">
                <label class="inline-flex h-10 items-center gap-2 cursor-pointer">
                    <Switch v-model="model.active" />
                    <span class="text-sm">{{ model.active ? t('common.active') : t('common.inactive') }}</span>
                </label>
            </FormField>
        </div>
    </div>
</template>
