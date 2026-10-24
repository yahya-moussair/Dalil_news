import { Head, Link } from '@inertiajs/react';
import AppLogoIcon from '@/components/app-logo-icon';
import { LocaleSwitcher } from '@/components/dalil/locale-switcher';
import { Meter } from '@/components/dalil/meter';
import { useLocaleDirection } from '@/hooks/use-locale-direction';
import { useI18n } from '@/lib/i18n';

const stepKeys = [
    ['stepQuestion', 'stepQuestionBody'],
    ['stepListen', 'stepListenBody'],
    ['stepTopics', 'stepTopicsBody'],
    ['stepVote', 'stepVoteBody'],
    ['stepAnswer', 'stepAnswerBody'],
] as const;

export default function Welcome() {
    useLocaleDirection();
    const { t } = useI18n();

    return (
        <>
            <Head title="Dalil News" />
            <div className="min-h-svh bg-[oklch(0.97_0.012_85)] text-foreground">
                <header className="sticky top-0 z-20 border-b border-border/70 bg-[oklch(0.97_0.012_85)]/90 backdrop-blur">
                    <div className="mx-auto flex w-full max-w-6xl items-center justify-between gap-3 px-4 py-3 md:px-6">
                        <Link
                            href="/"
                            className="flex items-center gap-2 font-semibold tracking-wide"
                        >
                            <span className="flex size-9 items-center justify-center rounded-xl bg-[oklch(0.32_0.05_175)] text-[oklch(0.82_0.1_85)]">
                                <AppLogoIcon className="size-6" />
                            </span>
                            <span>Dalil News</span>
                        </Link>
                        <div className="flex items-center gap-2 sm:gap-3">
                            <LocaleSwitcher />
                            <Link
                                href="/login"
                                className="hidden rounded-full bg-[oklch(0.32_0.05_175)] px-4 py-2 text-sm font-medium text-[oklch(0.97_0.01_90)] sm:inline-flex"
                            >
                                {t('welcomeLogin')}
                            </Link>
                        </div>
                    </div>
                </header>

                <main className="mx-auto flex w-full max-w-6xl flex-col gap-16 px-4 py-10 md:px-6 md:py-14">
                    <section className="grid items-center gap-8 lg:grid-cols-[1.15fr_0.85fr]">
                        <div className="flex flex-col gap-6">
                            <p className="text-sm font-medium tracking-wide text-[oklch(0.45_0.08_175)]">
                                {t('welcomeKicker')}
                            </p>
                            <h1 className="max-w-xl text-4xl leading-[1.15] font-semibold tracking-tight md:text-6xl">
                                {t('welcomeTitle')}
                            </h1>
                            <p className="max-w-xl text-lg text-muted-foreground">
                                {t('welcomeLead')}
                            </p>
                            <div className="flex flex-wrap gap-3">
                                <Link
                                    href="/login"
                                    className="rounded-full bg-[oklch(0.32_0.05_175)] px-5 py-2.5 text-sm font-medium text-[oklch(0.97_0.01_90)] shadow-sm transition-colors hover:bg-[oklch(0.38_0.06_175)] focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                >
                                    {t('welcomeLogin')}
                                </Link>
                                <Link
                                    href="/campaigns/create"
                                    className="rounded-full bg-[oklch(0.82_0.12_85)] px-5 py-2.5 text-sm font-medium text-[oklch(0.25_0.04_175)] shadow-sm transition-colors hover:bg-[oklch(0.86_0.12_85)] focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                >
                                    {t('startCampaign')}
                                </Link>
                                <Link
                                    href="/register"
                                    className="rounded-full border border-border bg-card px-5 py-2.5 text-sm font-medium transition-colors hover:bg-accent focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                >
                                    {t('welcomeSignup')}
                                </Link>
                            </div>
                        </div>
                        <PhoneMock />
                    </section>

                    <section className="flex flex-col gap-6">
                        <div>
                            <h2 className="text-2xl font-semibold tracking-tight">
                                {t('howTitle')}
                            </h2>
                            <p className="mt-1 text-sm text-muted-foreground">
                                {t('howLead')}
                            </p>
                        </div>
                        <ol className="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                            {stepKeys.map(([title, body], index) => (
                                <li
                                    key={title}
                                    className="rounded-2xl border bg-card p-4 shadow-sm"
                                >
                                    <span className="flex size-8 items-center justify-center rounded-full bg-[oklch(0.93_0.04_85)] text-sm font-semibold text-[oklch(0.32_0.05_175)]">
                                        {index + 1}
                                    </span>
                                    <h3 className="mt-3 font-medium">
                                        {t(title)}
                                    </h3>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        {t(body)}
                                    </p>
                                </li>
                            ))}
                        </ol>
                    </section>

                    <section className="grid gap-4 lg:grid-cols-[0.9fr_1.1fr]">
                        <article className="rounded-3xl border bg-card p-5 shadow-sm md:p-6">
                            <p className="text-xs font-medium tracking-wide text-muted-foreground">
                                {t('mockWaTitle')}
                            </p>
                            <h2 className="mt-1 text-xl font-semibold">
                                {t('stepListen')}
                            </h2>
                            <div className="mt-4 flex flex-col gap-3">
                                <Bubble who={t('mockReader')} tone="reader">
                                    {t('mockIn1')}
                                </Bubble>
                                <Bubble who={t('mockDesk')} tone="desk">
                                    {t('mockOut1')}
                                </Bubble>
                                <div className="max-w-[85%] self-end rounded-2xl rounded-ee-sm bg-[oklch(0.93_0.03_160)] px-3 py-2 text-sm">
                                    <p className="text-xs text-muted-foreground">
                                        {t('mockReader')} · {t('audio')}
                                    </p>
                                    <div className="mt-2 flex items-center gap-2">
                                        <span className="size-8 shrink-0 rounded-full bg-[oklch(0.45_0.07_175)]" />
                                        <span className="h-1.5 flex-1 rounded-full bg-[oklch(0.45_0.07_175)]/30">
                                            <span className="block h-full w-2/3 rounded-full bg-[oklch(0.45_0.07_175)]" />
                                        </span>
                                        <span className="text-xs tabular-nums">
                                            0:08
                                        </span>
                                    </div>
                                </div>
                                <Bubble who={t('mockReader')} tone="reader">
                                    {t('mockIn2')}
                                </Bubble>
                                <Bubble who={t('mockDesk')} tone="desk">
                                    {t('mockOut2')}
                                </Bubble>
                            </div>
                        </article>
                        <article className="rounded-3xl bg-[oklch(0.28_0.045_175)] p-5 text-[oklch(0.97_0.01_90)] shadow-sm md:p-6">
                            <div className="flex items-center justify-between gap-3">
                                <div>
                                    <p className="text-xs tracking-wide text-[oklch(0.82_0.1_85)]">
                                        {t('topics')}
                                    </p>
                                    <h2 className="text-xl font-semibold">
                                        {t('stepTopics')}
                                    </h2>
                                </div>
                                <span className="rounded-full bg-white/10 px-3 py-1 text-xs">
                                    {t('live')}
                                </span>
                            </div>
                            <div className="mt-5 flex flex-col gap-3">
                                <TopicRow
                                    label={t('mockTopicA')}
                                    sub={t('mockTopicASub')}
                                    quote={t('mockTopicAQuote')}
                                    count={68}
                                    share={34}
                                />
                                <TopicRow
                                    label={t('mockTopicB')}
                                    sub={t('mockTopicBSub')}
                                    quote={t('mockTopicBQuote')}
                                    count={42}
                                    share={21}
                                />
                                <TopicRow
                                    label={t('mockTopicC')}
                                    sub={t('mockTopicCSub')}
                                    quote={t('mockTopicCQuote')}
                                    count={26}
                                    share={13}
                                />
                            </div>
                        </article>
                    </section>

                    <section className="flex flex-col gap-4">
                        <h2 className="text-2xl font-semibold tracking-tight">
                            {t('whoTitle')}
                        </h2>
                        <div className="grid gap-4 md:grid-cols-2">
                            <article className="rounded-3xl border bg-card p-6 shadow-sm">
                                <h3 className="text-lg font-semibold">
                                    {t('whoNewsrooms')}
                                </h3>
                                <p className="mt-2 text-sm text-muted-foreground">
                                    {t('whoNewsroomsBody')}
                                </p>
                            </article>
                            <article className="rounded-3xl border bg-card p-6 shadow-sm">
                                <h3 className="text-lg font-semibold">
                                    {t('whoFunders')}
                                </h3>
                                <p className="mt-2 text-sm text-muted-foreground">
                                    {t('whoFundersBody')}
                                </p>
                            </article>
                        </div>
                    </section>

                    <section className="rounded-3xl border border-[oklch(0.82_0.08_85)] bg-[oklch(0.96_0.03_90)] p-6">
                        <h2 className="text-lg font-semibold">
                            {t('privacyTitle')}
                        </h2>
                        <p className="mt-2 max-w-3xl text-sm text-muted-foreground">
                            {t('law')}
                        </p>
                        <p className="mt-2 text-sm text-muted-foreground">
                            {t('stopHint')}
                        </p>
                    </section>

                    <section className="dalil-zellige overflow-hidden rounded-3xl bg-[oklch(0.32_0.05_175)] px-6 py-10 text-[oklch(0.97_0.01_90)] md:px-10">
                        <p className="text-sm text-[oklch(0.82_0.1_85)]">
                            {t('youAskedAr')}
                        </p>
                        <h2 className="mt-2 max-w-xl text-3xl font-semibold tracking-tight">
                            {t('footerCta')}
                        </h2>
                        <p className="mt-3 max-w-xl text-sm text-[oklch(0.9_0.02_90)]">
                            {t('footerCtaBody')}
                        </p>
                        <div className="mt-6 flex flex-wrap gap-3">
                            <Link
                                href="/login"
                                className="rounded-full bg-[oklch(0.82_0.12_85)] px-5 py-2.5 text-sm font-medium text-[oklch(0.25_0.04_175)]"
                            >
                                {t('welcomeLogin')}
                            </Link>
                            <Link
                                href="/register"
                                className="rounded-full border border-white/30 px-5 py-2.5 text-sm"
                            >
                                {t('welcomeSignup')}
                            </Link>
                        </div>
                    </section>
                </main>
                <footer className="mx-auto flex w-full max-w-6xl items-center justify-between px-4 py-8 text-xs text-muted-foreground md:px-6">
                    <span>Dalil News</span>
                    <span>{t('hackathon')}</span>
                </footer>
            </div>
        </>
    );
}

function PhoneMock() {
    const { t } = useI18n();

    return (
        <div className="mx-auto w-full max-w-sm rounded-[2rem] border-8 border-[oklch(0.25_0.03_175)] bg-[oklch(0.94_0.02_150)] shadow-xl">
            <div className="flex items-center justify-between rounded-t-[1.3rem] bg-[oklch(0.38_0.06_175)] px-4 py-3 text-[oklch(0.97_0.01_90)]">
                <div>
                    <p className="text-xs text-white/70">{t('mockWaTitle')}</p>
                    <p className="text-sm font-medium">Dalil News</p>
                </div>
                <span className="text-xs">{t('live')}</span>
            </div>
            <div className="flex flex-col gap-2 px-3 py-4">
                <Bubble who={t('mockDesk')} tone="desk">
                    {t('stepQuestionBody')}
                </Bubble>
                <Bubble who={t('mockReader')} tone="reader">
                    {t('mockIn1')}
                </Bubble>
                <Bubble who={t('mockDesk')} tone="desk">
                    {t('mockOut2')}
                </Bubble>
            </div>
        </div>
    );
}

function Bubble({
    who,
    tone,
    children,
}: {
    who: string;
    tone: 'reader' | 'desk';
    children: string;
}) {
    const reader = tone === 'reader';

    return (
        <div
            className={
                reader
                    ? 'max-w-[85%] self-end rounded-2xl rounded-ee-sm bg-[oklch(0.86_0.08_155)] px-3 py-2 text-sm text-[oklch(0.22_0.03_175)]'
                    : 'max-w-[85%] self-start rounded-2xl rounded-es-sm bg-card px-3 py-2 text-sm shadow-sm'
            }
        >
            <p className="mb-0.5 text-[10px] font-medium tracking-wide text-muted-foreground">
                {who}
            </p>
            {children}
        </div>
    );
}

function TopicRow({
    label,
    sub,
    quote,
    count,
    share,
}: {
    label: string;
    sub: string;
    quote: string;
    count: number;
    share: number;
}) {
    return (
        <div className="rounded-2xl bg-white/10 px-4 py-3">
            <div className="flex items-start justify-between gap-3">
                <div>
                    <p className="font-medium">{label}</p>
                    <p className="text-xs text-white/70">{sub}</p>
                </div>
                <p className="text-2xl font-semibold tabular-nums">{count}</p>
            </div>
            <div className="mt-2">
                <Meter value={share} />
            </div>
            <p className="mt-2 text-sm text-white/85">«{quote}»</p>
        </div>
    );
}
