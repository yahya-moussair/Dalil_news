import type { Auth } from '@/types/auth';
import type { Locale } from '@/types/dalil';

declare module 'react' {
    interface InputHTMLAttributes<T> {
        passwordrules?: string;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            sidebarOpen: boolean;
            locale: Locale;
            newsroom: {
                id: number;
                name: string;
                city: string | null;
            } | null;
            demoHint: boolean;
            drivers: {
                messaging: string;
                transcription: string;
                clustering: string;
            };
            [key: string]: unknown;
        };
    }
}
