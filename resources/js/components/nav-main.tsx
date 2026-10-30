import { Link } from '@inertiajs/react';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn } from '@/lib/utils';
import type { NavItem } from '@/types';

export function NavMain({
    items,
    label = 'Platform',
}: {
    items: NavItem[];
    label?: string;
}) {
    const { isCurrentUrl } = useCurrentUrl();

    return (
        <SidebarGroup className="px-2 py-0">
            <SidebarGroupLabel className="font-mono text-[11px] font-semibold tracking-wider text-brass uppercase">
                {label}
            </SidebarGroupLabel>
            <SidebarMenu className="mt-1 space-y-1">
                {items.map((item) => {
                    const active = isCurrentUrl(item.href);

                    return (
                        <SidebarMenuItem key={item.title}>
                            <SidebarMenuButton
                                asChild
                                isActive={active}
                                tooltip={{ children: item.title }}
                                className={cn(
                                    'rounded-lg px-3 py-2 text-sm font-medium transition-colors duration-150',
                                    active
                                        ? 'bg-listen font-semibold text-paper shadow-xs ring-1 ring-brass/40'
                                        : 'text-sidebar-foreground/80 hover:bg-white/10 hover:text-sidebar-foreground',
                                )}
                            >
                                <Link href={item.href} prefetch>
                                    {item.icon && (
                                        <item.icon
                                            className={cn(
                                                'size-4 shrink-0 transition-colors',
                                                active
                                                    ? 'text-brass'
                                                    : 'text-sidebar-foreground/70',
                                            )}
                                        />
                                    )}
                                    <span>{item.title}</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    );
                })}
            </SidebarMenu>
        </SidebarGroup>
    );
}
