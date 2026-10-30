import { Link, usePage } from '@inertiajs/react';
import { LocaleSwitcher } from '@/components/dalil/locale-switcher';
import { useLocaleDirection } from '@/hooks/use-locale-direction';
import { useI18n, type CopyKey } from '@/lib/i18n';
import { home } from '@/routes';
import type { AuthLayoutProps } from '@/types';

export default function AuthSimpleLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    useLocaleDirection();
    const { t } = useI18n();
    const page = usePage();
    const path = page.url.split('?')[0] ?? '';
    const copy = authCopy(path);
    const heading = copy ? t(copy.title) : title;
    const lead = copy ? t(copy.lead) : description;

    return (
        <div className="grid min-h-svh lg:grid-cols-2">
            {/* Editorial / Brand Showcase Panel */}
            <aside className="relative hidden min-h-svh flex-col justify-between overflow-hidden bg-ink p-10 text-paper lg:flex">
                {/* Signature Brand Rules at the Top */}
                <div className="absolute inset-x-0 top-0 z-20">
                    <div className="h-1 bg-brass" aria-hidden="true" />
                    <div className="h-0.5 bg-listen" aria-hidden="true" />
                </div>

                {/* The Human Mosaic Visual Artwork Foundation */}
                <div className="absolute inset-0 z-0">
                    <img
                        src="/images/editorial/dalil_human_mosaic_hero.jpg"
                        alt="The Human Mosaic: community voices gathered by Dalil News"
                        className="h-full w-full object-cover object-[center_20%] opacity-40 mix-blend-luminosity"
                    />
                    <div
                        className="absolute inset-0 bg-gradient-to-t from-ink via-ink/90 to-ink/75"
                        aria-hidden="true"
                    />
                    <div
                        className="dalil-zellige absolute inset-0 opacity-40"
                        aria-hidden="true"
                    />
                </div>

                {/* Top Logo & Brand Link */}
                <div className="relative z-10">
                    <Link
                        href={home()}
                        className="flex w-fit items-center gap-3 transition-opacity hover:opacity-90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brass"
                    >
                        <img
                            src="/brand/dalil-mark.png"
                            alt=""
                            width={398}
                            height={328}
                            className="h-11 w-auto"
                        />
                        <div>
                            <span className="block text-xl font-bold tracking-tight text-paper">
                                Dalil News
                            </span>
                            <span className="block font-mono text-[10px] text-brass tracking-wider uppercase">
                                Media Builders
                            </span>
                        </div>
                    </Link>
                </div>

                {/* Narrative: The Human Mosaic */}
                <div className="relative z-10 my-auto max-w-lg py-8">
                    <p className="font-mono text-xs font-semibold tracking-wider text-brass uppercase">
                        [ {t('heroEyebrow')} ]
                    </p>

                    <p
                        lang="ar"
                        dir="rtl"
                        className="mt-4 w-fit text-2xl font-bold tracking-tight text-brass sm:text-3xl"
                    >
                        <span className="border-b-2 border-brass pb-1">
                            {t('brandStatementAr')}
                        </span>
                    </p>

                    <h2 className="mt-5 text-3xl font-bold leading-tight tracking-tight text-paper sm:text-4xl">
                        <span className="block">{t('heroHeadlineLine1')}</span>
                        <span className="mt-1 block text-brass">
                            {t('heroHeadlineLine2')}
                        </span>
                    </h2>

                    <p className="mt-4 text-sm font-semibold text-paper/95 sm:text-base">
                        {t('tagline')}
                    </p>

                    <p className="mt-3 text-xs leading-relaxed text-paper/75 sm:text-sm">
                        {t('welcomeLead')}
                    </p>
                </div>

                {/* Footnote */}
                <div className="relative z-10 flex items-center justify-between border-t border-paper/10 pt-4 font-mono text-[11px] text-paper/60">
                    <span>{t('humanMosaicTitle')}</span>
                    <span>Dalil News Desk · {t('hackathon')}</span>
                </div>
            </aside>

            {/* Authentication Form Side */}
            <div className="relative flex min-h-svh flex-col justify-between bg-paper px-4 py-6 sm:px-8 sm:py-10 lg:px-12">
                {/* Top Nav with Home link and LocaleSwitcher */}
                <div className="flex items-center justify-between gap-3 border-b border-ink/10 pb-4">
                    <Link
                        href={home()}
                        className="inline-flex items-center gap-1.5 text-xs font-medium text-ink/70 underline-offset-4 transition-colors hover:text-ink hover:underline hover:decoration-brass focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brass"
                    >
                        <span aria-hidden="true">←</span>
                        <span>{t('previous') === 'السابق' ? 'الرئيسية' : 'Dalil News'}</span>
                    </Link>
                    <LocaleSwitcher tone="masthead" />
                </div>

                {/* Form Card Container */}
                <div className="mx-auto my-auto flex w-full max-w-md flex-col gap-6 py-6">
                    {/* Mobile Brand Mark */}
                    <div className="flex flex-col items-center text-center gap-2 lg:hidden">
                        <Link href={home()}>
                            <img
                                src="/brand/dalil-mark.png"
                                alt="Dalil News"
                                width={398}
                                height={328}
                                className="h-12 w-auto"
                            />
                        </Link>
                        <span className="text-xl font-bold tracking-tight text-ink">
                            Dalil News
                        </span>
                    </div>

                    {/* Form Header */}
                    <div className="space-y-1.5">
                        <span className="font-mono text-xs font-semibold tracking-wider text-brass uppercase">
                            «سألتم، فأجبنا»
                        </span>
                        <h1 className="text-2xl font-bold tracking-tight text-ink sm:text-3xl">
                            {heading}
                        </h1>
                        <p className="text-sm leading-relaxed text-ink/75">
                            {lead}
                        </p>
                    </div>

                    {/* Form Card */}
                    <div className="rounded-2xl border border-ink/10 bg-sheet p-6 shadow-xs sm:p-8">
                        {children}
                    </div>
                </div>

                {/* Bottom Footer Note */}
                <div className="border-t border-ink/10 pt-4 text-center font-mono text-[11px] text-ink/50">
                    Dalil News · «سألتم، فأجبنا» · {t('hackathon')}
                </div>
            </div>
        </div>
    );
}

function authCopy(path: string): { title: CopyKey; lead: CopyKey } | null {
    if (path.startsWith('/register')) {
        return { title: 'createAccount', lead: 'registerLead' };
    }

    if (path.startsWith('/forgot-password')) {
        return { title: 'forgotPassword', lead: 'forgotLead' };
    }

    if (path.startsWith('/reset-password')) {
        return { title: 'resetTitle', lead: 'resetLead' };
    }

    if (path.startsWith('/login')) {
        return { title: 'loginTitle', lead: 'loginLead' };
    }

    return null;
}
