<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { DetailModal } from '@/components/ui/Modal';
import type { Node } from '../types';

const props = defineProps<{
    modelValue: boolean;
    node: Node | null;
}>();

const emit = defineEmits<{ 'update:modelValue': [v: boolean] }>();

const { t } = useI18n();

const items = computed(() => {
    const n = props.node;
    if (!n) return [];
    return [
        { label: t('common.slug'), value: n.name },
        { label: t('common.url'), value: n.url ?? t('modules.detail.container') },
        { label: t('common.route'), value: n.route_name ?? '—' },
        { label: t('modules.detail.type'), value: n.is_leaf ? t('modules.typeLeafFull') : t('modules.typeContainerFull') },
        { label: t('modules.detail.icon'), value: n.icon ?? '—' },
        { label: t('modules.detail.order'), value: n.order },
        { label: t('modules.detail.status'), value: n.active ? t('common.active') : t('common.inactive') },
    ];
});
</script>

<template>
    <DetailModal
        :model-value="modelValue"
        :title="node?.label ?? t('common.detail')"
        :description="node?.name"
        :items="items"
        @update:model-value="(v) => emit('update:modelValue', v)"
    />
</template>
