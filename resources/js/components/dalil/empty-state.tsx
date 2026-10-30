import type { ReactNode } from 'react';

export function EmptyState({ children }: { children: ReactNode }) {
    return (
        <div className="rounded-2xl border border-dashed border-ink/20 bg-sheet/60 px-6 py-12 text-center text-sm text-ink/70 shadow-xs">
            {children}
        </div>
    );
}
