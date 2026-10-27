import { useI18n, type CopyKey } from '@/lib/i18n';

const steps: { number: string; titleKey: CopyKey; bodyKey: CopyKey }[] = [
    { number: '01', titleKey: 'step1Title', bodyKey: 'step1Body' },
    { number: '02', titleKey: 'step2Title', bodyKey: 'step2Body' },
    { number: '03', titleKey: 'step3Title', bodyKey: 'step3Body' },
    { number: '04', titleKey: 'step4Title', bodyKey: 'step4Body' },
    { number: '05', titleKey: 'step5Title', bodyKey: 'step5Body' },
];

export function Process() {
    const { t } = useI18n();

    return (
        <section
            id="process"
            className="mx-auto w-full max-w-7xl scroll-mt-24 px-4 py-16 md:px-6 md:py-24 lg:py-28"
        >
            {/* Section Eyebrow & Title */}
            <div className="max-w-3xl">
                <div className="flex items-center gap-3">
                    <span className="h-0.5 w-6 bg-brass" />
                    <p className="font-mono text-xs font-semibold tracking-wider text-brass uppercase">
                        02 — {t('navHow')}
                    </p>
                </div>

                <h2 className="mt-4 text-3xl leading-tight font-bold text-ink sm:text-4xl lg:text-5xl">
                    {t('howTitle')}
                </h2>

                <div className="mt-5 space-y-3">
                    <p className="text-lg leading-relaxed font-medium text-ink/90 sm:text-xl">
                        {t('howLead')}
                    </p>
                    <p className="text-base leading-relaxed text-ink/75 sm:text-lg">
                        {t('howIntro2')}
                    </p>
                </div>
            </div>

            {/* 5-Step Restrained Editorial Sequence */}
            <div className="mt-14 border-t border-ink/15 pt-8 lg:mt-18">
                <ol className="grid gap-8 sm:grid-cols-2 lg:grid-cols-5">
                    {steps.map((step) => (
                        <li
                            key={step.number}
                            className="group flex flex-col justify-between border-t-2 border-brass/70 pt-4 transition-colors hover:border-listen"
                        >
                            <div>
                                <span className="font-mono text-sm font-bold text-brass tabular-nums">
                                    {step.number}
                                </span>

                                <h3 className="mt-3 text-xl font-bold text-ink">
                                    {t(step.titleKey)}
                                </h3>

                                <p className="mt-2 text-sm leading-relaxed text-ink/80">
                                    {t(step.bodyKey)}
                                </p>
                            </div>

                            <div className="mt-6 pt-2 font-mono text-xs text-ink/40">
                                <span>Dalil Workflow</span>
                            </div>
                        </li>
                    ))}
                </ol>
            </div>
        </section>
    );
}
