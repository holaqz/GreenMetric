<script setup lang="ts">
import AuthSplitLayout from '@/layouts/auth/AuthSplitLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import type { PageProps } from '@/types';

const page = usePage<PageProps>();

const form = useForm({
    volume: '',
    collected_volume: '',
    description: '',
});

const submit = () => {
    form.post(route('water.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Добавить воду" />

    <AuthSplitLayout title="Новая запись" description="Укажите параметры воды">
        
        <div v-if="page.props.flash?.success" class="mb-4 p-3 bg-green-100 text-green-700 rounded-md text-sm">
            {{ page.props.flash.success }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            
            <!-- Объем -->
            <div class="space-y-2">
                <label for="volume" class="text-sm font-medium">Общий объем (л)</label>
                <input
                    id="volume"
                    type="number"
                    step="0.01"
                    min="0"
                    v-model="form.volume"
                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                    placeholder="Например: 100.50"
                />
                <div v-if="form.errors.volume" class="text-sm text-red-500">
                    {{ form.errors.volume }}
                </div>
            </div>

            <!-- Собранный объем -->
            <div class="space-y-2">
                <label for="collected_volume" class="text-sm font-medium">Собранный объем (л)</label>
                <input
                    id="collected_volume"
                    type="number"
                    step="0.01"
                    min="0"
                    v-model="form.collected_volume"
                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                    placeholder="Например: 75.00"
                />
                <div v-if="form.errors.collected_volume" class="text-sm text-red-500">
                    {{ form.errors.collected_volume }}
                </div>
            </div>

            <!-- Описание (опционально) -->
            <div class="space-y-2">
                <label for="description" class="text-sm font-medium">Описание</label>
                <input
                    id="description"
                    type="text"
                    v-model="form.description"
                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                    placeholder="Комментарий..."
                />
                <div v-if="form.errors.description" class="text-sm text-red-500">
                    {{ form.errors.description }}
                </div>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex items-center justify-center rounded-md text-sm font-medium h-10 px-4 py-2 w-full bg-primary text-primary-foreground hover:bg-primary/90 disabled:opacity-50"
            >
                <span v-if="form.processing">Сохранение...</span>
                <span v-else>Сохранить</span>
            </button>
        </form>
    </AuthSplitLayout>
</template>