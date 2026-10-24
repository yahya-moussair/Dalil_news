<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsrooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('city')->nullable();
            $table->string('wa_phone_number_id')->nullable()->unique();
            $table->string('plan')->default('pilot');
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('newsroom_id')->nullable()->constrained()->nullOnDelete();
            $table->string('role')->default('journalist');
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
        });

        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('newsroom_id')->constrained()->cascadeOnDelete();
            $table->text('wa_id');
            $table->string('wa_id_hash', 64);
            $table->string('display_name')->nullable();
            $table->string('neighborhood')->nullable();
            $table->string('language_pref')->nullable();
            $table->timestamp('consent_at')->nullable();
            $table->string('consent_source')->nullable();
            $table->string('consent_text_version')->nullable();
            $table->timestamp('opted_out_at')->nullable();
            $table->timestamp('last_inbound_at')->nullable();
            $table->string('channel')->default('whatsapp');
            $table->boolean('is_test_data')->default(false);
            $table->timestamps();

            $table->unique(['newsroom_id', 'wa_id_hash']);
        });

        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('newsroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('question');
            $table->string('template_name')->nullable();
            $table->string('template_lang')->default('ar');
            $table->string('status')->default('draft');
            $table->json('audience')->nullable();
            $table->json('vote_options')->nullable();
            $table->string('article_title')->nullable();
            $table->string('article_url')->nullable();
            $table->text('article_note')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('closes_at')->nullable();
            $table->timestamp('article_sent_at')->nullable();
            $table->timestamp('clustered_at')->nullable();
            $table->timestamps();
        });

        Schema::create('broadcasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->string('kind');
            $table->string('template_name')->nullable();
            $table->unsignedInteger('total')->default(0);
            $table->unsignedInteger('sent')->default(0);
            $table->unsignedInteger('delivered')->default(0);
            $table->unsignedInteger('read')->default(0);
            $table->unsignedInteger('failed')->default(0);
            $table->decimal('cost_estimate', 10, 4)->default(0);
            $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('broadcast_id')->nullable()->constrained()->nullOnDelete();
            $table->string('wa_message_id')->nullable()->unique();
            $table->string('direction');
            $table->string('type');
            $table->text('body')->nullable();
            $table->string('media_id')->nullable();
            $table->string('media_path')->nullable();
            $table->unsignedInteger('duration_sec')->nullable();
            $table->string('status')->default('received');
            $table->boolean('is_test_data')->default(false);
            $table->timestamp('received_at')->nullable();
            $table->timestamps();

            $table->index(['campaign_id', 'direction', 'received_at']);
        });

        Schema::create('transcripts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->unique()->constrained()->cascadeOnDelete();
            $table->text('raw_text')->nullable();
            $table->text('normalized_text')->nullable();
            $table->text('summary_msa')->nullable();
            $table->string('language')->nullable();
            $table->string('provider')->nullable();
            $table->string('model')->nullable();
            $table->decimal('confidence', 5, 4)->nullable();
            $table->foreignId('edited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('edited_at')->nullable();
            $table->timestamps();
        });

        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->string('label_ar');
            $table->string('label_fr');
            $table->text('summary')->nullable();
            $table->unsignedInteger('replies_count')->default(0);
            $table->json('centroid')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->timestamps();
        });

        Schema::create('topic_reply', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('message_id')->constrained()->cascadeOnDelete();
            $table->decimal('score', 8, 4)->default(0);
            $table->boolean('is_featured_quote')->default(false);
            $table->timestamps();

            $table->unique(['topic_id', 'message_id']);
        });

        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('topic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['campaign_id', 'contact_id']);
        });

        Schema::create('webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->json('payload');
            $table->timestamp('processed_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_events');
        Schema::dropIfExists('votes');
        Schema::dropIfExists('topic_reply');
        Schema::dropIfExists('topics');
        Schema::dropIfExists('transcripts');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('broadcasts');
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('contacts');

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('newsroom_id');
            $table->dropColumn([
                'role',
                'two_factor_secret',
                'two_factor_recovery_codes',
                'two_factor_confirmed_at',
            ]);
        });

        Schema::dropIfExists('newsrooms');
    }
};
