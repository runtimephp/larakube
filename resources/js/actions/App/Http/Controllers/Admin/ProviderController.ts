import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\ProviderController::index
* @see app/Http/Controllers/Admin/ProviderController.php:25
* @route '/admin/settings/providers'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/settings/providers',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\ProviderController::index
* @see app/Http/Controllers/Admin/ProviderController.php:25
* @route '/admin/settings/providers'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ProviderController::index
* @see app/Http/Controllers/Admin/ProviderController.php:25
* @route '/admin/settings/providers'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\ProviderController::index
* @see app/Http/Controllers/Admin/ProviderController.php:25
* @route '/admin/settings/providers'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\ProviderController::store
* @see app/Http/Controllers/Admin/ProviderController.php:52
* @route '/admin/settings/providers'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/admin/settings/providers',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\ProviderController::store
* @see app/Http/Controllers/Admin/ProviderController.php:52
* @route '/admin/settings/providers'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ProviderController::store
* @see app/Http/Controllers/Admin/ProviderController.php:52
* @route '/admin/settings/providers'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\ProviderController::show
* @see app/Http/Controllers/Admin/ProviderController.php:62
* @route '/admin/settings/providers/{provider}'
*/
export const show = (args: { provider: string | { id: string } } | [provider: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/admin/settings/providers/{provider}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\ProviderController::show
* @see app/Http/Controllers/Admin/ProviderController.php:62
* @route '/admin/settings/providers/{provider}'
*/
show.url = (args: { provider: string | { id: string } } | [provider: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { provider: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { provider: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            provider: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        provider: typeof args.provider === 'object'
        ? args.provider.id
        : args.provider,
    }

    return show.definition.url
            .replace('{provider}', parsedArgs.provider.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ProviderController::show
* @see app/Http/Controllers/Admin/ProviderController.php:62
* @route '/admin/settings/providers/{provider}'
*/
show.get = (args: { provider: string | { id: string } } | [provider: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\ProviderController::show
* @see app/Http/Controllers/Admin/ProviderController.php:62
* @route '/admin/settings/providers/{provider}'
*/
show.head = (args: { provider: string | { id: string } } | [provider: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\ProviderController::update
* @see app/Http/Controllers/Admin/ProviderController.php:76
* @route '/admin/settings/providers/{provider}'
*/
export const update = (args: { provider: string | { id: string } } | [provider: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: '/admin/settings/providers/{provider}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Admin\ProviderController::update
* @see app/Http/Controllers/Admin/ProviderController.php:76
* @route '/admin/settings/providers/{provider}'
*/
update.url = (args: { provider: string | { id: string } } | [provider: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { provider: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { provider: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            provider: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        provider: typeof args.provider === 'object'
        ? args.provider.id
        : args.provider,
    }

    return update.definition.url
            .replace('{provider}', parsedArgs.provider.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ProviderController::update
* @see app/Http/Controllers/Admin/ProviderController.php:76
* @route '/admin/settings/providers/{provider}'
*/
update.patch = (args: { provider: string | { id: string } } | [provider: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

const ProviderController = { index, store, show, update }

export default ProviderController