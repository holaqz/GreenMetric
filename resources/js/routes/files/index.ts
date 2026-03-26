import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\FileController::download
 * @see app/Http/Controllers/FileController.php:13
 * @route '/files/{file}/download'
 */
export const download = (args: { file: number | { id: number } } | [file: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: download.url(args, options),
    method: 'get',
})

download.definition = {
    methods: ["get","head"],
    url: '/files/{file}/download',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\FileController::download
 * @see app/Http/Controllers/FileController.php:13
 * @route '/files/{file}/download'
 */
download.url = (args: { file: number | { id: number } } | [file: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { file: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { file: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    file: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        file: typeof args.file === 'object'
                ? args.file.id
                : args.file,
                }

    return download.definition.url
            .replace('{file}', parsedArgs.file.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\FileController::download
 * @see app/Http/Controllers/FileController.php:13
 * @route '/files/{file}/download'
 */
download.get = (args: { file: number | { id: number } } | [file: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: download.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\FileController::download
 * @see app/Http/Controllers/FileController.php:13
 * @route '/files/{file}/download'
 */
download.head = (args: { file: number | { id: number } } | [file: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: download.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\FileController::show
 * @see app/Http/Controllers/FileController.php:32
 * @route '/files/{file}/view'
 */
export const show = (args: { file: number | { id: number } } | [file: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/files/{file}/view',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\FileController::show
 * @see app/Http/Controllers/FileController.php:32
 * @route '/files/{file}/view'
 */
show.url = (args: { file: number | { id: number } } | [file: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { file: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { file: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    file: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        file: typeof args.file === 'object'
                ? args.file.id
                : args.file,
                }

    return show.definition.url
            .replace('{file}', parsedArgs.file.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\FileController::show
 * @see app/Http/Controllers/FileController.php:32
 * @route '/files/{file}/view'
 */
show.get = (args: { file: number | { id: number } } | [file: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\FileController::show
 * @see app/Http/Controllers/FileController.php:32
 * @route '/files/{file}/view'
 */
show.head = (args: { file: number | { id: number } } | [file: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\IndicatorResponseController::deleteMethod
 * @see app/Http/Controllers/IndicatorResponseController.php:206
 * @route '/files/{file}'
 */
export const deleteMethod = (args: { file: number | { id: number } } | [file: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteMethod.url(args, options),
    method: 'delete',
})

deleteMethod.definition = {
    methods: ["delete"],
    url: '/files/{file}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\IndicatorResponseController::deleteMethod
 * @see app/Http/Controllers/IndicatorResponseController.php:206
 * @route '/files/{file}'
 */
deleteMethod.url = (args: { file: number | { id: number } } | [file: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { file: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { file: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    file: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        file: typeof args.file === 'object'
                ? args.file.id
                : args.file,
                }

    return deleteMethod.definition.url
            .replace('{file}', parsedArgs.file.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\IndicatorResponseController::deleteMethod
 * @see app/Http/Controllers/IndicatorResponseController.php:206
 * @route '/files/{file}'
 */
deleteMethod.delete = (args: { file: number | { id: number } } | [file: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteMethod.url(args, options),
    method: 'delete',
})
const files = {
    download: Object.assign(download, download),
show: Object.assign(show, show),
delete: Object.assign(deleteMethod, deleteMethod),
}

export default files