import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\ReportController::exportWord
 * @see app/Http/Controllers/ReportController.php:21
 * @route '/cycles/{cycle}/export/{category}/word'
 */
export const exportWord = (args: { cycle: number | { id: number }, category: string | number } | [cycle: number | { id: number }, category: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportWord.url(args, options),
    method: 'get',
})

exportWord.definition = {
    methods: ["get","head"],
    url: '/cycles/{cycle}/export/{category}/word',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ReportController::exportWord
 * @see app/Http/Controllers/ReportController.php:21
 * @route '/cycles/{cycle}/export/{category}/word'
 */
exportWord.url = (args: { cycle: number | { id: number }, category: string | number } | [cycle: number | { id: number }, category: string | number ], options?: RouteQueryOptions) => {
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

    return exportWord.definition.url
            .replace('{cycle}', parsedArgs.cycle.toString())
            .replace('{category}', parsedArgs.category.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\ReportController::exportWord
 * @see app/Http/Controllers/ReportController.php:21
 * @route '/cycles/{cycle}/export/{category}/word'
 */
exportWord.get = (args: { cycle: number | { id: number }, category: string | number } | [cycle: number | { id: number }, category: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportWord.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\ReportController::exportWord
 * @see app/Http/Controllers/ReportController.php:21
 * @route '/cycles/{cycle}/export/{category}/word'
 */
exportWord.head = (args: { cycle: number | { id: number }, category: string | number } | [cycle: number | { id: number }, category: string | number ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportWord.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\ReportController::exportEvidenceZip
 * @see app/Http/Controllers/ReportController.php:52
 * @route '/cycles/{cycle}/export/{category}/evidence'
 */
export const exportEvidenceZip = (args: { cycle: number | { id: number }, category: string | number } | [cycle: number | { id: number }, category: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportEvidenceZip.url(args, options),
    method: 'get',
})

exportEvidenceZip.definition = {
    methods: ["get","head"],
    url: '/cycles/{cycle}/export/{category}/evidence',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ReportController::exportEvidenceZip
 * @see app/Http/Controllers/ReportController.php:52
 * @route '/cycles/{cycle}/export/{category}/evidence'
 */
exportEvidenceZip.url = (args: { cycle: number | { id: number }, category: string | number } | [cycle: number | { id: number }, category: string | number ], options?: RouteQueryOptions) => {
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

    return exportEvidenceZip.definition.url
            .replace('{cycle}', parsedArgs.cycle.toString())
            .replace('{category}', parsedArgs.category.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\ReportController::exportEvidenceZip
 * @see app/Http/Controllers/ReportController.php:52
 * @route '/cycles/{cycle}/export/{category}/evidence'
 */
exportEvidenceZip.get = (args: { cycle: number | { id: number }, category: string | number } | [cycle: number | { id: number }, category: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportEvidenceZip.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\ReportController::exportEvidenceZip
 * @see app/Http/Controllers/ReportController.php:52
 * @route '/cycles/{cycle}/export/{category}/evidence'
 */
exportEvidenceZip.head = (args: { cycle: number | { id: number }, category: string | number } | [cycle: number | { id: number }, category: string | number ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportEvidenceZip.url(args, options),
    method: 'head',
})
const ReportController = { exportWord, exportEvidenceZip }

export default ReportController