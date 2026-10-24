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
    const { t } = useI18n();
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
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} label={t('navGroup')} />
            </SidebarContent>

            <SidebarFooter>
                <div className="px-2 pb-2">
                    <LocaleSwitcher />
                </div>
                <p className="px-2 text-xs text-sidebar-foreground/70">
                    DW Akademie × LionsGeek
                </p>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
