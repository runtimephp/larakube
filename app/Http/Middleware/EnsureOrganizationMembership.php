<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Actions\SwitchOrganization;
use App\Models\Organization;
use App\Queries\OrganizationQuery;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

final readonly class EnsureOrganizationMembership
{
    public function __construct(
        private SwitchOrganization $switchOrganization,
        private OrganizationQuery $organizationQuery,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $routeOrganization = $request->route('organization');
        $slug = $routeOrganization instanceof Organization ? $routeOrganization->slug : (string) $routeOrganization;

        $organization = ($this->organizationQuery)()
            ->bySlug($slug)
            ->byUser($user)
            ->first();

        if (! $organization instanceof Organization) {
            if ($user->organizations()->doesntExist()) {
                return redirect()->route('organizations.create');
            }

            if (($this->organizationQuery)()->bySlug($slug)->first() instanceof Organization) {
                abort(403);
            }

            abort(404);
        }

        if ($user->current_organization_id !== $organization->id) {
            $this->switchOrganization->handle($user, $organization);
        }

        URL::defaults(['organization' => $organization->slug]);

        $request->route()->setParameter('organization', $organization);

        return $next($request);
    }
}
