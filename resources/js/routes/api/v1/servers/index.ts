import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\V1\ServerController::index
* @see app/Http/Controllers/Api/V1/ServerController.php:24
* @route '/api/v1/servers'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/v1/servers',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\ServerController::index
* @see app/Http/Controllers/Api/V1/ServerController.php:24
* @route '/api/v1/servers'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\ServerController::index
* @see app/Http/Controllers/Api/V1/ServerController.php:24
* @route '/api/v1/servers'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\V1\ServerController::index
* @see app/Http/Controllers/Api/V1/ServerController.php:24
* @route '/api/v1/servers'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Api\V1\ServerController::show
* @see app/Http/Controllers/Api/V1/ServerController.php:71
* @route '/api/v1/servers/{server}'
*/
export const show = (args: { server: string | { id: string } } | [server: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/api/v1/servers/{server}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\ServerController::show
* @see app/Http/Controllers/Api/V1/ServerController.php:71
* @route '/api/v1/servers/{server}'
*/
show.url = (args: { server: string | { id: string } } | [server: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { server: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { server: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            server: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        server: typeof args.server === 'object'
        ? args.server.id
        : args.server,
    }

    return show.definition.url
            .replace('{server}', parsedArgs.server.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\ServerController::show
* @see app/Http/Controllers/Api/V1/ServerController.php:71
* @route '/api/v1/servers/{server}'
*/
show.get = (args: { server: string | { id: string } } | [server: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\V1\ServerController::show
* @see app/Http/Controllers/Api/V1/ServerController.php:71
* @route '/api/v1/servers/{server}'
*/
show.head = (args: { server: string | { id: string } } | [server: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Api\V1\ServerController::destroy
* @see app/Http/Controllers/Api/V1/ServerController.php:76
* @route '/api/v1/servers/{server}'
*/
export const destroy = (args: { server: string | { id: string } } | [server: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/api/v1/servers/{server}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\V1\ServerController::destroy
* @see app/Http/Controllers/Api/V1/ServerController.php:76
* @route '/api/v1/servers/{server}'
*/
destroy.url = (args: { server: string | { id: string } } | [server: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { server: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { server: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            server: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        server: typeof args.server === 'object'
        ? args.server.id
        : args.server,
    }

    return destroy.definition.url
            .replace('{server}', parsedArgs.server.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\ServerController::destroy
* @see app/Http/Controllers/Api/V1/ServerController.php:76
* @route '/api/v1/servers/{server}'
*/
destroy.delete = (args: { server: string | { id: string } } | [server: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Api\V1\ServerController::store
* @see app/Http/Controllers/Api/V1/ServerController.php:34
* @route '/api/v1/servers'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/api/v1/servers',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\ServerController::store
* @see app/Http/Controllers/Api/V1/ServerController.php:34
* @route '/api/v1/servers'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\ServerController::store
* @see app/Http/Controllers/Api/V1/ServerController.php:34
* @route '/api/v1/servers'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\V1\SyncServerController::sync
* @see app/Http/Controllers/Api/V1/SyncServerController.php:14
* @route '/api/v1/servers/sync'
*/
export const sync = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: sync.url(options),
    method: 'post',
})

sync.definition = {
    methods: ["post"],
    url: '/api/v1/servers/sync',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\SyncServerController::sync
* @see app/Http/Controllers/Api/V1/SyncServerController.php:14
* @route '/api/v1/servers/sync'
*/
sync.url = (options?: RouteQueryOptions) => {
    return sync.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\SyncServerController::sync
* @see app/Http/Controllers/Api/V1/SyncServerController.php:14
* @route '/api/v1/servers/sync'
*/
sync.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: sync.url(options),
    method: 'post',
})

const servers = {
    index: Object.assign(index, index),
    show: Object.assign(show, show),
    destroy: Object.assign(destroy, destroy),
    store: Object.assign(store, store),
    sync: Object.assign(sync, sync),
}

export default servers