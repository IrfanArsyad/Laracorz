<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { Save, Image as ImageIcon, X } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/shared/PageHeader.vue';
import { Card, CardContent } from '@/components/ui/Card';
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/Tabs';
import { FormField } from '@/components/ui/FormField';
import { Input } from '@/components/ui/Input';
import { Textarea } from '@/components/ui/Textarea';
import { Switch } from '@/components/ui/Switch';
import { Select } from '@/components/ui/Select';
import { Button } from '@/components/ui/Button';
import { FormActions } from '@/components/ui/FormActions';
import { useNavLabel } from '@/composables/useNavLabel';

interface SettingRow {
    key: string;
    label: string;
    type: string;
    description: string | null;
}

const props = defineProps<{
    groups: Record<string, SettingRow[]>;
    values: Record<string, unknown>;
}>();

const { t } = useI18n();
const { settingsGroup, settingsField } = useNavLabel();

const tab = ref(Object.keys(props.groups)[0] ?? 'general');

// Nilai setting bertipe dinamis: input yang dirender ditentukan `row.type` saat
// runtime (text/email/number/select/color → string|number, switch → boolean, dst),
// sehingga tipe statik per-binding tak bisa dipersempit. `any` di sini disengaja
// agar v-model tiap input valid tanpa cast per-baris di template.
// eslint-disable-next-line @typescript-eslint/no-explicit-any
type SettingValue = any;

// _method: 'put' → spoofing agar multipart (file upload) tetap sampai ke route PUT /settings.
interface SettingsForm {
    _method: 'put';
    values: Record<string, SettingValue>;
    files: Record<string, File | null>;
}

const form = useForm<SettingsForm>({
    _method: 'put',
    values: { ...props.values } as Record<string, SettingValue>,
    files: {},
});

const filePreviews = ref<Record<string, string>>({});

/**
 * Opsi static untuk setting type=select. Key = setting.key, value = list opsi.
 * Tidak di-store di DB supaya migration tetap simple — kalau perlu dinamis,
 * pindah ke kolom Setting.options (jsonb) nanti.
 */
const SETTING_OPTIONS: Record<string, Array<{ label: string; value: string }>> = {
    'app.timezone': [
        { label: 'Asia/Jakarta (WIB)', value: 'Asia/Jakarta' },
        { label: 'Asia/Makassar (WITA)', value: 'Asia/Makassar' },
        { label: 'Asia/Jayapura (WIT)', value: 'Asia/Jayapura' },
        { label: 'Asia/Singapore', value: 'Asia/Singapore' },
        { label: 'UTC', value: 'UTC' },
    ],
    'app.locale': [
        { label: 'English', value: 'en' },
        { label: 'Indonesia', value: 'id' },
    ],
    'mail.encryption': [
        { label: 'TLS', value: 'tls' },
        { label: 'SSL', value: 'ssl' },
        { label: 'None', value: '' },
    ],
    'theme.default': [
        { label: 'System (auto)', value: 'system' },
        { label: 'Light', value: 'light' },
        { label: 'Dark', value: 'dark' },
    ],
};

function optionsOf(key: string) {
    return SETTING_OPTIONS[key] ?? [];
}

function onFileChange(key: string, e: Event): void {
    const file = (e.target as HTMLInputElement).files?.[0] ?? null;
    form.files[key] = file;
    if (file) {
        const reader = new FileReader();
        reader.onload = () => (filePreviews.value[key] = reader.result as string);
        reader.readAsDataURL(file);
    } else {
        delete filePreviews.value[key];
    }
}

function clearFile(key: string): void {
    form.files[key] = null;
    delete filePreviews.value[key];
    // Tandai sebagai dihapus
    form.values[key] = null;
}

function previewOf(key: string): string | null {
    if (filePreviews.value[key]) return filePreviews.value[key];
    const v = props.values[key];
    if (typeof v === 'string' && v) {
        return v.startsWith('http') || v.startsWith('/') ? v : `/storage/${v}`;
    }
    return null;
}

const tabsList = computed(() => Object.keys(props.groups));

function submit(): void {
    form.post('/settings', { forceFormData: true });
}
</script>

<template>
    <Head :title="t('settings.title')" />
    <AppLayout>
        <PageHeader :title="t('settings.title')" :description="t('settings.descriptionLong')" />

        <form class="mt-6" @submit.prevent="submit">
            <Card>
                <CardContent class="!p-0">
                    <Tabs v-model="tab">
                        <div class="border-b border-[var(--border-subtle)] px-5 py-4">
                            <TabsList class="max-w-full overflow-x-auto">
                                <TabsTrigger v-for="g in tabsList" :key="g" :value="g">
                                    {{ settingsGroup(g) }}
                                </TabsTrigger>
                            </TabsList>
                        </div>

                        <TabsContent v-for="(rows, group) in groups" :key="group" :value="group" class="!mt-0 px-5 py-5">
                            <div class="space-y-5 max-w-2xl">
                                <FormField
                                    v-for="row in rows"
                                    :key="row.key"
                                    :label="settingsField(row.key, row.label ?? row.key)"
                                    :hint="row.description ?? undefined"
                                >
                                    <!-- Text -->
                                    <Input
                                        v-if="row.type === 'text'"
                                        v-model="form.values[row.key]"
                                    />

                                    <!-- Email -->
                                    <Input
                                        v-else-if="row.type === 'email'"
                                        v-model="form.values[row.key]"
                                        type="email"
                                    />

                                    <!-- Number -->
                                    <Input
                                        v-else-if="row.type === 'number'"
                                        v-model="form.values[row.key]"
                                        type="number"
                                    />

                                    <!-- Textarea -->
                                    <Textarea
                                        v-else-if="row.type === 'textarea'"
                                        v-model="form.values[row.key]"
                                        :rows="4"
                                    />

                                    <!-- Boolean -->
                                    <div v-else-if="row.type === 'boolean'" class="flex items-center gap-2">
                                        <Switch v-model="form.values[row.key]" />
                                        <span class="text-sm text-[var(--text-muted)]">
                                            {{ form.values[row.key] ? t('common.active') : t('common.inactive') }}
                                        </span>
                                    </div>

                                    <!-- Select -->
                                    <Select
                                        v-else-if="row.type === 'select'"
                                        v-model="form.values[row.key]"
                                        :options="optionsOf(row.key)"
                                    />

                                    <!-- Color -->
                                    <div v-else-if="row.type === 'color'" class="flex items-center gap-2">
                                        <input
                                            v-model="form.values[row.key]"
                                            type="color"
                                            class="h-9 w-12 rounded-md border border-[var(--border-default)] bg-transparent cursor-pointer"
                                        />
                                        <Input
                                            v-model="form.values[row.key]"
                                            class="font-mono max-w-[140px]"
                                            placeholder="#000000"
                                        />
                                    </div>

                                    <!-- Image -->
                                    <div v-else-if="row.type === 'image'" class="space-y-2">
                                        <div
                                            v-if="previewOf(row.key)"
                                            class="relative inline-flex h-20 w-20 items-center justify-center rounded-md border border-[var(--border-default)] bg-[var(--surface-sunken)] overflow-hidden"
                                        >
                                            <img :src="previewOf(row.key)!" :alt="row.label" class="max-h-full max-w-full object-contain" />
                                            <button
                                                type="button"
                                                class="absolute -top-2 -right-2 flex h-5 w-5 items-center justify-center rounded-full bg-[var(--status-danger-bg)] text-[var(--status-danger-fg)] hover:bg-[var(--status-danger-fg)] hover:text-[var(--brand-fg)] transition-colors"
                                                :aria-label="t('common.delete')"
                                                @click="clearFile(row.key)"
                                            >
                                                <X class="h-3 w-3" />
                                            </button>
                                        </div>
                                        <div
                                            v-else
                                            class="inline-flex h-20 w-20 items-center justify-center rounded-md border border-dashed border-[var(--border-default)] bg-[var(--surface-sunken)] text-[var(--text-muted)]"
                                        >
                                            <ImageIcon class="h-5 w-5" />
                                        </div>
                                        <input
                                            type="file"
                                            accept="image/png,image/jpeg,image/webp,image/svg+xml,image/x-icon"
                                            class="block text-xs file:mr-2 file:rounded-md file:border-0 file:bg-[var(--surface-sunken)] file:px-2.5 file:py-1.5 file:text-xs file:font-medium hover:file:bg-[var(--state-hover)]"
                                            @change="onFileChange(row.key, $event)"
                                        />
                                    </div>

                                    <!-- Fallback -->
                                    <Input
                                        v-else
                                        v-model="form.values[row.key]"
                                    />
                                </FormField>
                            </div>
                        </TabsContent>
                    </Tabs>
                </CardContent>

                <FormActions class="border-t border-[var(--border-subtle)] px-5 py-3.5">
                    <Button type="submit" :loading="form.processing">
                        <Save class="h-4 w-4" />
                        {{ t('settings.save') }}
                    </Button>
                </FormActions>
            </Card>
        </form>
    </AppLayout>
</template>
