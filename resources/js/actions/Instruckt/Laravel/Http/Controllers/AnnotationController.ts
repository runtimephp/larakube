import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \Instruckt\Laravel\Http\Controllers\AnnotationController::index
* @see vendor/joshcirre/instruckt-laravel/src/Http/Controllers/AnnotationController.php:16
* @route '/instruckt/annotations'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/instruckt/annotations',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Instruckt\Laravel\Http\Controllers\AnnotationController::index
* @see vendor/joshcirre/instruckt-laravel/src/Http/Controllers/AnnotationController.php:16
* @route '/instruckt/annotations'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \Instruckt\Laravel\Http\Controllers\AnnotationController::index
* @see vendor/joshcirre/instruckt-laravel/src/Http/Controllers/AnnotationController.php:16
* @route '/instruckt/annotations'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \Instruckt\Laravel\Http\Controllers\AnnotationController::index
* @see vendor/joshcirre/instruckt-laravel/src/Http/Controllers/AnnotationController.php:16
* @route '/instruckt/annotations'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \Instruckt\Laravel\Http\Controllers\AnnotationController::store
* @see vendor/joshcirre/instruckt-laravel/src/Http/Controllers/AnnotationController.php:21
* @route '/instruckt/annotations'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/instruckt/annotations',
} satisfies RouteDefinition<["post"]>

/**
* @see \Instruckt\Laravel\Http\Controllers\AnnotationController::store
* @see vendor/joshcirre/instruckt-laravel/src/Http/Controllers/AnnotationController.php:21
* @route '/instruckt/annotations'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \Instruckt\Laravel\Http\Controllers\AnnotationController::store
* @see vendor/joshcirre/instruckt-laravel/src/Http/Controllers/AnnotationController.php:21
* @route '/instruckt/annotations'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \Instruckt\Laravel\Http\Controllers\AnnotationController::update
* @see vendor/joshcirre/instruckt-laravel/src/Http/Controllers/AnnotationController.php:45
* @route '/instruckt/annotations/{id}'
*/
export const update = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: '/instruckt/annotations/{id}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \Instruckt\Laravel\Http\Controllers\AnnotationController::update
* @see vendor/joshcirre/instruckt-laravel/src/Http/Controllers/AnnotationController.php:45
* @route '/instruckt/annotations/{id}'
*/
update.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    if (Array.isArray(args)) {
        args = {
            id: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        id: args.id,
    }

    return update.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \Instruckt\Laravel\Http\Controllers\AnnotationController::update
* @see vendor/joshcirre/instruckt-laravel/src/Http/Controllers/AnnotationController.php:45
* @route '/instruckt/annotations/{id}'
*/
update.patch = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \Instruckt\Laravel\Http\Controllers\AnnotationController::resolveSource
* @see vendor/joshcirre/instruckt-laravel/src/Http/Controllers/AnnotationController.php:72
* @route '/instruckt/resolve-source'
*/
export const resolveSource = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resolveSource.url(options),
    method: 'post',
})

resolveSource.definition = {
    methods: ["post"],
    url: '/instruckt/resolve-source',
} satisfies RouteDefinition<["post"]>

/**
* @see \Instruckt\Laravel\Http\Controllers\AnnotationController::resolveSource
* @see vendor/joshcirre/instruckt-laravel/src/Http/Controllers/AnnotationController.php:72
* @route '/instruckt/resolve-source'
*/
resolveSource.url = (options?: RouteQueryOptions) => {
    return resolveSource.definition.url + queryParams(options)
}

/**
* @see \Instruckt\Laravel\Http\Controllers\AnnotationController::resolveSource
* @see vendor/joshcirre/instruckt-laravel/src/Http/Controllers/AnnotationController.php:72
* @route '/instruckt/resolve-source'
*/
resolveSource.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resolveSource.url(options),
    method: 'post',
})

/**
* @see \Instruckt\Laravel\Http\Controllers\AnnotationController::screenshot
* @see vendor/joshcirre/instruckt-laravel/src/Http/Controllers/AnnotationController.php:87
* @route '/instruckt/screenshots/{filename}'
*/
export const screenshot = (args: { filename: string | number } | [filename: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: screenshot.url(args, options),
    method: 'get',
})

screenshot.definition = {
    methods: ["get","head"],
    url: '/instruckt/screenshots/{filename}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Instruckt\Laravel\Http\Controllers\AnnotationController::screenshot
* @see vendor/joshcirre/instruckt-laravel/src/Http/Controllers/AnnotationController.php:87
* @route '/instruckt/screenshots/{filename}'
*/
screenshot.url = (args: { filename: string | number } | [filename: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { filename: args }
    }

    if (Array.isArray(args)) {
        args = {
            filename: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        filename: args.filename,
    }

    return screenshot.definition.url
            .replace('{filename}', parsedArgs.filename.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \Instruckt\Laravel\Http\Controllers\AnnotationController::screenshot
* @see vendor/joshcirre/instruckt-laravel/src/Http/Controllers/AnnotationController.php:87
* @route '/instruckt/screenshots/{filename}'
*/
screenshot.get = (args: { filename: string | number } | [filename: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: screenshot.url(args, options),
    method: 'get',
})

/**
* @see \Instruckt\Laravel\Http\Controllers\AnnotationController::screenshot
* @see vendor/joshcirre/instruckt-laravel/src/Http/Controllers/AnnotationController.php:87
* @route '/instruckt/screenshots/{filename}'
*/
screenshot.head = (args: { filename: string | number } | [filename: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: screenshot.url(args, options),
    method: 'head',
})

const AnnotationController = { index, store, update, resolveSource, screenshot }

export default AnnotationController