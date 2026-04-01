<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Actions\SwitchOrganization;
use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

final readonly class EnsureOrganizationMembership
{
    public function __construct(
        private SwitchOrganization $switchOrganization,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user->organizations()->doesntExist()) {
            return redirect()->route('organizations.create');
        }

        $slug = $request->route('organization');

        $organization = Organization::query()->where('slug', $slug)->first();

        if (! $organization instanceof Organization) {
            abort(404);
        }

        if (Gate::denies('view', $organization)) {
            abort(403);
        }

        if ($user->current_organization_id !== $organization->id) {
            $this->switchOrganization->handle($user, $organization);
        }

        URL::defaults(['organization' => $organization->slug]);

        $request->route()->setParameter('organization', $organization);

        return $next($request);
    }
}
