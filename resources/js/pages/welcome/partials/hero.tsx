import { LandingLink } from '@/pages/welcome/partials/links';
import { useI18n } from '@/lib/i18n';

export function Hero() {
    const { t } = useI18n();

    return (
        <section className="relative w-full overflow-hidden bg-ink text-paper">
            {/* The Human Mosaic Artwork as the Dominant Visual Foundation */}
            <div className="absolute inset-0 z-0">
                <img
                    src="/images/editorial/dalil_human_mosaic_hero.jpg"
                    alt="The Human Mosaic: a contemporary editorial collage of diverse Moroccan faces, reflecting community voices gathered by Dalil News"
                    width={1024}
                    height={576}
                    fetchPriority="high"
                    loading="eager"
                    decoding="async"
                    className="h-full w-full object-cover object-[center_20%] md:object-center"
                />

                {/* Restrained Navy Gradient Overlay — Maintains High Contrast while Preserving Faces */}
                <div
                    className="absolute inset-0 bg-gradient-to-t from-ink/95 via-ink/80 to-ink/40 md:bg-gradient-to-r md:from-ink/95 md:via-ink/75 md:to-transparent rtl:md:bg-gradient-to-l rtl:md:from-ink/95 rtl:md:via-ink/75 rtl:md:to-transparent"
                    aria-hidden="true"
                />
            </div>

            {/* Layered Editorial Content */}
            <div className="relative z-10 mx-auto flex min-h-[580px] w-full max-w-7xl flex-col justify-between px-4 py-12 md:min-h-[660px] md:px-6 md:py-16 lg:min-h-[720px] lg:py-20">
                {/* Header Brand Line */}
                <div className="flex items-center justify-between font-mono text-xs text-paper/70">
                    <span className="font-semibold tracking-wider text-brass uppercase">
                        {t('welcomeKicker')}
                    </span>
                    <span className="tracking-widest text-paper/60 uppercase">
                        [ {t('heroEyebrow')} ]
                    </span>
                </div>

                {/* Core Editorial Narrative */}
                <div className="my-auto max-w-2xl py-8">
                    {/* Arabic Brand Statement */}
                    <p
                        lang="ar"
                        dir="rtl"
                        className="w-fit text-2xl font-bold tracking-tight text-brass sm:text-3xl"
                    >
                        <span className="border-b-4 border-brass pb-1">
                            {t('brandStatementAr')}
                        </span>
                    </p>

                    {/* Primary Headline */}
                    <h1 className="dalil-rise mt-5 text-4xl leading-[1.08] font-bold tracking-tight text-paper sm:text-5xl md:text-6xl lg:text-7xl">
                        <span className="block">{t('heroHeadlineLine1')}</span>
                        <span className="mt-1 block text-brass">
                            {t('heroHeadlineLine2')}
                        </span>
                    </h1>

                    {/* Supporting Tagline */}
                    <p className="mt-5 text-lg font-semibold text-paper/95 sm:text-xl">
                        {t('tagline')}
                    </p>

                    {/* Editorial Body */}
                    <p className="mt-3 text-base leading-relaxed text-paper/80 sm:text-lg">
                        {t('heroBody')}
                    </p>

                    {/* Compact Primary & Secondary Actions */}
                    <div className="mt-8 flex flex-wrap items-center gap-4">
                        <LandingLink href="/campaigns/create" tone="solid">
                            {t('heroCtaPrimary')}
                        </LandingLink>
                        <a
                            href="#process"
                            className="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold text-paper/90 transition-colors hover:text-brass focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brass"
                        >
                            <span>{t('heroCtaSecondary')}</span>
                            <span aria-hidden="true" className="text-brass">
                                ↓
                            </span>
                        </a>
                    </div>
                </div>

                {/* Minimal Editorial Footnote */}
                <div className="flex items-center justify-between border-t border-paper/10 pt-4 font-mono text-[11px] text-paper/50">
                    <span>{t('humanMosaicTitle')}</span>
                    <span>Dalil News Desk</span>
                </div>
            </div>
        </section>
    );
}
