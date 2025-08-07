import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useAppStore = defineStore('app', () => {
  // State
  const isLoading = ref(false);
  const notifications = ref<Array<{
    id: string;
    type: 'success' | 'error' | 'warning' | 'info';
    message: string;
    duration?: number;
  }>>([]);

  // Getters
  const hasNotifications = computed(() => notifications.value.length > 0);
  const successNotifications = computed(() => 
    notifications.value.filter(n => n.type === 'success')
  );
  const errorNotifications = computed(() => 
    notifications.value.filter(n => n.type === 'error')
  );

  // Actions
  const setLoading = (loading: boolean) => {
    isLoading.value = loading;
  };

  const addNotification = (notification: {
    type: 'success' | 'error' | 'warning' | 'info';
    message: string;
    duration?: number;
  }) => {
    const id = Date.now().toString();
    const newNotification = {
      id,
      ...notification,
      duration: notification.duration || 5000
    };
    
    notifications.value.push(newNotification);

    // Auto-remove notification after duration
    if (newNotification.duration > 0) {
      setTimeout(() => {
        removeNotification(id);
      }, newNotification.duration);
    }
  };

  const removeNotification = (id: string) => {
    const index = notifications.value.findIndex(n => n.id === id);
    if (index > -1) {
      notifications.value.splice(index, 1);
    }
  };

  const clearNotifications = () => {
    notifications.value = [];
  };

  const showSuccess = (message: string, duration?: number) => {
    addNotification({ type: 'success', message, duration });
  };

  const showError = (message: string, duration?: number) => {
    addNotification({ type: 'error', message, duration });
  };

  const showWarning = (message: string, duration?: number) => {
    addNotification({ type: 'warning', message, duration });
  };

  const showInfo = (message: string, duration?: number) => {
    addNotification({ type: 'info', message, duration });
  };

  return {
    // State
    isLoading,
    notifications,
    
    // Getters
    hasNotifications,
    successNotifications,
    errorNotifications,
    
    // Actions
    setLoading,
    addNotification,
    removeNotification,
    clearNotifications,
    showSuccess,
    showError,
    showWarning,
    showInfo
  };
});
