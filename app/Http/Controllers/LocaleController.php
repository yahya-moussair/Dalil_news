<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'locale' => ['required', 'in:ar,fr,en'],
        ]);

        return back()->withCookie(cookie('locale', $data['locale'], 60 * 24 * 365));
    }
}
