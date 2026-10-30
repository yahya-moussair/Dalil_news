export function Stat({
    label,
    value,
}: {
    label: string;
    value: string | number;
}) {
    return (
        <div className="relative overflow-hidden rounded-xl border border-ink/10 bg-sheet p-5 shadow-xs transition-all duration-200 hover:border-ink/25 hover:shadow-sm">
            <div
                className="mb-3 h-0.5 w-7 rounded-full bg-brass"
                aria-hidden="true"
            />
            <p className="font-mono text-xs font-semibold tracking-wider uppercase text-ink/70">
                {label}
            </p>
            <p className="mt-1.5 font-mono text-3xl font-bold tracking-tight text-ink tabular-nums sm:text-4xl">
                {value}
            </p>
        </div>
    );
}
