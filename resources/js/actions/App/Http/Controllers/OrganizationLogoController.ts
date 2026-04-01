import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults, validateParameters } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\OrganizationLogoController::update
* @see app/Http/Controllers/OrganizationLogoController.php:14
* @param organization - Default: '$organization'
* @route '/{organization?}/settings/logo'
*/
export const update = (args?: { organization?: string | { slug: string } } | [organization: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: '/{organization?}/settings/logo',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\OrganizationLogoController::update
* @see app/Http/Controllers/OrganizationLogoController.php:14
* @param organization - Default: '$organization'
* @route '/{organization?}/settings/logo'
*/
update.url = (args?: { organization?: string | { slug: string } } | [organization: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { organization: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'slug' in args) {
        args = { organization: args.slug }
    }

    if (Array.isArray(args)) {
        args = {
            organization: args[0],
        }
    }

    args = applyUrlDefaults(args)

    validateParameters(args, [
        "organization",
    ])

    const parsedArgs = {
        organization: (typeof args?.organization === 'object'
        ? args.organization.slug
        : args?.organization) ?? '$organization',
    }

    return update.definition.url
            .replace('{organization?}', parsedArgs.organization?.toString() ?? '')
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\OrganizationLogoController::update
* @see app/Http/Controllers/OrganizationLogoController.php:14
* @param organization - Default: '$organization'
* @route '/{organization?}/settings/logo'
*/
update.patch = (args?: { organization?: string | { slug: string } } | [organization: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

const OrganizationLogoController = { update }

export default OrganizationLogoController
