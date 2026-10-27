import { Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

const tones = {
    solid: 'inline-flex items-center bg-ink px-5 py-2.5 text-sm font-medium text-paper transition-colors duration-200 hover:bg-listen focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brass',
    line: 'inline-flex items-center border border-ink px-5 py-2.5 text-sm font-medium text-ink transition-colors duration-200 hover:bg-ink hover:text-paper focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brass',
    onInk: 'inline-flex items-center bg-brass px-5 py-2.5 text-sm font-medium text-ink transition-colors duration-200 hover:bg-paper focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brass',
    onInkLine:
        'inline-flex items-center border border-paper/40 px-5 py-2.5 text-sm font-medium text-paper transition-colors duration-200 hover:border-paper focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brass',
};

export function LandingLink({
    href,
    tone,
    children,
}: {
    href: string;
    tone: keyof typeof tones;
    children: ReactNode;
}) {
    return (
        <Link href={href} className={tones[tone]}>
            {children}
        </Link>
    );
}

export function TextAnchor({
    href,
    children,
}: {
    href: string;
    children: ReactNode;
}) {
    return (
        <a
            href={href}
            className={cn(
                'inline-flex items-center text-sm font-medium text-ink underline decoration-brass decoration-2 underline-offset-4',
                'hover:decoration-listen focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brass',
            )}
        >
            {children}
        </a>
    );
}
