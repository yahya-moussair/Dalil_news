<?php

namespace App\Support;

final class MessageCopy
{
    public static function question(string $newsroom, string $question): string
    {
        return "سؤال اليوم من {$newsroom}:\n{$question}\nتقدر تجاوب بالصوت أو بالنص.";
    }

    public static function welcome(string $newsroom): string
    {
        return "مرحباً بك في «دليل» من {$newsroom} 👋\n"
            ."سنرسل لك أحياناً سؤالاً عن حيّك ومدينتك. أجب بنص أو برسالة صوتية.\n"
            ."لإلغاء الاشتراك أرسل STOP في أي وقت.\n\n"
            .'بموافقتك تُعالَج أجوبتك لإنتاج مواضيع صحفية، ويمكنك طلب الحذف في أي وقت (القانون 09-08).';
    }

    public static function stopped(): string
    {
        return 'تم إلغاء اشتراكك. لن تصلك رسائل جديدة. أرسل JOIN في أي وقت للعودة.';
    }

    public static function ack(): string
    {
        return 'شكراً! توصلنا بجوابك ✅ غادي نخبروك بالنتائج.';
    }

    public static function closed(): string
    {
        return 'شكراً على رسالتك. السؤال تسدّ اليوم، وغنخبرو المشاركين بالنتيجة.';
    }

    /**
     * @param  list<array{topic_id: int, label_ar: string, label_fr: string}>  $options
     */
    public static function vote(int $replyCount, array $options): string
    {
        $lines = ["شاركتم معنا {$replyCount} جواب. اختاروا الموضوع الذي نحقق فيه بالرد برقم:"];

        foreach ($options as $index => $option) {
            $number = $index + 1;
            $lines[] = "{$number}. {$option['label_ar']}";
        }

        $lines[] = 'Reply with a number. Répondez par un numéro.';

        return implode("\n", $lines);
    }

    public static function voteSaved(string $label, bool $changed): string
    {
        if ($changed) {
            return "بدّلنا صوتك إلى «{$label}». شكراً.";
        }

        return "تسجّل صوتك على «{$label}». شكراً لمشاركتك.";
    }

    public static function voteInvalid(): string
    {
        return 'جاوب برقم من اللائحة، مثلاً 1 أو 2 أو 3.';
    }

    public static function replyBack(string $title, string $url, ?string $note): string
    {
        $lines = ['سألتم، فأجبنا', $title];

        if ($note !== null && trim($note) !== '') {
            $lines[] = trim($note);
        }

        $lines[] = $url;
        $lines[] = 'شكراً على مشاركتكم.';
        $lines[] = 'You asked, we answered.';

        return implode("\n", $lines);
    }
}
