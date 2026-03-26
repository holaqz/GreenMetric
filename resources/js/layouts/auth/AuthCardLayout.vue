<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="gm-page gm-page-bg relative">
        <div class="gm-grid pointer-events-none absolute inset-0" />
        <div class="gm-auth-shell relative">
            <div class="w-full max-w-md">
                <div class="mb-6 flex justify-center">
                    <Link :href="home()" class="gm-brand">
                        <span class="gm-brand-badge">
                            <AppLogoIcon class="size-7 fill-current text-primary" />
                        </span>
                        <span class="text-base leading-none">GreenMetric</span>
                    </Link>
                </div>

                <Card class="gm-auth-card">
                    <CardHeader class="gm-auth-header">
                        <CardTitle class="text-xl">{{ title }}</CardTitle>
                        <CardDescription class="mt-1">
                            {{ description }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="gm-auth-content">
                        <slot />
                    </CardContent>
                </Card>

                <div class="mt-6 text-center text-xs text-muted-foreground">
                    © {{ new Date().getFullYear() }} GreenMetric
                </div>
            </div>
        </div>
    </div>
</template>
