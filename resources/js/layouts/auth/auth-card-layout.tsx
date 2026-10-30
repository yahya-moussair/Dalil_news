import { Link } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';
import AppLogoIcon from '@/components/app-logo-icon';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { home } from '@/routes';

export default function AuthCardLayout({
    children,
    title,
    description,
}: PropsWithChildren<{
    name?: string;
    title?: string;
    description?: string;
}>) {
    return (
        <div className="flex min-h-svh flex-col items-center justify-center gap-6 bg-paper p-6 text-ink md:p-10">
            <div className="flex w-full max-w-md flex-col gap-6">
                <Link
                    href={home()}
                    className="flex items-center gap-3 self-center font-semibold text-lg text-ink"
                >
                    <AppLogoIcon className="h-10 w-auto" />
                    <span>Dalil News</span>
                </Link>

                <div className="flex flex-col gap-6">
                    <Card className="rounded-2xl border-ink/10 bg-sheet shadow-xs">
                        <CardHeader className="px-10 pt-8 pb-0 text-center">
                            <CardTitle className="text-2xl font-bold tracking-tight text-ink">{title}</CardTitle>
                            <CardDescription className="text-ink/75">{description}</CardDescription>
                        </CardHeader>
                        <CardContent className="px-10 py-8">
                            {children}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    );
}
