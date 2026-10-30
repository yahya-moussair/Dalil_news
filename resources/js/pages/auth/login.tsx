import { Form, Head, usePage } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { useI18n } from '@/lib/i18n';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

type Props = {
    status?: string;
    canResetPassword: boolean;
};

export default function Login({ status, canResetPassword }: Props) {
    const { t } = useI18n();
    const demoHint = usePage().props.demoHint;

    return (
        <>
            <Head title={t('logIn')} />

            <Form
                {...store.form()}
                resetOnSuccess={['password']}
                className="flex flex-col gap-5"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-5">
                            <div className="grid gap-2">
                                <Label
                                    htmlFor="email"
                                    className="text-xs font-semibold tracking-wider text-ink/80 uppercase"
                                >
                                    {t('email')}
                                </Label>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    required
                                    autoFocus
                                    tabIndex={1}
                                    autoComplete="email"
                                    placeholder="email@example.com"
                                    className="border-ink/15 bg-paper/40 text-ink placeholder:text-ink/40 focus-visible:border-listen focus-visible:ring-brass"
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-2">
                                <div className="flex items-center justify-between">
                                    <Label
                                        htmlFor="password"
                                        className="text-xs font-semibold tracking-wider text-ink/80 uppercase"
                                    >
                                        {t('password')}
                                    </Label>
                                    {canResetPassword && (
                                        <TextLink
                                            href={request()}
                                            className="text-xs text-ink/75 underline decoration-brass underline-offset-4 hover:text-ink"
                                            tabIndex={5}
                                        >
                                            {t('forgotPassword')}
                                        </TextLink>
                                    )}
                                </div>
                                <PasswordInput
                                    id="password"
                                    name="password"
                                    required
                                    tabIndex={2}
                                    autoComplete="current-password"
                                    placeholder={t('password')}
                                    className="border-ink/15 bg-paper/40 text-ink placeholder:text-ink/40 focus-visible:border-listen focus-visible:ring-brass"
                                />
                                <InputError message={errors.password} />
                            </div>

                            <div className="flex items-center gap-2.5">
                                <Checkbox
                                    id="remember"
                                    name="remember"
                                    tabIndex={3}
                                    className="border-ink/30 data-[state=checked]:border-ink data-[state=checked]:bg-ink"
                                />
                                <Label
                                    htmlFor="remember"
                                    className="cursor-pointer text-sm font-normal text-ink/80"
                                >
                                    {t('remember')}
                                </Label>
                            </div>

                            <Button
                                type="submit"
                                className="mt-2 w-full bg-ink py-2.5 font-semibold text-paper shadow-sm transition-colors duration-200 hover:bg-listen focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brass"
                                tabIndex={4}
                                disabled={processing}
                                data-test="login-button"
                            >
                                {processing && <Spinner className="size-4" />}
                                {t('logIn')}
                            </Button>
                        </div>

                        <div className="text-center text-sm text-ink/70">
                            {t('noAccount')}{' '}
                            <TextLink
                                href={register()}
                                tabIndex={5}
                                className="font-semibold text-ink underline decoration-brass underline-offset-4 hover:text-listen"
                            >
                                {t('signUp')}
                            </TextLink>
                        </div>
                    </>
                )}
            </Form>

            {status && (
                <div className="rounded-lg border border-listen/30 bg-listen/10 p-3 text-center text-sm font-medium text-listen">
                    {status}
                </div>
            )}

            {demoHint ? (
                <div className="rounded-xl border border-brass/40 bg-paper/70 p-4 text-xs text-ink shadow-xs">
                    <div className="flex items-center gap-2 font-semibold text-ink">
                        <span className="size-2 rounded-full bg-brass" />
                        {t('demoLogin')}
                    </div>
                    <div className="mt-2 space-y-0.5 font-mono text-[11px] text-ink/80">
                        <p>hafsa@casahay.test</p>
                        <p>yahya@casahay.test</p>
                    </div>
                    <p className="mt-2 border-t border-ink/10 pt-2 font-medium text-ink/90">
                        {t('password')}:{' '}
                        <code className="font-mono font-bold text-ink">
                            password
                        </code>
                    </p>
                </div>
            ) : null}
        </>
    );
}

Login.layout = {
    title: 'Dalil News',
    description: 'سألتم، فأجبنا',
};
