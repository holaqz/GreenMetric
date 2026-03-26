import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\IndicatorResponseController::upload
 * @see app/Http/Controllers/IndicatorResponseController.php:157
 * @route '/responses/{response}/files'
 */
export const upload = (args: { response: number | { id: number } } | [response: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: upload.url(args, options),
    method: 'post',
})

upload.definition = {
    methods: ["post"],
    url: '/responses/{response}/files',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\IndicatorResponseController::upload
 * @see app/Http/Controllers/IndicatorResponseController.php:157
 * @route '/responses/{response}/files'
 */
upload.url = (args: { response: number | { id: number } } | [response: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return upload.definition.url
            .replace('{response}', parsedArgs.response.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\IndicatorResponseController::upload
 * @see app/Http/Controllers/IndicatorResponseController.php:157
 * @route '/responses/{response}/files'
 */
upload.post = (args: { response: number | { id: number } } | [response: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: upload.url(args, options),
    method: 'post',
})
const files = {
    upload: Object.assign(upload, upload),
}

export default files