export default function Heading({
    title,
    description,
    variant = 'default',
}: {
    title: string;
    description?: string;
    variant?: 'default' | 'small';
}) {
    return (
        <header className={variant === 'small' ? '' : 'mb-8 space-y-1'}>
            <h2
                className={
                    variant === 'small'
                        ? 'mb-1 text-base font-semibold text-ink dark:text-paper'
                        : 'text-2xl font-bold tracking-tight text-ink dark:text-paper'
                }
            >
                {title}
            </h2>
            {description && (
                <p className="text-sm leading-relaxed text-ink/75 dark:text-paper/75">{description}</p>
            )}
        </header>
    );
}
