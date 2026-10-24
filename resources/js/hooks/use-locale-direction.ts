import { usePage } from '@inertiajs/react';
import { useEffect } from 'react';

export function useLocaleDirection() {
    const { locale } = usePage().props;
    const code = locale === 'fr' || locale === 'en' ? locale : 'ar';

    useEffect(() => {
        document.documentElement.lang = code;
        document.documentElement.dir = code === 'ar' ? 'rtl' : 'ltr';
    }, [code]);
}
