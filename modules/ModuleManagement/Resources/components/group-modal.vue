<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { FormModal } from '@/components/ui/Modal';
import GroupForm from './GroupForm.vue';
import type { Group } from '../types';

const props = defineProps<{
    modelValue: boolean;
    group: Group | null;
}>();

const emit = defineEmits<{ 'update:modelValue': [v: boolean] }>();

const { t } = useI18n();

const blankGroup = {
    name: '',
    label: '',
    icon: '',
    order: 0,
    active: true,
};

const form = useForm({ ...blankGroup });

watch(
    () => props.modelValue,
    (open) => {
        if (!open) return;
        form.clearErrors();
        if (props.group) {
            const g = props.group;
            Object.assign(form, {
                ...blankGroup,
                name: g.name,
                label: g.label,
                icon: g.icon ?? '',
                order: g.order,
                active: g.active,
            });
        } else {
            Object.assign(form, blankGroup);
        }
    },
);

function submit(): void {
    const opts = {
        preserveScroll: true,
        onSuccess: () => emit('update:modelValue', false),
    };
    if (props.group) {
        form.put(`/modules/groups/${props.group.id}`, opts);
    } else {
        form.post('/modules/groups', opts);
    }
}
</script>

<template>
    <FormModal
        :model-value="modelValue"
        :title="group ? t('modules.editGroupTitle', { label: group.label }) : t('modules.createGroupTitle')"
        :description="group ? t('modules.editGroupDesc') : t('modules.createGroupDesc')"
        size="md"
        :processing="form.processing"
        @update:model-value="(v) => emit('update:modelValue', v)"
        @submit="submit"
        @cancel="emit('update:modelValue', false)"
    >
        <GroupForm :form="form" />
    </FormModal>
</template>
