import { Link } from '@inertiajs/react';
import {
    BarChart3,
    LayoutGrid,
    MessagesSquare,
    QrCode,
    Users,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { LocaleSwitcher } from '@/components/dalil/locale-switcher';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useI18n } from '@/lib/i18n';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

export function AppSidebar() {
    const { t, dir } = useI18n();
    const mainNavItems: NavItem[] = [
        { title: t('navDashboard'), href: '/dashboard', icon: LayoutGrid },
        {
            title: t('navCampaigns'),
            href: '/campaigns',
            icon: MessagesSquare,
        },
        { title: t('navSubscribers'), href: '/contacts', icon: Users },
        { title: t('navOptIn'), href: '/opt-in', icon: QrCode },
        { title: t('navMetrics'), href: '/metrics', icon: BarChart3 },
    ];

    return (
        <Sidebar
            collapsible="icon"
            variant="inset"
            side={dir === 'rtl' ? 'right' : 'left'}
            className="overflow-hidden border-sidebar-border bg-sidebar text-sidebar-foreground"
        >
            {/* Signature Masthead Accent Rules */}
            <div className="h-1 bg-brass shrink-0" aria-hidden="true" />
            <div className="h-0.5 bg-listen shrink-0" aria-hidden="true" />

            <SidebarHeader className="border-b border-sidebar-border/40 pb-3">
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            size="lg"
                            asChild
                            className="transition-colors hover:bg-sidebar-accent/50 focus-visible:outline-2 focus-visible:outline-brass"
                        >
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent className="pt-2">
                <NavMain items={mainNavItems} label={t('navGroup')} />
            </SidebarContent>

            <SidebarFooter className="border-t border-sidebar-border/40 pt-3">
                <div className="px-2 pb-2">
                    <LocaleSwitcher />
                </div>
                <div className="flex items-center justify-between px-2 text-[11px] font-mono text-sidebar-foreground/60">
                    <span>{t('hackathon')}</span>
                    <span className="text-brass">«سألتم، فأجبنا»</span>
                </div>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
