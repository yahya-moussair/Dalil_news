export type Locale = 'ar' | 'fr' | 'en';

export type CampaignStatus = 'draft' | 'sent' | 'voting' | 'closed';

export type CampaignMetrics = {
    replies: number;
    participants: number;
    voice: number;
    voice_percent: number;
    subscribers_reached: number | null;
    response_rate: number | null;
    neighborhoods: number;
    read_rate: number | null;
    days_to_article: number | null;
    has_test_data: boolean;
};

export type Campaign = {
    id: number;
    title: string;
    question: string;
    template_name: string | null;
    template_lang: string;
    status: CampaignStatus;
    audience: { neighborhoods?: string[] } | null;
    vote_options: VoteOption[];
    article_title: string | null;
    article_url: string | null;
    article_note: string | null;
    sent_at: string | null;
    closes_at: string | null;
    article_sent_at: string | null;
    replies_count: number;
    voice_count: number;
    has_test_data: boolean;
    metrics: CampaignMetrics | null;
};

export type VoteOption = {
    topic_id: number;
    label_ar: string;
    label_fr: string;
};

export type BroadcastRow = {
    id: number;
    kind: string;
    template_name: string | null;
    total: number;
    sent: number;
    delivered: number;
    read: number;
    failed: number;
    cost_estimate: number;
};

export type ReplyRow = {
    id: number;
    type: string;
    body: string;
    original: string | null;
    summary: string | null;
    language: string | null;
    neighborhood: string | null;
    is_test_data: boolean;
    audio_url: string | null;
    duration_sec: number | null;
    received_at: string | null;
    edited: boolean;
};

export type TopicQuote = {
    message_id: number;
    text: string;
    language: string | null;
    neighborhood: string | null;
    audio_url: string | null;
    is_test_data: boolean;
};

export type TopicCard = {
    id: number;
    label_ar: string;
    label_fr: string;
    summary: string | null;
    replies_count: number;
    share: number;
    is_locked: boolean;
    neighborhoods: { name: string; count: number }[];
    languages: string[];
    quotes: TopicQuote[];
};

export type VoteRow = {
    topic_id: number;
    label_ar: string;
    label_fr: string;
    count: number;
    share: number;
};

export type ContactRow = {
    id: number;
    masked_phone: string;
    neighborhood: string | null;
    language_pref: string | null;
    channel: string;
    consent_at: string | null;
    consent_source: string | null;
    opted_out_at: string | null;
    is_test_data: boolean;
    subscribed: boolean;
};

export type Paginator<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    last_page: number;
    total: number;
};
