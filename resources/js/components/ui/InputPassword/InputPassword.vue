<script setup lang="ts">
import { ref } from 'vue';
import { Eye, EyeOff } from 'lucide-vue-next';
import Input from '../Input/Input.vue';

const props = defineProps<{
    modelValue?: string | null;
    placeholder?: string;
    disabled?: boolean;
    error?: boolean | string;
    id?: string;
}>();
const emit = defineEmits<{ 'update:modelValue': [value: string] }>();

const show = ref(false);
</script>

<template>
    <Input
        :id="id"
        :model-value="modelValue ?? ''"
        :type="show ? 'text' : 'password'"
        :placeholder="placeholder"
        :disabled="disabled"
        :error="error"
        @update:model-value="(v) => emit('update:modelValue', v)"
    >
        <template #suffix>
            <button
                type="button"
                class="text-muted-foreground hover:text-foreground"
                tabindex="-1"
                :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                @click="show = !show"
            >
                <EyeOff v-if="show" class="h-4 w-4" />
                <Eye v-else class="h-4 w-4" />
            </button>
        </template>
    </Input>
</template>
