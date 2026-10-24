import { router, usePage } from '@inertiajs/react';
import type { Locale } from '@/types/dalil';

const options: { value: Locale; label: string }[] = [
    { value: 'ar', label: 'عربي' },
    { value: 'fr', label: 'FR' },
    { value: 'en', label: 'EN' },
];

export function LocaleSwitcher() {
    const { locale } = usePage().props;
    const current: Locale = locale === 'fr' || locale === 'en' ? locale : 'ar';

    return (
        <div className="flex items-center rounded-full border bg-card p-0.5 text-xs">
            {options.map((option) => (
                <button
                    key={option.value}
                    type="button"
                    className={
                        option.value === current
                            ? 'rounded-full bg-primary px-2.5 py-1 font-medium text-primary-foreground'
                            : 'rounded-full px-2.5 py-1 text-muted-foreground hover:text-foreground'
                    }
                    onClick={() => {
                        if (option.value !== current) {
                            router.post(
                                '/locale',
                                { locale: option.value },
                                { preserveScroll: true },
                            );
                        }
                    }}
                >
                    {option.label}
                </button>
            ))}
        </div>
    );
}
