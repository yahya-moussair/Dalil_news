<?php

namespace App\Http\Controllers;

use App\Enums\MessageDirection;
use App\Http\Controllers\Concerns\ResolvesNewsroom;
use App\Models\Campaign;
use App\Models\Message;
use App\Models\Transcript;
use App\Services\CampaignMetrics;
use App\Support\Presenter;
use App\Support\QuoteAnonymizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReplyController extends Controller
{
    use ResolvesNewsroom;

    public function index(Campaign $campaign, CampaignMetrics $metrics): Response
    {
        $campaign = $this->ownedCampaign($campaign);

        $messages = $campaign->messages()
            ->where('direction', MessageDirection::In)
            ->whereIn('type', ['text', 'audio'])
            ->with(['transcript', 'contact'])
            ->latest('received_at')
            ->limit(250)
            ->get()
            ->map(fn (Message $message): array => Presenter::message($message))
            ->values()
            ->all();

        return Inertia::render('replies/inbox', [
            'campaign' => Presenter::campaign($campaign, $metrics->summarize($campaign)),
            'replies' => $messages,
        ]);
    }

    public function updateTranscript(Request $request, Campaign $campaign, Message $message): RedirectResponse
    {
        $campaign = $this->ownedCampaign($campaign);
        abort_unless($message->campaign_id === $campaign->id, 404);

        $data = $request->validate([
            'normalized_text' => ['required', 'string', 'max:5000'],
        ]);

        $transcript = $message->transcript ?? new Transcript([
            'message_id' => $message->id,
            'raw_text' => $message->body,
        ]);

        $transcript->fill([
            'normalized_text' => QuoteAnonymizer::clean($data['normalized_text']),
            'edited_by' => $request->user()?->id,
            'edited_at' => now(),
        ])->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Transcript corrected.']);

        return back();
    }

    public function audio(Campaign $campaign, Message $message): StreamedResponse
    {
        $campaign = $this->ownedCampaign($campaign);
        abort_unless($message->campaign_id === $campaign->id, 404);

        $path = $message->media_path;

        if (! is_string($path) || ! Storage::disk('local')->exists($path)) {
            abort(404);
        }

        $wav = str_ends_with(strtolower($path), '.wav');
        $mime = $wav ? 'audio/wav' : 'audio/ogg';

        return Storage::disk('local')->response($path, $wav ? 'voice-note.wav' : 'voice-note.ogg', [
            'Content-Type' => $mime,
        ]);
    }
}
