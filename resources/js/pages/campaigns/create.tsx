import { Form, Head, Link } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useI18n } from '@/lib/i18n';

export default function CreateCampaign({
    neighborhoods,
    newsroomName,
}: {
    neighborhoods: string[];
    newsroomName: string;
}) {
    const { t } = useI18n();

    return (
        <>
            <Head title={t('newQuestion')} />
            <div className="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4 md:p-6">
                <div>
                    <p className="text-sm text-muted-foreground">
                        {newsroomName}
                    </p>
                    <h1 className="text-2xl font-semibold">
                        {t('newQuestion')}
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        {t('createLead')}
                    </p>
                </div>
                <Form action="/campaigns" method="post" className="grid gap-5">
                    {({ errors, processing }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="title">{t('title')}</Label>
                                <Input
                                    id="title"
                                    name="title"
                                    required
                                    maxLength={160}
                                    defaultValue="أكبر مشكل في الحي"
                                />
                                <InputError message={errors.title} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="question">
                                    {t('question')}
                                </Label>
                                <textarea
                                    id="question"
                                    name="question"
                                    required
                                    rows={4}
                                    maxLength={1000}
                                    defaultValue="ما هو أكبر مشكل كتعيشوه فالحي ديالكم؟"
                                    className="flex min-h-24 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                />
                                <InputError message={errors.question} />
                            </div>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="template_name">
                                        {t('template')}
                                    </Label>
                                    <Input
                                        id="template_name"
                                        name="template_name"
                                        defaultValue="daily_question"
                                    />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="template_lang">
                                        {t('templateLang')}
                                    </Label>
                                    <select
                                        id="template_lang"
                                        name="template_lang"
                                        defaultValue="ar"
                                        className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                    >
                                        <option value="ar">ar</option>
                                        <option value="fr">fr</option>
                                        <option value="en">en</option>
                                    </select>
                                </div>
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="closes_at">{t('closes')}</Label>
                                <Input
                                    id="closes_at"
                                    name="closes_at"
                                    type="datetime-local"
                                />
                            </div>
                            <fieldset className="grid gap-2">
                                <legend className="text-sm font-medium">
                                    {t('neighborhoods')}
                                </legend>
                                <p className="text-xs text-muted-foreground">
                                    {t('allNeighborhoods')}
                                </p>
                                <div className="grid gap-2 sm:grid-cols-2">
                                    {neighborhoods.map((name) => (
                                        <label
                                            key={name}
                                            className="flex items-center gap-2 text-sm"
                                        >
                                            <input
                                                type="checkbox"
                                                name="neighborhoods[]"
                                                value={name}
                                            />
                                            {name}
                                        </label>
                                    ))}
                                </div>
                            </fieldset>
                            <label className="flex items-center gap-2 text-sm">
                                <input
                                    type="checkbox"
                                    name="send"
                                    value="1"
                                    defaultChecked
                                />
                                {t('sendNow')}
                            </label>
                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>
                                    {t('queueSend')}
                                </Button>
                                <Button variant="outline" asChild>
                                    <Link href="/campaigns">
                                        {t('campaigns')}
                                    </Link>
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

CreateCampaign.layout = {
    breadcrumbs: [
        { title: 'Dalil', href: '/dashboard' },
        { title: 'Questions', href: '/campaigns' },
    ],
};
