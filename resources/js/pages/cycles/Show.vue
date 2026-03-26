<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    Download,
    FileText,
    CheckCircle2,
    Clock,
    AlertCircle,
    ChevronRight,
    Upload,
    Link2,
    Trash2,
    Save,
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes/index.ts';
import type { BreadcrumbItem, Category } from '@/types';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const authUser = computed(() => page.props.auth.user);

// Проверка, является ли пользователь администратором
const isAdmin = computed(() => authUser.value?.is_admin === true);

// Проверка, может ли пользователь редактировать категорию
const canEditCategory = computed(() => {
    if (isAdmin.value) return true;
    // Здесь можно проверить доступ к категории через props
    return true; // Пока возвращаем true для всех
});

// Проверка, может ли пользователь редактировать индикатор
function canEditIndicator(status: string, canEditCategory: boolean = true): boolean {
    if (isAdmin.value) return true;
    // Не админ не может редактировать approved и ready_for_review
    if (!canEditCategory) return false;
    return !['approved', 'ready_for_review'].includes(status);
}

// Получение сообщения о статусе для отображения
function getStatusMessage(status: string): string {
    if (status === 'approved') return 'Утверждено';
    if (status === 'ready_for_review') return 'На проверке';
    return '';
}

// Проверка, есть ли данные для экспорта индикатора
function hasExportData(indicator: Indicator): boolean {
    const response = indicator.response;
    if (!response) return false;
    
    // Есть ли значение (включая вычисленное)
    const hasValue = response.value_numeric !== null || 
                     response.value_text !== null || 
                     response.selected_option !== null ||
                     response.program_description !== null ||
                     (indicator.is_computed && indicator.computed_value !== null && indicator.computed_value !== undefined);
    
    // Есть ли файлы
    const hasFiles = response.files && response.files.length > 0;
    
    return hasValue || hasFiles;
}

// Проверка, есть ли файлы для скачивания
function hasDownloadableFiles(indicator: Indicator): boolean {
    const response = indicator.response;
    if (!response || !response.files) return false;
    
    return response.files.some(file => !file.is_link);
}

// Проверка, есть ли файлы для скачивания во всех индикаторах категории
function hasAnyDownloadableFiles(): boolean {
    return props.indicators.some(indicator => hasDownloadableFiles(indicator));
}

interface File {
    id: number;
    file_name_original: string;
    file_type: string;
    file_size_bytes: number;
    description?: string;
    is_link: boolean;
    external_url?: string;
    download_url: string;
}

interface Response {
    id: number;
    selected_option?: number;
    selected_option_text?: string;
    value_numeric?: number;
    value_text?: string;
    value_boolean?: boolean;
    formatted_value?: string;
    program_description?: string;
    status: string;
    files: File[];
}

interface Indicator {
    id: number;
    category_id: number;
    category_code: string;
    category_name: string;
    code_in_category: number;
    code_full: string;
    question_text: string;
    unit: string;
    input_type: string;
    filename_slug: string;
    description_help: string;
    options: string[];
    is_computed: boolean;
    formula?: string;
    depends_on?: string[];
    computed_value?: number | null;
    response?: Response;
}

interface Cycle {
    id: number;
    year: number;
    status: string;
    data_period_start: string;
    data_period_end: string;
}

interface Profile {
    total_area_hectares?: number;
    green_area_percent?: number;
    total_students?: number;
    total_staff?: number;
}

interface Props {
    cycle: Cycle;
    profile?: Profile;
    category_code: string;
    indicators: Indicator[];
    categories: Category[];
    category_completion: Record<string, number>;
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
    {
        title: `${props.cycle.year}`,
        href: `/cycles/${props.cycle.id}`,
    },
];

const selectedCategory = ref(props.category_code);
const selectedIndicator = ref<number | null>(null);
const showFileModal = ref(false);
const showLinkModal = ref(false);
const showDescriptionModal = ref(false);
const currentIndicatorId = ref<number | null>(null);
const fileForm = useForm({ file: null as File | null, description: '' });
const fileError = ref('');
const linkForm = useForm({ url: '', description: '' });
const linkError = ref('');
const descriptionForm = useForm({ program_description: '' });

const currentIndicator = computed(() => {
    return props.indicators.find((i) => i.id === currentIndicatorId.value);
});

function getCategoryColor(code: string) {
    const colors: Record<string, string> = {
        SI: 'bg-purple-100 text-purple-700 border-purple-200',
        EC: 'bg-orange-100 text-orange-700 border-orange-200',
        WS: 'bg-yellow-100 text-yellow-700 border-yellow-200',
        WR: 'bg-blue-100 text-blue-700 border-blue-200',
        TR: 'bg-indigo-100 text-indigo-700 border-indigo-200',
        ED: 'bg-pink-100 text-pink-700 border-pink-200',
        GD: 'bg-teal-100 text-teal-700 border-teal-200',
    };
    return colors[code] || 'bg-gray-100 text-gray-700 border-gray-200';
}

function getStatusConfig(status: string) {
    const configs: Record<string, { bg: string; text: string; label: string }> = {
        in_progress: {
            bg: 'bg-yellow-100',
            text: 'text-yellow-700',
            label: 'В процессе',
        },
        ready_for_review: {
            bg: 'bg-blue-100',
            text: 'text-blue-700',
            label: 'На проверке',
        },
        approved: {
            bg: 'bg-green-100',
            text: 'text-green-700',
            label: 'Утверждено',
        },
    };
    return configs[status] || configs.in_progress;
}

function updateResponse(indicator: Indicator, data: Record<string, any>) {
    const form = useForm(data);
    form.patch(`/responses/${indicator.response?.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            // Данные обновлены
        },
    });
}

function updateStatus(indicator: Indicator, status: string) {
    const form = useForm({ status });
    form.patch(`/responses/${indicator.response?.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            // Статус обновлён
        },
    });
}

function openFileModal(indicatorId: number) {
    currentIndicatorId.value = indicatorId;
    fileForm.file = null;
    fileForm.description = '';
    showFileModal.value = true;
}

function openDescriptionModal(indicator: Indicator) {
    currentIndicatorId.value = indicator.id;
    descriptionForm.program_description = indicator.response?.program_description || '';
    showDescriptionModal.value = true;
}

function openLinkModal(indicatorId: number) {
    currentIndicatorId.value = indicatorId;
    linkForm.url = '';
    linkForm.description = '';
    showLinkModal.value = true;
}

function submitFile() {
    if (!currentIndicatorId.value) return;

    const indicator = props.indicators.find((i) => i.id === currentIndicatorId.value);
    if (!indicator?.response?.id) return;

    fileError.value = ''; // Очищаем предыдущую ошибку

    const form = useForm({
        file: fileForm.file,
        description: fileForm.description,
    });

    form.post(`/responses/${indicator.response.id}/files`, {
        onSuccess: () => {
            showFileModal.value = false;
            router.reload({ preserveScroll: true });
        },
        onError: (error) => {
            // Ошибка с бэкенда (например, превышен лимит)
            fileError.value = Object.values(error)[0] || 'Ошибка при загрузке файла';
        },
    });
}

function submitLink() {
    if (!currentIndicatorId.value) return;
    const indicator = props.indicators.find((i) => i.id === currentIndicatorId.value);
    if (!indicator?.response?.id) return;

    linkError.value = ''; // Очищаем предыдущую ошибку

    linkForm.post(`/responses/${indicator.response.id}/links`, {
        onSuccess: () => {
            showLinkModal.value = false;
            router.reload({ preserveScroll: true });
        },
        onError: (error) => {
            // Ошибка с бэкенда (например, превышен лимит)
            linkError.value = Object.values(error)[0] || 'Ошибка при добавлении ссылки';
        },
    });
}

function submitDescription() {
    if (!currentIndicatorId.value) return;
    
    const indicator = props.indicators.find((i) => i.id === currentIndicatorId.value);
    if (!indicator?.response?.id) return;

    descriptionForm.patch(`/responses/${indicator.response.id}`, {
        onSuccess: () => {
            showDescriptionModal.value = false;
        },
    });
}

function deleteFile(fileId: number) {
    if (!confirm('Вы уверены, что хотите удалить этот файл?')) return;

    const form = useForm({});
    form.delete(`/files/${fileId}`, {
        onSuccess: () => {
            router.reload({ preserveScroll: true });
        },
    });
}

function exportWord() {
    window.open(`/cycles/${props.cycle.id}/export/${selectedCategory.value}/word`, '_blank');
}

function exportEvidence() {
    window.open(`/cycles/${props.cycle.id}/export/${selectedCategory.value}/evidence`, '_blank');
}

function changeCategory(categoryCode: string) {
    window.location.href = `/cycles/${props.cycle.id}?category=${categoryCode}`;
}
</script>

<template>
    <Head :title="`Цикл ${cycle.year} - ${categories.find(c => c.code === category_code)?.name || ''}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex min-h-screen flex-col">
            <!-- Верхняя панель с категориями -->
            <div class="border-b border-gray-200 bg-white">
                <div class="px-6">
                    <!-- Информация о цикле -->
                    <div class="flex items-center justify-between py-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">
                                Цикл {{ cycle.year }}
                            </h1>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ new Date(cycle.data_period_start).toLocaleDateString('ru-RU') }} – {{ new Date(cycle.data_period_end).toLocaleDateString('ru-RU') }}
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <button
                                @click="exportWord"
                                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors"
                            >
                                <FileText class="h-4 w-4" />
                                Экспорт всех отчетов (ZIP)
                            </button>
                            <button
                                @click="exportEvidence"
                                :disabled="!hasAnyDownloadableFiles()"
                                :class="[
                                    'inline-flex items-center gap-2 rounded-lg border px-4 py-2 text-sm font-semibold transition-colors',
                                    hasAnyDownloadableFiles()
                                        ? 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'
                                        : 'border-gray-200 bg-gray-100 text-gray-400 cursor-not-allowed',
                                ]"
                                :title="hasAnyDownloadableFiles() ? 'Скачать файлы' : 'Нет файлов для скачивания'"
                            >
                                <Download class="h-4 w-4" />
                                Скачать файлы
                            </button>
                        </div>
                    </div>

                    <!-- Табы категорий -->
                    <div class="flex gap-2 overflow-x-auto pb-2">
                        <button
                            v-for="category in categories"
                            :key="category.code"
                            @click="changeCategory(category.code)"
                            :class="[
                                'flex items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-medium whitespace-nowrap transition-colors',
                                selectedCategory === category.code
                                    ? getCategoryColor(category.code)
                                    : 'border-gray-200 text-gray-600 hover:bg-gray-50',
                            ]"
                        >
                            <span>{{ category.code }}</span>
                            <span class="text-xs opacity-75">{{ category.name }}</span>
                            <span
                                :class="[
                                    'ml-1 rounded-full px-2 py-0.5 text-xs',
                                    selectedCategory === category.code
                                        ? 'bg-white/50'
                                        : 'bg-gray-100',
                                ]"
                            >
                                {{ category_completion[category.code] || 0 }}%
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Основной контент -->
            <div class="flex-1 bg-gray-50 p-6">
                <div class="mx-auto max-w-5xl space-y-4">
                    <!-- Индикаторы -->
                    <div
                        v-for="indicator in indicators"
                        :key="indicator.id"
                        :class="[
                            'rounded-xl border bg-white p-6 shadow-sm transition-all hover:shadow-md',
                            indicator.response?.status === 'approved'
                                ? 'border-green-200'
                                : 'border-gray-200',
                        ]"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <span
                                        :class="[
                                            'inline-flex h-8 w-8 items-center justify-center rounded-full text-sm font-bold',
                                            getCategoryColor(indicator.category_code),
                                        ]"
                                    >
                                        {{ indicator.code_in_category }}
                                    </span>
                                    <span
                                        v-if="indicator.is_computed"
                                        class="rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-700"
                                    >
                                        Вычисляемый
                                    </span>
                                    <span
                                        v-if="indicator.response"
                                        :class="[
                                            'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium',
                                            getStatusConfig(indicator.response.status).bg,
                                            getStatusConfig(indicator.response.status).text,
                                        ]"
                                    >
                                        <CheckCircle2 v-if="indicator.response.status === 'approved'" class="h-3 w-3" />
                                        <Clock v-else-if="indicator.response.status === 'ready_for_review'" class="h-3 w-3" />
                                        <AlertCircle v-else class="h-3 w-3" />
                                        {{ getStatusConfig(indicator.response.status).label }}
                                    </span>
                                    
                                    <!-- Бейдж "Только просмотр" для не админов -->
                                    <span
                                        v-if="!canEditIndicator(indicator.response?.status || '', indicator.can_edit_category)"
                                        class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600"
                                        title="Редактирование доступно только администратору или пока статус не утверждён"
                                    >
                                        <AlertCircle class="mr-1 inline h-3 w-3" />
                                        Только просмотр
                                    </span>
                                    
                                    <!-- Кнопка экспорта индикатора -->
                                    <a
                                        :href="`/cycles/${cycle.id}/indicators/${indicator.id}/export`"
                                        target="_blank"
                                        :class="[
                                            'ml-auto inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-semibold transition-colors',
                                            hasExportData(indicator)
                                                ? 'border-green-600 bg-green-600 text-white hover:bg-green-700'
                                                : 'border-gray-300 bg-gray-200 text-gray-400 cursor-not-allowed pointer-events-none',
                                        ]"
                                        :title="hasExportData(indicator) ? 'Экспорт индикатора в Word' : 'Нет данных для экспорта'"
                                    >
                                        <FileText class="h-3.5 w-3.5" />
                                        Экспорт
                                    </a>
                                    
                                    <!-- Выпадающий список для смены статуса (только админ) -->
                                    <select
                                        v-if="isAdmin"
                                        :value="indicator.response?.status"
                                        @change="(e) => updateStatus(indicator, (e.target as HTMLSelectElement).value)"
                                        class="ml-2 rounded-lg border border-gray-300 bg-white px-2 py-1 text-xs font-medium text-gray-900 shadow-sm focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-500"
                                    >
                                        <option value="in_progress">В процессе</option>
                                        <option value="ready_for_review">На проверке</option>
                                        <option value="approved">Утверждено</option>
                                    </select>
                                </div>

                                <h3 class="mt-3 text-lg font-semibold text-gray-900">
                                    {{ indicator.question_text }}
                                </h3>

                                <p class="mt-1 text-sm text-gray-600">
                                    {{ indicator.description_help }}
                                </p>

                                <!-- Поле ввода -->
                                <div class="mt-4">
                                    <!-- Числовое поле -->
                                    <div v-if="indicator.input_type === 'number'">
                                        <div class="flex items-center gap-2">
                                            <input
                                                v-if="!indicator.is_computed"
                                                :value="indicator.response?.value_numeric"
                                                @change="(e) => updateResponse(indicator, { value_numeric: (e.target as HTMLInputElement).value })"
                                                :disabled="!canEditIndicator(indicator.response?.status || '', indicator.can_edit_category)"
                                                type="number"
                                                step="0.01"
                                                :placeholder="`Введите значение (${indicator.unit})`"
                                                :class="[
                                                    'block w-full max-w-md rounded-lg border px-3 py-2 text-gray-900 shadow-sm focus:outline-none focus:ring-2',
                                                    canEditIndicator(indicator.response?.status || '', indicator.can_edit_category)
                                                        ? 'border-gray-300 focus:border-green-500 focus:ring-green-500'
                                                        : 'border-gray-200 bg-gray-100 text-gray-500 cursor-not-allowed',
                                                ]"
                                            />
                                            <div
                                                v-else
                                                class="block w-full max-w-md rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-blue-900"
                                            >
                                                <template v-if="indicator.computed_value !== null && indicator.computed_value !== undefined">
                                                    {{ indicator.computed_value.toFixed(2) }}
                                                </template>
                                                <template v-else>
                                                    Недостаточно данных для вычисления
                                                </template>
                                            </div>
                                            <span class="text-sm text-gray-500">{{ indicator.unit }}</span>
                                        </div>
                                        
                                        <!-- Информация о вычисляемом индикаторе -->
                                        <div v-if="indicator.is_computed" class="mt-3 rounded-lg bg-blue-50 p-3 text-sm">
                                            <div class="flex items-center gap-2 text-blue-700">
                                                <AlertCircle class="h-4 w-4" />
                                                <span class="font-semibold">Вычисляемый индикатор</span>
                                            </div>
                                            <div class="mt-2 space-y-2">
                                                <p class="text-blue-800">
                                                    <span class="font-medium">Формула:</span> 
                                                    <code class="rounded bg-white px-2 py-0.5">{{ indicator.formula }}</code>
                                                </p>
                                                <p class="text-blue-800">
                                                    <span class="font-medium">Зависит от:</span> 
                                                    <span class="font-mono">{{ indicator.depends_on?.join(', ') }}</span>
                                                </p>
                                                <p v-if="indicator.computed_value === null || indicator.computed_value === undefined" class="text-blue-600">
                                                    <span class="font-medium">Требуется заполнить:</span>
                                                    <span class="ml-1">Для вычисления необходимо заполнить все зависимые индикаторы</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Select -->
                                    <div v-else-if="indicator.input_type === 'select'">
                                        <select
                                            :value="indicator.response?.selected_option"
                                            :disabled="!canEditIndicator(indicator.response?.status || '', indicator.can_edit_category)"
                                            @change="(e) => updateResponse(indicator, { selected_option: (e.target as HTMLSelectElement).value })"
                                            :class="[
                                                'block w-full max-w-md rounded-lg border px-3 py-2 text-gray-900 shadow-sm focus:outline-none focus:ring-2',
                                                canEditIndicator(indicator.response?.status || '', indicator.can_edit_category)
                                                    ? 'border-gray-300 focus:border-green-500 focus:ring-green-500'
                                                    : 'border-gray-200 bg-gray-100 text-gray-500 cursor-not-allowed',
                                            ]"
                                        >
                                            <option value="">Выберите вариант...</option>
                                            <option
                                                v-for="(option, idx) in indicator.options"
                                                :key="idx"
                                                :value="idx + 1"
                                            >
                                                {{ option }}
                                            </option>
                                        </select>
                                    </div>

                                    <!-- Text -->
                                    <div v-else-if="indicator.input_type === 'text'">
                                        <input
                                            :value="indicator.response?.value_text"
                                            :disabled="!canEditIndicator(indicator.response?.status || '', indicator.can_edit_category)"
                                            @change="(e) => updateResponse(indicator, { value_text: (e.target as HTMLInputElement).value })"
                                            type="text"
                                            :placeholder="indicator.unit"
                                            :class="[
                                                'block w-full max-w-md rounded-lg border px-3 py-2 text-gray-900 shadow-sm focus:outline-none focus:ring-2',
                                                canEditIndicator(indicator.response?.status || '', indicator.can_edit_category)
                                                    ? 'border-gray-300 focus:border-green-500 focus:ring-green-500'
                                                    : 'border-gray-200 bg-gray-100 text-gray-500 cursor-not-allowed',
                                            ]"
                                        />
                                    </div>
                                </div>

                                <!-- Файлы -->
                                <div class="mt-4">
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-sm font-medium text-gray-700">Доказательства (фото и ссылки):</h4>
                                        <button
                                            v-if="canEditIndicator(indicator.response?.status || '', indicator.can_edit_category)"
                                            @click="openFileModal(indicator.id)"
                                            class="inline-flex items-center gap-1 text-xs font-medium text-green-600 hover:text-green-800"
                                        >
                                            <Upload class="h-3 w-3" />
                                            Загрузить фото
                                        </button>
                                        <button
                                            v-if="canEditIndicator(indicator.response?.status || '', indicator.can_edit_category)"
                                            @click="openLinkModal(indicator.id)"
                                            class="inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-800"
                                        >
                                            <Link2 class="h-3 w-3" />
                                            Добавить ссылку
                                        </button>
                                    </div>

                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <div
                                            v-for="file in indicator.response?.files || []"
                                            :key="file.id"
                                            class="group flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-sm hover:bg-gray-100"
                                        >
                                            <FileText class="h-4 w-4 text-gray-400" />
                                            <a
                                                :href="file.download_url"
                                                :title="file.file_name_original"
                                                class="max-w-[200px] truncate text-gray-700 hover:text-green-600"
                                                target="_blank"
                                            >
                                                {{ file.file_name_original }}
                                            </a>
                                            <button
                                                v-if="canEditIndicator(indicator.response?.status || '', indicator.can_edit_category)"
                                                @click="deleteFile(file.id)"
                                                class="ml-1 text-gray-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition-opacity"
                                            >
                                                <Trash2 class="h-3 w-3" />
                                            </button>
                                        </div>
                                        <span
                                            v-if="!indicator.response?.files?.length"
                                            class="text-xs text-gray-400 italic"
                                        >
                                            Нет файлов
                                        </span>
                                    </div>
                                </div>

                                <!-- Описание программы -->
                                <div class="mt-4">
                                    <button
                                        v-if="canEditIndicator(indicator.response?.status || '', indicator.can_edit_category)"
                                        @click="openDescriptionModal(indicator)"
                                        class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-blue-600"
                                    >
                                        <FileText class="h-4 w-4" />
                                        {{ indicator.response?.program_description ? 'Редактировать описание' : 'Добавить описание программы' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>

    <!-- Модальное окно загрузки файла -->
    <div
        v-if="showFileModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        @click.self="showFileModal = false"
    >
        <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
            <h2 class="text-xl font-bold text-gray-900">
                Загрузить фото
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                Прикрепите фото-доказательство для индикатора
            </p>
            
            <!-- Ошибка -->
            <div
                v-if="fileError"
                class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800"
            >
                {{ fileError }}
            </div>

            <form @submit.prevent="submitFile" class="mt-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Файл
                    </label>
                    <input
                        type="file"
                        @input="(e) => fileForm.file = (e.target as HTMLInputElement).files?.[0] || null"
                        accept=".png,.jpg,.jpeg,image/png,image/jpeg"
                        class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-500"
                    />
                    <p class="mt-1 text-xs text-gray-500">
                        Только изображения: PNG, JPG, JPEG (макс. 10MB)
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Описание (опционально)
                    </label>
                    <input
                        v-model="fileForm.description"
                        type="text"
                        placeholder="Краткое описание файла"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-500"
                    />
                </div>

                <div class="mt-6 flex gap-3">
                    <button
                        type="button"
                        @click="showFileModal = false"
                        class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors"
                    >
                        Отмена
                    </button>
                    <button
                        type="submit"
                        class="flex-1 rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700 transition-colors"
                    >
                        Загрузить
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Модальное окно добавления ссылки -->
    <div
        v-if="showLinkModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        @click.self="showLinkModal = false"
    >
        <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
            <h2 class="text-xl font-bold text-gray-900">
                Добавить ссылку
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                Укажите URL на внешний ресурс с доказательствами
            </p>
            
            <!-- Ошибка -->
            <div
                v-if="linkError"
                class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800"
            >
                {{ linkError }}
            </div>

            <form @submit.prevent="submitLink" class="mt-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        URL
                    </label>
                    <input
                        v-model="linkForm.url"
                        type="url"
                        placeholder="https://example.com/evidence"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Описание (опционально)
                    </label>
                    <input
                        v-model="linkForm.description"
                        type="text"
                        placeholder="Краткое описание ссылки"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                </div>

                <div class="mt-6 flex gap-3">
                    <button
                        type="button"
                        @click="showLinkModal = false"
                        class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors"
                    >
                        Отмена
                    </button>
                    <button
                        type="submit"
                        class="flex-1 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition-colors"
                    >
                        Добавить
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Модальное окно описания программы -->
    <div
        v-if="showDescriptionModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        @click.self="showDescriptionModal = false"
    >
        <div class="w-full max-w-2xl rounded-xl bg-white p-6 shadow-xl">
            <h2 class="text-xl font-bold text-gray-900">
                Описание программы
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                {{ currentIndicator?.question_text }}
            </p>

            <form @submit.prevent="submitDescription" class="mt-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Описание программы / инициативы
                    </label>
                    <textarea
                        v-model="descriptionForm.program_description"
                        :disabled="!canEditIndicator(currentIndicator?.response?.status || '', currentIndicator?.can_edit_category)"
                        rows="6"
                        placeholder="Опишите программу, инициативу или меры, принимаемые университетом..."
                        :class="[
                            'block w-full rounded-lg border px-3 py-2 text-gray-900 shadow-sm focus:outline-none focus:ring-2',
                            canEditIndicator(currentIndicator?.response?.status || '', currentIndicator?.can_edit_category)
                                ? 'border-gray-300 focus:border-green-500 focus:ring-green-500'
                                : 'border-gray-200 bg-gray-100 text-gray-500 cursor-not-allowed',
                        ]"
                    />
                    <p class="mt-1 text-xs text-gray-500">
                        Это описание будет включено в отчёт для UI GreenMetric
                    </p>
                </div>

                <div class="mt-6 flex gap-3">
                    <button
                        type="button"
                        @click="showDescriptionModal = false"
                        class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors"
                    >
                        Отмена
                    </button>
                    <button
                        type="submit"
                        class="flex-1 rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700 transition-colors"
                    >
                        <Save class="inline h-4 w-4" />
                        Сохранить
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
