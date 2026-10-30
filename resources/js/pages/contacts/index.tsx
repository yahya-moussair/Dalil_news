import { Head, Link, router } from '@inertiajs/react';
import { PageHeader } from '@/components/dalil/page-header';
import { Button } from '@/components/ui/button';
import { useCrumbs } from '@/hooks/use-crumbs';
import { fieldClass } from '@/lib/field';
import { formatWhen, languageLabel } from '@/lib/format';
import { useI18n } from '@/lib/i18n';
import type { ContactRow, Paginator } from '@/types/dalil';

export default function Contacts({
    contacts,
    neighborhoods,
    filter,
    counts,
}: {
    contacts: Paginator<ContactRow>;
    neighborhoods: string[];
    filter: string;
    counts: { subscribed: number; opted_out: number };
}) {
    const { t, locale } = useI18n();
    useCrumbs([
        { title: t('navDashboard'), href: '/dashboard' },
        { title: t('subscribers'), href: '/contacts' },
    ]);

    return (
        <>
            <Head title={t('subscribers')} />
            <div className="flex flex-col gap-6 p-4 md:p-6">
                <PageHeader
                    title={t('subscribers')}
                    lead={t('contactsLead')}
                    actions={
                        <>
                            <Button variant="outline" asChild>
                                <Link href="/opt-in">{t('navOptIn')}</Link>
                            </Button>
                            <Button variant="outline" asChild>
                                <a href="/contacts/export">{t('export')}</a>
                            </Button>
                        </>
                    }
                />
                <p className="text-sm font-semibold text-ink">
                    {t('subscribed')}: {counts.subscribed} · {t('optedOut')}:{' '}
                    {counts.opted_out}
                </p>
                <label className="flex items-center gap-2 text-sm font-medium text-ink">
                    {t('filter')}
                    <select
                        value={filter}
                        onChange={(event) =>
                            router.get(
                                '/contacts',
                                { neighborhood: event.target.value },
                                { preserveState: true, preserveScroll: true },
                            )
                        }
                        className={`${fieldClass} h-9 w-auto bg-card`}
                    >
                        <option value="">{t('filterAll')}</option>
                        {neighborhoods.map((name) => (
                            <option key={name} value={name}>
                                {name}
                            </option>
                        ))}
                    </select>
                </label>
                <div className="overflow-x-auto rounded-2xl border bg-card shadow-sm">
                    <table className="w-full text-sm">
                        <thead className="border-b border-ink/10 bg-paper/60 text-ink">
                            <tr>
                                <th className="px-3 py-2 text-start font-semibold text-ink">
                                    {t('displayNumber')}
                                </th>
                                <th className="px-3 py-2 text-start font-semibold text-ink">
                                    {t('neighborhood')}
                                </th>
                                <th className="px-3 py-2 text-start font-semibold text-ink">
                                    {t('channel')}
                                </th>
                                <th className="px-3 py-2 text-start font-semibold text-ink">
                                    {t('consent')}
                                </th>
                                <th className="px-3 py-2 text-start font-semibold text-ink">
                                    {t('source')}
                                </th>
                                <th className="px-3 py-2 text-start font-semibold text-ink">
                                    {t('actions')}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {contacts.data.map((contact) => (
                                <tr key={contact.id} className="border-t">
                                    <td className="px-3 py-3 font-mono text-xs">
                                        {contact.masked_phone}
                                        {contact.is_test_data ? (
                                            <span className="ms-2 rounded-full bg-[oklch(0.94_0.04_85)] px-2 py-0.5 font-sans text-[oklch(0.35_0.06_60)]">
                                                {t('demoData')}
                                            </span>
                                        ) : null}
                                    </td>
                                    <td className="px-3 py-3">
                                        {contact.neighborhood ?? '—'}
                                        {contact.language_pref ? (
                                            <span className="ms-2 text-xs text-muted-foreground">
                                                {languageLabel(
                                                    contact.language_pref,
                                                    locale,
                                                )}
                                            </span>
                                        ) : null}
                                    </td>
                                    <td className="px-3 py-3">
                                        {contact.channel}
                                    </td>
                                    <td className="px-3 py-3">
                                        {contact.opted_out_at
                                            ? t('optedOut')
                                            : formatWhen(
                                                  contact.consent_at,
                                                  locale,
                                              )}
                                    </td>
                                    <td className="px-3 py-3">
                                        {contact.consent_source ?? '—'}
                                    </td>
                                    <td className="px-3 py-3">
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            onClick={() => {
                                                if (
                                                    window.confirm(
                                                        t('confirmDelete'),
                                                    )
                                                ) {
                                                    router.delete(
                                                        `/contacts/${contact.id}`,
                                                    );
                                                }
                                            }}
                                        >
                                            {t('delete')}
                                        </Button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
                <div className="flex flex-wrap gap-2">
                    {contacts.links
                        .filter((link) => link.url !== null)
                        .map((link) => (
                            <Link
                                key={link.label}
                                href={link.url ?? '/contacts'}
                                preserveScroll
                                className={
                                    link.active
                                        ? 'rounded-full bg-primary px-3 py-1 text-sm text-primary-foreground'
                                        : 'rounded-full border bg-card px-3 py-1 text-sm hover:bg-accent'
                                }
                            >
                                {pageLabel(link.label, t)}
                            </Link>
                        ))}
                </div>
            </div>
        </>
    );
}

function pageLabel(label: string, t: ReturnType<typeof useI18n>['t']): string {
    const plain = label
        .replaceAll('&laquo;', '')
        .replaceAll('&raquo;', '')
        .replaceAll(/<[^>]+>/g, '')
        .trim();

    if (plain === 'Previous' || plain === 'pagination.previous') {
        return t('previous');
    }

    if (plain === 'Next' || plain === 'pagination.next') {
        return t('next');
    }

    return plain;
}

Contacts.layout = {
    breadcrumbs: [
        { title: 'Dalil', href: '/dashboard' },
        { title: 'Subscribers', href: '/contacts' },
    ],
};
