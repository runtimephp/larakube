import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\V1\OrganizationController::index
* @see app/Http/Controllers/Api/V1/OrganizationController.php:18
* @route '/api/v1/organizations'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/v1/organizations',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\OrganizationController::index
* @see app/Http/Controllers/Api/V1/OrganizationController.php:18
* @route '/api/v1/organizations'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\OrganizationController::index
* @see app/Http/Controllers/Api/V1/OrganizationController.php:18
* @route '/api/v1/organizations'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\V1\OrganizationController::index
* @see app/Http/Controllers/Api/V1/OrganizationController.php:18
* @route '/api/v1/organizations'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Api\V1\OrganizationController::store
* @see app/Http/Controllers/Api/V1/OrganizationController.php:28
* @route '/api/v1/organizations'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/api/v1/organizations',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\OrganizationController::store
* @see app/Http/Controllers/Api/V1/OrganizationController.php:28
* @route '/api/v1/organizations'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\OrganizationController::store
* @see app/Http/Controllers/Api/V1/OrganizationController.php:28
* @route '/api/v1/organizations'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

const OrganizationController = { index, store }

export default OrganizationController