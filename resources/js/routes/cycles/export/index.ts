import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\ReportController::word
 * @see app/Http/Controllers/ReportController.php:21
 * @route '/cycles/{cycle}/export/{category}/word'
 */
export const word = (args: { cycle: number | { id: number }, category: string | number } | [cycle: number | { id: number }, category: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: word.url(args, options),
    method: 'get',
})

word.definition = {
    methods: ["get","head"],
    url: '/cycles/{cycle}/export/{category}/word',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ReportController::word
 * @see app/Http/Controllers/ReportController.php:21
 * @route '/cycles/{cycle}/export/{category}/word'
 */
word.url = (args: { cycle: number | { id: number }, category: string | number } | [cycle: number | { id: number }, category: string | number ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
                    cycle: args[0],
                    category: args[1],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        cycle: typeof args.cycle === 'object'
                ? args.cycle.id
                : args.cycle,
                                category: args.category,
                }

    return word.definition.url
            .replace('{cycle}', parsedArgs.cycle.toString())
            .replace('{category}', parsedArgs.category.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\ReportController::word
 * @see app/Http/Controllers/ReportController.php:21
 * @route '/cycles/{cycle}/export/{category}/word'
 */
word.get = (args: { cycle: number | { id: number }, category: string | number } | [cycle: number | { id: number }, category: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: word.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\ReportController::word
 * @see app/Http/Controllers/ReportController.php:21
 * @route '/cycles/{cycle}/export/{category}/word'
 */
word.head = (args: { cycle: number | { id: number }, category: string | number } | [cycle: number | { id: number }, category: string | number ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: word.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\ReportController::evidence
 * @see app/Http/Controllers/ReportController.php:52
 * @route '/cycles/{cycle}/export/{category}/evidence'
 */
export const evidence = (args: { cycle: number | { id: number }, category: string | number } | [cycle: number | { id: number }, category: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: evidence.url(args, options),
    method: 'get',
})

evidence.definition = {
    methods: ["get","head"],
    url: '/cycles/{cycle}/export/{category}/evidence',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ReportController::evidence
 * @see app/Http/Controllers/ReportController.php:52
 * @route '/cycles/{cycle}/export/{category}/evidence'
 */
evidence.url = (args: { cycle: number | { id: number }, category: string | number } | [cycle: number | { id: number }, category: string | number ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
                    cycle: args[0],
                    category: args[1],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        cycle: typeof args.cycle === 'object'
                ? args.cycle.id
                : args.cycle,
                                category: args.category,
                }

    return evidence.definition.url
            .replace('{cycle}', parsedArgs.cycle.toString())
            .replace('{category}', parsedArgs.category.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\ReportController::evidence
 * @see app/Http/Controllers/ReportController.php:52
 * @route '/cycles/{cycle}/export/{category}/evidence'
 */
evidence.get = (args: { cycle: number | { id: number }, category: string | number } | [cycle: number | { id: number }, category: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: evidence.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\ReportController::evidence
 * @see app/Http/Controllers/ReportController.php:52
 * @route '/cycles/{cycle}/export/{category}/evidence'
 */
evidence.head = (args: { cycle: number | { id: number }, category: string | number } | [cycle: number | { id: number }, category: string | number ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: evidence.url(args, options),
    method: 'head',
})
const exportMethod = {
    word: Object.assign(word, word),
evidence: Object.assign(evidence, evidence),
}

export default exportMethod