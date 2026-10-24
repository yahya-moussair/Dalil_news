import { Badge } from '@/components/ui/badge';
import { useI18n } from '@/lib/i18n';
import type { CampaignStatus } from '@/types/dalil';

export function StatusPill({ status }: { status: CampaignStatus }) {
    const { t } = useI18n();
    const label = {
        draft: t('statusDraft'),
        sent: t('statusSent'),
        voting: t('statusVoting'),
        closed: t('statusClosed'),
    }[status];

    return (
        <Badge
            variant={status === 'draft' ? 'secondary' : 'default'}
            className={
                status === 'voting'
                    ? 'border-transparent bg-[oklch(0.82_0.12_85)] text-[oklch(0.25_0.04_60)]'
                    : status === 'sent'
                      ? 'border-transparent bg-[oklch(0.4_0.07_175)] text-white'
                      : status === 'closed'
                        ? 'border-transparent bg-[oklch(0.45_0.02_175)] text-white'
                        : undefined
            }
        >
            {label}
        </Badge>
    );
}
