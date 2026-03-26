import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\IndicatorExportController::exportMethod
 * @see app/Http/Controllers/IndicatorExportController.php:20
 * @route '/cycles/{cycle}/indicators/{indicator}/export'
 */
export const exportMethod = (args: { cycle: number | { id: number }, indicator: string | number } | [cycle: number | { id: number }, indicator: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod.url(args, options),
    method: 'get',
})

exportMethod.definition = {
    methods: ["get","head"],
    url: '/cycles/{cycle}/indicators/{indicator}/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\IndicatorExportController::exportMethod
 * @see app/Http/Controllers/IndicatorExportController.php:20
 * @route '/cycles/{cycle}/indicators/{indicator}/export'
 */
exportMethod.url = (args: { cycle: number | { id: number }, indicator: string | number } | [cycle: number | { id: number }, indicator: string | number ], options?: RouteQueryOptions) => {
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

    return exportMethod.definition.url
            .replace('{cycle}', parsedArgs.cycle.toString())
            .replace('{indicator}', parsedArgs.indicator.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\IndicatorExportController::exportMethod
 * @see app/Http/Controllers/IndicatorExportController.php:20
 * @route '/cycles/{cycle}/indicators/{indicator}/export'
 */
exportMethod.get = (args: { cycle: number | { id: number }, indicator: string | number } | [cycle: number | { id: number }, indicator: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\IndicatorExportController::exportMethod
 * @see app/Http/Controllers/IndicatorExportController.php:20
 * @route '/cycles/{cycle}/indicators/{indicator}/export'
 */
exportMethod.head = (args: { cycle: number | { id: number }, indicator: string | number } | [cycle: number | { id: number }, indicator: string | number ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportMethod.url(args, options),
    method: 'head',
})
const indicators = {
    export: Object.assign(exportMethod, exportMethod),
}

export default indicators