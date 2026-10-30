import { useI18n } from '@/lib/i18n';

export function DemoBanner() {
    const { t } = useI18n();

    return (
        <div className="flex items-center gap-3 rounded-xl border border-brass/50 bg-brass/10 px-4 py-2.5 text-xs text-ink">
            <span className="rounded bg-ink px-2 py-0.5 font-mono text-[10px] font-semibold tracking-wider text-paper uppercase">
                DEMO
            </span>
            <p className="font-medium text-ink/90">
                {t('demoData')}
            </p>
        </div>
    );
}
