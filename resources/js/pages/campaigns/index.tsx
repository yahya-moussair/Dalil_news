import { Head, Link } from '@inertiajs/react';
import { EmptyState } from '@/components/dalil/empty-state';
import { PageHeader } from '@/components/dalil/page-header';
import { StatusPill } from '@/components/dalil/status-pill';
import { Button } from '@/components/ui/button';
import { useCrumbs } from '@/hooks/use-crumbs';
import { formatWhen } from '@/lib/format';
import { useI18n } from '@/lib/i18n';
import type { Campaign } from '@/types/dalil';

export default function CampaignIndex({
    campaigns,
}: {
    campaigns: Campaign[];
}) {
    const { t, locale } = useI18n();
    useCrumbs([
        { title: t('navDashboard'), href: '/dashboard' },
        { title: t('campaigns'), href: '/campaigns' },
    ]);

    return (
        <>
            <Head title={t('campaigns')} />
            <div className="flex flex-col gap-6 p-4 md:p-6">
                <PageHeader
                    title={t('campaigns')}
                    actions={
                        <Button asChild>
                            <Link href="/campaigns/create">
                                {t('newQuestion')}
                            </Link>
                        </Button>
                    }
                />
                {campaigns.length === 0 ? (
                    <EmptyState>{t('noCampaigns')}</EmptyState>
                ) : (
                    <div className="overflow-hidden rounded-2xl border">
                        {campaigns.map((campaign) => (
                            <Link
                                key={campaign.id}
                                href={`/campaigns/${campaign.id}`}
                                className="flex flex-wrap items-center justify-between gap-3 border-b bg-card px-4 py-4 transition-colors last:border-b-0 hover:bg-accent focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                            >
                                <div>
                                    <div className="flex items-center gap-2">
                                        <span className="font-medium">
                                            {campaign.title}
                                        </span>
                                        <StatusPill status={campaign.status} />
                                        {campaign.has_test_data ? (
                                            <span className="rounded-full bg-[oklch(0.94_0.04_85)] px-2 py-0.5 text-xs text-[oklch(0.35_0.06_60)]">
                                                {t('demoData')}
                                            </span>
                                        ) : null}
                                    </div>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        {campaign.replies_count} {t('replies')}{' '}
                                        · {campaign.voice_count} {t('voice')}
                                    </p>
                                </div>
                                <span className="text-xs text-muted-foreground">
                                    {formatWhen(campaign.sent_at, locale)}
                                </span>
                            </Link>
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}

CampaignIndex.layout = {
    breadcrumbs: [
        { title: 'Dalil', href: '/dashboard' },
        { title: 'Questions', href: '/campaigns' },
    ],
};
