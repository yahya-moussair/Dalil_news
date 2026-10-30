import { Link } from '@inertiajs/react';
import { useI18n, type CopyKey } from '@/lib/i18n';

export function CampaignNav({
    campaignId,
    current,
}: {
    campaignId: number;
    current: 'overview' | 'inbox' | 'topics' | 'vote' | 'article';
}) {
    const { t } = useI18n();
    const items: { key: typeof current; href: string; label: CopyKey }[] = [
        {
            key: 'overview',
            href: `/campaigns/${campaignId}`,
            label: 'overview',
        },
        {
            key: 'inbox',
            href: `/campaigns/${campaignId}/inbox`,
            label: 'inbox',
        },
        {
            key: 'topics',
            href: `/campaigns/${campaignId}/topics`,
            label: 'topics',
        },
        {
            key: 'vote',
            href: `/campaigns/${campaignId}/votes`,
            label: 'vote',
        },
        {
            key: 'article',
            href: `/campaigns/${campaignId}/reply-back`,
            label: 'article',
        },
    ];

    return (
        <nav className="flex flex-wrap gap-2">
            {items.map((item) => (
                <Link
                    key={item.key}
                    href={item.href}
                    className={
                        item.key === current
                            ? 'rounded-full bg-ink px-4 py-1.5 text-sm font-semibold text-paper shadow-xs ring-1 ring-brass/40 focus-visible:outline-2 focus-visible:outline-brass'
                            : 'rounded-full border border-ink/15 bg-sheet px-4 py-1.5 text-sm font-medium text-ink transition-colors hover:border-ink/30 hover:bg-white focus-visible:outline-2 focus-visible:outline-brass'
                    }
                >
                    {t(item.label)}
                </Link>
            ))}
        </nav>
    );
}
