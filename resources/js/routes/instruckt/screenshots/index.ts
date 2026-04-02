import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \Instruckt\Laravel\Http\Controllers\AnnotationController::show
* @see vendor/joshcirre/instruckt-laravel/src/Http/Controllers/AnnotationController.php:87
* @route '/instruckt/screenshots/{filename}'
*/
export const show = (args: { filename: string | number } | [filename: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/instruckt/screenshots/{filename}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Instruckt\Laravel\Http\Controllers\AnnotationController::show
* @see vendor/joshcirre/instruckt-laravel/src/Http/Controllers/AnnotationController.php:87
* @route '/instruckt/screenshots/{filename}'
*/
show.url = (args: { filename: string | number } | [filename: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return show.definition.url
            .replace('{filename}', parsedArgs.filename.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \Instruckt\Laravel\Http\Controllers\AnnotationController::show
* @see vendor/joshcirre/instruckt-laravel/src/Http/Controllers/AnnotationController.php:87
* @route '/instruckt/screenshots/{filename}'
*/
show.get = (args: { filename: string | number } | [filename: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \Instruckt\Laravel\Http\Controllers\AnnotationController::show
* @see vendor/joshcirre/instruckt-laravel/src/Http/Controllers/AnnotationController.php:87
* @route '/instruckt/screenshots/{filename}'
*/
show.head = (args: { filename: string | number } | [filename: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

const screenshots = {
    show: Object.assign(show, show),
}

export default screenshots