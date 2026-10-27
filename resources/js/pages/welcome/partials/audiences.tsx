import { useI18n, type CopyKey } from '@/lib/i18n';

const audienceGroups: { titleKey: CopyKey; bodyKey: CopyKey; tag: string }[] = [
    {
        titleKey: 'audienceNewsroomsTitle',
        bodyKey: 'audienceNewsroomsBody',
        tag: 'Editorial Teams',
    },
    {
        titleKey: 'audienceCivicTitle',
        bodyKey: 'audienceCivicBody',
        tag: 'Civil Society',
    },
    {
        titleKey: 'audienceFundersTitle',
        bodyKey: 'audienceFundersBody',
        tag: 'Media Partners',
    },
];

export function Audiences() {
    const { t } = useI18n();

    return (
        <section
            id="for"
            className="border-t border-ink/10 bg-sheet/30 py-16 md:py-24"
        >
            <div className="mx-auto w-full max-w-7xl px-4 md:px-6">
                <div className="flex items-center gap-3">
                    <span className="h-0.5 w-6 bg-brass" />
                    <p className="font-mono text-xs font-semibold tracking-wider text-brass uppercase">
                        05 — {t('navFor')}
                    </p>
                </div>

                <h2 className="mt-4 max-w-3xl text-3xl leading-tight font-bold text-ink sm:text-4xl lg:text-5xl">
                    {t('whoTitle')}
                </h2>

                {/* Restrained Editorial Audience Layout */}
                <div className="mt-12 grid gap-8 md:grid-cols-3">
                    {audienceGroups.map((group) => (
                        <div
                            key={group.titleKey}
                            className="border-t-2 border-brass/70 pt-4"
                        >
                            <span className="font-mono text-xs font-semibold text-listen">
                                {group.tag}
                            </span>
                            <h3 className="mt-3 text-lg font-bold text-ink sm:text-xl">
                                {t(group.titleKey)}
                            </h3>
                            <p className="mt-2.5 text-sm leading-relaxed text-ink/80">
                                {t(group.bodyKey)}
                            </p>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}
