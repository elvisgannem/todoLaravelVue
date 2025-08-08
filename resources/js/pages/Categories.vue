<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import NotificationToast from '../components/NotificationToast.vue';
import CategoryManager from '../components/CategoryManager.vue';

interface Category {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  color: string;
  user_id: number;
  created_at: string;
  updated_at: string;
  tasks_count?: number;
}

defineProps<{
  categories: Category[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Categories',
    href: '/categories',
  },
];
</script>

<template>
  <Head title="Categories" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
      <!-- Category Manager -->
      <CategoryManager :categories="categories" />
    </div>

    <!-- Notification Toast Component -->
    <NotificationToast />
  </AppLayout>
</template>
