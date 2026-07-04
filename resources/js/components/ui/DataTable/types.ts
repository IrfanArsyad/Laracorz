export interface Column<T = unknown> {
    key: string;
    label: string;
    sortable?: boolean;
    align?: 'left' | 'center' | 'right';
    class?: string;
    width?: string;
    accessor?: (row: T) => unknown;
}
