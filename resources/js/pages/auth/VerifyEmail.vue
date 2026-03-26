<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { logout } from '@/routes/index';
import { send } from '@/routes/verification/index';

defineProps<{
    status?: string;
}>();
</script>

<template>
    <AuthLayout
        title="Подтверждение email"
        description="Пожалуйста, подтвердите вашу почту, перейдя по ссылке, которую мы отправили вам"
    >
        <Head title="Подтверждение email" />

        <div
            v-if="status === 'verification-link-sent'"
            class="mb-4 text-center text-sm font-medium text-green-600"
        >
            Новая ссылка для подтверждения была отправлена на вашу почту
        </div>

        <Form
            v-bind="send.form()"
            class="space-y-6 text-center"
            v-slot="{ processing }"
        >
            <Button :disabled="processing" variant="secondary">
                <Spinner v-if="processing" />
                Отправить письмо повторно
            </Button>

            <TextLink
                :href="logout()"
                as="button"
                class="mx-auto block text-sm"
            >
                Выйти
            </TextLink>
        </Form>
    </AuthLayout>
</template>
