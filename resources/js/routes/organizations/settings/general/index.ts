import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults, validateParameters } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\OrganizationGeneralSettingsController::edit
* @see app/Http/Controllers/OrganizationGeneralSettingsController.php:19
* @param organization - Default: '$organization'
* @route '/{organization?}/settings/general'
*/
export const edit = (args?: { organization?: string | { slug: string } } | [organization: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/{organization?}/settings/general',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\OrganizationGeneralSettingsController::edit
* @see app/Http/Controllers/OrganizationGeneralSettingsController.php:19
* @param organization - Default: '$organization'
* @route '/{organization?}/settings/general'
*/
edit.url = (args?: { organization?: string | { slug: string } } | [organization: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { organization: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'slug' in args) {
        args = { organization: args.slug }
    }

    if (Array.isArray(args)) {
        args = {
            organization: args[0],
        }
    }

    args = applyUrlDefaults(args)

    validateParameters(args, [
        "organization",
    ])

    const parsedArgs = {
        organization: (typeof args?.organization === 'object'
        ? args.organization.slug
        : args?.organization) ?? '$organization',
    }

    return edit.definition.url
            .replace('{organization?}', parsedArgs.organization?.toString() ?? '')
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\OrganizationGeneralSettingsController::edit
* @see app/Http/Controllers/OrganizationGeneralSettingsController.php:19
* @param organization - Default: '$organization'
* @route '/{organization?}/settings/general'
*/
edit.get = (args?: { organization?: string | { slug: string } } | [organization: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\OrganizationGeneralSettingsController::edit
* @see app/Http/Controllers/OrganizationGeneralSettingsController.php:19
* @param organization - Default: '$organization'
* @route '/{organization?}/settings/general'
*/
edit.head = (args?: { organization?: string | { slug: string } } | [organization: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\OrganizationGeneralSettingsController::update
* @see app/Http/Controllers/OrganizationGeneralSettingsController.php:30
* @param organization - Default: '$organization'
* @route '/{organization?}/settings/general'
*/
export const update = (args?: { organization?: string | { slug: string } } | [organization: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: '/{organization?}/settings/general',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\OrganizationGeneralSettingsController::update
* @see app/Http/Controllers/OrganizationGeneralSettingsController.php:30
* @param organization - Default: '$organization'
* @route '/{organization?}/settings/general'
*/
update.url = (args?: { organization?: string | { slug: string } } | [organization: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { organization: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'slug' in args) {
        args = { organization: args.slug }
    }

    if (Array.isArray(args)) {
        args = {
            organization: args[0],
        }
    }

    args = applyUrlDefaults(args)

    validateParameters(args, [
        "organization",
    ])

    const parsedArgs = {
        organization: (typeof args?.organization === 'object'
        ? args.organization.slug
        : args?.organization) ?? '$organization',
    }

    return update.definition.url
            .replace('{organization?}', parsedArgs.organization?.toString() ?? '')
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\OrganizationGeneralSettingsController::update
* @see app/Http/Controllers/OrganizationGeneralSettingsController.php:30
* @param organization - Default: '$organization'
* @route '/{organization?}/settings/general'
*/
update.patch = (args?: { organization?: string | { slug: string } } | [organization: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

const general = {
    edit: Object.assign(edit, edit),
    update: Object.assign(update, update),
}

export default general
