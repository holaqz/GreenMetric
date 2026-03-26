export * from './auth';
export * from './navigation';
export * from './ui';
import { PageProps as InertiaPageProps } from '@inertiajs/core';

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
}

export interface FlashMessages {
    success?: string;
    error?: string;
}

export interface PageProps extends InertiaPageProps {
    auth: {
        user: User;
    };
    flash: FlashMessages;
    name?: string;
    errors: Record<string, string>;
}