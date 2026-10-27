import { Link } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { LocaleSwitcher } from '@/components/dalil/locale-switcher';
import { useI18n, type CopyKey } from '@/lib/i18n';

const links: { href: string; key: CopyKey }[] = [
    { href: '#process', key: 'navHow' },
    { href: '#examples', key: 'composedExamplesTitle' },
    { href: '#trust', key: 'navTrust' },
    { href: '#for', key: 'navFor' },
];

export function SiteHeader() {
    const { t } = useI18n();
    const [open, setOpen] = useState(false);

    useEffect(() => {
        if (!open) {
            return;
        }

        const onKey = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                setOpen(false);
            }
        };

        window.addEventListener('keydown', onKey);

        return () => window.removeEventListener('keydown', onKey);
    }, [open]);

    return (
        <header className="sticky top-0 z-30 bg-paper/95 backdrop-blur-md">
            <div className="h-1 bg-brass" aria-hidden="true" />
            <div className="h-0.5 bg-listen" aria-hidden="true" />
            <a
                href="#content"
                className="sr-only focus:not-sr-only focus:absolute focus:start-4 focus:top-4 focus:z-50 focus:bg-paper focus:px-3 focus:py-2 focus:text-ink"
            >
                {t('skipToContent')}
            </a>
            <div className="border-b border-ink/10">
                <div className="mx-auto flex w-full max-w-6xl items-center justify-between gap-3 px-4 py-3 md:px-6">
                    <Link
                        href="/"
                        className="flex items-center gap-3 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brass"
                    >
                        <img
                            src="/brand/dalil-mark.png"
                            alt=""
                            width={398}
                            height={328}
                            className="h-11 w-auto"
                        />
                        <span className="hidden text-sm font-semibold sm:inline">
                            Dalil News
                        </span>
                    </Link>
                    <nav
                        className="hidden items-center gap-6 lg:flex"
                        aria-label={t('navGroup')}
                    >
                        {links.map((link) => (
                            <a
                                key={link.href}
                                href={link.href}
                                className="text-sm text-ink/80 underline-offset-4 hover:text-ink hover:underline hover:decoration-brass focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brass"
                            >
                                {t(link.key)}
                            </a>
                        ))}
                    </nav>
                    <div className="flex items-center gap-2 sm:gap-3">
                        <LocaleSwitcher tone="masthead" />
                        <Link
                            href="/login"
                            className="hidden bg-ink px-4 py-2 text-sm font-medium text-paper transition-colors duration-200 hover:bg-listen focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brass md:inline-flex"
                        >
                            {t('welcomeLogin')}
                        </Link>
                        <button
                            type="button"
                            className="px-2 py-2 text-sm font-medium text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brass lg:hidden"
                            aria-expanded={open}
                            aria-controls="landing-menu"
                            onClick={() => setOpen((value) => !value)}
                        >
                            {open ? t('menuClose') : t('menuOpen')}
                        </button>
                    </div>
                </div>
                {open ? (
                    <nav
                        id="landing-menu"
                        className="border-t border-ink/10 px-4 py-2 lg:hidden"
                        aria-label={t('navGroup')}
                    >
                        <ul className="flex flex-col">
                            {links.map((link) => (
                                <li
                                    key={link.href}
                                    className="border-b border-ink/10"
                                >
                                    <a
                                        href={link.href}
                                        className="block py-3 text-base"
                                        onClick={() => setOpen(false)}
                                    >
                                        {t(link.key)}
                                    </a>
                                </li>
                            ))}
                            <li className="border-b border-ink/10">
                                <Link
                                    href="/login"
                                    className="block py-3 text-base font-medium"
                                    onClick={() => setOpen(false)}
                                >
                                    {t('welcomeLogin')}
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href="/register"
                                    className="block py-3 text-base"
                                    onClick={() => setOpen(false)}
                                >
                                    {t('welcomeSignup')}
                                </Link>
                            </li>
                        </ul>
                    </nav>
                ) : null}
            </div>
        </header>
    );
}
