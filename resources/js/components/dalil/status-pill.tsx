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
                    ? 'bg-amber-700 text-white'
                    : status === 'closed'
                      ? 'bg-stone-700 text-white'
                      : undefined
            }
        >
            {label}
        </Badge>
    );
}
