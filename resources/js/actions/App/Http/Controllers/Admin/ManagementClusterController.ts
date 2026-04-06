import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\ManagementClusterController::index
* @see app/Http/Controllers/Admin/ManagementClusterController.php:17
* @route '/admin/management-clusters'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/management-clusters',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\ManagementClusterController::index
* @see app/Http/Controllers/Admin/ManagementClusterController.php:17
* @route '/admin/management-clusters'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ManagementClusterController::index
* @see app/Http/Controllers/Admin/ManagementClusterController.php:17
* @route '/admin/management-clusters'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\ManagementClusterController::index
* @see app/Http/Controllers/Admin/ManagementClusterController.php:17
* @route '/admin/management-clusters'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\ManagementClusterController::show
* @see app/Http/Controllers/Admin/ManagementClusterController.php:26
* @route '/admin/management-clusters/{management_cluster}'
*/
export const show = (args: { management_cluster: string | number } | [management_cluster: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/admin/management-clusters/{management_cluster}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\ManagementClusterController::show
* @see app/Http/Controllers/Admin/ManagementClusterController.php:26
* @route '/admin/management-clusters/{management_cluster}'
*/
show.url = (args: { management_cluster: string | number } | [management_cluster: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { management_cluster: args }
    }

    if (Array.isArray(args)) {
        args = {
            management_cluster: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        management_cluster: args.management_cluster,
    }

    return show.definition.url
            .replace('{management_cluster}', parsedArgs.management_cluster.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ManagementClusterController::show
* @see app/Http/Controllers/Admin/ManagementClusterController.php:26
* @route '/admin/management-clusters/{management_cluster}'
*/
show.get = (args: { management_cluster: string | number } | [management_cluster: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\ManagementClusterController::show
* @see app/Http/Controllers/Admin/ManagementClusterController.php:26
* @route '/admin/management-clusters/{management_cluster}'
*/
show.head = (args: { management_cluster: string | number } | [management_cluster: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

const ManagementClusterController = { index, show }

export default ManagementClusterController