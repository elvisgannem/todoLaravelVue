<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import NotificationToast from '../components/NotificationToast.vue';
import { useAppStore } from '@/stores';
import { storeToRefs } from 'pinia';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const appStore = useAppStore();
const { isLoading } = storeToRefs(appStore);
const { setLoading, showSuccess, showError, showWarning, showInfo } = appStore;

const handleTestNotifications = () => {
    showSuccess('This is a success message!');
    setTimeout(() => showError('This is an error message!'), 1000);
    setTimeout(() => showWarning('This is a warning message!'), 2000);
    setTimeout(() => showInfo('This is an info message!'), 3000);
};

const handleTestLoading = () => {
    setLoading(true);
    setTimeout(() => setLoading(false), 3000);
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <!-- Pinia Store Demo Section -->
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm border border-sidebar-border/70">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    Pinia Store Demo
                </h2>
                <div class="flex flex-wrap gap-3">
                    <button
                        @click="handleTestNotifications"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
                    >
                        Test Notifications
                    </button>
                    <button
                        @click="handleTestLoading"
                        :disabled="isLoading"
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors disabled:opacity-50"
                    >
                        {{ isLoading ? 'Loading...' : 'Test Loading State' }}
                    </button>
                </div>
            </div>

            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
            </div>
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <PlaceholderPattern />
            </div>
        </div>

        <!-- Notification Toast Component -->
        <NotificationToast />
    </AppLayout>
</template>
