import { Link } from '@inertiajs/react';
import { LandingLink } from '@/pages/welcome/partials/links';
import { useI18n } from '@/lib/i18n';

export function Close() {
    const { t } = useI18n();

    return (
        <section className="relative overflow-hidden bg-ink text-paper">
            {/* Final Call to Action Editorial Stage */}
            <div className="relative mx-auto w-full max-w-7xl px-4 py-20 md:px-6 md:py-28 lg:py-32">
                <div className="max-w-3xl">
                    <div className="flex items-center gap-3">
                        <span className="h-0.5 w-6 bg-brass" />
                        <p className="font-mono text-xs font-semibold tracking-wider text-brass uppercase">
                            [ {t('closeEyebrow')} ]
                        </p>
                    </div>

                    {/* Arabic Brand Statement */}
                    <p
                        lang="ar"
                        dir="rtl"
                        className="mt-6 w-fit text-2xl font-bold tracking-tight text-brass sm:text-3xl"
                    >
                        <span className="border-b-4 border-brass pb-1">
                            {t('closeBrandStatement')}
                        </span>
                    </p>

                    {/* Monumental Headline */}
                    <h2 className="mt-5 text-4xl leading-[1.08] font-bold tracking-tight text-paper sm:text-5xl md:text-6xl lg:text-7xl">
                        {t('closeHeadline')}
                    </h2>

                    {/* Body */}
                    <p className="mt-6 text-lg leading-relaxed text-paper/85 sm:text-xl">
                        {t('closeBody')}
                    </p>

                    {/* Action Controls */}
                    <div className="mt-10 flex flex-wrap items-center gap-5">
                        <LandingLink href="/login" tone="onInk">
                            {t('closeCtaPrimary')}
                        </LandingLink>
                        <Link
                            href="/login"
                            className="inline-flex items-center px-4 py-2.5 text-sm font-semibold text-paper/90 transition-colors hover:text-brass focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brass"
                        >
                            {t('closeCtaSecondary')}
                        </Link>
                        <Link
                            href="/campaigns/create"
                            className="text-sm font-medium text-paper/70 underline decoration-paper/30 underline-offset-4 transition-colors hover:text-paper hover:decoration-brass"
                        >
                            {t('startCampaign')}
                        </Link>
                    </div>
                </div>
            </div>

            {/* Simplified Editorial Footer */}
            <footer className="border-t border-paper/15 bg-ink/95">
                <div className="mx-auto flex w-full max-w-7xl flex-wrap items-center justify-between gap-4 px-4 py-8 text-xs text-paper/70 md:px-6">
                    <div className="flex items-center gap-3">
                        <img
                            src="/brand/dalil-mark.png"
                            alt="Dalil News mark"
                            width={398}
                            height={328}
                            className="h-7 w-auto brightness-90"
                        />
                        <span className="font-semibold text-paper/90">
                            {t('footerBrand')}
                        </span>
                    </div>

                    <div className="flex items-center gap-4 font-mono text-paper/50">
                        <span>Casablanca · Morocco</span>
                        <span>·</span>
                        <span>Loi 09-08</span>
                    </div>
                </div>
            </footer>
        </section>
    );
}
