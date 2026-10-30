import { Head, useForm, usePoll } from '@inertiajs/react';
import { CampaignNav } from '@/components/dalil/campaign-nav';
import { Meter } from '@/components/dalil/meter';
import { Button } from '@/components/ui/button';
import { useCrumbs } from '@/hooks/use-crumbs';
import { useI18n } from '@/lib/i18n';
import type { Campaign, VoteRow } from '@/types/dalil';

type TopicOption = {
    id: number;
    label_ar: string;
    label_fr: string;
    replies_count: number;
};

export default function Votes({
    campaign,
    topics,
    tally,
}: {
    campaign: Campaign;
    topics: TopicOption[];
    tally: { total: number; rows: VoteRow[] };
}) {
    const { t } = useI18n();
    useCrumbs([
        { title: t('navDashboard'), href: '/dashboard' },
        { title: t('campaigns'), href: '/campaigns' },
        { title: t('vote'), href: `/campaigns/${campaign.id}/votes` },
    ]);
    const form = useForm<{ topic_ids: number[] }>({
        topic_ids: campaign.vote_options.map((option) => option.topic_id),
    });
    usePoll(4000, { only: ['tally', 'campaign'] });

    const toggle = (id: number, checked: boolean) => {
        const next = checked
            ? [...form.data.topic_ids, id]
            : form.data.topic_ids.filter((current) => current !== id);
        form.setData('topic_ids', next);
    };

    return (
        <>
            <Head title={t('vote')} />
            <div className="flex flex-col gap-6 p-4 md:p-6">
                <div>
                    <h1 className="text-2xl sm:text-3xl font-bold tracking-tight text-ink">{campaign.title}</h1>
                    <p className="mt-1 text-sm text-ink/80">
                        {t('voteLead')}
                    </p>
                </div>
                <CampaignNav campaignId={campaign.id} current="vote" />
                <form
                    className="grid gap-3 rounded-2xl border bg-card p-4 shadow-sm"
                    onSubmit={(event) => {
                        event.preventDefault();
                        form.post(`/campaigns/${campaign.id}/votes`);
                    }}
                >
                    <h2 className="text-base font-bold text-ink">{t('pickTopics')}</h2>
                    <p className="text-xs text-muted-foreground">
                        {t('changeVote')}
                    </p>
                    {topics.length === 0 ? (
                        <p className="text-sm text-muted-foreground">
                            {t('noTopics')}
                        </p>
                    ) : (
                        topics.map((topic) => (
                            <label
                                key={topic.id}
                                className="flex items-center justify-between gap-3 rounded-xl border px-3 py-2 transition-colors hover:bg-accent has-checked:border-primary has-checked:bg-accent"
                            >
                                <span className="flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        checked={form.data.topic_ids.includes(
                                            topic.id,
                                        )}
                                        onChange={(event) =>
                                            toggle(
                                                topic.id,
                                                event.target.checked,
                                            )
                                        }
                                    />
                                    <span className="font-semibold text-ink">
                                        {topic.label_ar}
                                        <span className="ms-2 font-normal text-xs text-ink/70">
                                            {topic.label_fr}
                                        </span>
                                    </span>
                                </span>
                                <span className="font-mono text-sm font-bold text-ink">
                                    {topic.replies_count}
                                </span>
                            </label>
                        ))
                    )}
                    <Button
                        type="submit"
                        disabled={
                            form.processing || form.data.topic_ids.length < 2
                        }
                    >
                        {t('sendVote')}
                    </Button>
                </form>
                <section className="rounded-2xl border bg-card p-4 shadow-sm">
                    <div className="flex items-baseline justify-between">
                        <h2 className="text-base font-bold text-ink">{t('results')}</h2>
                        <p className="text-sm font-semibold text-ink">
                            {tally.total} {t('votesCast')}
                        </p>
                    </div>
                    {tally.rows.length === 0 ? (
                        <p className="mt-3 text-sm text-muted-foreground">—</p>
                    ) : (
                        <ul className="mt-4 flex flex-col gap-3">
                            {tally.rows.map((row, index) => (
                                <li key={row.topic_id}>
                                    <div className="flex justify-between text-sm font-semibold text-ink">
                                        <span>
                                            {index === 0
                                                ? `${t('winner')} · `
                                                : ''}
                                            {row.label_ar}
                                        </span>
                                        <span className="font-mono">
                                            {row.count} · {row.share}%
                                        </span>
                                    </div>
                                    <div className="mt-1">
                                        <Meter value={row.share} />
                                    </div>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>
            </div>
        </>
    );
}

Votes.layout = {
    breadcrumbs: [
        { title: 'Dalil', href: '/dashboard' },
        { title: 'Vote', href: '/campaigns' },
    ],
};
