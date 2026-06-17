<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { Lock, AlertCircle } from 'lucide-vue-next';
import { FormModal } from '@/components/ui/Modal';
import { FormField } from '@/components/ui/FormField';
import { Input } from '@/components/ui/Input';
import { Textarea } from '@/components/ui/Textarea';
import { Switch } from '@/components/ui/Switch';
import PermissionMatrix from './PermissionMatrix.vue';

interface RoleRow {
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
}

interface MatrixGroup {
    id: number;
    name: string;
    label: string;
    modules: Array<{
        id: number;
        name: string;
        label: string;
        is_leaf: boolean;
        extra_actions: string[];
        children: unknown[];
    }>;
}

const props = defineProps<{
    modelValue: boolean;
    role: RoleRow | null;
    matrix?: MatrixGroup[];
}>();

const emit = defineEmits<{ 'update:modelValue': [v: boolean] }>();

const { t } = useI18n();

const blankForm = {
    name: '',
    display_name: '',
    description: '',
    is_active: true,
    /*
     * Field di-prefix `perm_` supaya tidak bentrok dengan Inertia useForm
     * built-in methods (`form.delete()` HTTP DELETE). Sebelum submit,
     * transform balik ke nama backend (read/create/update/delete).
     */
    perm_read: [] as Array<number | string>,
    perm_create: [] as Array<number | string>,
    perm_update: [] as Array<number | string>,
    perm_delete: [] as Array<number | string>,
    extra: {} as Record<string, string[]>,
};

const form = useForm<typeof blankForm & { _method?: string }>({ ...blankForm });

form.transform((data) => ({
    name: data.name,
    display_name: data.display_name,
    description: data.description,
    is_active: data.is_active,
    read: data.perm_read,
    create: data.perm_create,
    update: data.perm_update,
    delete: data.perm_delete,
    extra: data.extra,
    ...(data._method ? { _method: data._method } : {}),
}));

const isEditing = computed(() => !!props.role);
const isSuperAdmin = computed(() => props.role?.name === 'super-admin');

// Isi form dari role terpilih setiap modal dibuka (null = create).
watch(
    () => props.modelValue,
    (open) => {
        if (!open) return;
        form.clearErrors();
        if (props.role) {
            const r = props.role;
            Object.assign(form, {
                ...blankForm,
                _method: 'put',
                name: r.name,
                display_name: r.display_name,
                description: r.description ?? '',
                is_active: r.is_active,
                perm_read: Array.isArray(r.read) ? r.read : [],
                perm_create: Array.isArray(r.create) ? r.create : [],
                perm_update: Array.isArray(r.update) ? r.update : [],
                perm_delete: Array.isArray(r.delete) ? r.delete : [],
                extra: r.extra ?? {},
            });
        } else {
            Object.assign(form, blankForm);
            delete (form as { _method?: string })._method;
        }
    },
);

function submit(): void {
    const opts = {
        preserveScroll: true,
        onSuccess: () => emit('update:modelValue', false),
    };
    if (props.role) {
        form.post(`/roles/${props.role.id}`, opts);
    } else {
        form.post('/roles', opts);
    }
}
</script>

<template>
    <FormModal
        :model-value="modelValue"
        :title="isEditing ? t('roles.editTitle', { name: role?.display_name }) : t('roles.createNew')"
        :description="isEditing ? t('roles.modalEditDesc') : t('roles.modalCreateDesc')"
        size="2xl"
        :processing="form.processing"
        @update:model-value="(v) => emit('update:modelValue', v)"
        @submit="submit"
        @cancel="emit('update:modelValue', false)"
    >
        <!-- Banner protection super-admin -->
        <div
            v-if="isSuperAdmin"
            class="mb-4 flex items-start gap-2 rounded-md border border-[var(--status-warning-border)] bg-[var(--status-warning-bg)] p-3 text-sm text-[var(--status-warning-fg)]"
        >
            <Lock class="h-4 w-4 shrink-0 mt-px" />
            <p>{{ t('roles.superAdminBanner', { role: 'super-admin' }) }}</p>
        </div>

        <!-- Identitas role -->
        <div class="grid gap-3.5 sm:grid-cols-2">
            <FormField :label="t('roles.name')" :hint="t('roles.nameHint')" :error="form.errors.name" required>
                <Input v-model="form.name" :placeholder="t('roles.namePlaceholder')" :disabled="isSuperAdmin" />
            </FormField>
            <FormField :label="t('roles.displayName')" :error="form.errors.display_name" required>
                <Input v-model="form.display_name" :placeholder="t('roles.displayNamePlaceholder')" :disabled="isSuperAdmin" />
            </FormField>
            <FormField :label="t('roles.description2')" class="sm:col-span-2">
                <Textarea v-model="form.description" :rows="2" :disabled="isSuperAdmin" />
            </FormField>
            <FormField :label="t('common.status')">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <Switch v-model="form.is_active" :disabled="isSuperAdmin" />
                    <span class="text-sm">{{ form.is_active ? t('roles.statusActive') : t('roles.statusInactive') }}</span>
                </label>
            </FormField>
        </div>

        <!-- Matrix permission -->
        <div class="mt-6 pt-5 border-t border-[var(--border-subtle)]">
            <div class="flex items-start justify-between gap-3 mb-3">
                <div>
                    <h3 class="text-sm font-semibold text-[var(--text-strong)]">{{ t('roles.permissionMatrixTitle') }}</h3>
                    <p class="text-sm text-[var(--text-muted)]">{{ t('roles.permissionMatrixDesc') }}</p>
                </div>
            </div>

            <div v-if="!matrix" class="flex items-center gap-2 text-sm text-[var(--text-muted)] py-8 justify-center">
                <AlertCircle class="h-4 w-4" />
                {{ t('roles.loadingMatrix') }}
            </div>
            <PermissionMatrix
                v-else
                :matrix="matrix as never"
                v-model:model-read="form.perm_read"
                v-model:model-create="form.perm_create"
                v-model:model-update="form.perm_update"
                v-model:model-delete="form.perm_delete"
                v-model:model-extra="form.extra"
            />
        </div>
    </FormModal>
</template>
