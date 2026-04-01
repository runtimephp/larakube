import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\V1\RegisterController::store
* @see app/Http/Controllers/Api/V1/RegisterController.php:15
* @route '/api/v1/auth/register'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/api/v1/auth/register',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\RegisterController::store
* @see app/Http/Controllers/Api/V1/RegisterController.php:15
* @route '/api/v1/auth/register'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\RegisterController::store
* @see app/Http/Controllers/Api/V1/RegisterController.php:15
* @route '/api/v1/auth/register'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

const RegisterController = { store }

export default RegisterController