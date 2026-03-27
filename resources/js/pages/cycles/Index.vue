<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Plus, Calendar, CheckCircle2, Clock, Lock } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes/index.ts';
import type { BreadcrumbItem, Category } from '@/types';
import { ref } from 'vue';

interface Cycle {
    id: number;
    year: number;
    status: 'draft' | 'open' | 'closed' | 'submitted';
    data_period_start: string;
    data_period_end: string;
    submission_start?: string;
    submission_end?: string;
    completion_percentage: number;
}

interface Props {
    cycles: Cycle[];
    categories: Category[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
    {
        title: 'Циклы',
        href: '/cycles',
    },
];

function getStatusConfig(status: string) {
    const configs = {
        draft: {
            bg: 'bg-gray-100',
            text: 'text-gray-700',
            icon: Clock,
            label: 'Черновик',
        },
        open: {
            bg: 'bg-green-100',
            text: 'text-green-700',
            icon: CheckCircle2,
            label: 'Открыт',
        },
        closed: {
            bg: 'bg-red-100',
            text: 'text-red-700',
            icon: Lock,
            label: 'Закрыт',
        },
        submitted: {
            bg: 'bg-blue-100',
            text: 'text-blue-700',
            icon: CheckCircle2,
            label: 'Отправлен',
        },
    };
    return configs[status as keyof typeof configs] || configs.draft;
}

function getProgressColor(percentage: number) {
    if (percentage >= 80) return 'bg-green-500';
    if (percentage >= 50) return 'bg-blue-500';
    if (percentage >= 25) return 'bg-yellow-500';
    return 'bg-gray-400';
}
</script>

<template>
    <Head title="Циклы" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Заголовок -->
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        Циклы подачи данных
                    </h1>
                    <p class="mt-1 text-gray-600">
                        Управление циклами для рейтинга UI GreenMetric
                    </p>
                </div>
            </div>

            <!-- Список циклов -->
            <div class="space-y-4">
                <div
                    v-for="cycle in cycles"
                    :key="cycle.id"
                    class="group relative overflow-hidden rounded-xl border border-gray-200 bg-white p-6 shadow-sm hover:shadow-md hover:border-green-300 transition-all duration-200"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3">
                                <h3 class="text-2xl font-bold text-gray-900">
                                    {{ cycle.year }}
                                </h3>
                                <span
                                    :class="[
                                        'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium',
                                        getStatusConfig(cycle.status).bg,
                                        getStatusConfig(cycle.status).text,
                                    ]"
                                >
                                    <component
                                        :is="getStatusConfig(cycle.status).icon"
                                        class="h-3 w-3"
                                    />
                                    {{ getStatusConfig(cycle.status).label }}
                                </span>
                            </div>

                            <div class="mt-4 grid grid-cols-2 gap-4 md:grid-cols-4">
                                <div>
                                    <p class="text-xs font-medium text-gray-500">
                                        Период данных
                                    </p>
                                    <p class="mt-1 text-sm text-gray-900">
                                        {{ new Date(cycle.data_period_start).toLocaleDateString('ru-RU') }}
                                        –
                                        {{ new Date(cycle.data_period_end).toLocaleDateString('ru-RU') }}
                                    </p>
                                </div>
                                <div v-if="cycle.submission_start">
                                    <p class="text-xs font-medium text-gray-500">
                                        Приём данных
                                    </p>
                                    <p class="mt-1 text-sm text-gray-900">
                                        {{ new Date(cycle.submission_start).toLocaleDateString('ru-RU') }}
                                        –
                                        {{ new Date(cycle.submission_end!).toLocaleDateString('ru-RU') }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500">
                                        Заполнение
                                    </p>
                                    <p class="mt-1 text-sm font-semibold text-gray-900">
                                        {{ cycle.completion_percentage }}%
                                    </p>
                                </div>
                                <div class="flex items-center">
                                    <Link
                                        :href="`/cycles/${cycle.id}?category=WR`"
                                        class="inline-flex items-center gap-1.5 text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors"
                                    >
                                        Редактировать
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </Link>
                                </div>
                            </div>

                            <!-- Прогресс бар -->
                            <div class="mt-4">
                                <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                                    <span>Прогресс заполнения</span>
                                    <span>{{ cycle.completion_percentage }}%</span>
                                </div>
                                <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100">
                                    <div
                                        :class="[
                                            'h-full rounded-full transition-all duration-500',
                                            getProgressColor(cycle.completion_percentage),
                                        ]"
                                        :style="{ width: `${cycle.completion_percentage}%` }"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Пустое состояние -->
                <div
                    v-if="cycles.length === 0"
                    class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 py-16"
                >
                    <Calendar class="h-12 w-12 text-gray-400" />
                    <h3 class="mt-4 text-lg font-semibold text-gray-900">
                        Нет циклов
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Создайте первый цикл для начала работы
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
