import { Breadcrumbs } from '@/components/breadcrumbs';
import { LocaleSwitcher } from '@/components/dalil/locale-switcher';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem as BreadcrumbItemType } from '@/types';

export function AppSidebarHeader({
    breadcrumbs = [],
}: {
    breadcrumbs?: BreadcrumbItemType[];
}) {
    return (
        <header className="sticky top-0 z-20 flex h-16 shrink-0 items-center justify-between gap-3 border-b border-ink/10 bg-sheet/90 px-4 backdrop-blur-md transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-6">
            <div className="flex items-center gap-2.5">
                <SidebarTrigger className="-ms-1 text-ink/80 hover:bg-ink/5 hover:text-ink focus-visible:outline-2 focus-visible:outline-brass" />
                <Breadcrumbs breadcrumbs={breadcrumbs} />
            </div>
            <div className="flex shrink-0 items-center gap-3">
                <span className="hidden items-center gap-1.5 rounded-full border border-brass/40 bg-brass/10 px-3 py-1 font-mono text-xs font-semibold text-ink sm:inline-flex">
                    <span
                        className="size-1.5 rounded-full bg-brass"
                        aria-hidden="true"
                    />
                    سألتم، فأجبنا
                </span>
                <LocaleSwitcher tone="masthead" />
            </div>
        </header>
    );
}
