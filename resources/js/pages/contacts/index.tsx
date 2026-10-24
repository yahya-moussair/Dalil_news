import { Head, Link, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
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

    return (
        <>
            <Head title={t('subscribers')} />
            <div className="flex flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h1 className="text-2xl font-semibold">
                            {t('subscribers')}
                        </h1>
                        <p className="mt-1 max-w-2xl text-sm text-muted-foreground">
                            {t('contactsLead')}
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline" asChild>
                            <Link href="/opt-in">{t('navOptIn')}</Link>
                        </Button>
                        <Button variant="outline" asChild>
                            <a href="/contacts/export">{t('export')}</a>
                        </Button>
                    </div>
                </div>
                <p className="text-sm">
                    {t('subscribed')}: {counts.subscribed} · {t('optedOut')}:{' '}
                    {counts.opted_out}
                </p>
                <label className="flex items-center gap-2 text-sm">
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
                        className="h-9 rounded-md border border-input bg-card px-2"
                    >
                        <option value="">{t('filterAll')}</option>
                        {neighborhoods.map((name) => (
                            <option key={name} value={name}>
                                {name}
                            </option>
                        ))}
                    </select>
                </label>
                <div className="overflow-x-auto rounded-2xl border">
                    <table className="w-full text-sm">
                        <thead className="bg-muted/50 text-muted-foreground">
                            <tr>
                                <th className="px-3 py-2 text-start font-normal">
                                    {t('displayNumber')}
                                </th>
                                <th className="px-3 py-2 text-start font-normal">
                                    {t('neighborhood')}
                                </th>
                                <th className="px-3 py-2 text-start font-normal">
                                    {t('channel')}
                                </th>
                                <th className="px-3 py-2 text-start font-normal">
                                    {t('consent')}
                                </th>
                                <th className="px-3 py-2 text-start font-normal">
                                    {t('source')}
                                </th>
                                <th className="px-3 py-2 text-start font-normal">
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
                                            <span className="ms-2 font-sans text-amber-800">
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
                                        ? 'rounded-md bg-primary px-3 py-1 text-sm text-primary-foreground'
                                        : 'rounded-md border px-3 py-1 text-sm'
                                }
                            >
                                {link.label
                                    .replace('&laquo; ', '')
                                    .replace(' &raquo;', '')}
                            </Link>
                        ))}
                </div>
            </div>
        </>
    );
}

Contacts.layout = {
    breadcrumbs: [
        { title: 'Dalil', href: '/dashboard' },
        { title: 'Subscribers', href: '/contacts' },
    ],
};
