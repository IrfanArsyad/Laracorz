<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { FormModal } from '@/components/ui/Modal';
import ModuleForm from './ModuleForm.vue';
import type { Node, Option } from '../types';

const props = defineProps<{
    modelValue: boolean;
    node: Node | null;
    groups: Option[];
    modules: Option[];
    presetGroupId?: number | null;
    presetParentId?: number | null;
}>();

const emit = defineEmits<{ 'update:modelValue': [v: boolean] }>();

const { t } = useI18n();

const blankModule = {
    name: '',
    label: '',
    icon: '',
    module_group_id: null as number | null,
    parent_id: null as number | null,
    url: '',
    route_name: '',
    badge_source: '',
    extra_actions: [] as string[],
    order: 0,
    active: true,
    external: false,
};

const form = useForm({ ...blankModule });

watch(
    () => props.modelValue,
    (open) => {
        if (!open) return;
        form.clearErrors();
        if (props.node) {
            const n = props.node;
            Object.assign(form, {
                ...blankModule,
                name: n.name,
                label: n.label,
                icon: n.icon ?? '',
                module_group_id: n.module_group_id ?? null,
                parent_id: n.parent_id ?? null,
                url: n.url ?? '',
                route_name: n.route_name ?? '',
                badge_source: n.badge_source ?? '',
                extra_actions: n.extra_actions ?? [],
                order: n.order,
                active: n.active,
                external: n.external ?? false,
            });
        } else {
            Object.assign(form, blankModule, {
                module_group_id: props.presetGroupId ?? null,
                parent_id: props.presetParentId ?? null,
            });
        }
    },
);

function submit(): void {
    const opts = {
        preserveScroll: true,
        onSuccess: () => emit('update:modelValue', false),
    };
    if (props.node) {
        form.put(`/modules/${props.node.id}`, opts);
    } else {
        form.post('/modules', opts);
    }
}
</script>

<template>
    <FormModal
        :model-value="modelValue"
        :title="node ? t('modules.editModuleTitle', { label: node.label }) : t('modules.createModuleTitle')"
        :description="node ? t('modules.editModuleDesc') : t('modules.createModuleDesc')"
        size="xl"
        :processing="form.processing"
        @update:model-value="(v) => emit('update:modelValue', v)"
        @submit="submit"
        @cancel="emit('update:modelValue', false)"
    >
        <ModuleForm :form="form" :groups="groups" :modules="modules" />
    </FormModal>
</template>
