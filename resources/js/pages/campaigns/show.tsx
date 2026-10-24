import { Head, router, useForm } from '@inertiajs/react';
import { CampaignNav } from '@/components/dalil/campaign-nav';
import { DemoBanner } from '@/components/dalil/demo-banner';
import { Stat } from '@/components/dalil/stat';
import { StatusPill } from '@/components/dalil/status-pill';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useCrumbs } from '@/hooks/use-crumbs';
import { fieldClass } from '@/lib/field';
import { formatWhen, money } from '@/lib/format';
import { useI18n, type CopyKey } from '@/lib/i18n';
import type { BroadcastRow, Campaign } from '@/types/dalil';

type Props = {
    campaign: Campaign;
    broadcasts: BroadcastRow[];
    canSimulate: boolean;
    neighborhoods: string[];
    drivers: { messaging: string; transcription: string };
};

export default function CampaignShow({
    campaign,
    broadcasts,
    canSimulate,
    neighborhoods,
    drivers,
}: Props) {
    const { t, locale } = useI18n();
    useCrumbs([
        { title: t('navDashboard'), href: '/dashboard' },
        { title: t('campaigns'), href: '/campaigns' },
        { title: t('overview'), href: `/campaigns/${campaign.id}` },
    ]);
    const metrics = campaign.metrics;
    const testSend = useForm({ phone: '' });
    const simulate = useForm({
        body: 'الما كيتقطع فسيدي مومن من الصباح',
        type: 'text',
        neighborhood: neighborhoods[0] ?? '',
    });

    const stats = [
        [t('replies'), metrics?.replies ?? campaign.replies_count],
        [t('participants'), metrics?.participants ?? '—'],
        [
            t('voice'),
            metrics ? `${metrics.voice_percent}%` : campaign.voice_count,
        ],
        [
            t('responseRate'),
            metrics?.response_rate !== null &&
            metrics?.response_rate !== undefined
                ? `${metrics.response_rate}%`
                : '—',
        ],
    ] as const;

    return (
        <>
            <Head title={campaign.title} />
            <div className="flex flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <div className="flex items-center gap-2">
                            <h1 className="text-2xl font-semibold">
                                {campaign.title}
                            </h1>
                            <StatusPill status={campaign.status} />
                        </div>
                        <p className="mt-2 max-w-2xl text-sm">
                            {campaign.question}
                        </p>
                    </div>
                    {campaign.status === 'draft' ? (
                        <Button
                            type="button"
                            onClick={() =>
                                router.post(`/campaigns/${campaign.id}/send`)
                            }
                        >
                            {t('queueSend')}
                        </Button>
                    ) : null}
                </div>
                {campaign.has_test_data || metrics?.has_test_data ? (
                    <DemoBanner />
                ) : null}
                <CampaignNav campaignId={campaign.id} current="overview" />
                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    {stats.map(([label, value]) => (
                        <Stat key={label} label={label} value={value} />
                    ))}
                </div>
                <section className="rounded-2xl border bg-card p-4">
                    <h2 className="font-medium">{t('broadcasts')}</h2>
                    {broadcasts.length === 0 ? (
                        <p className="mt-2 text-sm text-muted-foreground">—</p>
                    ) : (
                        <div className="mt-3 overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead className="text-muted-foreground">
                                    <tr>
                                        <th className="py-2 text-start font-normal">
                                            {t('kind')}
                                        </th>
                                        <th className="py-2 text-start font-normal">
                                            {t('sent')}
                                        </th>
                                        <th className="py-2 text-start font-normal">
                                            {t('delivered')}
                                        </th>
                                        <th className="py-2 text-start font-normal">
                                            {t('read')}
                                        </th>
                                        <th className="py-2 text-start font-normal">
                                            {t('failed')}
                                        </th>
                                        <th className="py-2 text-start font-normal">
                                            {t('cost')}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {broadcasts.map((row) => (
                                        <tr key={row.id} className="border-t">
                                            <td className="py-2">
                                                {kindLabel(row.kind, t)}
                                            </td>
                                            <td>{row.sent}</td>
                                            <td>{row.delivered}</td>
                                            <td>{row.read}</td>
                                            <td>{row.failed}</td>
                                            <td>
                                                {money(
                                                    row.cost_estimate,
                                                    locale,
                                                )}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                    <p className="mt-3 text-xs text-muted-foreground">
                        {t('sent')} {formatWhen(campaign.sent_at, locale)}
                    </p>
                </section>
                <div className="grid gap-4 lg:grid-cols-2">
                    <form
                        className="grid gap-3 rounded-2xl border bg-card p-4 shadow-sm"
                        onSubmit={(event) => {
                            event.preventDefault();
                            testSend.post(
                                `/campaigns/${campaign.id}/test-send`,
                            );
                        }}
                    >
                        <h2 className="font-medium">{t('testSend')}</h2>
                        <p className="text-xs text-muted-foreground">
                            {t('testHint')}
                        </p>
                        <Label htmlFor="phone">{t('testPhone')}</Label>
                        <Input
                            id="phone"
                            value={testSend.data.phone}
                            onChange={(event) =>
                                testSend.setData('phone', event.target.value)
                            }
                            placeholder="2126…"
                        />
                        <InputError message={testSend.errors.phone} />
                        <Button
                            type="submit"
                            disabled={testSend.processing}
                            variant="outline"
                        >
                            {t('testSend')}
                        </Button>
                    </form>
                    {canSimulate ? (
                        <form
                            className="grid gap-3 rounded-2xl border bg-card p-4 shadow-sm"
                            onSubmit={(event) => {
                                event.preventDefault();
                                simulate.post(
                                    `/campaigns/${campaign.id}/simulate`,
                                );
                            }}
                        >
                            <h2 className="font-medium">{t('simulate')}</h2>
                            <p className="text-xs text-muted-foreground">
                                {t('simulateHint')}
                                {drivers.transcription !== 'fake'
                                    ? ` · ${drivers.transcription}`
                                    : ''}
                            </p>
                            <textarea
                                value={simulate.data.body}
                                onChange={(event) =>
                                    simulate.setData('body', event.target.value)
                                }
                                rows={3}
                                className={fieldClass}
                            />
                            <div className="grid grid-cols-2 gap-2">
                                <select
                                    value={simulate.data.type}
                                    onChange={(event) =>
                                        simulate.setData(
                                            'type',
                                            event.target.value,
                                        )
                                    }
                                    className={fieldClass}
                                >
                                    <option value="text">{t('text')}</option>
                                    <option value="audio">{t('audio')}</option>
                                </select>
                                <select
                                    value={simulate.data.neighborhood}
                                    onChange={(event) =>
                                        simulate.setData(
                                            'neighborhood',
                                            event.target.value,
                                        )
                                    }
                                    className={fieldClass}
                                >
                                    {neighborhoods.map((name) => (
                                        <option key={name} value={name}>
                                            {name}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <Button
                                type="submit"
                                disabled={simulate.processing}
                                variant="outline"
                            >
                                {t('simulate')}
                            </Button>
                        </form>
                    ) : null}
                </div>
            </div>
        </>
    );
}

function kindLabel(kind: string, t: (key: CopyKey) => string): string {
    if (kind === 'question') {
        return t('kindQuestion');
    }

    if (kind === 'vote') {
        return t('kindVote');
    }

    if (kind === 'reply_back') {
        return t('kindReplyBack');
    }

    return kind;
}

CampaignShow.layout = {
    breadcrumbs: [
        { title: 'Dalil', href: '/dashboard' },
        { title: 'Questions', href: '/campaigns' },
    ],
};
