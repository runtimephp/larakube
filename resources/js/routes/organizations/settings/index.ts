import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults, validateParameters } from './../../../wayfinder'
import general from './general'
import cloudProviders151255 from './cloud-providers'
/**
* @see routes/web.php:40
* @param organization - Default: '$organization'
* @route '/{organization?}/settings/members'
*/
export const members = (args?: { organization?: string | { slug: string } } | [organization: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: members.url(args, options),
    method: 'get',
})

members.definition = {
    methods: ["get","head"],
    url: '/{organization?}/settings/members',
} satisfies RouteDefinition<["get","head"]>

/**
* @see routes/web.php:40
* @param organization - Default: '$organization'
* @route '/{organization?}/settings/members'
*/
members.url = (args?: { organization?: string | { slug: string } } | [organization: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions) => {
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

    return members.definition.url
            .replace('{organization?}', parsedArgs.organization?.toString() ?? '')
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see routes/web.php:40
* @param organization - Default: '$organization'
* @route '/{organization?}/settings/members'
*/
members.get = (args?: { organization?: string | { slug: string } } | [organization: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: members.url(args, options),
    method: 'get',
})

/**
* @see routes/web.php:40
* @param organization - Default: '$organization'
* @route '/{organization?}/settings/members'
*/
members.head = (args?: { organization?: string | { slug: string } } | [organization: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: members.url(args, options),
    method: 'head',
})

/**
* @see routes/web.php:47
* @param organization - Default: '$organization'
* @route '/{organization?}/settings/billing'
*/
export const billing = (args?: { organization?: string | { slug: string } } | [organization: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: billing.url(args, options),
    method: 'get',
})

billing.definition = {
    methods: ["get","head"],
    url: '/{organization?}/settings/billing',
} satisfies RouteDefinition<["get","head"]>

/**
* @see routes/web.php:47
* @param organization - Default: '$organization'
* @route '/{organization?}/settings/billing'
*/
billing.url = (args?: { organization?: string | { slug: string } } | [organization: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions) => {
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

    return billing.definition.url
            .replace('{organization?}', parsedArgs.organization?.toString() ?? '')
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see routes/web.php:47
* @param organization - Default: '$organization'
* @route '/{organization?}/settings/billing'
*/
billing.get = (args?: { organization?: string | { slug: string } } | [organization: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: billing.url(args, options),
    method: 'get',
})

/**
* @see routes/web.php:47
* @param organization - Default: '$organization'
* @route '/{organization?}/settings/billing'
*/
billing.head = (args?: { organization?: string | { slug: string } } | [organization: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: billing.url(args, options),
    method: 'head',
})

/**
* @see \OrganizationCloudProvidersController::cloudProviders
* @see [unknown]:0
* @param organization - Default: '$organization'
* @route '/{organization?}/settings/cloud-providers'
*/
export const cloudProviders = (args?: { organization?: string | number } | [organization: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: cloudProviders.url(args, options),
    method: 'get',
})

cloudProviders.definition = {
    methods: ["get","head"],
    url: '/{organization?}/settings/cloud-providers',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \OrganizationCloudProvidersController::cloudProviders
* @see [unknown]:0
* @param organization - Default: '$organization'
* @route '/{organization?}/settings/cloud-providers'
*/
cloudProviders.url = (args?: { organization?: string | number } | [organization: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return cloudProviders.definition.url
            .replace('{organization?}', parsedArgs.organization?.toString() ?? '')
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \OrganizationCloudProvidersController::cloudProviders
* @see [unknown]:0
* @param organization - Default: '$organization'
* @route '/{organization?}/settings/cloud-providers'
*/
cloudProviders.get = (args?: { organization?: string | number } | [organization: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: cloudProviders.url(args, options),
    method: 'get',
})

/**
* @see \OrganizationCloudProvidersController::cloudProviders
* @see [unknown]:0
* @param organization - Default: '$organization'
* @route '/{organization?}/settings/cloud-providers'
*/
cloudProviders.head = (args?: { organization?: string | number } | [organization: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: cloudProviders.url(args, options),
    method: 'head',
})

/**
* @see routes/web.php:57
* @param organization - Default: '$organization'
* @route '/{organization?}/settings/danger-zone'
*/
export const dangerZone = (args?: { organization?: string | { slug: string } } | [organization: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dangerZone.url(args, options),
    method: 'get',
})

dangerZone.definition = {
    methods: ["get","head"],
    url: '/{organization?}/settings/danger-zone',
} satisfies RouteDefinition<["get","head"]>

/**
* @see routes/web.php:57
* @param organization - Default: '$organization'
* @route '/{organization?}/settings/danger-zone'
*/
dangerZone.url = (args?: { organization?: string | { slug: string } } | [organization: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions) => {
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

    return dangerZone.definition.url
            .replace('{organization?}', parsedArgs.organization?.toString() ?? '')
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see routes/web.php:57
* @param organization - Default: '$organization'
* @route '/{organization?}/settings/danger-zone'
*/
dangerZone.get = (args?: { organization?: string | { slug: string } } | [organization: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dangerZone.url(args, options),
    method: 'get',
})

/**
* @see routes/web.php:57
* @param organization - Default: '$organization'
* @route '/{organization?}/settings/danger-zone'
*/
dangerZone.head = (args?: { organization?: string | { slug: string } } | [organization: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dangerZone.url(args, options),
    method: 'head',
})

const settings = {
    general: Object.assign(general, general),
    members: Object.assign(members, members),
    billing: Object.assign(billing, billing),
    cloudProviders: Object.assign(cloudProviders, cloudProviders151255),
    dangerZone: Object.assign(dangerZone, dangerZone),
}

export default settings