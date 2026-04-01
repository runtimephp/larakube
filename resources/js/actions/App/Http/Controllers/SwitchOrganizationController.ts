import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\SwitchOrganizationController::store
* @see app/Http/Controllers/SwitchOrganizationController.php:14
* @route '/organizations/{organization}/switch'
*/
export const store = (args: { organization: string | { slug: string } } | [organization: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/organizations/{organization}/switch',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\SwitchOrganizationController::store
* @see app/Http/Controllers/SwitchOrganizationController.php:14
* @route '/organizations/{organization}/switch'
*/
store.url = (args: { organization: string | { slug: string } } | [organization: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions) => {
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

    const parsedArgs = {
        organization: typeof args.organization === 'object'
        ? args.organization.slug
        : args.organization,
    }

    return store.definition.url
            .replace('{organization}', parsedArgs.organization.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\SwitchOrganizationController::store
* @see app/Http/Controllers/SwitchOrganizationController.php:14
* @route '/organizations/{organization}/switch'
*/
store.post = (args: { organization: string | { slug: string } } | [organization: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

const SwitchOrganizationController = { store }

export default SwitchOrganizationController