export type RouteHttpMethod =
    | 'get'
    | 'head'
    | 'post'
    | 'put'
    | 'patch'
    | 'delete'
    | 'options'

export type RouteDefinition<Methods extends RouteHttpMethod | readonly RouteHttpMethod[] = RouteHttpMethod> = {
    url: string
    method: RouteHttpMethod
    definition?: {
        methods: readonly RouteHttpMethod[]
        url: string
    }
}

export type RouteQueryOptions = {
    query?: Record<string, unknown>
}

type FormRoute = {
    definition?: {
        methods: readonly RouteHttpMethod[]
    }
    url?: (options?: RouteQueryOptions) => string
}

declare global {
    interface Function {
        form?: (this: FormRoute) => { action: string; method: RouteHttpMethod }
    }
}

function encodeQueryValue(value: unknown): string {
    if (value === null || value === undefined) return ''
    if (value instanceof Date) return value.toISOString()
    if (typeof value === 'boolean') return value ? '1' : '0'
    return String(value)
}

export function queryParams(options?: RouteQueryOptions): string {
    const query = options?.query
    if (!query) return ''

    const parts: string[] = []
    for (const [key, value] of Object.entries(query)) {
        if (value === undefined) continue

        if (Array.isArray(value)) {
            for (const item of value) {
                parts.push(`${encodeURIComponent(key)}[]=${encodeURIComponent(encodeQueryValue(item))}`)
            }
            continue
        }

        parts.push(`${encodeURIComponent(key)}=${encodeURIComponent(encodeQueryValue(value))}`)
    }

    return parts.length ? `?${parts.join('&')}` : ''
}

export function applyUrlDefaults<T extends Record<string, unknown>>(args: T): T {
    return args
}

if (typeof Function.prototype.form !== 'function') {
    Object.defineProperty(Function.prototype, 'form', {
        value: function (this: FormRoute) {
            const routeUrl = typeof this.url === 'function' ? this.url() : ''
            const routeMethod = this.definition?.methods?.[0] ?? 'get'

            return {
                action: routeUrl,
                method: routeMethod,
            }
        },
        configurable: true,
        writable: true,
    })
}

