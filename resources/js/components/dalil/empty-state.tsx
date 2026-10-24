import type { ReactNode } from 'react';

export function EmptyState({ children }: { children: ReactNode }) {
    return (
        <div className="rounded-2xl border border-dashed bg-card/70 px-6 py-12 text-center text-sm text-muted-foreground">
            {children}
        </div>
    );
}
