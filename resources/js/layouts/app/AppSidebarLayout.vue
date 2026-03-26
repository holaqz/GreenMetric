<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <div class="gm-page gm-page-bg relative">
        <div class="gm-grid pointer-events-none absolute inset-0" />
        <AppShell variant="sidebar">
            <AppSidebar />
            <AppContent
                variant="sidebar"
                class="overflow-x-hidden bg-transparent"
            >
                <AppSidebarHeader :breadcrumbs="breadcrumbs" />
                <div class="px-4 py-6 md:px-6">
                    <slot />
                </div>
            </AppContent>
        </AppShell>
    </div>
</template>
