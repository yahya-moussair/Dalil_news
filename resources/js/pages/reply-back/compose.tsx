import { Form, Head } from '@inertiajs/react';
import { useState } from 'react';
import { CampaignNav } from '@/components/dalil/campaign-nav';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useCrumbs } from '@/hooks/use-crumbs';
import { fieldClass } from '@/lib/field';
import { money } from '@/lib/format';
import { useI18n } from '@/lib/i18n';
import type { Campaign } from '@/types/dalil';

export default function ReplyBack({
    campaign,
    recipients,
    cost_estimate,
}: {
    campaign: Campaign;
    recipients: number;
    cost_estimate: number;
    rate: number;
}) {
    const { t, locale } = useI18n();
    useCrumbs([
        { title: t('navDashboard'), href: '/dashboard' },
        { title: t('campaigns'), href: '/campaigns' },
        { title: t('article'), href: `/campaigns/${campaign.id}/reply-back` },
    ]);
    const [title, setTitle] = useState(campaign.article_title ?? '');
    const [url, setUrl] = useState(campaign.article_url ?? 'https://');
    const [note, setNote] = useState(campaign.article_note ?? '');
    const preview = [
        t('youAskedAr'),
        title || t('articleTitle'),
        note,
        url,
        t('thanksShare'),
        t('youAsked'),
    ]
        .filter((line) => line.trim() !== '')
        .join('\n');

    return (
        <>
            <Head title={t('article')} />
            <div className="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4 md:p-6">
                <div>
                    <h1 className="text-2xl font-semibold">
                        {t('youAskedAr')}
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        {t('youAsked')}
                    </p>
                    <p className="mt-2 text-sm text-muted-foreground">
                        {t('replyLead')}
                    </p>
                </div>
                <CampaignNav campaignId={campaign.id} current="article" />
                <p className="text-sm">
                    {t('recipients')}: {recipients} · {t('estimatedCost')}{' '}
                    {money(cost_estimate, locale)}
                </p>
                {campaign.article_sent_at ? (
                    <p className="text-sm text-emerald-800">
                        {t('sent')} · {campaign.article_sent_at}
                    </p>
                ) : null}
                <Form
                    action={`/campaigns/${campaign.id}/reply-back`}
                    method="post"
                    className="grid gap-4"
                >
                    {({ errors, processing }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="article_title">
                                    {t('articleTitle')}
                                </Label>
                                <Input
                                    id="article_title"
                                    name="article_title"
                                    value={title}
                                    onChange={(event) =>
                                        setTitle(event.target.value)
                                    }
                                    required
                                />
                                <InputError message={errors.article_title} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="article_url">
                                    {t('articleUrl')}
                                </Label>
                                <Input
                                    id="article_url"
                                    name="article_url"
                                    type="url"
                                    value={url}
                                    onChange={(event) =>
                                        setUrl(event.target.value)
                                    }
                                    required
                                />
                                <InputError message={errors.article_url} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="note">{t('note')}</Label>
                                <textarea
                                    id="note"
                                    name="note"
                                    value={note}
                                    onChange={(event) =>
                                        setNote(event.target.value)
                                    }
                                    rows={3}
                                    className={fieldClass}
                                />
                            </div>
                            <div className="rounded-[1.6rem] border-8 border-[oklch(0.25_0.03_175)] bg-[oklch(0.94_0.02_150)] p-4 text-sm whitespace-pre-wrap text-foreground shadow-sm">
                                <p className="mb-2 text-xs font-medium text-muted-foreground">
                                    {t('preview')}
                                </p>
                                {preview}
                            </div>
                            <Button type="submit" disabled={processing}>
                                {t('sendArticle')}
                            </Button>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

ReplyBack.layout = {
    breadcrumbs: [
        { title: 'Dalil', href: '/dashboard' },
        { title: 'Article', href: '/campaigns' },
    ],
};
