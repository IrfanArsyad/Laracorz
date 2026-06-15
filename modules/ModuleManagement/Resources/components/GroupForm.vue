<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { FormField } from '@/components/ui/FormField';
import { Input } from '@/components/ui/Input';
import { InputNumber } from '@/components/ui/InputNumber';
import { Switch } from '@/components/ui/Switch';
import { IconPicker } from '@/components/ui/IconPicker';

defineProps<{ form: Record<string, any> }>();
const { t } = useI18n();
</script>

<template>
    <div class="space-y-4">
        <FormField :label="t('modules.slug')" :error="form.errors.name" required :hint="t('modules.slugHint')">
            <Input v-model="form.name" placeholder="main" />
        </FormField>

        <FormField :label="t('common.label')" :error="form.errors.label" required :hint="t('modules.labelHint')">
            <Input v-model="form.label" placeholder="Main" />
        </FormField>

        <FormField :label="t('common.icon')" :hint="t('modules.iconHint')">
            <IconPicker v-model="form.icon" />
        </FormField>

        <div class="grid gap-3.5 sm:grid-cols-2">
            <FormField :label="t('common.order')" :hint="t('modules.orderHint')">
                <InputNumber v-model="form.order" :min="0" :format="false" />
            </FormField>
            <FormField :label="t('common.status')">
                <label class="inline-flex h-10 items-center gap-2 cursor-pointer">
                    <Switch v-model="form.active" />
                    <span class="text-sm">{{ form.active ? t('common.active') : t('common.inactive') }}</span>
                </label>
            </FormField>
        </div>
    </div>
</template>
