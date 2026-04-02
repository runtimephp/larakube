import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../wayfinder'
import annotations from './annotations'
import screenshots from './screenshots'
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

const instruckt = {
    annotations: Object.assign(annotations, annotations),
    resolveSource: Object.assign(resolveSource, resolveSource),
    screenshots: Object.assign(screenshots, screenshots),
}

export default instruckt