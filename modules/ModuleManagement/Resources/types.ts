export interface Node {
    id: number;
    name: string;
    label: string;
    icon: string | null;
    url: string | null;
    route_name: string | null;
    order: number;
    active: boolean;
    is_leaf: boolean;
    children: Node[];
    parent_id?: number | null;
    module_group_id?: number | null;
    badge_source?: string | null;
    extra_actions?: string[] | null;
    external?: boolean;
}

export interface Group {
    id: number;
    name: string;
    label: string;
    icon: string | null;
    order: number;
    active: boolean;
    modules: Node[];
}

export interface Option {
    id: number;
    name: string;
    label: string;
}
