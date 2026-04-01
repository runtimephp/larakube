import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults, validateParameters } from './../../wayfinder'
import settings from './settings'
import logo from './logo'
/**
* @see \App\Http\Controllers\OrganizationController::create
* @see app/Http/Controllers/OrganizationController.php:16
* @route '/organizations/create'
*/
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/organizations/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\OrganizationController::create
* @see app/Http/Controllers/OrganizationController.php:16
* @route '/organizations/create'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\OrganizationController::create
* @see app/Http/Controllers/OrganizationController.php:16
* @route '/organizations/create'
*/
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\OrganizationController::create
* @see app/Http/Controllers/OrganizationController.php:16
* @route '/organizations/create'
*/
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\OrganizationController::store
* @see app/Http/Controllers/OrganizationController.php:21
* @route '/organizations'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/organizations',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\OrganizationController::store
* @see app/Http/Controllers/OrganizationController.php:21
* @route '/organizations'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\OrganizationController::store
* @see app/Http/Controllers/OrganizationController.php:21
* @route '/organizations'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SwitchOrganizationController::switchMethod
* @see app/Http/Controllers/SwitchOrganizationController.php:14
* @route '/organizations/{organization}/switch'
*/
export const switchMethod = (args: { organization: string | { slug: string } } | [organization: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: switchMethod.url(args, options),
    method: 'post',
})

switchMethod.definition = {
    methods: ["post"],
    url: '/organizations/{organization}/switch',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\SwitchOrganizationController::switchMethod
* @see app/Http/Controllers/SwitchOrganizationController.php:14
* @route '/organizations/{organization}/switch'
*/
switchMethod.url = (args: { organization: string | { slug: string } } | [organization: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions) => {
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

    return switchMethod.definition.url
            .replace('{organization}', parsedArgs.organization.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\SwitchOrganizationController::switchMethod
* @see app/Http/Controllers/SwitchOrganizationController.php:14
* @route '/organizations/{organization}/switch'
*/
switchMethod.post = (args: { organization: string | { slug: string } } | [organization: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: switchMethod.url(args, options),
    method: 'post',
})

/**
* @see routes/web.php:36
* @param organization - Default: '$organization'
* @route '/{organization?}/dashboard'
*/
export const dashboard = (args?: { organization?: string | number } | [organization: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(args, options),
    method: 'get',
})

dashboard.definition = {
    methods: ["get","head"],
    url: '/{organization?}/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see routes/web.php:36
* @param organization - Default: '$organization'
* @route '/{organization?}/dashboard'
*/
dashboard.url = (args?: { organization?: string | number } | [organization: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { organization: args }
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
        organization: args?.organization ?? '$organization',
    }

    return dashboard.definition.url
            .replace('{organization?}', parsedArgs.organization?.toString() ?? '')
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see routes/web.php:36
* @param organization - Default: '$organization'
* @route '/{organization?}/dashboard'
*/
dashboard.get = (args?: { organization?: string | number } | [organization: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(args, options),
    method: 'get',
})

/**
* @see routes/web.php:36
* @param organization - Default: '$organization'
* @route '/{organization?}/dashboard'
*/
dashboard.head = (args?: { organization?: string | number } | [organization: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dashboard.url(args, options),
    method: 'head',
})

const organizations = {
    create: Object.assign(create, create),
    store: Object.assign(store, store),
    switch: Object.assign(switchMethod, switchMethod),
    dashboard: Object.assign(dashboard, dashboard),
    settings: Object.assign(settings, settings),
    logo: Object.assign(logo, logo),
}

export default organizations
