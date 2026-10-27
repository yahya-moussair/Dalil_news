import { useI18n, type CopyKey } from '@/lib/i18n';

const principles: { titleKey: CopyKey; bodyKey: CopyKey; badge: string }[] = [
    {
        titleKey: 'trustPrinciple1Title',
        bodyKey: 'trustPrinciple1Body',
        badge: 'JOIN · STOP · وقف · arrêt',
    },
    {
        titleKey: 'trustPrinciple2Title',
        bodyKey: 'trustPrinciple2Body',
        badge: 'Masked Numbers · +212 ••• ••• 42',
    },
    {
        titleKey: 'trustPrinciple3Title',
        bodyKey: 'trustPrinciple3Body',
        badge: 'Fact Verification',
    },
];

export function Trust() {
    const { t } = useI18n();

    return (
        <section
            id="trust"
            className="scroll-mt-24 border-t border-ink/10 bg-paper py-16 md:py-24 lg:py-28"
        >
            <div className="mx-auto w-full max-w-7xl px-4 md:px-6">
                {/* Section Eyebrow & Title */}
                <div className="max-w-3xl">
                    <div className="flex items-center gap-3">
                        <span className="h-0.5 w-6 bg-brass" />
                        <p className="font-mono text-xs font-semibold tracking-wider text-brass uppercase">
                            04 — {t('navTrust')}
                        </p>
                    </div>

                    <h2 className="mt-4 text-3xl leading-tight font-bold text-ink sm:text-4xl lg:text-5xl">
                        {t('trustSectionTitle')}
                    </h2>

                    <p className="mt-4 text-base leading-relaxed text-ink/80 sm:text-lg">
                        {t('trustIntro')}
                    </p>
                </div>

                {/* 3 Principles Grid */}
                <div className="mt-12 grid gap-6 md:grid-cols-3">
                    {principles.map((p) => (
                        <div
                            key={p.titleKey}
                            className="flex flex-col justify-between border-t-2 border-brass/60 bg-sheet p-6 shadow-2xs"
                        >
                            <div>
                                <span className="font-mono text-xs font-semibold text-listen">
                                    {p.badge}
                                </span>
                                <h3 className="mt-4 text-xl font-bold text-ink">
                                    {t(p.titleKey)}
                                </h3>
                                <p className="mt-2.5 text-sm leading-relaxed text-ink/80">
                                    {t(p.bodyKey)}
                                </p>
                            </div>
                        </div>
                    ))}
                </div>

                {/* Moroccan Law 09-08 Verification Note */}
                <div className="mt-10 border-t border-ink/10 pt-6">
                    <p className="max-w-3xl font-mono text-xs leading-relaxed text-ink/60">
                        {t('trustLawBody')}
                    </p>
                </div>
            </div>
        </section>
    );
}
