# Dalil News

WhatsApp audience listening for Moroccan newsrooms. A journalist writes one question, opted-in readers answer by text or voice note in Darija, Arabizi, Arabic, or French, and the desk groups those replies into topics with counts and real quotes. Readers then vote on what to investigate. The published story goes back only to the people who took part: **سألتم، فأجبنا** / **You asked, we answered**.

Built for the Media Builders Hackathon (DW Akademie × LionsGeek), Casablanca, 29–30 October 2026. The default install uses a `fake` messaging driver, so the demo runs with no Meta, OpenAI, or Telegram keys.

## What works

- WhatsApp Cloud API webhook: `GET` verification and `POST` with `X-Hub-Signature-256`. The controller stores the payload and returns 200; a queue job does the work.
- Text and audio messages are stored. Voice-note media is downloaded onto the local disk.
- `JOIN` / `ابدأ` / `انضم` subscribes and stores `consent_at` (law 09-08, consent text version `2026-10-24`). `STOP` / `وقف` / `arrêt` unsubscribes.
- Transcription behind `TranscriptionService` (OpenAI, with a configurable model and a fallback model, or `fake`).
- Topic clustering behind `ClusteringService`. The model returns message ids; quotes are taken from stored replies and anonymized. Re-runnable per campaign. Locked topics are kept.
- Dashboard: login, questions, live inbox (polls every 4s), topics, vote setup and results, reply-back, masked subscribers, opt-in QR, impact.
- Voting by numbered reply (or an interactive list id `topic_{id}`). A later number replaces the vote.
- Reply-back broadcast to participants only.
- Telegram webhook on the same pipeline, when you want a second channel.
- Demo seed: 200 multilingual replies across six Casablanca neighborhood problems, flagged `is_test_data`.

Phone numbers are encrypted, looked up by hash, and shown masked (`+2126 •• ••• ••78`). Quotes strip phone-like digit runs and emails. The desk never receives `display_name`.

## Setup

Requirements: PHP 8.4, Composer, Node 22, SQLite (default) or MySQL.

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

In another terminal, so sends, transcription, and clustering leave the request:

```bash
php artisan queue:work
```

Optional, for automatic reclustering of open campaigns:

```bash
php artisan schedule:work
```

Log in as `hafsa@casahay.test` or `yahya@casahay.test`. Password: `password`.

Reseed the morning of the demo. Seeded replies sit inside the last 18 hours so the 24-hour WhatsApp service window is still open. After that, vote and reply-back fall back to a template.

```bash
php artisan migrate:fresh --seed
```

### MySQL

In `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dalil_news
DB_USERNAME=root
DB_PASSWORD=
```

Create the database, then `php artisan migrate --seed`.

### Frontend during development

```bash
composer dev
```

That starts the PHP server, the queue, the logs, and Vite together when the starter script is available. Otherwise run `php artisan serve`, `php artisan queue:work`, and `npm run dev` in three terminals.

## Drivers

| Env | `fake` (default) | Real |
| --- | --- | --- |
| `MESSAGING_DRIVER` | Stores outbound text. No network. | `whatsapp` or `telegram` |
| `TRANSCRIPTION_DRIVER` | Reads a sidecar `{file}.txt`, else a canned line. | `openai` |
| `NORMALIZATION_DRIVER` | Detects ary / arabizi / ar / fr. | `openai` (falls back to fake) |
| `CLUSTERING_DRIVER` | Keyword lexicon for the six demo topics. | `openai` (falls back to fake if the JSON is bad) |

`MESSAGING_DRIVER=fake` always uses the fake channel, including for Telegram contacts. With a real driver, contacts whose channel is Telegram use the bot; everyone else uses WhatsApp.

## Meta test number

1. In [Meta for Developers](https://developers.facebook.com/), create an app, add the WhatsApp product, and copy the temporary token, phone number id, and business account id.
2. Set `WHATSAPP_TOKEN`, `WHATSAPP_PHONE_NUMBER_ID`, `WHATSAPP_BUSINESS_ACCOUNT_ID`, `WHATSAPP_APP_SECRET`, and a long random `WHATSAPP_VERIFY_TOKEN`.
3. Set `WHATSAPP_DISPLAY_NUMBER` to the test number in digits (`212…` or the Meta test number) so the opt-in QR opens `wa.me`.
4. Set `MESSAGING_DRIVER=whatsapp`.
5. Put journalist handsets in `WHATSAPP_TEST_RECIPIENTS` (comma-separated). A dashboard test send refuses any other number. On a real token, the recipient must also have sent `JOIN`.
6. Create three approved templates (language `ar`):
   - `daily_question` — body parameters: newsroom name, question.
   - `vote_prompt` — one body parameter: the numbered ballot.
   - `you_asked_we_answered` — body parameters: headline, URL.
7. Inside the 24-hour window after a reader writes in, the app sends a free session message instead of a template.

## ngrok

Meta must reach your laptop.

```bash
ngrok http 8000
```

Callback URL: `https://<subdomain>.ngrok-free.app/webhooks/whatsapp`

Verify token: the same value as `WHATSAPP_VERIFY_TOKEN`.

Subscribe to the `messages` field. `APP_URL` should be the ngrok origin if you generate absolute links.

Signature check stays on. Do not set `WHATSAPP_SKIP_SIGNATURE=true` except for a local probe, and never in production.

## OpenAI

```env
OPENAI_API_KEY=sk-...
TRANSCRIPTION_DRIVER=openai
NORMALIZATION_DRIVER=openai
CLUSTERING_DRIVER=openai
```

`OPENAI_TRANSCRIBE_MODEL` defaults to `gpt-transcribe`. If that call fails, the service retries `OPENAI_TRANSCRIBE_FALLBACK_MODEL` (`gpt-4o-mini-transcribe`). The prompt asks for Darija in Arabic script and does not force a language. Clustering uses `OPENAI_CHAT_MODEL` (`gpt-4o-mini`) and must return ids of real messages. Turn off automatic recharge on the OpenAI account before the demo. Do not commit `.env`.

## Telegram (optional)

```env
TELEGRAM_BOT_TOKEN=
TELEGRAM_WEBHOOK_SECRET=
TELEGRAM_BOT_USERNAME=
```

Point the bot webhook at `POST /webhooks/telegram` with the secret token header. `/start` and `JOIN` opt in. This MVP resolves Telegram traffic to the first newsroom.

## Three-minute demo

1. Log in as Hafsa. The desk is in Arabic; switch to FR or EN from the header if the jury prefers it.
2. Open the seeded question. Show the broadcast totals and the amber demo-data banner.
3. Open **Topics**. Six Casablanca problems, counts, and anonymized quotes. Play one voice note and correct a transcript in place.
4. Open **Vote**, tick two topics, send. With the fake driver this queues a numbered message. On the question page, **Simulate a reply** with `1` to cast a vote, then show the result bar.
5. Open **Article**, paste a headline and a URL, and send «سألتم، فأجبنا».
6. Open **Opt-in QR** and **Subscribers**. Point at a masked number. Mention JOIN / STOP and law 09-08.
7. If keys are live: flip the drivers, start ngrok, send yourself the question, reply with a Darija voice note, and run the queue worker so the transcript lands in the inbox.

## Tests

```bash
php artisan test
npm run build
```

`composer ci:check` also runs formatting, PHPStan, and the TypeScript check. Feature tests cover the webhook (verify, signature, opt-in, idempotent messages), mocked transcription, mocked clustering, voting, and the masked subscriber list.

## Arabic — باختصار

دليل نيوز أداة استماع للجمهور عبر واتساب. الصحفي يكتب سؤالاً في المكتب، ويصله فقط من وافق بإرسال JOIN. القارئ يجاوب بنص أو برسالة صوتية بالدارجة أو العربيزي أو العربية أو الفرنسية. التطبيق يفرّغ الصوت ويجمع الردود في مواضيع بعدد واقتباسات حقيقية، ثم يصوّت القراء على موضوع التحقيق، ويرجع المقال لمن شارك: «سألتم، فأجبنا».

التشغيل المحلي لا يحتاج مفاتيح: انسخ `.env.example`، نفّذ `php artisan migrate --seed`، ثم `php artisan serve` و`php artisan queue:work`. الدخول: `hafsa@casahay.test` / `password`. أرقام الهواتف مخفية، ووقت الموافقة محفوظ وفق القانون 09-08. يوم العرض أعد البذر حتى تبقى نافذة الـ 24 ساعة مفتوحة.

## Templates the team must create

Nothing in this repo calls Meta until `MESSAGING_DRIVER=whatsapp` and the token is set. The fake driver is what the seeded demo uses.
