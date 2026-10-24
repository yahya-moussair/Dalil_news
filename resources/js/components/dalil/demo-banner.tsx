import { useI18n } from '@/lib/i18n';

export function DemoBanner() {
    const { t } = useI18n();

    return (
        <p className="rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-sm text-amber-950">
            {t('demoData')}
        </p>
    );
}
