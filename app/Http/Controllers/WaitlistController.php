<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\JoinWaitlist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class WaitlistController extends Controller
{
    public function store(Request $request, JoinWaitlist $joinWaitlist): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $joinWaitlist->handle($validated['email']);

        return back()->with('waitlist_success', true);
    }
}
