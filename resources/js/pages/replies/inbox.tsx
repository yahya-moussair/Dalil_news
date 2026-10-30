import { Form, Head, usePoll } from '@inertiajs/react';
import { useState } from 'react';
import { CampaignNav } from '@/components/dalil/campaign-nav';
import { DemoBanner } from '@/components/dalil/demo-banner';
import { EmptyState } from '@/components/dalil/empty-state';
import { Button } from '@/components/ui/button';
import { useCrumbs } from '@/hooks/use-crumbs';
import { fieldClass } from '@/lib/field';
import { formatWhen, languageLabel } from '@/lib/format';
import { useI18n } from '@/lib/i18n';
import type { Campaign, ReplyRow } from '@/types/dalil';

export default function Inbox({
    campaign,
    replies,
}: {
    campaign: Campaign;
    replies: ReplyRow[];
}) {
    const { t, locale } = useI18n();
    useCrumbs([
        { title: t('navDashboard'), href: '/dashboard' },
        { title: t('campaigns'), href: '/campaigns' },
        { title: t('inbox'), href: `/campaigns/${campaign.id}/inbox` },
    ]);
    const [editing, setEditing] = useState<number | null>(null);
    usePoll(4000, { only: ['replies', 'campaign'] });

    return (
        <>
            <Head title={t('inbox')} />
            <div className="flex flex-col gap-6 p-4 md:p-6">
                <div className="flex items-center justify-between gap-3">
                    <div>
                        <h1 className="text-2xl sm:text-3xl font-bold tracking-tight text-ink">
                            {campaign.title}
                        </h1>
                        <p className="mt-1 text-sm text-ink/80">
                            {t('inboxLead')}
                        </p>
                    </div>
                    <span className="inline-flex items-center gap-1.5 rounded-full bg-listen px-3 py-1 text-xs font-semibold text-paper shadow-xs">
                        <span className="size-1.5 rounded-full bg-brass" />
                        {t('live')}
                    </span>
                </div>
                {campaign.has_test_data ? <DemoBanner /> : null}
                <CampaignNav campaignId={campaign.id} current="inbox" />
                {replies.length === 0 ? (
                    <EmptyState>{t('noReplies')}</EmptyState>
                ) : (
                    <div className="flex flex-col gap-3">
                        {replies.map((reply) => (
                            <article
                                key={reply.id}
                                className="rounded-2xl border bg-card p-4 shadow-sm"
                            >
                                <div className="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                                    <span>
                                        {formatWhen(reply.received_at, locale)}
                                    </span>
                                    {reply.neighborhood ? (
                                        <span>{reply.neighborhood}</span>
                                    ) : null}
                                    {languageLabel(reply.language, locale) ? (
                                        <span>
                                            {languageLabel(
                                                reply.language,
                                                locale,
                                            )}
                                        </span>
                                    ) : null}
                                    <span>
                                        {reply.type === 'audio'
                                            ? t('audio')
                                            : t('text')}
                                    </span>
                                    {reply.is_test_data ? (
                                        <span className="rounded-full bg-[oklch(0.94_0.04_85)] px-2 py-0.5 text-[oklch(0.35_0.06_60)]">
                                            {t('demoData')}
                                        </span>
                                    ) : null}
                                    {reply.edited ? (
                                        <span>{t('edited')}</span>
                                    ) : null}
                                </div>
                                <p className="mt-3 text-base leading-relaxed">
                                    {reply.body || '—'}
                                </p>
                                {reply.summary ? (
                                    <p className="mt-2 text-sm text-muted-foreground">
                                        {t('summary')}: {reply.summary}
                                    </p>
                                ) : null}
                                {reply.original ? (
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        {t('original')}: {reply.original}
                                    </p>
                                ) : null}
                                {reply.audio_url ? (
                                    <audio
                                        className="mt-3 w-full"
                                        controls
                                        src={reply.audio_url}
                                        preload="none"
                                    >
                                        {t('play')}
                                    </audio>
                                ) : null}
                                {editing === reply.id ? (
                                    <Form
                                        action={`/campaigns/${campaign.id}/messages/${reply.id}/transcript`}
                                        method="patch"
                                        className="mt-3 grid gap-2"
                                        onSuccess={() => setEditing(null)}
                                    >
                                        {({ processing }) => (
                                            <>
                                                <textarea
                                                    name="normalized_text"
                                                    defaultValue={reply.body}
                                                    rows={3}
                                                    required
                                                    className={fieldClass}
                                                />
                                                <Button
                                                    type="submit"
                                                    size="sm"
                                                    disabled={processing}
                                                >
                                                    {t('saveCorrection')}
                                                </Button>
                                            </>
                                        )}
                                    </Form>
                                ) : (
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        className="mt-2 px-0"
                                        onClick={() => setEditing(reply.id)}
                                    >
                                        {t('correct')}
                                    </Button>
                                )}
                            </article>
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}

Inbox.layout = {
    breadcrumbs: [
        { title: 'Dalil', href: '/dashboard' },
        { title: 'Inbox', href: '/campaigns' },
    ],
};
