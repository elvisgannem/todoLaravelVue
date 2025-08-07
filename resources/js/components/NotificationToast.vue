<template>
  <div class="fixed top-4 right-4 z-50 space-y-2">
    <TransitionGroup
      name="notification"
      tag="div"
      class="space-y-2"
    >
      <div
        v-for="notification in notifications"
        :key="notification.id"
        class="max-w-md w-full bg-white dark:bg-gray-800 shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden"
        :class="getNotificationClasses(notification.type)"
      >
        <div class="p-4">
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <div 
                class="h-6 w-6 rounded-full flex items-center justify-center"
                :class="getIconClasses(notification.type)"
              >
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                  <path v-if="notification.type === 'success'" fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                  <path v-else-if="notification.type === 'error'" fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                  <path v-else-if="notification.type === 'warning'" fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                  <path v-else fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
              </div>
            </div>
            <div class="ml-3 flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 dark:text-gray-100 break-words">
                {{ notification.message }}
              </p>
            </div>
            <div class="ml-4 flex flex-shrink-0">
              <button
                @click="removeNotification(notification.id)"
                class="bg-white dark:bg-gray-800 rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                <span class="sr-only">Close</span>
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup lang="ts">
import { useAppStore } from '@/stores';
import { storeToRefs } from 'pinia';

const appStore = useAppStore();
const { notifications } = storeToRefs(appStore);
const { removeNotification } = appStore;

const getNotificationClasses = (type: string) => {
  const baseClasses = 'border-l-4';
  switch (type) {
    case 'success':
      return `${baseClasses} border-green-400 bg-green-50 dark:bg-green-900/20`;
    case 'error':
      return `${baseClasses} border-red-400 bg-red-50 dark:bg-red-900/20`;
    case 'warning':
      return `${baseClasses} border-yellow-400 bg-yellow-50 dark:bg-yellow-900/20`;
    case 'info':
      return `${baseClasses} border-blue-400 bg-blue-50 dark:bg-blue-900/20`;
    default:
      return `${baseClasses} border-gray-400 bg-gray-50 dark:bg-gray-900/20`;
  }
};

const getIconClasses = (type: string) => {
  switch (type) {
    case 'success':
      return 'bg-green-100 text-green-600 dark:bg-green-800 dark:text-green-200';
    case 'error':
      return 'bg-red-100 text-red-600 dark:bg-red-800 dark:text-red-200';
    case 'warning':
      return 'bg-yellow-100 text-yellow-600 dark:bg-yellow-800 dark:text-yellow-200';
    case 'info':
      return 'bg-blue-100 text-blue-600 dark:bg-blue-800 dark:text-blue-200';
    default:
      return 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-200';
  }
};


</script>

<style scoped>
.notification-enter-active,
.notification-leave-active {
  transition: all 0.3s ease;
}

.notification-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.notification-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

.notification-move {
  transition: transform 0.3s ease;
}
</style>
