import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../wayfinder'
import token from './token'
/**
* @see \App\Http\Controllers\Api\V1\RegisterController::register
* @see app/Http/Controllers/Api/V1/RegisterController.php:15
* @route '/api/v1/auth/register'
*/
export const register = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: register.url(options),
    method: 'post',
})

register.definition = {
    methods: ["post"],
    url: '/api/v1/auth/register',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\RegisterController::register
* @see app/Http/Controllers/Api/V1/RegisterController.php:15
* @route '/api/v1/auth/register'
*/
register.url = (options?: RouteQueryOptions) => {
    return register.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\RegisterController::register
* @see app/Http/Controllers/Api/V1/RegisterController.php:15
* @route '/api/v1/auth/register'
*/
register.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: register.url(options),
    method: 'post',
})

const auth = {
    register: Object.assign(register, register),
    token: Object.assign(token, token),
}

export default auth