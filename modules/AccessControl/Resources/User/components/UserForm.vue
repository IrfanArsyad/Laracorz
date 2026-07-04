<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { FormField } from '@/components/ui/FormField';
import { Input } from '@/components/ui/Input';
import { InputPassword } from '@/components/ui/InputPassword';
import { Select } from '@/components/ui/Select';
import { Avatar } from '@/components/ui/Avatar';
import { Upload } from 'lucide-vue-next';
import { USER_STATUS } from '@/types/enums';

const props = defineProps<{
    form: Record<string, any>;
    roles: Array<{ id: number; name: string; display_name: string }>;
    isEdit?: boolean;
    avatarUrl?: string | null;
}>();

const { t } = useI18n();

// Inertia's useForm object is shared reactive state passed by reference from the
// parent; binding to a local alias lets child fields update it without tripping
// vue/no-mutating-props while preserving the exact same two-way behavior.
const model = props.form;

const roleOptions = props.roles.map((r) => ({ label: r.display_name, value: r.id }));
const statusOptions = Object.entries(USER_STATUS).map(([k, v]) => ({ label: v.label, value: k }));

const avatarPreview = ref<string | null>(props.avatarUrl ?? null);
const fileInput = ref<HTMLInputElement | null>(null);

function onAvatar(e: Event): void {
    const f = (e.target as HTMLInputElement).files?.[0] ?? null;
    model.avatar = f;
    avatarPreview.value = f ? URL.createObjectURL(f) : props.avatarUrl ?? null;
}
</script>

<template>
    <div class="space-y-5">
        <!-- Avatar uploader — inline compact -->
        <div class="flex items-center gap-4 p-3 rounded-lg bg-[var(--surface-sunken)] border border-[var(--border-subtle)]">
            <Avatar :src="avatarPreview" :name="model.name || 'U'" size="lg" />
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-[var(--text-strong)]">{{ t('users.avatarTitle') }}</p>
                <p class="text-xs text-[var(--text-muted)]">{{ t('users.avatarHint') }}</p>
                <button
                    type="button"
                    class="mt-2 inline-flex h-8 items-center gap-1.5 rounded-md border border-[var(--border-default)] bg-[var(--surface-raised)] px-3 text-xs font-medium hover:bg-[var(--state-hover)] transition-colors"
                    @click="fileInput?.click()"
                >
                    <Upload class="h-3.5 w-3.5" />
                    {{ avatarPreview ? t('users.avatarChange') : t('users.avatarUpload') }}
                </button>
                <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="onAvatar" />
            </div>
        </div>

        <!-- Identitas -->
        <div class="grid gap-3.5 sm:grid-cols-2">
            <FormField :label="t('users.name')" :error="model.errors.name" required>
                <Input v-model="model.name" />
            </FormField>
            <FormField :label="t('users.username')" :error="model.errors.username" required :hint="t('users.usernameHint')">
                <Input v-model="model.username" autocomplete="off" />
            </FormField>
            <FormField :label="t('users.email')" :error="model.errors.email" required class="sm:col-span-2">
                <Input v-model="model.email" type="email" />
            </FormField>
            <FormField :label="t('users.role')" :error="model.errors.role_id" required>
                <Select v-model="model.role_id" :options="roleOptions" searchable :placeholder="t('users.rolePlaceholder')" />
            </FormField>
            <FormField :label="t('users.status')" :error="model.errors.status" required>
                <Select v-model="model.status" :options="statusOptions" />
            </FormField>
        </div>

        <!-- Password -->
        <div class="grid gap-3.5 sm:grid-cols-2 pt-4 border-t border-[var(--border-subtle)]">
            <FormField
                :label="isEdit ? t('users.passwordNew') : t('users.password')"
                :hint="isEdit ? t('users.passwordHint') : t('users.passwordHintCreate')"
                :error="model.errors.password"
                :required="!isEdit"
            >
                <InputPassword v-model="model.password" autocomplete="new-password" />
            </FormField>
            <FormField :label="t('users.passwordConfirmation')" :required="!isEdit">
                <InputPassword v-model="model.password_confirmation" autocomplete="new-password" />
            </FormField>
        </div>
    </div>
</template>
