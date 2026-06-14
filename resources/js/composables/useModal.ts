import { ref, type Ref } from 'vue';

/**
 * Controller modal yang reusable untuk berbagai kebutuhan (form, detail, json, dst).
 *
 * Pola pemakaian:
 *
 *   const formModal = useModal<Module | null>();
 *   formModal.open(record); // buka dengan data context
 *   formModal.open();       // buka tanpa context (mis. mode "create")
 *   formModal.close();      // tutup; data dipertahankan sampai transisi keluar selesai
 *
 *   <Modal v-model="formModal.isOpen.value">...</Modal>
 *   <Button @click="formModal.open(row)">Edit</Button>
 *   {{ formModal.data.value?.name }}
 *
 * Generic T menentukan tipe data context (mis. record yang sedang di-edit).
 */
export function useModal<T = unknown>(initial: T | null = null) {
    const isOpen = ref(false);
    const data = ref<T | null>(initial) as Ref<T | null>;

    function open(value: T | null = null): void {
        data.value = value;
        isOpen.value = true;
    }

    function close(): void {
        isOpen.value = false;
        // Pertahankan data hingga animasi keluar selesai (200ms) agar konten tidak "kosong" saat fade-out.
        setTimeout(() => (data.value = null), 220);
    }

    function toggle(value: T | null = null): void {
        if (isOpen.value) close();
        else open(value);
    }

    return { isOpen, data, open, close, toggle };
}
