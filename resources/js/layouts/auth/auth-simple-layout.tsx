import { Link, usePage } from '@inertiajs/react';
import AppLogoIcon from '@/components/app-logo-icon';
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
            <aside className="dalil-zellige relative hidden flex-col justify-between bg-[oklch(0.28_0.045_175)] p-10 text-[oklch(0.97_0.01_90)] lg:flex">
                <Link href={home()} className="flex items-center gap-3">
                    <span className="flex size-10 items-center justify-center rounded-xl bg-[oklch(0.82_0.1_85)] text-[oklch(0.25_0.04_175)]">
                        <AppLogoIcon className="size-6" />
                    </span>
                    <span className="text-lg font-semibold">Dalil News</span>
                </Link>
                <div className="max-w-md">
                    <p className="text-sm text-[oklch(0.82_0.1_85)]">
                        {t('welcomeKicker')}
                    </p>
                    <p className="mt-3 text-4xl leading-tight font-semibold">
                        {t('welcomeTitle')}
                    </p>
                    <p className="mt-4 text-sm text-white/80">
                        {t('welcomeLead')}
                    </p>
                </div>
                <p className="text-xs text-white/60">{t('hackathon')}</p>
            </aside>
            <div className="relative flex flex-col justify-center bg-background px-6 py-16">
                <div className="absolute end-4 top-4">
                    <LocaleSwitcher />
                </div>
                <div className="mx-auto flex w-full max-w-sm flex-col gap-8">
                    <div className="flex flex-col gap-3">
                        <Link
                            href={home()}
                            className="flex items-center gap-2 font-semibold lg:hidden"
                        >
                            <span className="flex size-9 items-center justify-center rounded-xl bg-[oklch(0.32_0.05_175)] text-[oklch(0.82_0.1_85)]">
                                <AppLogoIcon className="size-6" />
                            </span>
                            Dalil News
                        </Link>
                        <div className="space-y-2">
                            <h1 className="text-2xl font-semibold tracking-tight">
                                {heading}
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                {lead}
                            </p>
                        </div>
                    </div>
                    {children}
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
