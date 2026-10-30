import { Badge } from '@/components/ui/badge';
import { useI18n } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import type { CampaignStatus } from '@/types/dalil';

export function StatusPill({ status }: { status: CampaignStatus }) {
    const { t } = useI18n();
    const label = {
        draft: t('statusDraft'),
        sent: t('statusSent'),
        voting: t('statusVoting'),
        closed: t('statusClosed'),
    }[status];

    const styles: Record<CampaignStatus, string> = {
        sent: 'border-transparent bg-listen text-paper font-semibold',
        voting: 'border-transparent bg-brass text-ink font-bold',
        closed: 'border-ink/20 bg-ink/10 text-ink/80 font-medium',
        draft: 'border-ink/20 bg-paper text-ink/80 font-medium',
    };

    return (
        <Badge
            variant="outline"
            className={cn('text-xs px-2.5 py-0.5 tracking-wide shadow-none', styles[status])}
        >
            {label}
        </Badge>
    );
}
