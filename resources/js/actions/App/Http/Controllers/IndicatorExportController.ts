import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\IndicatorExportController::exportIndicator
 * @see app/Http/Controllers/IndicatorExportController.php:20
 * @route '/cycles/{cycle}/indicators/{indicator}/export'
 */
export const exportIndicator = (args: { cycle: number | { id: number }, indicator: string | number } | [cycle: number | { id: number }, indicator: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportIndicator.url(args, options),
    method: 'get',
})

exportIndicator.definition = {
    methods: ["get","head"],
    url: '/cycles/{cycle}/indicators/{indicator}/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\IndicatorExportController::exportIndicator
 * @see app/Http/Controllers/IndicatorExportController.php:20
 * @route '/cycles/{cycle}/indicators/{indicator}/export'
 */
exportIndicator.url = (args: { cycle: number | { id: number }, indicator: string | number } | [cycle: number | { id: number }, indicator: string | number ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
                    cycle: args[0],
                    indicator: args[1],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        cycle: typeof args.cycle === 'object'
                ? args.cycle.id
                : args.cycle,
                                indicator: args.indicator,
                }

    return exportIndicator.definition.url
            .replace('{cycle}', parsedArgs.cycle.toString())
            .replace('{indicator}', parsedArgs.indicator.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\IndicatorExportController::exportIndicator
 * @see app/Http/Controllers/IndicatorExportController.php:20
 * @route '/cycles/{cycle}/indicators/{indicator}/export'
 */
exportIndicator.get = (args: { cycle: number | { id: number }, indicator: string | number } | [cycle: number | { id: number }, indicator: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportIndicator.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\IndicatorExportController::exportIndicator
 * @see app/Http/Controllers/IndicatorExportController.php:20
 * @route '/cycles/{cycle}/indicators/{indicator}/export'
 */
exportIndicator.head = (args: { cycle: number | { id: number }, indicator: string | number } | [cycle: number | { id: number }, indicator: string | number ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportIndicator.url(args, options),
    method: 'head',
})
const IndicatorExportController = { exportIndicator }

export default IndicatorExportController