import { Form, Head, usePoll } from '@inertiajs/react';
import { useState } from 'react';
import { CampaignNav } from '@/components/dalil/campaign-nav';
import { DemoBanner } from '@/components/dalil/demo-banner';
import { Button } from '@/components/ui/button';
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
    const [editing, setEditing] = useState<number | null>(null);
    usePoll(4000, { only: ['replies', 'campaign'] });

    return (
        <>
            <Head title={t('inbox')} />
            <div className="flex flex-col gap-6 p-4 md:p-6">
                <div className="flex items-center justify-between gap-3">
                    <div>
                        <h1 className="text-2xl font-semibold">
                            {campaign.title}
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            {t('inboxLead')}
                        </p>
                    </div>
                    <span className="rounded-full bg-emerald-700 px-3 py-1 text-xs text-white">
                        {t('live')}
                    </span>
                </div>
                {campaign.has_test_data ? <DemoBanner /> : null}
                <CampaignNav campaignId={campaign.id} current="inbox" />
                {replies.length === 0 ? (
                    <p className="text-sm text-muted-foreground">
                        {t('noReplies')}
                    </p>
                ) : (
                    <div className="flex flex-col gap-3">
                        {replies.map((reply) => (
                            <article
                                key={reply.id}
                                className="rounded-2xl border bg-card p-4"
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
                                        <span className="text-amber-800">
                                            {t('demoData')}
                                        </span>
                                    ) : null}
                                    {reply.edited ? (
                                        <span>{t('edited')}</span>
                                    ) : null}
                                </div>
                                <p className="mt-2 text-base leading-relaxed">
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
                                                    className="rounded-md border border-input px-3 py-2 text-sm"
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
