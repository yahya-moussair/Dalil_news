import { Head, Link, usePage } from '@inertiajs/react';
import { DemoBanner } from '@/components/dalil/demo-banner';
import { StatusPill } from '@/components/dalil/status-pill';
import { Button } from '@/components/ui/button';
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
            <div className="flex flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <p className="text-sm text-muted-foreground">
                            {room?.name}
                            {room?.city ? ` · ${room.city}` : ''}
                        </p>
                        <h1 className="text-2xl font-semibold tracking-tight">
                            {t('navDashboard')}
                        </h1>
                        <p className="mt-1 max-w-2xl text-sm text-muted-foreground">
                            {t('dashboardLead')}
                        </p>
                    </div>
                    <Button asChild>
                        <Link href="/campaigns/create">{t('newQuestion')}</Link>
                    </Button>
                </div>
                {demo ? <DemoBanner /> : null}
                {fake ? (
                    <p className="text-sm text-muted-foreground">
                        {t('fakeDriver')} · {drivers.messaging} /{' '}
                        {drivers.transcription} / {drivers.clustering}
                    </p>
                ) : null}
                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    {cards.map(([label, value]) => (
                        <div
                            key={label}
                            className="rounded-2xl border bg-card px-4 py-5"
                        >
                            <p className="text-sm text-muted-foreground">
                                {label}
                            </p>
                            <p className="mt-2 text-3xl font-semibold">
                                {value}
                            </p>
                        </div>
                    ))}
                </div>
                <div className="flex flex-col gap-3">
                    {campaigns.length === 0 ? (
                        <p className="text-sm text-muted-foreground">
                            {t('noCampaigns')}
                        </p>
                    ) : (
                        campaigns.map((campaign) => (
                            <Link
                                key={campaign.id}
                                href={`/campaigns/${campaign.id}`}
                                className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border bg-card px-4 py-4 hover:bg-accent"
                            >
                                <div>
                                    <div className="flex items-center gap-2">
                                        <h2 className="font-medium">
                                            {campaign.title}
                                        </h2>
                                        <StatusPill status={campaign.status} />
                                    </div>
                                    <p className="mt-1 line-clamp-2 text-sm text-muted-foreground">
                                        {campaign.question}
                                    </p>
                                </div>
                                <p className="text-sm">
                                    {campaign.metrics?.replies ??
                                        campaign.replies_count}{' '}
                                    {t('replies')}
                                </p>
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
