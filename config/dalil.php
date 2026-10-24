<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Drivers
    |--------------------------------------------------------------------------
    |
    | fake     — no network. Safe default for a clone-and-demo.
    | whatsapp — Meta Cloud API (needs WHATSAPP_* keys).
    | telegram — Bot API fallback channel.
    | openai   — real transcription, normalization, and clustering.
    |
    */

    'drivers' => [
        'messaging' => env('MESSAGING_DRIVER', 'fake'),
        'transcription' => env('TRANSCRIPTION_DRIVER', 'fake'),
        'normalization' => env('NORMALIZATION_DRIVER', 'fake'),
        'clustering' => env('CLUSTERING_DRIVER', 'fake'),
    ],

    'consent_text_version' => '2026-10-24',

    'pricing' => [
        'marketing_usd' => (float) env('WHATSAPP_MARKETING_USD', 0.0414),
        'utility_usd' => (float) env('WHATSAPP_UTILITY_USD', 0.0230),
    ],

    'transcribe_prompt' => 'Transcribe this voice note exactly as spoken. The speaker is in Morocco and may use Darija (Moroccan Arabic), Modern Standard Arabic, French, or a mix in the same sentence. Keep Darija in Arabic script. Do not translate and do not clean up grammar. Likely place names: سيدي مومن، البرنوصي، عين السبع، مولاي رشيد، الحي المحمدي، سباتة، الحي الحسني، درب السلطان، عين الشق، المعاريف، الدار البيضاء.',

    'neighborhoods' => [
        'سيدي مومن',
        'البرنوصي',
        'عين السبع',
        'مولاي رشيد',
        'الحي المحمدي',
        'سباتة',
        'الحي الحسني',
        'درب السلطان',
        'عين الشق',
        'المعاريف',
    ],

];
