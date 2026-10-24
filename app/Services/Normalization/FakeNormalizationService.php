<?php

namespace App\Services\Normalization;

class FakeNormalizationService implements NormalizationService
{
    public function normalize(string $text): array
    {
        $language = $this->detect($text);
        $normalized = $language === 'arabizi' ? $this->arabiziToArabic($text) : $text;
        $summary = $this->summarize($normalized);

        return [
            'language' => $language,
            'normalized_text' => $normalized,
            'summary_msa' => $summary,
        ];
    }

    private function detect(string $text): string
    {
        $arabic = preg_match_all('/\p{Arabic}/u', $text) ?: 0;
        $latin = preg_match_all('/[A-Za-z]/', $text) ?: 0;

        if ($arabic > 8 && $arabic >= $latin) {
            $darija = ['كيت', 'ديال', 'حنا', 'باش', 'فين', 'ماشي', 'بزاف', 'دابا', 'غادي', 'كاين', 'زعما', 'واش', 'حتى'];

            foreach ($darija as $marker) {
                if (mb_stripos($text, $marker) !== false) {
                    return 'ary';
                }
            }

            return 'ar';
        }

        $lower = mb_strtolower($text);
        $arabizi = ['dyal', 'dial', 'kayn', 'kay', '3la', '7na', 'chno', 'bzaf', 'khasso', 'wlad', 'l7ay', 'lma', '9'];
        $arabiziHits = 0;

        foreach ($arabizi as $marker) {
            if (str_contains($lower, $marker)) {
                $arabiziHits++;
            }
        }

        if ($arabiziHits >= 2 || preg_match('/[23579]/', $text) === 1 && $latin > 8) {
            return 'arabizi';
        }

        return 'fr';
    }

    private function arabiziToArabic(string $text): string
    {
        $dictionary = [
            'salam' => 'السلام',
            '3likom' => 'عليكم',
            'alikom' => 'عليكم',
            'hna' => 'حنا',
            '7na' => 'حنا',
            'dyal' => 'ديال',
            'dial' => 'ديال',
            'lma' => 'الما',
            'kayt9ate3' => 'كيتقطع',
            'kaytqa3' => 'كيتقطع',
            'kayn' => 'كاين',
            'kaynin' => 'كاينين',
            'wlad' => 'ولاد',
            'l7ay' => 'الحي',
            'kaykhafou' => 'كيخافو',
            'kaykhafo' => 'كيخافو',
            'ykhrjo' => 'يخرجو',
            'lil' => 'الليل',
            'zn9a' => 'الزنقة',
            'dwaw' => 'أضواء',
            'tafyin' => 'طافيين',
            'chhour' => 'شهور',
            'hadi' => 'هادي',
            'f' => 'ف',
            'men' => 'من',
            'sbah' => 'الصباح',
            '3chiya' => 'العشية',
            'bach' => 'باش',
            'yghslo' => 'يغسلو',
            'lmedrasa' => 'المدرسة',
            'ma' => 'ما',
            'sidi' => 'سيدي',
            'moumen' => 'مومن',
            'bernoussi' => 'البرنوصي',
            'moulay' => 'مولاي',
            'rachid' => 'رشيد',
        ];

        $parts = preg_split('/(\s+)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        if (! is_array($parts)) {
            return $text;
        }

        $converted = [];

        foreach ($parts as $part) {
            if (trim($part) === '') {
                $converted[] = $part;

                continue;
            }

            $key = mb_strtolower(trim($part, ".,!?;:\"'"));
            $converted[] = $dictionary[$key] ?? $this->mapToken($part);
        }

        return trim(implode('', $converted));
    }

    private function mapToken(string $token): string
    {
        if (preg_match('/\p{Arabic}/u', $token) === 1) {
            return $token;
        }

        $lower = mb_strtolower($token);
        $replacements = [
            'kh' => 'خ',
            'gh' => 'غ',
            'ch' => 'ش',
            'sh' => 'ش',
            'ou' => 'و',
            '7' => 'ح',
            '9' => 'ق',
            '5' => 'خ',
            '3' => 'ع',
            '2' => 'ء',
            '8' => 'غ',
            '6' => 'ط',
            'a' => 'ا',
            'b' => 'ب',
            't' => 'ت',
            'd' => 'د',
            'e' => '',
            'f' => 'ف',
            'h' => 'ه',
            'i' => 'ي',
            'j' => 'ج',
            'k' => 'ك',
            'l' => 'ل',
            'm' => 'م',
            'n' => 'ن',
            'o' => 'و',
            'r' => 'ر',
            's' => 'س',
            'u' => 'و',
            'w' => 'و',
            'y' => 'ي',
            'z' => 'ز',
        ];

        $output = $lower;

        foreach (['kh', 'gh', 'ch', 'sh', 'ou'] as $chunk) {
            $output = str_replace($chunk, $replacements[$chunk], $output);
        }

        $chars = preg_split('//u', $output, -1, PREG_SPLIT_NO_EMPTY);

        if (! is_array($chars)) {
            return $token;
        }

        $mapped = '';

        foreach ($chars as $char) {
            $mapped .= $replacements[$char] ?? $char;
        }

        return $mapped;
    }

    private function summarize(string $text): string
    {
        $stripped = preg_replace('/^(السلام عليكم[،,]?\s*|صباح الخير[،,]?\s*|bonjour[,.]?\s*)/iu', '', $text) ?? $text;
        $stripped = trim($stripped);

        if (mb_strlen($stripped) <= 180) {
            return $stripped;
        }

        return mb_substr($stripped, 0, 177).'…';
    }
}
