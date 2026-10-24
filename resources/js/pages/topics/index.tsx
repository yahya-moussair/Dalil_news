import { Form, Head, router, usePoll } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import { CampaignNav } from '@/components/dalil/campaign-nav';
import { DemoBanner } from '@/components/dalil/demo-banner';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { languageLabel } from '@/lib/format';
import { useI18n } from '@/lib/i18n';
import type { Campaign, TopicCard } from '@/types/dalil';

export default function Topics({
    campaign,
    topics,
    total,
}: {
    campaign: Campaign;
    topics: TopicCard[];
    total: number;
}) {
    const { t, locale } = useI18n();
    const [neighborhood, setNeighborhood] = useState('all');
    const [language, setLanguage] = useState('all');
    usePoll(4000, { only: ['topics', 'total', 'campaign'] });

    const neighborhoods = useMemo(() => {
        const names = new Set<string>();
        topics.forEach((topic) => {
            topic.neighborhoods.forEach((row) => names.add(row.name));
        });

        return [...names];
    }, [topics]);

    const languages = useMemo(() => {
        const codes = new Set<string>();
        topics.forEach((topic) => {
            topic.languages.forEach((code) => codes.add(code));
        });

        return [...codes];
    }, [topics]);

    const visible = topics.filter((topic) => {
        const neighborhoodOk =
            neighborhood === 'all' ||
            topic.neighborhoods.some((row) => row.name === neighborhood);
        const languageOk =
            language === 'all' || topic.languages.includes(language);

        return neighborhoodOk && languageOk;
    });

    return (
        <>
            <Head title={t('topics')} />
            <div className="flex flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h1 className="text-2xl font-semibold">
                            {campaign.title}
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            {t('topicsLead')} · {total} {t('replies')}
                        </p>
                    </div>
                    <Button
                        type="button"
                        variant="outline"
                        onClick={() =>
                            router.post(`/campaigns/${campaign.id}/recluster`)
                        }
                    >
                        {t('recluster')}
                    </Button>
                </div>
                {campaign.has_test_data ? <DemoBanner /> : null}
                <CampaignNav campaignId={campaign.id} current="topics" />
                <div className="flex flex-wrap gap-2">
                    <select
                        value={neighborhood}
                        onChange={(event) =>
                            setNeighborhood(event.target.value)
                        }
                        className="h-9 rounded-md border border-input bg-card px-2 text-sm"
                    >
                        <option value="all">{t('filterAll')}</option>
                        {neighborhoods.map((name) => (
                            <option key={name} value={name}>
                                {name}
                            </option>
                        ))}
                    </select>
                    <select
                        value={language}
                        onChange={(event) => setLanguage(event.target.value)}
                        className="h-9 rounded-md border border-input bg-card px-2 text-sm"
                    >
                        <option value="all">{t('filterAll')}</option>
                        {languages.map((code) => (
                            <option key={code} value={code}>
                                {languageLabel(code, locale) ?? code}
                            </option>
                        ))}
                    </select>
                </div>
                {visible.length === 0 ? (
                    <p className="text-sm text-muted-foreground">
                        {t('noTopics')}
                    </p>
                ) : (
                    <div className="grid gap-4">
                        {visible.map((topic) => (
                            <article
                                key={topic.id}
                                className="rounded-2xl border bg-card p-5"
                            >
                                <div className="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <h2 className="text-xl font-semibold">
                                            {topic.label_ar}
                                        </h2>
                                        <p className="text-sm text-muted-foreground">
                                            {topic.label_fr}
                                        </p>
                                    </div>
                                    <div className="text-end">
                                        <p className="text-3xl font-semibold">
                                            {topic.replies_count}
                                        </p>
                                        <p className="text-xs text-muted-foreground">
                                            {topic.share}% {t('share')}
                                        </p>
                                    </div>
                                </div>
                                <div className="mt-3 h-2 overflow-hidden rounded-full bg-muted">
                                    <div
                                        className="h-full bg-[oklch(0.45_0.08_175)]"
                                        style={{
                                            width: `${Math.max(topic.share, 2)}%`,
                                        }}
                                    />
                                </div>
                                {topic.summary ? (
                                    <p className="mt-3 text-sm">
                                        {topic.summary}
                                    </p>
                                ) : null}
                                <div className="mt-2 flex flex-wrap gap-2 text-xs text-muted-foreground">
                                    {topic.is_locked ? (
                                        <span>{t('locked')}</span>
                                    ) : null}
                                    {topic.neighborhoods.map((row) => (
                                        <span key={row.name}>
                                            {row.name} {row.count}
                                        </span>
                                    ))}
                                </div>
                                <h3 className="mt-4 text-sm font-medium">
                                    {t('quotes')}
                                </h3>
                                {topic.quotes.length === 0 ? (
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        {t('emptyQuotes')}
                                    </p>
                                ) : (
                                    <ul className="mt-2 flex flex-col gap-3">
                                        {topic.quotes.map((quote) => (
                                            <li
                                                key={quote.message_id}
                                                className="rounded-xl bg-muted/60 px-3 py-3"
                                            >
                                                <p>«{quote.text}»</p>
                                                <p className="mt-1 text-xs text-muted-foreground">
                                                    {quote.neighborhood}{' '}
                                                    {languageLabel(
                                                        quote.language,
                                                        locale,
                                                    )}
                                                    {quote.is_test_data
                                                        ? ` · ${t('demoData')}`
                                                        : ''}
                                                </p>
                                                {quote.audio_url ? (
                                                    <audio
                                                        className="mt-2 w-full"
                                                        controls
                                                        src={quote.audio_url}
                                                        preload="none"
                                                    >
                                                        {t('play')}
                                                    </audio>
                                                ) : null}
                                            </li>
                                        ))}
                                    </ul>
                                )}
                                <Form
                                    action={`/campaigns/${campaign.id}/topics/${topic.id}`}
                                    method="patch"
                                    className="mt-4 grid gap-2 border-t pt-3"
                                >
                                    {({ processing }) => (
                                        <>
                                            <div className="grid gap-2 sm:grid-cols-2">
                                                <Input
                                                    name="label_ar"
                                                    defaultValue={
                                                        topic.label_ar
                                                    }
                                                    aria-label={t('title')}
                                                />
                                                <Input
                                                    name="label_fr"
                                                    defaultValue={
                                                        topic.label_fr
                                                    }
                                                    aria-label={t('title')}
                                                />
                                            </div>
                                            <textarea
                                                name="summary"
                                                defaultValue={
                                                    topic.summary ?? ''
                                                }
                                                rows={2}
                                                className="rounded-md border border-input px-3 py-2 text-sm"
                                            />
                                            <Button
                                                type="submit"
                                                size="sm"
                                                variant="outline"
                                                disabled={processing}
                                            >
                                                {t('rename')}
                                            </Button>
                                        </>
                                    )}
                                </Form>
                            </article>
                        ))}
                    </div>
                )}
                {topics.length > 1 ? (
                    <Form
                        action={`/campaigns/${campaign.id}/topics/merge`}
                        method="post"
                        className="grid gap-3 rounded-2xl border p-4 sm:grid-cols-[1fr_auto_1fr_auto] sm:items-end"
                    >
                        {({ processing }) => (
                            <>
                                <div className="grid gap-1">
                                    <Label htmlFor="source_id">
                                        {t('merge')}
                                    </Label>
                                    <select
                                        id="source_id"
                                        name="source_id"
                                        className="h-9 rounded-md border border-input px-2 text-sm"
                                        defaultValue={topics[1]?.id}
                                    >
                                        {topics.map((topic) => (
                                            <option
                                                key={topic.id}
                                                value={topic.id}
                                            >
                                                {topic.label_ar}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                                <span className="pb-2 text-sm text-muted-foreground">
                                    {t('mergeInto')}
                                </span>
                                <select
                                    name="target_id"
                                    className="h-9 rounded-md border border-input px-2 text-sm"
                                    defaultValue={topics[0]?.id}
                                >
                                    {topics.map((topic) => (
                                        <option key={topic.id} value={topic.id}>
                                            {topic.label_ar}
                                        </option>
                                    ))}
                                </select>
                                <Button type="submit" disabled={processing}>
                                    {t('merge')}
                                </Button>
                            </>
                        )}
                    </Form>
                ) : null}
            </div>
        </>
    );
}

Topics.layout = {
    breadcrumbs: [
        { title: 'Dalil', href: '/dashboard' },
        { title: 'Topics', href: '/campaigns' },
    ],
};
