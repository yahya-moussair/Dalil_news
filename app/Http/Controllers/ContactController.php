<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesNewsroom;
use App\Models\Contact;
use App\Support\Presenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    use ResolvesNewsroom;

    public function index(Request $request): Response
    {
        $newsroom = $this->newsroom();
        $neighborhood = $request->string('neighborhood')->toString();

        $contacts = Contact::query()
            ->where('newsroom_id', $newsroom->id)
            ->when($neighborhood !== '', function ($query) use ($neighborhood): void {
                $query->where('neighborhood', $neighborhood);
            })
            ->latest('consent_at')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Contact $contact): array => Presenter::contact($contact));

        return Inertia::render('contacts/index', [
            'contacts' => $contacts,
            'neighborhoods' => config('dalil.neighborhoods'),
            'filter' => $neighborhood,
            'counts' => [
                'subscribed' => Contact::query()->where('newsroom_id', $newsroom->id)->whereNotNull('consent_at')->whereNull('opted_out_at')->count(),
                'opted_out' => Contact::query()->where('newsroom_id', $newsroom->id)->whereNotNull('opted_out_at')->count(),
            ],
        ]);
    }

    public function export(): HttpResponse
    {
        $contacts = Contact::query()
            ->where('newsroom_id', $this->newsroom()->id)
            ->orderBy('id')
            ->get();

        $lines = ["\xEF\xBB\xBF".'masked_phone,neighborhood,channel,consent_at,consent_source,opted_out_at,test_data'];

        foreach ($contacts as $contact) {
            $row = [
                $contact->maskedPhone(),
                $contact->neighborhood ?? '',
                $contact->channel->value,
                $contact->consent_at?->toIso8601String() ?? '',
                $contact->consent_source ?? '',
                $contact->opted_out_at?->toIso8601String() ?? '',
                $contact->is_test_data ? '1' : '0',
            ];
            $lines[] = implode(',', array_map(
                fn (string $value): string => '"'.str_replace('"', '""', $value).'"',
                $row,
            ));
        }

        return response(implode("\n", $lines), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="subscribers.csv"',
        ]);
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        abort_unless($contact->newsroom_id === $this->newsroom()->id, 404);
        $contact->delete();
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Subscriber and their messages were deleted.']);

        return back();
    }
}
