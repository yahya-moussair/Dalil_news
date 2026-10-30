import { Head, Link, usePage } from '@inertiajs/react';
import { DemoBanner } from '@/components/dalil/demo-banner';
import { EmptyState } from '@/components/dalil/empty-state';
import { PageHeader } from '@/components/dalil/page-header';
import { Stat } from '@/components/dalil/stat';
import { StatusPill } from '@/components/dalil/status-pill';
import { Button } from '@/components/ui/button';
import { useCrumbs } from '@/hooks/use-crumbs';
import { useI18n } from '@/lib/i18n';
import type { Campaign } from '@/types/dalil';

type Props = {
    stats: {
        subscribers: number;
        opted_out: number;
        campaigns: number;
        open: number;
    };
    campaigns: Campaign[];
    drivers: {
        messaging: string;
        transcription: string;
        clustering: string;
    };
};

export default function Dashboard({ stats, campaigns, drivers }: Props) {
    const { t } = useI18n();
    useCrumbs([{ title: t('navDashboard'), href: '/dashboard' }]);
    const { newsroom: room } = usePage().props;
    const fake = Object.values(drivers).some((driver) => driver === 'fake');
    const demo = campaigns.some((campaign) => campaign.has_test_data);

    const cards = [
        [t('subscribers'), stats.subscribers],
        [t('optedOut'), stats.opted_out],
        [t('campaigns'), stats.campaigns],
        [t('openQuestions'), stats.open],
    ] as const;

    return (
        <>
            <Head title={t('navDashboard')} />
            <div className="flex flex-col gap-6 p-4 md:p-8 bg-paper min-h-full">
                <PageHeader
                    kicker={
                        room
                            ? `${room.name}${room.city ? ` · ${room.city}` : ''}`
                            : undefined
                    }
                    title={t('navDashboard')}
                    lead={t('dashboardLead')}
                    actions={
                        <Button
                            asChild
                            className="bg-ink hover:bg-listen text-paper font-semibold px-4 py-2 transition-colors duration-200 shadow-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brass"
                        >
                            <Link href="/campaigns/create" className="flex items-center gap-2">
                                <span
                                    aria-hidden="true"
                                    className="font-bold text-brass leading-none"
                                >
                                    +
                                </span>
                                <span>{t('newQuestion')}</span>
                            </Link>
                        </Button>
                    }
                />

                {demo ? <DemoBanner /> : null}

                {fake ? (
                    <div className="inline-flex w-fit items-center gap-2 rounded-lg border border-ink/10 bg-sheet/80 px-3 py-1.5 font-mono text-xs text-ink/70">
                        <span className="size-1.5 rounded-full bg-brass" />
                        <span>
                            {t('fakeDriver')} · {drivers.messaging} /{' '}
                            {drivers.transcription} / {drivers.clustering}
                        </span>
                    </div>
                ) : null}

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    {cards.map(([label, value]) => (
                        <Stat key={label} label={label} value={value} />
                    ))}
                </div>

                <div className="flex flex-col gap-3 pt-2">
                    <div className="flex items-center justify-between pb-1">
                        <div className="flex items-center gap-2">
                            <h2 className="font-mono text-xs font-bold tracking-wider text-ink uppercase">
                                {t('campaigns')}
                            </h2>
                            <span className="rounded-full bg-ink/10 px-2 py-0.5 font-mono text-[11px] font-semibold text-ink">
                                {campaigns.length}
                            </span>
                        </div>
                        <span className="font-mono text-xs font-medium text-ink/70">
                            «سألتم، فأجبنا»
                        </span>
                    </div>

                    {campaigns.length === 0 ? (
                        <EmptyState>{t('noCampaigns')}</EmptyState>
                    ) : (
                        campaigns.map((campaign) => (
                            <Link
                                key={campaign.id}
                                href={`/campaigns/${campaign.id}`}
                                className="group flex flex-wrap items-center justify-between gap-4 rounded-xl border border-ink/10 bg-sheet p-5 shadow-xs transition-all duration-200 hover:border-listen/40 hover:bg-white hover:shadow-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brass"
                            >
                                <div className="min-w-0 max-w-3xl">
                                    <div className="flex items-center gap-3">
                                        <h3 className="text-base font-bold text-ink transition-colors group-hover:text-listen">
                                            {campaign.title}
                                        </h3>
                                        <StatusPill status={campaign.status} />
                                    </div>
                                    <p className="mt-1.5 line-clamp-2 text-sm leading-relaxed text-ink/75">
                                        {campaign.question}
                                    </p>
                                </div>
                                <div className="flex shrink-0 items-center gap-4">
                                    <span className="rounded-lg border border-ink/10 bg-paper px-3 py-1.5 font-mono text-xs font-medium text-ink">
                                        {campaign.metrics?.replies ??
                                            campaign.replies_count}{' '}
                                        {t('replies')}
                                    </span>
                                    <span
                                        aria-hidden="true"
                                        className="font-bold text-brass transition-transform duration-200 group-hover:translate-x-1 rtl:group-hover:-translate-x-1"
                                    >
                                        →
                                    </span>
                                </div>
                            </Link>
                        ))
                    )}
                </div>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [{ title: 'Dalil', href: '/dashboard' }],
};
