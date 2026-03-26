import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\IndicatorResponseController::update
 * @see app/Http/Controllers/IndicatorResponseController.php:18
 * @route '/responses/{response}'
 */
export const update = (args: { response: number | { id: number } } | [response: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: '/responses/{response}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\IndicatorResponseController::update
 * @see app/Http/Controllers/IndicatorResponseController.php:18
 * @route '/responses/{response}'
 */
update.url = (args: { response: number | { id: number } } | [response: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { response: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { response: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    response: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        response: typeof args.response === 'object'
                ? args.response.id
                : args.response,
                }

    return update.definition.url
            .replace('{response}', parsedArgs.response.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\IndicatorResponseController::update
 * @see app/Http/Controllers/IndicatorResponseController.php:18
 * @route '/responses/{response}'
 */
update.patch = (args: { response: number | { id: number } } | [response: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\IndicatorResponseController::uploadFile
 * @see app/Http/Controllers/IndicatorResponseController.php:157
 * @route '/responses/{response}/files'
 */
export const uploadFile = (args: { response: number | { id: number } } | [response: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: uploadFile.url(args, options),
    method: 'post',
})

uploadFile.definition = {
    methods: ["post"],
    url: '/responses/{response}/files',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\IndicatorResponseController::uploadFile
 * @see app/Http/Controllers/IndicatorResponseController.php:157
 * @route '/responses/{response}/files'
 */
uploadFile.url = (args: { response: number | { id: number } } | [response: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { response: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { response: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    response: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        response: typeof args.response === 'object'
                ? args.response.id
                : args.response,
                }

    return uploadFile.definition.url
            .replace('{response}', parsedArgs.response.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\IndicatorResponseController::uploadFile
 * @see app/Http/Controllers/IndicatorResponseController.php:157
 * @route '/responses/{response}/files'
 */
uploadFile.post = (args: { response: number | { id: number } } | [response: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: uploadFile.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\IndicatorResponseController::addLink
 * @see app/Http/Controllers/IndicatorResponseController.php:183
 * @route '/responses/{response}/links'
 */
export const addLink = (args: { response: number | { id: number } } | [response: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: addLink.url(args, options),
    method: 'post',
})

addLink.definition = {
    methods: ["post"],
    url: '/responses/{response}/links',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\IndicatorResponseController::addLink
 * @see app/Http/Controllers/IndicatorResponseController.php:183
 * @route '/responses/{response}/links'
 */
addLink.url = (args: { response: number | { id: number } } | [response: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { response: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { response: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    response: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        response: typeof args.response === 'object'
                ? args.response.id
                : args.response,
                }

    return addLink.definition.url
            .replace('{response}', parsedArgs.response.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\IndicatorResponseController::addLink
 * @see app/Http/Controllers/IndicatorResponseController.php:183
 * @route '/responses/{response}/links'
 */
addLink.post = (args: { response: number | { id: number } } | [response: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: addLink.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\IndicatorResponseController::history
 * @see app/Http/Controllers/IndicatorResponseController.php:222
 * @route '/responses/{response}/history'
 */
export const history = (args: { response: number | { id: number } } | [response: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: history.url(args, options),
    method: 'get',
})

history.definition = {
    methods: ["get","head"],
    url: '/responses/{response}/history',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\IndicatorResponseController::history
 * @see app/Http/Controllers/IndicatorResponseController.php:222
 * @route '/responses/{response}/history'
 */
history.url = (args: { response: number | { id: number } } | [response: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { response: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { response: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    response: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        response: typeof args.response === 'object'
                ? args.response.id
                : args.response,
                }

    return history.definition.url
            .replace('{response}', parsedArgs.response.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\IndicatorResponseController::history
 * @see app/Http/Controllers/IndicatorResponseController.php:222
 * @route '/responses/{response}/history'
 */
history.get = (args: { response: number | { id: number } } | [response: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: history.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\IndicatorResponseController::history
 * @see app/Http/Controllers/IndicatorResponseController.php:222
 * @route '/responses/{response}/history'
 */
history.head = (args: { response: number | { id: number } } | [response: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: history.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\IndicatorResponseController::deleteFile
 * @see app/Http/Controllers/IndicatorResponseController.php:206
 * @route '/files/{file}'
 */
export const deleteFile = (args: { file: number | { id: number } } | [file: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteFile.url(args, options),
    method: 'delete',
})

deleteFile.definition = {
    methods: ["delete"],
    url: '/files/{file}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\IndicatorResponseController::deleteFile
 * @see app/Http/Controllers/IndicatorResponseController.php:206
 * @route '/files/{file}'
 */
deleteFile.url = (args: { file: number | { id: number } } | [file: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return deleteFile.definition.url
            .replace('{file}', parsedArgs.file.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\IndicatorResponseController::deleteFile
 * @see app/Http/Controllers/IndicatorResponseController.php:206
 * @route '/files/{file}'
 */
deleteFile.delete = (args: { file: number | { id: number } } | [file: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteFile.url(args, options),
    method: 'delete',
})
const IndicatorResponseController = { update, uploadFile, addLink, history, deleteFile }

export default IndicatorResponseController