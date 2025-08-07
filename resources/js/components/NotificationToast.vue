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
        class="max-w-sm w-full bg-white dark:bg-gray-800 shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden"
        :class="getNotificationClasses(notification.type)"
      >
        <div class="p-4">
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <component
                :is="getNotificationIcon(notification.type)"
                class="h-6 w-6"
                :class="getIconClasses(notification.type)"
              />
            </div>
            <div class="ml-3 w-0 flex-1 pt-0.5">
              <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
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
      return 'text-green-400';
    case 'error':
      return 'text-red-400';
    case 'warning':
      return 'text-yellow-400';
    case 'info':
      return 'text-blue-400';
    default:
      return 'text-gray-400';
  }
};

const getNotificationIcon = (type: string) => {
  switch (type) {
    case 'success':
      return 'CheckCircleIcon';
    case 'error':
      return 'XCircleIcon';
    case 'warning':
      return 'ExclamationTriangleIcon';
    case 'info':
      return 'InformationCircleIcon';
    default:
      return 'InformationCircleIcon';
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
