<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, FileText, CheckCircle2 } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes/index';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex min-h-screen items-center justify-center p-6">
            <div class="w-full max-w-2xl text-center">
                <!-- Иконка -->
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-green-100">
                    <FileText class="h-10 w-10 text-green-600" />
                </div>

                <!-- Заголовок -->
                <h1 class="mt-6 text-3xl font-bold text-gray-900">
                    Добро пожаловать в систему UI GreenMetric
                </h1>
                <p class="mt-3 text-lg text-gray-600">
                    Управление данными для рейтинга устойчивого развития университета
                </p>

                <!-- Карточки действий -->
                <div class="mt-10 grid gap-4 md:grid-cols-2">
                    <Link
                        :href="route('cycles.index')"
                        class="group flex flex-col items-center rounded-xl border-2 border-gray-200 bg-white p-8 shadow-sm transition-all hover:border-green-500 hover:shadow-md"
                    >
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-green-100 transition-colors group-hover:bg-green-500">
                            <FileText class="h-7 w-7 text-green-600 transition-colors group-hover:text-white" />
                        </div>
                        <h3 class="mt-4 text-lg font-semibold text-gray-900">
                            Циклы подачи данных
                        </h3>
                        <p class="mt-2 text-sm text-gray-600">
                            Управление циклами и заполнение индикаторов
                        </p>
                        <div class="mt-4 flex items-center gap-2 text-sm font-medium text-green-600 group-hover:text-green-700">
                            Перейти
                            <ArrowRight class="h-4 w-4 transition-transform group-hover:translate-x-1" />
                        </div>
                    </Link>

                    <div class="flex flex-col items-center rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 p-8">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gray-200">
                            <CheckCircle2 class="h-7 w-7 text-gray-400" />
                        </div>
                        <h3 class="mt-4 text-lg font-semibold text-gray-700">
                            Скоро
                        </h3>
                        <p class="mt-2 text-sm text-gray-500">
                            Статистика и отчёты
                        </p>
                    </div>
                </div>

                <!-- Подсказка -->
                <div class="mt-10 rounded-lg bg-blue-50 border border-blue-200 p-4">
                    <p class="text-sm text-blue-800">
                        <strong>Совет:</strong> Начните с создания нового цикла подачи данных для текущего года.
                        Система автоматически создаст все необходимые индикаторы для 7 категорий UI GreenMetric.
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
