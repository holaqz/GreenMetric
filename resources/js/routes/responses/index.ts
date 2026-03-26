import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../wayfinder'
import files from './files'
import links from './links'
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
const responses = {
    update: Object.assign(update, update),
files: Object.assign(files, files),
links: Object.assign(links, links),
history: Object.assign(history, history),
}

export default responses