<script setup lang="ts">
import { onErrorCaptured, ref } from 'vue';
import Alert from '../ui/Alert/Alert.vue';

const error = ref<Error | null>(null);

onErrorCaptured((e) => {
    error.value = e instanceof Error ? e : new Error(String(e));
    return false;
});

function reset(): void {
    error.value = null;
}
</script>

<template>
    <Alert v-if="error" variant="destructive" title="Terjadi kesalahan" dismissible @dismiss="reset">
        {{ error.message }}
    </Alert>
    <slot v-else />
</template>
