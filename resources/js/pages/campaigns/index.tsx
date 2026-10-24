import { Head, Link } from '@inertiajs/react';
import { StatusPill } from '@/components/dalil/status-pill';
import { Button } from '@/components/ui/button';
import { formatWhen } from '@/lib/format';
import { useI18n } from '@/lib/i18n';
import type { Campaign } from '@/types/dalil';

export default function CampaignIndex({
    campaigns,
}: {
    campaigns: Campaign[];
}) {
    const { t, locale } = useI18n();

    return (
        <>
            <Head title={t('campaigns')} />
            <div className="flex flex-col gap-6 p-4 md:p-6">
                <div className="flex items-end justify-between gap-3">
                    <h1 className="text-2xl font-semibold">{t('campaigns')}</h1>
                    <Button asChild>
                        <Link href="/campaigns/create">{t('newQuestion')}</Link>
                    </Button>
                </div>
                {campaigns.length === 0 ? (
                    <p className="text-sm text-muted-foreground">
                        {t('noCampaigns')}
                    </p>
                ) : (
                    <div className="overflow-hidden rounded-2xl border">
                        {campaigns.map((campaign) => (
                            <Link
                                key={campaign.id}
                                href={`/campaigns/${campaign.id}`}
                                className="flex flex-wrap items-center justify-between gap-3 border-b bg-card px-4 py-4 last:border-b-0 hover:bg-accent"
                            >
                                <div>
                                    <div className="flex items-center gap-2">
                                        <span className="font-medium">
                                            {campaign.title}
                                        </span>
                                        <StatusPill status={campaign.status} />
                                        {campaign.has_test_data ? (
                                            <span className="text-xs text-amber-800">
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
