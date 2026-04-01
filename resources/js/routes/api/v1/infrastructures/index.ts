import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\V1\InfrastructureController::index
* @see app/Http/Controllers/Api/V1/InfrastructureController.php:19
* @route '/api/v1/infrastructures'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/v1/infrastructures',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\InfrastructureController::index
* @see app/Http/Controllers/Api/V1/InfrastructureController.php:19
* @route '/api/v1/infrastructures'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\InfrastructureController::index
* @see app/Http/Controllers/Api/V1/InfrastructureController.php:19
* @route '/api/v1/infrastructures'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\V1\InfrastructureController::index
* @see app/Http/Controllers/Api/V1/InfrastructureController.php:19
* @route '/api/v1/infrastructures'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Api\V1\InfrastructureController::store
* @see app/Http/Controllers/Api/V1/InfrastructureController.php:29
* @route '/api/v1/infrastructures'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/api/v1/infrastructures',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\InfrastructureController::store
* @see app/Http/Controllers/Api/V1/InfrastructureController.php:29
* @route '/api/v1/infrastructures'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\InfrastructureController::store
* @see app/Http/Controllers/Api/V1/InfrastructureController.php:29
* @route '/api/v1/infrastructures'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

const infrastructures = {
    index: Object.assign(index, index),
    store: Object.assign(store, store),
}

export default infrastructures