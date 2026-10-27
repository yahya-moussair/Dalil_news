import { useI18n } from '@/lib/i18n';

export function ComposedExamples() {
    const { t, locale } = useI18n();

    return (
        <section
            id="examples"
            className="scroll-mt-24 border-t border-ink/10 bg-sheet/40 py-16 md:py-24 lg:py-28"
        >
            <div className="mx-auto w-full max-w-7xl px-4 md:px-6">
                {/* Header & Concept Intro */}
                <div className="max-w-3xl">
                    <div className="flex items-center gap-3">
                        <span className="h-0.5 w-6 bg-brass" />
                        <p className="font-mono text-xs font-semibold tracking-wider text-brass uppercase">
                            03 — {t('composedExamplesTitle')}
                        </p>
                    </div>

                    <h2 className="mt-4 text-3xl leading-tight font-bold text-ink sm:text-4xl lg:text-5xl">
                        {t('examplesTitle')}
                    </h2>

                    <p className="mt-4 text-lg leading-relaxed font-medium text-ink/90 sm:text-xl">
                        {t('examplesIntro')}
                    </p>

                    {/* Prominent Disclosure Directly Beneath Intro */}
                    <div className="mt-5 border-l-2 border-brass/70 bg-paper/80 p-4 text-sm leading-relaxed text-ink/80 rtl:border-r-2 rtl:border-l-0">
                        <p>{t('examplesDisclosure')}</p>
                    </div>
                </div>

                {/* ONE Compact, Well-Designed Multilingual Demonstration */}
                <div className="mt-12 overflow-hidden border border-ink/20 bg-sheet shadow-md">
                    {/* Demonstration Header Bar */}
                    <div className="flex flex-wrap items-center justify-between gap-4 border-b border-ink/15 bg-paper px-6 py-4">
                        <div className="flex items-center gap-3">
                            <span className="size-2 rounded-full bg-brass" />
                            <h3 className="text-base font-bold text-ink sm:text-lg">
                                {t('examplesTopic')}
                            </h3>
                        </div>

                        <span className="font-mono text-[11px] font-semibold tracking-wider text-listen uppercase">
                            [ {t('examplesBadge')} ]
                        </span>
                    </div>

                    {/* 3 Multilingual Inputs */}
                    <div className="grid divide-y divide-ink/10 md:grid-cols-3 md:divide-x md:divide-y-0 rtl:md:divide-x-reverse">
                        {/* Darija */}
                        <div className="p-6">
                            <div className="flex items-center justify-between">
                                <span className="font-mono text-xs font-bold text-listen">
                                    {locale === 'ar'
                                        ? 'الدارجة المغربية'
                                        : 'Darija'}
                                </span>
                                <span className="font-mono text-[11px] text-ink/50">
                                    Audio dispatch
                                </span>
                            </div>
                            <p
                                dir="rtl"
                                className="mt-4 text-base leading-relaxed font-medium text-ink"
                            >
                                «{t('mockDarija')}»
                            </p>
                        </div>

                        {/* Arabizi */}
                        <div className="p-6">
                            <div className="flex items-center justify-between">
                                <span className="font-mono text-xs font-bold text-listen">
                                    {locale === 'ar' ? 'العربيزي' : 'Arabizi'}
                                </span>
                                <span className="font-mono text-[11px] text-ink/50">
                                    Text message
                                </span>
                            </div>
                            <p
                                dir="ltr"
                                className="mt-4 font-mono text-base leading-relaxed font-medium text-ink"
                            >
                                “{t('mockArabizi')}”
                            </p>
                        </div>

                        {/* French */}
                        <div className="p-6">
                            <div className="flex items-center justify-between">
                                <span className="font-mono text-xs font-bold text-listen">
                                    {locale === 'ar' ? 'الفرنسية' : 'French'}
                                </span>
                                <span className="font-mono text-[11px] text-ink/50">
                                    Written dispatch
                                </span>
                            </div>
                            <p
                                dir="ltr"
                                className="mt-4 text-base leading-relaxed font-medium text-ink"
                            >
                                “{t('mockFrenchLine')}”
                            </p>
                        </div>
                    </div>

                    {/* Concluding Synthesis Box: One Concern to Investigate */}
                    <div className="border-t border-ink/20 bg-ink p-6 text-paper sm:p-8">
                        <div className="flex flex-col gap-2 md:flex-row md:items-baseline md:justify-between md:gap-6">
                            <div className="flex items-center gap-2 text-brass">
                                <span className="size-2 rounded-full bg-brass" />
                                <span className="font-mono text-xs font-bold tracking-wider uppercase">
                                    {t('examplesOutcomeLabel')}
                                </span>
                            </div>
                            <p className="max-w-2xl text-sm leading-relaxed text-paper/85 sm:text-base">
                                {t('examplesOutcomeText')}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
