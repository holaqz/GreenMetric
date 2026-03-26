<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Plus, Calendar, CheckCircle2, Clock, Lock } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
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

const showCreateModal = ref(false);
const form = useForm({
    year: new Date().getFullYear(),
    data_period_start: '',
    data_period_end: '',
    submission_start: '',
    submission_end: '',
});

function openCreateModal() {
    const year = new Date().getFullYear();
    form.year = year;
    form.data_period_start = `${year - 1}-01-01`;
    form.data_period_end = `${year - 1}-12-31`;
    form.submission_start = `${year}-02-26`;
    form.submission_end = `${year}-06-30`;
    showCreateModal.value = true;
}

function submitCreate() {
    form.post('/cycles', {
        onSuccess: () => {
            showCreateModal.value = false;
        },
    });
}

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
                <button
                    @click="openCreateModal"
                    class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors"
                >
                    <Plus class="h-4 w-4" />
                    Создать цикл
                </button>
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
                    <button
                        @click="openCreateModal"
                        class="mt-4 inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700 transition-colors"
                    >
                        <Plus class="h-4 w-4" />
                        Создать цикл
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>

    <!-- Модальное окно создания цикла -->
    <div
        v-if="showCreateModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        @click.self="showCreateModal = false"
    >
        <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
            <h2 class="text-xl font-bold text-gray-900">
                Новый цикл
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                Заполните данные для нового цикла подачи
            </p>

            <form @submit.prevent="submitCreate" class="mt-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Год рейтинга
                    </label>
                    <input
                        v-model="form.year"
                        type="number"
                        class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-500"
                    />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Начало периода данных
                        </label>
                        <input
                            v-model="form.data_period_start"
                            type="date"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-500"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Конец периода данных
                        </label>
                        <input
                            v-model="form.data_period_end"
                            type="date"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-500"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Начало приёма
                        </label>
                        <input
                            v-model="form.submission_start"
                            type="date"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-500"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Дедлайн
                        </label>
                        <input
                            v-model="form.submission_end"
                            type="date"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-500"
                        />
                    </div>
                </div>

                <div class="mt-6 flex gap-3">
                    <button
                        type="button"
                        @click="showCreateModal = false"
                        class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors"
                    >
                        Отмена
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex-1 rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50 transition-colors"
                    >
                        {{ form.processing ? 'Создание...' : 'Создать' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
