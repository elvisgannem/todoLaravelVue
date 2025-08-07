<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import NotificationToast from '../components/NotificationToast.vue';
import TodoList from '../components/TodoList.vue';
import { useAppStore } from '@/stores';
import { storeToRefs } from 'pinia';

interface Task {
    id: number;
    title: string;
    description: string | null;
    completed: boolean;
    priority: number;
    due_date: string | null;
    completed_at: string | null;
    created_at: string;
    updated_at: string;
}

interface PriorityOption {
    value: number;
    label: string;
}

const props = defineProps<{
    tasks: Task[];
    priorityOptions: PriorityOption[];
}>();

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
            <!-- Todo List -->
            <TodoList :tasks="tasks" :priority-options="priorityOptions" />
        </div>

        <!-- Notification Toast Component -->
        <NotificationToast />
    </AppLayout>
</template>
