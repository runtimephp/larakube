<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Enums\PlatformRole;
use App\Features\RegistrationFeature;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Pennant\Feature;

final class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if (! Feature::active(RegistrationFeature::class)) {
            throw ValidationException::withMessages([
                'email' => 'Registration is not available yet. Join the waitlist at kuven.io for early access.',
            ]);
        }

        $user = User::create([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'password' => Hash::make($request->string('password')->toString()),
            'platform_role' => PlatformRole::Member,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return to_route('organizations.create');
    }
}
