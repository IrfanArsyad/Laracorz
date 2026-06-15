<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/shared/PageHeader.vue';
import { Card, CardContent } from '@/components/ui/Card';
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/Tabs';
import { FormField } from '@/components/ui/FormField';
import { Input } from '@/components/ui/Input';
import { Textarea } from '@/components/ui/Textarea';
import { Switch } from '@/components/ui/Switch';
import { Button } from '@/components/ui/Button';
import { FormActions } from '@/components/ui/FormActions';
import { useNavLabel } from '@/composables/useNavLabel';

const props = defineProps<{ groups: Record<string, Array<{ key: string; label: string; type: string; description: string | null }>>; values: Record<string, unknown> }>();

const { t } = useI18n();
const { settingsGroup, settingsField } = useNavLabel();

const tab = ref(Object.keys(props.groups)[0] ?? 'general');
const form = useForm({
    values: { ...props.values },
    logo: null as File | null,
});

function submit(): void {
    form.post('/settings', { _method: 'put', forceFormData: true });
}
</script>

<template>
    <Head :title="t('settings.title')" />
    <AppLayout>
        <PageHeader :title="t('settings.title')" :description="t('settings.descriptionLong')" />

        <Card class="mt-6">
            <CardContent>
                <form @submit.prevent="submit">
                    <Tabs v-model="tab">
                        <TabsList>
                            <TabsTrigger v-for="(rows, group) in groups" :key="group" :value="group">
                                {{ settingsGroup(group) }}
                            </TabsTrigger>
                        </TabsList>

                        <TabsContent v-for="(rows, group) in groups" :key="group" :value="group">
                            <div class="space-y-4 mt-4">
                                <FormField
                                    v-for="row in rows"
                                    :key="row.key"
                                    :label="settingsField(row.key, row.label ?? row.key)"
                                    :hint="row.description ?? undefined"
                                >
                                    <Input
                                        v-if="row.type === 'text' || row.type === 'select'"
                                        v-model="form.values[row.key]"
                                    />
                                    <Textarea
                                        v-else-if="row.type === 'textarea'"
                                        v-model="form.values[row.key]"
                                    />
                                    <Switch
                                        v-else-if="row.type === 'boolean'"
                                        v-model="form.values[row.key]"
                                    />
                                    <input
                                        v-else-if="row.type === 'image'"
                                        type="file"
                                        accept="image/*"
                                        class="block text-sm"
                                        @change="(e) => (form.logo = (e.target as HTMLInputElement).files?.[0] ?? null)"
                                    />
                                    <Input v-else v-model="form.values[row.key]" />
                                </FormField>
                            </div>
                        </TabsContent>
                    </Tabs>

                    <FormActions>
                        <Button type="submit" :loading="form.processing">{{ t('settings.save') }}</Button>
                    </FormActions>
                </form>
            </CardContent>
        </Card>
    </AppLayout>
</template>
