import { LucideIcon } from 'lucide-react';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    url: string;
    icon?: LucideIcon | null;
    isActive?: boolean;
}

export interface CloudProvider {
    id: string;
    name: string;
    type: 'hetzner' | 'digital_ocean';
    is_verified: boolean;
}

export interface Organization {
    id: string;
    name: string;
    slug: string;
    description: string | null;
    logo: string | null;
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    currentOrganization: Organization | null;
    organizations: Organization[] | null;
    [key: string]: unknown;
}

export interface User {
    id: string;
    name: string;
    email: string;
    avatar?: string;
    platform_role: 'admin' | 'member';
    current_organization_id: string | null;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
}

export interface Provider {
    id: string;
    name: string;
    slug: string;
    is_active: boolean;
    has_api_token: boolean;
    created_at: string;
    regions?: PlatformRegion[]
}

export interface PlatformRegion {
    id: string;
    name: string;
    slug: string;
    country: string | null;
    city: string | null;
    is_available: boolean;
}

export interface KubernetesVersion {
    name: string;
    is_supported: boolean;
    end_of_life: string;
}

export interface ManagementCluster {
    id: string;
    name: string;
    provider: Provider;
    region: PlatformRegion;
    status: string;
    version: KubernetesVersion;
    created_at: string;
}
