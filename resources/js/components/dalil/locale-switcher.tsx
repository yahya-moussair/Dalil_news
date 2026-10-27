import { router, usePage } from '@inertiajs/react';
import { useI18n } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import type { Locale } from '@/types/dalil';

const options: { value: Locale; label: string }[] = [
    { value: 'ar', label: 'عربي' },
    { value: 'fr', label: 'FR' },
    { value: 'en', label: 'EN' },
];

export function LocaleSwitcher({
    tone = 'pill',
}: {
    tone?: 'pill' | 'masthead';
}) {
    const { locale } = usePage().props;
    const { t } = useI18n();
    const current: Locale = locale === 'fr' || locale === 'en' ? locale : 'ar';

    return (
        <div
            className={
                tone === 'masthead'
                    ? 'flex items-center gap-1 text-sm'
                    : 'flex items-center rounded-full border bg-card p-1 text-sm shadow-sm'
            }
            role="group"
            aria-label={t('languageName')}
        >
            {options.map((option) => {
                const active = option.value === current;

                return (
                    <button
                        key={option.value}
                        type="button"
                        lang={option.value}
                        aria-pressed={active}
                        className={
                            tone === 'masthead'
                                ? cn(
                                      'px-2 py-2 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brass',
                                      active
                                          ? 'border-b-2 border-brass font-semibold text-ink'
                                          : 'text-ink/60 hover:text-ink',
                                  )
                                : active
                                  ? 'rounded-full bg-primary px-3 py-1 font-medium text-primary-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none'
                                  : 'rounded-full px-3 py-1 text-muted-foreground hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none'
                        }
                        onClick={() => {
                            if (!active) {
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
                );
            })}
        </div>
    );
}
