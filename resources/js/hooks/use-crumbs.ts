import { setLayoutProps } from '@inertiajs/react';
import { useLayoutEffect } from 'react';

type Crumb = { title: string; href: string };

export function useCrumbs(items: Crumb[]): void {
    const signature = JSON.stringify(items);

    useLayoutEffect(() => {
        setLayoutProps({
            breadcrumbs: JSON.parse(signature) as Crumb[],
        });
    }, [signature]);
}
