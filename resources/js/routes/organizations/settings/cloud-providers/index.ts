import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults, validateParameters } from './../../../../wayfinder'
/**
* @see \OrganizationCloudProvidersController::store
* @see [unknown]:0
* @param organization - Default: '$organization'
* @route '/{organization?}/settings/cloud-providers'
*/
export const store = (args?: { organization?: string | number } | [organization: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/{organization?}/settings/cloud-providers',
} satisfies RouteDefinition<["post"]>

/**
* @see \OrganizationCloudProvidersController::store
* @see [unknown]:0
* @param organization - Default: '$organization'
* @route '/{organization?}/settings/cloud-providers'
*/
store.url = (args?: { organization?: string | number } | [organization: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return store.definition.url
            .replace('{organization?}', parsedArgs.organization?.toString() ?? '')
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \OrganizationCloudProvidersController::store
* @see [unknown]:0
* @param organization - Default: '$organization'
* @route '/{organization?}/settings/cloud-providers'
*/
store.post = (args?: { organization?: string | number } | [organization: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

/**
* @see \OrganizationCloudProvidersController::destroy
* @see [unknown]:0
* @param organization - Default: '$organization'
* @route '/{organization?}/settings/cloud-providers/{cloudProvider}'
*/
export const destroy = (args: { organization?: string | number, cloudProvider: string | number } | [organization: string | number, cloudProvider: string | number ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/{organization?}/settings/cloud-providers/{cloudProvider}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \OrganizationCloudProvidersController::destroy
* @see [unknown]:0
* @param organization - Default: '$organization'
* @route '/{organization?}/settings/cloud-providers/{cloudProvider}'
*/
destroy.url = (args: { organization?: string | number, cloudProvider: string | number } | [organization: string | number, cloudProvider: string | number ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            organization: args[0],
            cloudProvider: args[1],
        }
    }

    args = applyUrlDefaults(args)

    validateParameters(args, [
        "organization",
    ])

    const parsedArgs = {
        organization: args.organization ?? '$organization',
        cloudProvider: args.cloudProvider,
    }

    return destroy.definition.url
            .replace('{organization?}', parsedArgs.organization?.toString() ?? '')
            .replace('{cloudProvider}', parsedArgs.cloudProvider.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \OrganizationCloudProvidersController::destroy
* @see [unknown]:0
* @param organization - Default: '$organization'
* @route '/{organization?}/settings/cloud-providers/{cloudProvider}'
*/
destroy.delete = (args: { organization?: string | number, cloudProvider: string | number } | [organization: string | number, cloudProvider: string | number ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

const cloudProviders = {
    store: Object.assign(store, store),
    destroy: Object.assign(destroy, destroy),
}

export default cloudProviders