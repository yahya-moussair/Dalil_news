import { Head, Link } from '@inertiajs/react';
import { LocaleSwitcher } from '@/components/dalil/locale-switcher';
import { useLocaleDirection } from '@/hooks/use-locale-direction';
import { useI18n } from '@/lib/i18n';

export default function Welcome() {
    useLocaleDirection();
    const { t } = useI18n();
    const steps = [
        [t('stepQuestion'), t('stepQuestionBody')],
        [t('stepListen'), t('stepListenBody')],
        [t('stepTopics'), t('stepTopicsBody')],
        [t('stepAnswer'), t('stepAnswerBody')],
    ];

    return (
        <>
            <Head title="Dalil News" />
            <div className="min-h-svh bg-[oklch(0.97_0.012_90)] text-foreground">
                <header className="mx-auto flex w-full max-w-5xl items-center justify-between px-6 py-5">
                    <div className="text-sm font-semibold tracking-wide">
                        DALIL NEWS
                    </div>
                    <LocaleSwitcher />
                </header>
                <main className="mx-auto flex w-full max-w-5xl flex-col gap-10 px-6 pb-16">
                    <section className="grid gap-8 rounded-3xl bg-[oklch(0.32_0.05_175)] px-8 py-12 text-[oklch(0.97_0.01_90)] md:grid-cols-[1.4fr_1fr] md:px-12">
                        <div className="flex flex-col gap-5">
                            <p className="text-sm text-[oklch(0.82_0.08_85)]">
                                {t('welcomeKicker')}
                            </p>
                            <h1 className="text-4xl leading-tight font-semibold md:text-5xl">
                                {t('welcomeTitle')}
                            </h1>
                            <p className="max-w-xl text-base text-[oklch(0.9_0.02_90)]">
                                {t('welcomeLead')}
                            </p>
                            <div className="flex flex-wrap gap-3">
                                <Link
                                    href="/login"
                                    className="rounded-full bg-[oklch(0.82_0.1_85)] px-5 py-2 text-sm font-medium text-[oklch(0.25_0.04_175)]"
                                >
                                    {t('welcomeLogin')}
                                </Link>
                                <Link
                                    href="/register"
                                    className="rounded-full border border-white/30 px-5 py-2 text-sm"
                                >
                                    {t('welcomeSignup')}
                                </Link>
                            </div>
                        </div>
                        <div className="flex flex-col justify-end gap-3 rounded-2xl bg-white/10 p-5 text-sm">
                            <p className="text-xs tracking-wide text-[oklch(0.82_0.08_85)]">
                                سيدي مومن
                            </p>
                            <p className="text-2xl font-semibold">
                                انقطاع الماء
                            </p>
                            <p className="text-[oklch(0.9_0.02_90)]">
                                Coupures d&apos;eau · 68
                            </p>
                            <p className="border-t border-white/15 pt-3 text-[oklch(0.92_0.02_90)]">
                                «الما كيتقطع من الصباح حتى الليل.»
                            </p>
                        </div>
                    </section>
                    <section className="grid gap-4 md:grid-cols-4">
                        {steps.map(([title, body]) => (
                            <article
                                key={title}
                                className="rounded-2xl border bg-card p-4"
                            >
                                <h2 className="font-medium">{title}</h2>
                                <p className="mt-2 text-sm text-muted-foreground">
                                    {body}
                                </p>
                            </article>
                        ))}
                    </section>
                </main>
            </div>
        </>
    );
}
