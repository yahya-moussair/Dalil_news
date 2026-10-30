import type { HTMLAttributes } from 'react';

export default function AppLogoIcon({
    className = 'size-6',
    ...props
}: HTMLAttributes<HTMLElement>) {
    return (
        <img
            src="/brand/dalil-mark.png"
            alt="Dalil News"
            width={398}
            height={328}
            className={className}
            {...props}
        />
    );
}
