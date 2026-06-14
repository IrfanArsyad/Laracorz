export const USER_STATUS = {
    active: { label: 'Aktif', color: 'success' },
    inactive: { label: 'Nonaktif', color: 'muted' },
    banned: { label: 'Diblokir', color: 'destructive' },
} as const;

export type UserStatusKey = keyof typeof USER_STATUS;

export const LOG_LEVELS = {
    debug: { label: 'Debug', color: 'muted' },
    info: { label: 'Info', color: 'info' },
    warning: { label: 'Peringatan', color: 'warning' },
    error: { label: 'Error', color: 'destructive' },
    critical: { label: 'Kritis', color: 'destructive' },
} as const;

export type LogLevel = keyof typeof LOG_LEVELS;

export const ADMIN_LOG_ACTIONS = {
    created: { label: 'Dibuat', color: 'success' },
    updated: { label: 'Diubah', color: 'info' },
    deleted: { label: 'Dihapus', color: 'destructive' },
    restored: { label: 'Dipulihkan', color: 'success' },
    login: { label: 'Login', color: 'info' },
    logout: { label: 'Logout', color: 'muted' },
    login_failed: { label: 'Login Gagal', color: 'warning' },
    export: { label: 'Ekspor', color: 'info' },
    approve: { label: 'Setujui', color: 'success' },
    reject: { label: 'Tolak', color: 'destructive' },
} as const;

export type AdminLogAction = keyof typeof ADMIN_LOG_ACTIONS;
