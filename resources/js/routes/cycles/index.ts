import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../wayfinder'
import exportMethod from './export'
/**
* @see \App\Http\Controllers\CycleController::index
 * @see app/Http/Controllers/CycleController.php:19
 * @route '/cycles'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/cycles',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\CycleController::index
 * @see app/Http/Controllers/CycleController.php:19
 * @route '/cycles'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\CycleController::index
 * @see app/Http/Controllers/CycleController.php:19
 * @route '/cycles'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\CycleController::index
 * @see app/Http/Controllers/CycleController.php:19
 * @route '/cycles'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\CycleController::store
 * @see app/Http/Controllers/CycleController.php:161
 * @route '/cycles'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/cycles',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\CycleController::store
 * @see app/Http/Controllers/CycleController.php:161
 * @route '/cycles'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\CycleController::store
 * @see app/Http/Controllers/CycleController.php:161
 * @route '/cycles'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CycleController::show
 * @see app/Http/Controllers/CycleController.php:61
 * @route '/cycles/{cycle}'
 */
export const show = (args: { cycle: number | { id: number } } | [cycle: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/cycles/{cycle}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\CycleController::show
 * @see app/Http/Controllers/CycleController.php:61
 * @route '/cycles/{cycle}'
 */
show.url = (args: { cycle: number | { id: number } } | [cycle: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { cycle: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { cycle: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    cycle: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        cycle: typeof args.cycle === 'object'
                ? args.cycle.id
                : args.cycle,
                }

    return show.definition.url
            .replace('{cycle}', parsedArgs.cycle.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\CycleController::show
 * @see app/Http/Controllers/CycleController.php:61
 * @route '/cycles/{cycle}'
 */
show.get = (args: { cycle: number | { id: number } } | [cycle: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\CycleController::show
 * @see app/Http/Controllers/CycleController.php:61
 * @route '/cycles/{cycle}'
 */
show.head = (args: { cycle: number | { id: number } } | [cycle: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\CycleController::updateStatus
 * @see app/Http/Controllers/CycleController.php:202
 * @route '/cycles/{cycle}/status'
 */
export const updateStatus = (args: { cycle: number | { id: number } } | [cycle: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateStatus.url(args, options),
    method: 'patch',
})

updateStatus.definition = {
    methods: ["patch"],
    url: '/cycles/{cycle}/status',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\CycleController::updateStatus
 * @see app/Http/Controllers/CycleController.php:202
 * @route '/cycles/{cycle}/status'
 */
updateStatus.url = (args: { cycle: number | { id: number } } | [cycle: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { cycle: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { cycle: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    cycle: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        cycle: typeof args.cycle === 'object'
                ? args.cycle.id
                : args.cycle,
                }

    return updateStatus.definition.url
            .replace('{cycle}', parsedArgs.cycle.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\CycleController::updateStatus
 * @see app/Http/Controllers/CycleController.php:202
 * @route '/cycles/{cycle}/status'
 */
updateStatus.patch = (args: { cycle: number | { id: number } } | [cycle: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateStatus.url(args, options),
    method: 'patch',
})
const cycles = {
    index: Object.assign(index, index),
store: Object.assign(store, store),
show: Object.assign(show, show),
updateStatus: Object.assign(updateStatus, updateStatus),
export: Object.assign(exportMethod, exportMethod),
}

export default cycles