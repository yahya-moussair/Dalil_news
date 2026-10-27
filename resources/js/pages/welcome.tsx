import { Head } from '@inertiajs/react';
import { useLocaleDirection } from '@/hooks/use-locale-direction';
import { useI18n } from '@/lib/i18n';
import { Audiences } from '@/pages/welcome/partials/audiences';
import { Close } from '@/pages/welcome/partials/close';
import { ComposedExamples } from '@/pages/welcome/partials/composed-examples';
import { Hero } from '@/pages/welcome/partials/hero';
import { Process } from '@/pages/welcome/partials/process';
import { SiteHeader } from '@/pages/welcome/partials/site-header';
import { Trust } from '@/pages/welcome/partials/trust';

export default function Welcome() {
    useLocaleDirection();
    const { t } = useI18n();

    return (
        <>
            <Head title="Dalil News">
                <meta name="description" content={t('welcomeLead')} />
            </Head>
            <div className="min-h-svh bg-paper text-ink">
                <SiteHeader />
                <main id="content">
                    {/* Section 01: Hero — The Human Mosaic */}
                    <Hero />

                    {/* Section 02: From voice to investigation */}
                    <Process />

                    {/* Section 03: Composed examples — a single illustrative demonstration */}
                    <ComposedExamples />

                    {/* Section 04: Trust, consent, and privacy */}
                    <Trust />

                    {/* Section 05: Who it serves */}
                    <Audiences />

                    {/* Section 06: Final call to action & footer */}
                    <Close />
                </main>
            </div>
        </>
    );
}
