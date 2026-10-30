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
        <div className="flex flex-wrap items-end justify-between gap-4 border-b border-ink/10 pb-5">
            <div className="min-w-0 max-w-3xl">
                {kicker ? (
                    <div className="flex items-center gap-2">
                        <span className="font-mono text-xs font-semibold tracking-wider text-brass uppercase">
                            {kicker}
                        </span>
                        <span className="text-ink/30">•</span>
                        <span className="font-mono text-xs text-ink/60">
                            سألتم، فأجبنا
                        </span>
                    </div>
                ) : null}
                <h1 className="mt-1.5 text-3xl font-bold tracking-tight text-ink sm:text-4xl">
                    {title}
                </h1>
                {lead ? (
                    <p className="mt-2 text-sm leading-relaxed text-ink/80 sm:text-base">
                        {lead}
                    </p>
                ) : null}
            </div>
            {actions ? (
                <div className="flex flex-wrap items-center gap-3">{actions}</div>
            ) : null}
        </div>
    );
}
