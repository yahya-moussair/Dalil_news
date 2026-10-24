import { useI18n } from '@/lib/i18n';

export function DemoBanner() {
    const { t } = useI18n();

    return (
        <p className="rounded-xl border border-[oklch(0.82_0.1_85)] bg-[oklch(0.96_0.03_90)] px-3 py-2 text-sm text-[oklch(0.32_0.05_60)]">
            {t('demoData')}
        </p>
    );
}
