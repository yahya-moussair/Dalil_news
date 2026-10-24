<?php

namespace App\Support;

final class TopicLexicon
{
    /**
     * @return list<array{key: string, label_ar: string, label_fr: string, summary: string, keywords: list<array{term: string, weight: int}>}>
     */
    public static function definitions(): array
    {
        return [
            [
                'key' => 'water',
                'label_ar' => 'انقطاع الماء',
                'label_fr' => "Coupures d'eau",
                'summary' => 'انقطاع متكرر للماء الصالح للشرب، غالباً من الصباح إلى المساء، ويؤثر على الاستحمام والمدرسة.',
                'keywords' => self::weighted([
                    'انقطاع الماء' => 4,
                    'الما كيتقطع' => 4,
                    'الماء منقطع' => 3,
                    'kayt9ate3' => 4,
                    'lma' => 3,
                    "coupures d'eau" => 4,
                    'l\'eau est coupée' => 4,
                    'eau coupée' => 4,
                    'robinet' => 2,
                ]),
            ],
            [
                'key' => 'lighting',
                'label_ar' => 'الإنارة العمومية',
                'label_fr' => 'Éclairage public',
                'summary' => 'أعمدة الإنارة مطفأة منذ أشهر، والسكان يخافون الخروج ليلاً.',
                'keywords' => self::weighted([
                    'الإنارة' => 4,
                    'أضواء' => 3,
                    'طافيين' => 3,
                    'dwaw' => 4,
                    'tafyin' => 3,
                    'éclairage' => 4,
                    'lampadaire' => 4,
                    'الظلام' => 2,
                ]),
            ],
            [
                'key' => 'transport',
                'label_ar' => 'النقل الحضري',
                'label_fr' => 'Transport urbain',
                'summary' => 'الحافلات تتأخر أو تغيب في الصباح، فيصل الناس متأخرين إلى العمل.',
                'keywords' => self::weighted([
                    'الطوبيس' => 4,
                    'الحافلة' => 4,
                    'الطوبيسات' => 4,
                    'autobus' => 3,
                    'bus' => 2,
                    'en retard' => 2,
                    'transport' => 2,
                ]),
            ],
            [
                'key' => 'waste',
                'label_ar' => 'النفايات',
                'label_fr' => 'Déchets',
                'summary' => 'الأزبال تتراكم قرب الأسواق والأزقة ولا تُرفع بانتظام.',
                'keywords' => self::weighted([
                    'الزبل' => 4,
                    'الأزبال' => 4,
                    'zbel' => 4,
                    'déchets' => 4,
                    'ordures' => 4,
                    'poubelle' => 3,
                ]),
            ],
            [
                'key' => 'roads',
                'label_ar' => 'الحفر في الطرق',
                'label_fr' => 'Nids-de-poule',
                'summary' => 'حفر عميقة في الشوارع تعرقل السيارات والدراجات وتصبح بركاً حين تمطر.',
                'keywords' => self::weighted([
                    'الحفر' => 4,
                    'nid-de-poule' => 4,
                    'nids-de-poule' => 4,
                    'chaussée' => 3,
                    'trou' => 2,
                ]),
            ],
            [
                'key' => 'sewage',
                'label_ar' => 'واد الحار',
                'label_fr' => 'Eaux usées',
                'summary' => 'مياه الصرف تطفو في الأزقة مع روائح قوية، خصوصاً قرب المدارس.',
                'keywords' => self::weighted([
                    'واد الحار' => 5,
                    'الصرف الصحي' => 4,
                    'eaux usées' => 4,
                    'égouts' => 4,
                    'الريحة' => 2,
                    'riha' => 3,
                ]),
            ],
        ];
    }

    public static function match(string $text): ?string
    {
        $bestKey = null;
        $bestScore = 0;

        foreach (self::definitions() as $definition) {
            $score = self::score($definition['keywords'], $text);

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestKey = $definition['key'];
            }
        }

        return $bestScore > 0 ? $bestKey : null;
    }

    /**
     * @param  list<array{term: string, weight: int}>  $keywords
     */
    public static function score(array $keywords, string $text): int
    {
        $total = 0;

        foreach ($keywords as $keyword) {
            if (self::contains($text, $keyword['term'])) {
                $total += $keyword['weight'];
            }
        }

        return $total;
    }

    /**
     * @param  array<string, int>  $pairs
     * @return list<array{term: string, weight: int}>
     */
    private static function weighted(array $pairs): array
    {
        $keywords = [];

        foreach ($pairs as $term => $weight) {
            $keywords[] = ['term' => $term, 'weight' => $weight];
        }

        return $keywords;
    }

    private static function contains(string $haystack, string $needle): bool
    {
        if (preg_match('/[A-Za-z]/', $needle) === 1) {
            $pattern = '/(?<![\p{L}\p{N}])'.preg_quote($needle, '/').'(?![\p{L}\p{N}])/iu';

            return preg_match($pattern, $haystack) === 1;
        }

        return mb_stripos($haystack, $needle) !== false;
    }
}
