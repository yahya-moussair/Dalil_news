import { Link } from '@inertiajs/react';
import type { ComponentProps } from 'react';
import { cn } from '@/lib/utils';

type Props = ComponentProps<typeof Link>;

export default function TextLink({
    className = '',
    children,
    ...props
}: Props) {
    return (
        <Link
            className={cn(
                'text-foreground underline decoration-brass decoration-2 underline-offset-4 transition-colors duration-200 hover:text-listen hover:decoration-listen',
                className,
            )}
            {...props}
        >
            {children}
        </Link>
    );
}
