import type { Locale } from '@/types/dalil';

export function formatWhen(iso: string | null, locale: Locale): string {
    if (!iso) {
        return '—';
    }

    const date = new Date(iso);

    if (Number.isNaN(date.getTime())) {
        return '—';
    }

    return new Intl.DateTimeFormat(locale === 'ar' ? 'ar-MA' : locale, {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(date);
}

export function languageLabel(
    code: string | null,
    locale: Locale,
): string | null {
    if (!code) {
        return null;
    }

    const labels: Record<string, Record<Locale, string>> = {
        ary: { ar: 'الدارجة', fr: 'Darija', en: 'Darija' },
        arabizi: { ar: 'عربيزي', fr: 'Arabizi', en: 'Arabizi' },
        ar: { ar: 'العربية', fr: 'Arabe', en: 'Arabic' },
        fr: { ar: 'الفرنسية', fr: 'Français', en: 'French' },
    };

    return labels[code]?.[locale] ?? code;
}

export function money(amount: number, locale: Locale): string {
    return new Intl.NumberFormat(locale === 'ar' ? 'ar-MA' : locale, {
        style: 'currency',
        currency: 'USD',
        maximumFractionDigits: 2,
    }).format(amount);
}
