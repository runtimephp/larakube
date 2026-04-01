import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\V1\SyncServerController::store
* @see app/Http/Controllers/Api/V1/SyncServerController.php:14
* @route '/api/v1/servers/sync'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/api/v1/servers/sync',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\SyncServerController::store
* @see app/Http/Controllers/Api/V1/SyncServerController.php:14
* @route '/api/v1/servers/sync'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\SyncServerController::store
* @see app/Http/Controllers/Api/V1/SyncServerController.php:14
* @route '/api/v1/servers/sync'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

const SyncServerController = { store }

export default SyncServerController