<script setup lang="ts">
import { computed, ref } from 'vue';
import { Bell, BellOff, Check, Info, AlertCircle, CheckCircle, AlertTriangle } from 'lucide-vue-next';
import { Link, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { DropdownMenu } from '../ui/DropdownMenu';
import { Badge } from '../ui/Badge';

interface NotificationItem {
    id: string | number;
    title: string;
    message?: string;
    url?: string;
    read_at?: string | null;
    created_at?: string;
    type?: 'info' | 'success' | 'warning' | 'danger';
}

const { t } = useI18n();
const page = usePage();

// Server bisa share `notifications` array dari Inertia. Kalau belum ada, default empty.
const items = computed<NotificationItem[]>(() => {
    const fromServer = (page.props.notifications as { items?: NotificationItem[] })?.items;
    return Array.isArray(fromServer) ? fromServer : [];
});

const unreadCount = computed(() => items.value.filter((n) => !n.read_at).length);

const TYPE_ICON = {
    info: Info,
    success: CheckCircle,
    warning: AlertTriangle,
    danger: AlertCircle,
} as const;

const TYPE_COLOR = {
    info: 'text-[var(--status-info-fg)] bg-[var(--status-info-bg)]',
    success: 'text-[var(--status-success-fg)] bg-[var(--status-success-bg)]',
    warning: 'text-[var(--status-warning-fg)] bg-[var(--status-warning-bg)]',
    danger: 'text-[var(--status-danger-fg)] bg-[var(--status-danger-bg)]',
} as const;

function iconOf(type?: NotificationItem['type']) {
    return TYPE_ICON[type ?? 'info'];
}

function colorOf(type?: NotificationItem['type']) {
    return TYPE_COLOR[type ?? 'info'];
}

const marking = ref(false);

async function markAllRead(): Promise<void> {
    marking.value = true;
    try {
        await fetch('/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN':
                    (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null)?.content ?? '',
                Accept: 'application/json',
            },
        });
        // Optimistic: clear locally — server akan re-share via Inertia next request
        items.value.forEach((n) => (n.read_at = new Date().toISOString()));
    } catch {
        /* noop */
    } finally {
        marking.value = false;
    }
}
</script>

<template>
    <DropdownMenu align="end" class="!p-0 w-80 max-w-[calc(100vw-1.5rem)]">
        <template #trigger>
            <button
                type="button"
                class="relative inline-flex h-8 w-8 items-center justify-center rounded-md text-[var(--text-muted)] hover:bg-[var(--state-hover)] hover:text-[var(--text-default)] transition-colors"
                :aria-label="t('topbar.notifications')"
            >
                <Bell class="h-4 w-4" />
                <span
                    v-if="unreadCount > 0"
                    class="absolute -top-0.5 -right-0.5 flex h-2 w-2"
                >
                    <span class="absolute inline-flex h-full w-full rounded-full bg-[var(--status-danger-bg)] opacity-75 animate-ping" />
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-[var(--status-danger-fg)]" />
                </span>
            </button>
        </template>

        <div>
            <!-- Header -->
            <div class="flex items-center justify-between gap-2 px-3 py-2.5 border-b border-[var(--border-subtle)]">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-[var(--text-strong)]">{{ t('topbar.notifications') }}</span>
                    <Badge v-if="unreadCount > 0" variant="muted" class="text-[10px] px-1.5">
                        {{ unreadCount > 99 ? '99+' : unreadCount }}
                    </Badge>
                </div>
                <button
                    v-if="unreadCount > 0"
                    type="button"
                    :disabled="marking"
                    class="inline-flex items-center gap-1 text-xs text-[var(--text-muted)] hover:text-[var(--text-default)] transition-colors disabled:opacity-50"
                    @click="markAllRead"
                >
                    <Check class="h-3 w-3" />
                    {{ t('notifications.markAllRead') }}
                </button>
            </div>

            <!-- List / Empty -->
            <div class="max-h-96 overflow-y-auto">
                <div v-if="items.length === 0" class="flex flex-col items-center justify-center px-3 py-10 text-center">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[var(--surface-sunken)] text-[var(--text-muted)] ring-1 ring-[var(--border-subtle)] mb-2">
                        <BellOff class="h-4 w-4" />
                    </div>
                    <p class="text-sm text-[var(--text-muted)]">{{ t('notifications.empty') }}</p>
                </div>

                <ul v-else class="divide-y divide-[var(--border-subtle)]">
                    <li v-for="n in items" :key="n.id">
                        <component
                            :is="n.url ? Link : 'div'"
                            :href="n.url"
                            :class="
                                [
                                    'flex items-start gap-2.5 px-3 py-2.5 transition-colors',
                                    n.url ? 'hover:bg-[var(--state-hover)] cursor-pointer' : '',
                                    !n.read_at ? 'bg-[var(--brand-soft-bg)]/30' : '',
                                ]
                            "
                        >
                            <div
                                :class="
                                    [
                                        'mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full',
                                        colorOf(n.type),
                                    ]
                                "
                            >
                                <component :is="iconOf(n.type)" class="h-3.5 w-3.5" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-[var(--text-strong)] truncate">{{ n.title }}</p>
                                <p v-if="n.message" class="text-xs text-[var(--text-muted)] line-clamp-2 mt-0.5">{{ n.message }}</p>
                                <p v-if="n.created_at" class="text-[10px] text-[var(--text-muted)] mt-1 tabular-nums">{{ n.created_at }}</p>
                            </div>
                            <span
                                v-if="!n.read_at"
                                class="mt-1.5 h-1.5 w-1.5 rounded-full bg-[var(--brand-bg)] shrink-0"
                                :title="t('notifications.new')"
                            />
                        </component>
                    </li>
                </ul>
            </div>

            <!-- Footer -->
            <div class="border-t border-[var(--border-subtle)]">
                <Link
                    href="/notifications"
                    class="block px-3 py-2 text-center text-xs font-medium text-[var(--text-muted)] hover:text-[var(--text-default)] hover:bg-[var(--state-hover)] transition-colors"
                >
                    {{ t('common.more') }}
                </Link>
            </div>
        </div>
    </DropdownMenu>
</template>
