<script setup lang="ts">
import { Sun } from 'lucide-vue-next';
import { useAppearance } from '@/composables/useAppearance';

const { updateAppearance } = useAppearance();

const tabs = [
    { value: 'light', Icon: Sun, label: 'Светлая' },
] as const;
</script>

<template>
    <div
        class="inline-flex gap-1 rounded-lg bg-neutral-100 p-1"
    >
        <button
            v-for="{ value, Icon, label } in tabs"
            :key="value"
            @click="updateAppearance(value)"
            class="flex items-center rounded-md bg-white px-3.5 py-1.5 shadow-xs transition-colors"
        >
            <component :is="Icon" class="-ml-1 h-4 w-4" />
            <span class="ml-1.5 text-sm">{{ label }}</span>
        </button>
    </div>
</template>
