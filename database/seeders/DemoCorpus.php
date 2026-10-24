<?php

namespace Database\Seeders;

/**
 * Realistic Casablanca replies for the demo topics page.
 * Every item contains a keyword the offline clusterer understands,
 * so "rebuild topics" works without an API key.
 */
final class DemoCorpus
{
    /**
     * @return list<array{topic: string, language: string, neighborhood: string, text: string, normalized: string, summary: string, audio: bool, featured: bool}>
     */
    public static function messages(): array
    {
        $plan = [
            'water' => ['ary' => 28, 'arabizi' => 14, 'ar' => 14, 'fr' => 12],
            'lighting' => ['ary' => 18, 'arabizi' => 10, 'ar' => 8, 'fr' => 6],
            'transport' => ['ary' => 14, 'arabizi' => 8, 'ar' => 6, 'fr' => 6],
            'waste' => ['ary' => 10, 'arabizi' => 6, 'ar' => 6, 'fr' => 4],
            'roads' => ['ary' => 8, 'arabizi' => 4, 'ar' => 4, 'fr' => 2],
            'sewage' => ['ary' => 5, 'arabizi' => 3, 'ar' => 2, 'fr' => 2],
        ];

        $messages = [];

        foreach ($plan as $topic => $counts) {
            foreach ($counts as $language => $count) {
                $templates = self::templates($topic, $language);
                $places = self::places($topic);

                for ($i = 0; $i < $count; $i++) {
                    $template = $templates[$i % count($templates)];
                    $place = $places[$i % count($places)];
                    $tail = self::tail($language, $i);
                    $spoken = $language === 'fr' ? $place['fr'] : ($language === 'arabizi' ? $place['lat'] : $place['ar']);

                    $messages[] = [
                        'topic' => $topic,
                        'language' => $language,
                        'neighborhood' => $place['ar'],
                        'text' => str_replace('{n}', $spoken, $template['text']).$tail['raw'],
                        'normalized' => str_replace('{n}', $place['ar'], $template['normalized']).$tail['ar'],
                        'summary' => str_replace('{n}', $place['ar'], $template['summary']),
                        'audio' => $language === 'ary' || ($language === 'arabizi' && $i % 2 === 0),
                        'featured' => ($language === 'ary' && $i < 2) || ($language === 'fr' && $i === 0),
                    ];
                }
            }
        }

        return $messages;
    }

    /**
     * @return list<array{ar: string, fr: string, lat: string}>
     */
    private static function places(string $topic): array
    {
        $all = [
            'sidi' => ['ar' => 'سيدي مومن', 'fr' => 'Sidi Moumen', 'lat' => 'Sidi Moumen'],
            'bernoussi' => ['ar' => 'البرنوصي', 'fr' => 'Bernoussi', 'lat' => 'Bernoussi'],
            'rachid' => ['ar' => 'مولاي رشيد', 'fr' => 'Moulay Rachid', 'lat' => 'Moulay Rachid'],
            'mohammadi' => ['ar' => 'الحي المحمدي', 'fr' => 'Hay Mohammadi', 'lat' => 'Hay Mohammadi'],
            'sebaa' => ['ar' => 'عين السبع', 'fr' => 'Aïn Sebaâ', 'lat' => 'Ain Sebaa'],
            'sbata' => ['ar' => 'سباتة', 'fr' => 'Sbata', 'lat' => 'Sbata'],
            'hassani' => ['ar' => 'الحي الحسني', 'fr' => 'Hay Hassani', 'lat' => 'Hay Hassani'],
            'chock' => ['ar' => 'عين الشق', 'fr' => 'Aïn Chock', 'lat' => 'Ain Chock'],
            'sultan' => ['ar' => 'درب السلطان', 'fr' => 'Derb Sultan', 'lat' => 'Derb Sultan'],
        ];

        $keys = match ($topic) {
            'water' => ['sidi', 'bernoussi'],
            'lighting' => ['rachid', 'mohammadi'],
            'transport' => ['sebaa', 'sbata'],
            'waste' => ['mohammadi', 'sultan'],
            'roads' => ['hassani', 'chock'],
            default => ['bernoussi', 'sidi'],
        };

        return [$all[$keys[0]], $all[$keys[1]]];
    }

    /**
     * @return array{raw: string, ar: string}
     */
    private static function tail(string $language, int $index): array
    {
        $tails = [
            'ary' => ['', ' هادشي كل نهار.', ' عفاكم شوفوا هاد المشكل.', ' بقينا هكاك أسابيع.'],
            'arabizi' => ['', ' hadchi kol nhar.', ' 3afakom chofo hadchi.', ' bqina haka'],
            'ar' => ['', ' نرجو تدخل الجهة المعنية.', ' الوضع يتكرر كل أسبوع.', ''],
            'fr' => ['', ' Merci de venir constater.', ' Cela dure depuis des semaines.', ''],
        ];

        $arabic = [
            '',
            ' هذا يتكرر كل يوم.',
            ' نرجو أن تأتوا لتروا الوضع.',
            ' مستمر منذ أسابيع.',
        ];

        $raw = $tails[$language][$index % 4];

        if ($language === 'arabizi') {
            return ['raw' => $raw, 'ar' => $arabic[$index % 4]];
        }

        if ($language === 'fr') {
            return ['raw' => $raw, 'ar' => ''];
        }

        return ['raw' => $raw, 'ar' => $raw];
    }

    /**
     * @return list<array{text: string, normalized: string, summary: string}>
     */
    private static function templates(string $topic, string $language): array
    {
        $bank = [
            'water' => [
                'ary' => [
                    ['text' => 'السلام عليكم، حنا ف{n} الما كيتقطع كل نهار من الصباح حتى للعشية، والدراري ما كيلقاوش باش يغسلو قبل ما يمشيو للمدرسة.', 'summary' => 'انقطاع يومي للماء في {n} من الصباح إلى المساء، ويؤثر على الأطفال قبل المدرسة.'],
                    ['text' => 'فالحي ديال {n} الماء منقطع بلا ما يعلمونا. البارح بقينا بلا ماء من الظهر.', 'summary' => 'انقطع الماء في {n} دون إشعار مسبق.'],
                    ['text' => 'كنخلصو فاتورة الماء وفـ{n} الما كيتقطع ثلاث مرات فالأسبوع.', 'summary' => 'الماء ينقطع في {n} رغم أداء الفاتورة.'],
                    ['text' => 'الصباح كنمشيو نجيبو الماء من بعيد حيت الما كيتقطع عندنا ف{n}.', 'summary' => 'السكان في {n} يجلبون الماء من مكان بعيد بسبب الانقطاع.'],
                ],
                'arabizi' => [
                    ['text' => 'salam, f {n} lma kayt9ate3 kol nhar men sbah l 3chiya, drari ma kaylqawch bach yghslo 9bel lmadrasa', 'normalized' => 'السلام، ف{n} الما كيتقطع كل نهار من الصباح للعشية، والدراري ما كيلقاوش باش يغسلو قبل المدرسة.', 'summary' => 'انقطاع يومي للماء في {n} يؤثر على تحضير الأطفال للمدرسة.'],
                    ['text' => 'lma ma kaynch f {n} w fatura katkhrj 3adi, kayt9ate3 bla ma y3elmouna', 'normalized' => 'الما ما كاينش ف{n} والفاتورة كتخرج عادي، الما كيتقطع بلا ما يعلمونا.', 'summary' => 'الماء مقطوع في {n} دون إشعار والفاتورة تُحتسب.'],
                    ['text' => 'f {n} kayt9ate3 lma mn ba3d l3ser, kanmchiw l s9aya l b3ida', 'normalized' => 'ف{n} الما كيتقطع من بعد العصر، وكنمشيو للسقاية البعيدة.', 'summary' => 'انقطاع الماء بعد العصر في {n} يدفع السكان للسقاية البعيدة.'],
                    ['text' => '7ta 7na f {n} lma kayt9ate3, lghassil bqa mjmo3 men nhar jomo3a', 'normalized' => 'حتى حنا ف{n} الما كيتقطع، والغسيل بقى مجموع من نهار الجمعة.', 'summary' => 'انقطاع الماء في {n} عطّل الغسيل منذ الجمعة.'],
                ],
                'ar' => [
                    ['text' => 'نواجه انقطاع الماء في {n} يومياً من الصباح حتى المساء، ولا يستطيع الأطفال الاغتسال قبل المدرسة.', 'summary' => 'انقطاع يومي للماء في {n} يعيق استعداد الأطفال للمدرسة.'],
                    ['text' => 'انقطاع الماء في {n} يتم دون إشعار مسبق رغم التزام السكان بدفع الفاتورة.', 'summary' => 'الماء ينقطع في {n} دون إعلان سابق.'],
                    ['text' => 'خزان الحي في {n} يفرغ بعد الظهر، فنضطر لشراء الماء من المتجر.', 'summary' => 'سكان {n} يشترون الماء بعد فراغ الخزان.'],
                    ['text' => 'مصلحة التوزيع لا تجيب على شكايات انقطاع الماء المتكررة في {n}.', 'summary' => 'شكايات انقطاع الماء في {n} تبقى بلا جواب.'],
                ],
                'fr' => [
                    ['text' => 'Bonjour, à {n} l\'eau est coupée tous les matins jusqu\'au soir. Les enfants partent à l\'école sans pouvoir se laver.', 'summary' => 'انقطاع الماء في {n} من الصباح إلى المساء يمنع الأطفال من الاغتسال قبل المدرسة.'],
                    ['text' => 'Les coupures d\'eau à {n} arrivent sans prévenir. Hier le robinet est resté sec depuis midi.', 'summary' => 'انقطاع الماء في {n} يحدث دون إشعار.'],
                    ['text' => 'Nous payons la facture d\'eau à {n} mais l\'eau est coupée trois fois par semaine.', 'summary' => 'الفاتورة تُؤدى في {n} والماء ينقطع ثلاث مرات أسبوعياً.'],
                    ['text' => 'À {n} on marche loin pour remplir des bidons quand l\'eau est coupée.', 'summary' => 'سكان {n} يقطعون مسافة لملء القارورات عند انقطاع الماء.'],
                ],
            ],
            'lighting' => [
                'ary' => [
                    ['text' => 'ف{n} أعمدة الإنارة طافيين هادي ثلاث شهور، وولاد الحي كيخافو يخرجو فالليل.', 'summary' => 'الإنارة العمومية مطفأة في {n} منذ ثلاثة أشهر والسكان يخافون الخروج ليلاً.'],
                    ['text' => 'الظلام ف{n} ولى عادة. الإنارة العمومية مطفية قدام المدرسة.', 'summary' => 'أعمدة الإنارة أمام المدرسة في {n} لا تعمل.'],
                    ['text' => 'أضواء الزنقة ف{n} ما كيشعلوش من بعد العشاء، والناس كيرجعو للدار بالجماعات.', 'summary' => 'أضواء {n} تنطفئ بعد العشاء.'],
                    ['text' => 'طلبنا غير مرة على الإنارة ف{n} وما جا حتى شي واحد يصلحها.', 'summary' => 'طلبات إصلاح الإنارة في {n} بلا استجابة.'],
                ],
                'arabizi' => [
                    ['text' => 'f {n} dwaw dyal zn9a tafyin hadi 3 chhour, wlad l7ay kaykhafo ykhrjo f lil', 'normalized' => 'ف{n} أضواء الزنقة طافيين هادي ثلاث شهور، ولاد الحي كيخافو يخرجو فالليل. الإنارة مطفية.', 'summary' => 'أضواء {n} مطفأة منذ ثلاثة أشهر.'],
                    ['text' => 'l2inara f {n} mafiya 9dam lmedrasa, dwaw ma kaych3loch', 'normalized' => 'الإنارة ف{n} مطفية قدام المدرسة، والأضواء ما كيشعلوش.', 'summary' => 'الإنارة أمام مدرسة {n} مطفأة.'],
                    ['text' => 'mn ba3d l3cha {n} kayb9a f dlam, tafyin kol chi', 'normalized' => 'من بعد العشاء {n} كتبقى فالظلام، والأضواء طافيين.', 'summary' => 'الحي {n} يغرق في الظلام بعد العشاء.'],
                    ['text' => '3awd tlabna l2inara f {n} w ma jach 7ta chi wa7ed', 'normalized' => 'عاود طلبنا الإنارة ف{n} وما جاش حتى شي واحد.', 'summary' => 'لا أحد أصلح الإنارة في {n}.'],
                ],
                'ar' => [
                    ['text' => 'أعمدة الإنارة العمومية في {n} مطفأة منذ أشهر، ويخشى السكان الخروج ليلاً.', 'summary' => 'الإنارة مطفأة في {n} والخوف من الخروج ليلاً.'],
                    ['text' => 'الظلام أمام المدرسة في {n} يعرّض التلاميذ للخطر عند العودة المسائية.', 'summary' => 'ظلام أمام مدرسة {n} يهدد التلاميذ.'],
                    ['text' => 'طلبات إصلاح أضواء {n} قُدمت للمقاطعة دون نتيجة.', 'summary' => 'شكايات أضواء {n} بلا متابعة.'],
                    ['text' => 'شارع رئيسي في {n} بلا إنارة، والنساء يتجنبن المرور منه بعد المغرب.', 'summary' => 'شارع في {n} بلا إنارة بعد المغرب.'],
                ],
                'fr' => [
                    ['text' => 'À {n} l\'éclairage public est éteint depuis trois mois. Les lampadaires devant l\'école ne marchent plus.', 'summary' => 'الإنارة العمومية في {n} مطفأة منذ ثلاثة أشهر.'],
                    ['text' => 'Le quartier {n} reste dans le noir après le dîner. Les familles ont peur de sortir.', 'summary' => 'حي {n} مظلم بعد العشاء والناس يخافون الخروج.'],
                    ['text' => 'Nous avons signalé les lampadaires cassés à {n} plusieurs fois.', 'summary' => 'أعمدة الإنارة المعطلة في {n} أُبلغ عنها مراراً.'],
                    ['text' => 'Sans éclairage à {n}, les commerces ferment plus tôt.', 'summary' => 'غياب الإنارة في {n} يدفع المتاجر للإغلاق مبكراً.'],
                ],
            ],
            'transport' => [
                'ary' => [
                    ['text' => 'الطوبيس ف{n} كيدوز كل 45 دقيقة فالصباح، وكنوصلو متأخرين للخدمة.', 'summary' => 'حافلة {n} تمر كل 45 دقيقة صباحاً فيتأخر الناس عن العمل.'],
                    ['text' => 'الحافلة ما كتجيش ف{n}، وكنمشيو على رجلينا حتى للمحطة اللي بعيدة.', 'summary' => 'الحافلة لا تأتي إلى {n}.'],
                    ['text' => 'الطوبيسات ف{n} عامرين بزاف، وما كيوقفوش فالمحطة ديالنا.', 'summary' => 'حافلات {n} مكتظة ولا تتوقف في المحطة.'],
                    ['text' => 'فالصباح الباكر ف{n} كيتسناو الناس الطوبيس حتى كيتأخروا على الخدمة.', 'summary' => 'انتظار الحافلة في {n} يسبب تأخراً عن العمل.'],
                ],
                'arabizi' => [
                    ['text' => 'tobis f {n} kaydoz kol 45 dqiqa f sbah, kanwslo m3tlin l khdma', 'normalized' => 'الطوبيس ف{n} كيدوز كل 45 دقيقة فالصباح، وكنوصلو متأخرين للخدمة.', 'summary' => 'حافلة {n} كل 45 دقيقة تسبب التأخر عن العمل.'],
                    ['text' => 'l7afla ma katjich l {n}, kanmchiw 3la rjlina', 'normalized' => 'الحافلة ما كتجيش ل{n}، وكنمشيو على رجلينا.', 'summary' => 'لا توجد حافلة تصل إلى {n}.'],
                    ['text' => 'tobisat f {n} 3amrin w ma kayw9foch f lma7ata', 'normalized' => 'الطوبيسات ف{n} عامرين وما كيوقفوش فالمحطة.', 'summary' => 'الحافلات المكتظة لا تتوقف في محطة {n}.'],
                    ['text' => 'kol sba7 f {n} ntsenaw tobis w nwslou m3atalin', 'normalized' => 'كل صباح ف{n} نتسناو الطوبيس ونوصلو متأخرين.', 'summary' => 'انتظار الصباح في {n} ينتهي بالتأخر.'],
                ],
                'ar' => [
                    ['text' => 'حافلات {n} تمر كل 45 دقيقة في الصباح، فيصل الموظفون متأخرين إلى العمل.', 'summary' => 'ضعف وتيرة الحافلات في {n} يسبب التأخر.'],
                    ['text' => 'لا تتوقف الحافلة في المحطة القريبة من {n} رغم أن الخط معلن.', 'summary' => 'الحافلة لا تتوقف قرب {n}.'],
                    ['text' => 'الاكتظاظ في النقل الحضري بـ{n} يجعل الركوب صعباً على كبار السن.', 'summary' => 'اكتظاظ النقل في {n} يرهق كبار السن.'],
                    ['text' => 'غياب حافلة مبكرة من {n} يضطر الطلاب لركوب سيارات الأجرة.', 'summary' => 'طلاب {n} يلجأون إلى سيارات الأجرة.'],
                ],
                'fr' => [
                    ['text' => 'Bonjour, à {n} le bus passe toutes les 45 minutes le matin, on arrive en retard au travail chaque jour.', 'summary' => 'الحافلة في {n} كل 45 دقيقة صباحاً تسبب التأخر عن العمل.'],
                    ['text' => 'Le bus ne s\'arrête plus à la station de {n}. On marche longtemps.', 'summary' => 'الحافلة لم تعد تتوقف في محطة {n}.'],
                    ['text' => 'Les bus de {n} sont bondés et refusent des passagers à l\'heure de pointe.', 'summary' => 'حافلات {n} مكتظة في ساعة الذروة.'],
                    ['text' => 'Sans bus tôt le matin à {n}, les étudiants prennent un taxi.', 'summary' => 'غياب حافلة الصباح في {n} يدفع الطلاب لسيارات الأجرة.'],
                ],
            ],
            'waste' => [
                'ary' => [
                    ['text' => 'الأزبال متراكمة قرب السوق ف{n}، والزنقة فيها الزبل من الجمعة.', 'summary' => 'الأزبال متراكمة قرب سوق {n} منذ الجمعة.'],
                    ['text' => 'الزبل ما كيتجمعش ف{n}، والناس كيسدو الشبابك من الريحة.', 'summary' => 'النفايات لا تُرفع في {n}.'],
                    ['text' => 'الحاوية ديال الزبل ف{n} طاحت وما تبدلاتش.', 'summary' => 'حاوية النفايات في {n} مقلوبة ولم تُستبدل.'],
                    ['text' => 'كل أسبوع الأزبال كترجع قدام الحانوت ف{n}.', 'summary' => 'النفايات تعود أمام الحانوت في {n} كل أسبوع.'],
                ],
                'arabizi' => [
                    ['text' => 'zbel mtrakm 9rab sou9 f {n}, zn9a 3amra men jomo3a', 'normalized' => 'الزبل متراكم قرب السوق ف{n}، والزنقة عامرة من الجمعة.', 'summary' => 'زبالة متراكمة قرب سوق {n}.'],
                    ['text' => 'ma kaytjm3och zbel f {n}, nass kayssdo chbbak', 'normalized' => 'ما كيتجمعش الزبل ف{n}، والناس كيسدو الشبابك.', 'summary' => 'جمع النفايات متوقف في {n}.'],
                    ['text' => 'l7awia dyal zbel f {n} ta7et w ma tbddlatch', 'normalized' => 'الحاوية ديال الزبل ف{n} طاحت وما تبدلاتش.', 'summary' => 'حاوية {n} مكسورة.'],
                    ['text' => 'kol smana azbal katraje 9dam l7anout f {n}', 'normalized' => 'كل سمانة الأزبال كترجع قدام الحانوت ف{n}.', 'summary' => 'الأزبال تعود أسبوعياً في {n}.'],
                ],
                'ar' => [
                    ['text' => 'تتكدس النفايات قرب السوق في {n} ولا تُرفع بانتظام.', 'summary' => 'نفايات السوق في {n} لا تُرفع بانتظام.'],
                    ['text' => 'حاويات الأزبال في {n} ممتلئة وتسد الرصيف.', 'summary' => 'حاويات {n} تسد الرصيف.'],
                    ['text' => 'سكان {n} يغلقون النوافذ بسبب رائحة الزبل المتراكم.', 'summary' => 'رائحة النفايات في {n} تدخل البيوت.'],
                    ['text' => 'جدول جمع الأزبال في {n} لا يُحترم منذ شهر.', 'summary' => 'جمع النفايات في {n} متعطل منذ شهر.'],
                ],
                'fr' => [
                    ['text' => 'Les déchets s\'accumulent près du marché à {n}. Les poubelles débordent depuis une semaine.', 'summary' => 'النفايات تتكدس قرب سوق {n}.'],
                    ['text' => 'Les ordures de {n} ne sont plus ramassées. L\'odeur entre dans les maisons.', 'summary' => 'رفع النفايات توقف في {n}.'],
                    ['text' => 'Une poubelle renversée bloque le trottoir à {n}.', 'summary' => 'حاوية مقلوبة تسد الرصيف في {n}.'],
                    ['text' => 'Chaque semaine les déchets reviennent devant les commerces de {n}.', 'summary' => 'النفايات تعود أسبوعياً أمام محلات {n}.'],
                ],
            ],
            'roads' => [
                'ary' => [
                    ['text' => 'الحفر ف{n} ولاو بحال البرك، والسيارات كيتقطعو عليهم. الطريق مكسرة.', 'summary' => 'حفر عميقة في طرق {n} تعرقل السيارات.'],
                    ['text' => 'كاين حفرة كبيرة فمدخل {n}، والدراجات كيطيحو.', 'summary' => 'حفرة كبيرة عند مدخل {n} تُسقط الدراجات.'],
                    ['text' => 'منين كتمطر الحفر ف{n} كتعمر بالماء وما نقدروش ندوزو.', 'summary' => 'حفر {n} تمتلئ بالماء حين تمطر.'],
                    ['text' => 'الطريق الرئيسية ف{n} فيها حفر من العام اللي فات وما تصلحاتش.', 'summary' => 'الطريق الرئيسية في {n} لم تُصلح منذ السنة الماضية.'],
                ],
                'arabizi' => [
                    ['text' => 'l7for f {n} wlaw b7al lbrak, tomobilat kayt9t3o', 'normalized' => 'الحفر ف{n} ولاو بحال البرك، والطوموبيلات كيتقطعو.', 'summary' => 'حفر {n} تعطل السيارات.'],
                    ['text' => '7ofra kbira f dkhol {n}, drajat kayti7o', 'normalized' => 'حفرة كبيرة فمدخل {n}، والدراجات كيطيحو.', 'summary' => 'حفرة عند مدخل {n} خطرة على الدراجات.'],
                    ['text' => 'mli katmter l7for f {n} kayt3mmro b lma', 'normalized' => 'ملي كتمطر الحفر ف{n} كتعمر بالماء.', 'summary' => 'حفر {n} تصبح بركاً مع المطر.'],
                    ['text' => 'tri9 kbira f {n} fiha 7for men l3am li fat', 'normalized' => 'طريق كبيرة ف{n} فيها حفر من العام اللي فات.', 'summary' => 'حفر قديمة في طريق {n}.'],
                ],
                'ar' => [
                    ['text' => 'الحفر في شوارع {n} تتلف السيارات وتصبح بركاً عند المطر.', 'summary' => 'حفر {n} تتلف السيارات.'],
                    ['text' => 'حفرة عميقة عند مدخل {n} تسببت في سقوط دراجات.', 'summary' => 'حفرة مدخل {n} تسقط الدراجات.'],
                    ['text' => 'الطريق الرئيسية لـ{n} لم تُعبد منذ السنة الماضية.', 'summary' => 'طريق {n} بلا صيانة منذ سنة.'],
                    ['text' => 'السكان يتجنبون شارعاً في {n} لكثرة الحفر ليلاً.', 'summary' => 'الحفر ليلاً في {n} تدفع السكان لتجنب الشارع.'],
                ],
                'fr' => [
                    ['text' => 'Les nids-de-poule à {n} abîment les voitures. La chaussée est défoncée depuis des mois.', 'summary' => 'حفر الطريق في {n} تتلف السيارات.'],
                    ['text' => 'Un trou énorme à l\'entrée de {n} fait tomber les vélos quand il pleut.', 'summary' => 'حفرة عند مدخل {n} تُسقط الدراجات عند المطر.'],
                    ['text' => 'La chaussée principale de {n} n\'a pas été refaite depuis un an.', 'summary' => 'الشارع الرئيسي في {n} بلا إعادة تعبيد منذ سنة.'],
                    ['text' => 'Après la pluie, les nids-de-poule de {n} deviennent des flaques infranchissables.', 'summary' => 'بعد المطر تتحول حفر {n} إلى برك.'],
                ],
            ],
            'sewage' => [
                'ary' => [
                    ['text' => 'واد الحار طالع فالزنقة ف{n}، والريحة واصلة حتى للمدرسة.', 'summary' => 'مياه الصرف تطفو في أزقة {n} ورائحتها تصل المدرسة.'],
                    ['text' => 'الصرف الصحي مسدود ف{n}، والماء الوسخ كيجري قدام الدور.', 'summary' => 'الصرف الصحي مسدود في {n}.'],
                    ['text' => 'منين كيسدو واد الحار ف{n} كنسدّو الشبابك نهار كامل.', 'summary' => 'انسداد واد الحار في {n} يجبر السكان على إغلاق النوافذ.'],
                    ['text' => 'البلعة قدام الدار ف{n} كاتفيض، والصرف الصحي ما كيشربش.', 'summary' => 'بالوعة {n} تفيض لأن الصرف لا يستوعب.'],
                ],
                'arabizi' => [
                    ['text' => 'wad l7ar tale3 f zn9a f {n}, riha wasla l lmadrasa', 'normalized' => 'واد الحار طالع فالزنقة ف{n}، والريحة واصلة للمدرسة.', 'summary' => 'واد الحار في {n} ورائحته تصل المدرسة.'],
                    ['text' => 'sarf si7i msdod f {n}, lma lwssekh kayjri', 'normalized' => 'الصرف الصحي مسدود ف{n}، والماء الوسخ كيجري.', 'summary' => 'انسداد الصرف الصحي في {n}.'],
                    ['text' => 'mli kaysed wad l7ar f {n} kanssdo chbbak', 'normalized' => 'ملي كيسد واد الحار ف{n} كنسدو الشبابك.', 'summary' => 'انسداد واد الحار في {n} يدفع لإغلاق النوافذ.'],
                    ['text' => 'lbal3a 9dam dar f {n} katfid, riha 9wia', 'normalized' => 'البلعة قدام الدار ف{n} كاتفيض، والريحة قوية من واد الحار.', 'summary' => 'بالوعة {n} تفيض برائحة قوية.'],
                ],
                'ar' => [
                    ['text' => 'مياه الصرف الصحي تطفو في أزقة {n} وتصل رائحتها إلى المدرسة.', 'summary' => 'طفح الصرف الصحي في {n} يصل إلى المدرسة.'],
                    ['text' => 'شبكة واد الحار في {n} مسدودة والمياه الوسخة تجري أمام المنازل.', 'summary' => 'شبكة الصرف في {n} مسدودة.'],
                    ['text' => 'يُغلق السكان نوافذهم في {n} طوال اليوم بسبب رائحة الصرف.', 'summary' => 'رائحة الصرف في {n} تدخل البيوت.'],
                    ['text' => 'البالوعة أمام المنازل في {n} تفيض عند كل استعمال كثيف.', 'summary' => 'بالوعات {n} تفيض باستمرار.'],
                ],
                'fr' => [
                    ['text' => 'Les eaux usées remontent dans la rue à {n}. Les égouts sont bouchés près de l\'école.', 'summary' => 'مياه الصرف تطفو في {n} قرب المدرسة.'],
                    ['text' => 'L\'odeur des égouts de {n} oblige les familles à fermer les fenêtres.', 'summary' => 'رائحة الصرف في {n} تجبر العائلات على إغلاق النوافذ.'],
                    ['text' => 'À {n} les eaux usées coulent devant les portes après chaque pluie.', 'summary' => 'الصرف في {n} يسيل أمام الأبواب بعد المطر.'],
                    ['text' => 'Une bouche d\'égout déborde à {n} et personne n\'est venu la déboucher.', 'summary' => 'بالوعة {n} تفيض بلا تدخل.'],
                ],
            ],
        ];

        $rows = $bank[$topic][$language];
        $ready = [];

        foreach ($rows as $row) {
            $ready[] = [
                'text' => $row['text'],
                'normalized' => $row['normalized'] ?? $row['text'],
                'summary' => $row['summary'],
            ];
        }

        return $ready;
    }
}
