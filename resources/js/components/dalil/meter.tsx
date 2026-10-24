export function Meter({ value, max = 100 }: { value: number; max?: number }) {
    const width = Math.max(2, Math.min(100, (value / Math.max(max, 1)) * 100));

    return (
        <div
            className="h-2 overflow-hidden rounded-full bg-muted"
            role="presentation"
        >
            <div
                className="h-full rounded-full bg-linear-to-r from-primary to-[oklch(0.72_0.13_85)] rtl:bg-linear-to-l"
                style={{ width: `${width}%` }}
            />
        </div>
    );
}
