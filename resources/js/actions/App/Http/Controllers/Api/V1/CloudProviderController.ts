import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\V1\CloudProviderController::index
* @see app/Http/Controllers/Api/V1/CloudProviderController.php:23
* @route '/api/v1/cloud-providers'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/v1/cloud-providers',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\CloudProviderController::index
* @see app/Http/Controllers/Api/V1/CloudProviderController.php:23
* @route '/api/v1/cloud-providers'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\CloudProviderController::index
* @see app/Http/Controllers/Api/V1/CloudProviderController.php:23
* @route '/api/v1/cloud-providers'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\V1\CloudProviderController::index
* @see app/Http/Controllers/Api/V1/CloudProviderController.php:23
* @route '/api/v1/cloud-providers'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Api\V1\CloudProviderController::store
* @see app/Http/Controllers/Api/V1/CloudProviderController.php:33
* @route '/api/v1/cloud-providers'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/api/v1/cloud-providers',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\CloudProviderController::store
* @see app/Http/Controllers/Api/V1/CloudProviderController.php:33
* @route '/api/v1/cloud-providers'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\CloudProviderController::store
* @see app/Http/Controllers/Api/V1/CloudProviderController.php:33
* @route '/api/v1/cloud-providers'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\V1\CloudProviderController::destroy
* @see app/Http/Controllers/Api/V1/CloudProviderController.php:57
* @route '/api/v1/cloud-providers/{cloud_provider}'
*/
export const destroy = (args: { cloud_provider: string | number } | [cloud_provider: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/api/v1/cloud-providers/{cloud_provider}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\V1\CloudProviderController::destroy
* @see app/Http/Controllers/Api/V1/CloudProviderController.php:57
* @route '/api/v1/cloud-providers/{cloud_provider}'
*/
destroy.url = (args: { cloud_provider: string | number } | [cloud_provider: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { cloud_provider: args }
    }

    if (Array.isArray(args)) {
        args = {
            cloud_provider: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        cloud_provider: args.cloud_provider,
    }

    return destroy.definition.url
            .replace('{cloud_provider}', parsedArgs.cloud_provider.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\CloudProviderController::destroy
* @see app/Http/Controllers/Api/V1/CloudProviderController.php:57
* @route '/api/v1/cloud-providers/{cloud_provider}'
*/
destroy.delete = (args: { cloud_provider: string | number } | [cloud_provider: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

const CloudProviderController = { index, store, destroy }

export default CloudProviderController