import { Head, Link } from '@inertiajs/react';
import { Meter } from '@/components/dalil/meter';
import { PageHeader } from '@/components/dalil/page-header';
import { Stat } from '@/components/dalil/stat';
import { StatusPill } from '@/components/dalil/status-pill';
import { useCrumbs } from '@/hooks/use-crumbs';
import { useI18n } from '@/lib/i18n';
import type { Campaign } from '@/types/dalil';

type Neighborhood = { name: string; total: number };

export default function Metrics({
    campaigns,
    neighborhoods,
    overview,
}: {
    campaigns: Campaign[];
    neighborhoods: Neighborhood[];
    overview: {
        subscribers: number;
        opted_out: number;
        replies: number;
        voice_percent: number;
        neighborhoods: number;
    };
}) {
    const { t } = useI18n();
    useCrumbs([
        { title: t('navDashboard'), href: '/dashboard' },
        { title: t('impact'), href: '/metrics' },
    ]);
    const max = Math.max(...neighborhoods.map((row) => row.total), 1);
    const cards = [
        [t('subscribers'), overview.subscribers],
        [t('optedOut'), overview.opted_out],
        [t('replies'), overview.replies],
        [t('voice'), `${overview.voice_percent}%`],
        [t('neighborhoods'), overview.neighborhoods],
    ] as const;

    return (
        <>
            <Head title={t('impact')} />
            <div className="flex flex-col gap-6 p-4 md:p-6">
                <PageHeader title={t('impact')} lead={t('metricsLead')} />
                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                    {cards.map(([label, value]) => (
                        <Stat key={label} label={label} value={value} />
                    ))}
                </div>
                <section className="rounded-2xl border bg-card p-5 shadow-sm">
                    <h2 className="font-medium">{t('neighborhoods')}</h2>
                    <ul className="mt-4 flex flex-col gap-3">
                        {neighborhoods.map((row) => (
                            <li key={row.name}>
                                <div className="flex justify-between text-sm">
                                    <span>{row.name}</span>
                                    <span>{row.total}</span>
                                </div>
                                <div className="mt-1">
                                    <Meter value={row.total} max={max} />
                                </div>
                            </li>
                        ))}
                    </ul>
                </section>
                <section className="overflow-x-auto rounded-2xl border bg-card shadow-sm">
                    <table className="w-full text-sm">
                        <thead className="bg-muted/40 text-muted-foreground">
                            <tr>
                                <th className="px-3 py-2 text-start font-normal">
                                    {t('title')}
                                </th>
                                <th className="px-3 py-2 text-start font-normal">
                                    {t('replies')}
                                </th>
                                <th className="px-3 py-2 text-start font-normal">
                                    {t('responseRate')}
                                </th>
                                <th className="px-3 py-2 text-start font-normal">
                                    {t('readRate')}
                                </th>
                                <th className="px-3 py-2 text-start font-normal">
                                    {t('daysToArticle')}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {campaigns.map((campaign) => (
                                <tr key={campaign.id} className="border-t">
                                    <td className="px-3 py-3">
                                        <Link
                                            href={`/campaigns/${campaign.id}`}
                                            className="font-medium"
                                        >
                                            {campaign.title}
                                        </Link>
                                        <div className="mt-1">
                                            <StatusPill
                                                status={campaign.status}
                                            />
                                        </div>
                                    </td>
                                    <td className="px-3 py-3">
                                        {campaign.metrics?.replies ?? 0}
                                    </td>
                                    <td className="px-3 py-3">
                                        {campaign.metrics?.response_rate ?? '—'}
                                        {campaign.metrics?.response_rate !==
                                            null &&
                                        campaign.metrics?.response_rate !==
                                            undefined
                                            ? '%'
                                            : ''}
                                    </td>
                                    <td className="px-3 py-3">
                                        {campaign.metrics?.read_rate ?? '—'}
                                        {campaign.metrics?.read_rate !== null &&
                                        campaign.metrics?.read_rate !==
                                            undefined
                                            ? '%'
                                            : ''}
                                    </td>
                                    <td className="px-3 py-3">
                                        {campaign.metrics?.days_to_article ??
                                            '—'}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </section>
            </div>
        </>
    );
}

Metrics.layout = {
    breadcrumbs: [
        { title: 'Dalil', href: '/dashboard' },
        { title: 'Impact', href: '/metrics' },
    ],
};
