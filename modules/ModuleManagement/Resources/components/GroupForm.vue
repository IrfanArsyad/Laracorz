<script setup lang="ts">
import { FormField } from '@/components/ui/FormField';
import { Input } from '@/components/ui/Input';
import { InputNumber } from '@/components/ui/InputNumber';
import { Switch } from '@/components/ui/Switch';

defineProps<{ form: Record<string, any> }>();
</script>

<template>
    <div class="space-y-4">
        <FormField label="Slug" :error="form.errors.name" required hint="huruf kecil, tanpa spasi (contoh: user-access)">
            <Input v-model="form.name" placeholder="contoh: main" />
        </FormField>

        <FormField label="Label" :error="form.errors.label" required hint="nama yang muncul di sidebar">
            <Input v-model="form.label" placeholder="contoh: Main" />
        </FormField>

        <FormField label="Icon (lucide)" hint="nama icon dari lucide.dev (contoh: house, shield-check)">
            <Input v-model="form.icon" placeholder="contoh: shield-check" />
        </FormField>

        <div class="grid gap-3.5 sm:grid-cols-2">
            <FormField label="Urutan" hint="angka kecil = duluan">
                <InputNumber v-model="form.order" :min="0" :format="false" />
            </FormField>
            <FormField label="Status">
                <label class="inline-flex h-10 items-center gap-2 cursor-pointer">
                    <Switch v-model="form.active" />
                    <span class="text-sm">{{ form.active ? 'Aktif' : 'Nonaktif' }}</span>
                </label>
            </FormField>
        </div>
    </div>
</template>
