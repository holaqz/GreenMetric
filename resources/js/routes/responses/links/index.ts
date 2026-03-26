import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\IndicatorResponseController::add
 * @see app/Http/Controllers/IndicatorResponseController.php:183
 * @route '/responses/{response}/links'
 */
export const add = (args: { response: number | { id: number } } | [response: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: add.url(args, options),
    method: 'post',
})

add.definition = {
    methods: ["post"],
    url: '/responses/{response}/links',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\IndicatorResponseController::add
 * @see app/Http/Controllers/IndicatorResponseController.php:183
 * @route '/responses/{response}/links'
 */
add.url = (args: { response: number | { id: number } } | [response: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return add.definition.url
            .replace('{response}', parsedArgs.response.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\IndicatorResponseController::add
 * @see app/Http/Controllers/IndicatorResponseController.php:183
 * @route '/responses/{response}/links'
 */
add.post = (args: { response: number | { id: number } } | [response: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: add.url(args, options),
    method: 'post',
})
const links = {
    add: Object.assign(add, add),
}

export default links