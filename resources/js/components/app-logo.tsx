import { usePage } from '@inertiajs/react';

export default function AppLogo() {
    const { name } = usePage().props;
    const brandName = name && name !== 'Laravel' ? name : 'Dalil News';

    return (
        <div className="flex items-center gap-2.5">
            <div className="flex aspect-square size-9 items-center justify-center rounded-lg bg-paper/10 p-1 ring-1 ring-brass/30 shrink-0">
                <img
                    src="/brand/dalil-mark.png"
                    alt="Dalil News"
                    width={398}
                    height={328}
                    className="h-7 w-auto object-contain"
                />
            </div>
            <div className="grid flex-1 text-start leading-tight group-data-[collapsible=icon]:hidden">
                <span className="truncate text-sm font-bold tracking-tight text-sidebar-foreground">
                    {brandName}
                </span>
                <span className="truncate font-mono text-[10px] font-medium text-brass tracking-wider">
                    سألتم، فأجبنا
                </span>
            </div>
        </div>
    );
}
