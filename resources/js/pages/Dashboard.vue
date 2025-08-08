<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import NotificationToast from '../components/NotificationToast.vue';
import TodoList from '../components/TodoList.vue';

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

defineProps<{
    tasks: Task[];
    priorityOptions: PriorityOption[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tasks',
        href: '/dashboard',
    },
];
</script>

<template>
    <Head title="Tasks" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <!-- Todo List -->
            <TodoList :tasks="tasks" :priority-options="priorityOptions" />
        </div>

        <!-- Notification Toast Component -->
        <NotificationToast />
    </AppLayout>
</template>
