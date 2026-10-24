import type { ReactNode } from 'react';

export function PageHeader({
    kicker,
    title,
    lead,
    actions,
}: {
    kicker?: string;
    title: string;
    lead?: string;
    actions?: ReactNode;
}) {
    return (
        <div className="flex flex-wrap items-end justify-between gap-3">
            <div className="min-w-0">
                {kicker ? (
                    <p className="text-sm text-muted-foreground">{kicker}</p>
                ) : null}
                <h1 className="text-2xl font-semibold tracking-tight">
                    {title}
                </h1>
                {lead ? (
                    <p className="mt-1 max-w-2xl text-sm text-muted-foreground">
                        {lead}
                    </p>
                ) : null}
            </div>
            {actions ? (
                <div className="flex flex-wrap gap-2">{actions}</div>
            ) : null}
        </div>
    );
}
