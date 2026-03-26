<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes/index';
import type { BreadcrumbItem } from '@/types';

interface User {
    id: number;
    name: string;
    email: string;
    is_admin: boolean;
    accessible_categories: number[];
}

interface Category {
    id: number;
    code: string;
    name: string;
}

const props = defineProps<{
    users: User[];
    categories: Category[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
    {
        title: 'Управление доступом',
        href: '/settings/user-access',
    },
];

const forms = ref<Record<number, any>>({});

// Инициализация форм для каждого пользователя
props.users.forEach(user => {
    forms.value[user.id] = useForm({
        is_admin: user.is_admin,
        accessible_categories: [...user.accessible_categories],
    });
    
    // Следим за изменением is_admin
    watch(
        () => forms.value[user.id].is_admin,
        (isAdmin) => {
            if (isAdmin) {
                // Если админ - очищаем категории
                forms.value[user.id].accessible_categories = [];
            } else {
                // Если не админ - восстанавливаем из props
                forms.value[user.id].accessible_categories = [...user.accessible_categories];
            }
        }
    );
});

function saveAccess(user: User) {
    const form = forms.value[user.id];
    form.post(`/settings/user-access/${user.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            // Обновляем исходные данные
            user.is_admin = form.is_admin;
            user.accessible_categories = [...form.accessible_categories];
        },
    });
}
</script>

<template>
    <Head title="Управление доступом" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Управление доступом пользователей</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Назначьте пользователям роли и доступ к категориям
                </p>
            </div>

            <div class="space-y-6">
                <div
                    v-for="user in users"
                    :key="user.id"
                    class="rounded-xl border bg-white p-6 shadow-sm"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ user.name }}</h3>
                            <p class="text-sm text-gray-600">{{ user.email }}</p>
                        </div>
                        <button
                            @click="saveAccess(user)"
                            :disabled="!forms[user.id]?.isDirty"
                            :class="[
                                'rounded-lg px-4 py-2 text-sm font-semibold transition-colors',
                                forms[user.id]?.isDirty
                                    ? 'bg-green-600 text-white hover:bg-green-700'
                                    : 'bg-gray-200 text-gray-400 cursor-not-allowed',
                            ]"
                        >
                            Сохранить
                        </button>
                    </div>

                    <div class="space-y-4">
                        <!-- Роль админа -->
                        <div class="flex items-center gap-3">
                            <input
                                :id="`admin-${user.id}`"
                                v-model="forms[user.id].is_admin"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-green-600 focus:ring-green-500"
                            />
                            <label :for="`admin-${user.id}`" class="text-sm font-medium text-gray-700">
                                Администратор (полный доступ ко всем категориям)
                            </label>
                        </div>

                        <!-- Категории -->
                        <div
                            v-if="!forms[user.id]?.is_admin"
                            class="rounded-lg border border-gray-200 bg-gray-50 p-4"
                        >
                            <h4 class="mb-3 text-sm font-medium text-gray-700">Доступ к категориям:</h4>
                            <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4">
                                <div
                                    v-for="category in categories"
                                    :key="category.id"
                                    class="flex items-center gap-2"
                                >
                                    <input
                                        :id="`cat-${user.id}-${category.id}`"
                                        v-model="forms[user.id].accessible_categories"
                                        :value="category.id"
                                        type="checkbox"
                                        class="h-4 w-4 rounded border-gray-300 text-green-600 focus:ring-green-500"
                                    />
                                    <label :for="`cat-${user.id}-${category.id}`" class="text-sm text-gray-700">
                                        {{ category.code }} - {{ category.name }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Сообщение для админа -->
                        <div
                            v-else
                            class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800"
                        >
                            ✓ Пользователь имеет полный доступ ко всем категориям как администратор
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
