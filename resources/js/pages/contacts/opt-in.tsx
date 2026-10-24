import { Head } from '@inertiajs/react';
import { PageHeader } from '@/components/dalil/page-header';
import { useCrumbs } from '@/hooks/use-crumbs';
import { useI18n } from '@/lib/i18n';

export default function OptIn({
    link,
    display_number,
    newsroom,
    consent_version,
}: {
    link: string | null;
    display_number: string | null;
    newsroom: string;
    consent_version: string;
}) {
    const { t } = useI18n();
    useCrumbs([
        { title: t('navDashboard'), href: '/dashboard' },
        { title: t('navOptIn'), href: '/opt-in' },
    ]);

    return (
        <>
            <Head title={t('optInTitle')} />
            <div className="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4 md:p-6">
                <PageHeader kicker={newsroom} title={t('optInTitle')} />
                <div className="grid items-center gap-6 rounded-3xl border bg-card p-6 shadow-sm md:grid-cols-[220px_1fr]">
                    {link ? (
                        <img
                            src="/opt-in/qr.svg"
                            alt={t('qrAlt')}
                            className="size-52 rounded-xl border bg-white p-2"
                        />
                    ) : (
                        <div className="flex size-52 items-center justify-center rounded-xl border text-center text-sm text-muted-foreground">
                            {t('missingNumber')}
                        </div>
                    )}
                    <div className="flex flex-col gap-3 text-sm">
                        <p>
                            {t('displayNumber')}: {display_number ?? '—'}
                        </p>
                        {link ? (
                            <a
                                href={link}
                                className="break-all text-primary underline"
                            >
                                {link}
                            </a>
                        ) : (
                            <p>{t('missingNumber')}</p>
                        )}
                        <p>{t('joinHint')}</p>
                        <p>{t('stopHint')}</p>
                        <p className="text-muted-foreground">{t('law')}</p>
                        <p className="text-xs text-muted-foreground">
                            {t('consentVersion')}: {consent_version}
                        </p>
                    </div>
                </div>
            </div>
        </>
    );
}

OptIn.layout = {
    breadcrumbs: [
        { title: 'Dalil', href: '/dashboard' },
        { title: 'Opt-in', href: '/opt-in' },
    ],
};
