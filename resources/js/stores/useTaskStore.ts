import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { useAppStore } from './useAppStore';

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

export const useTaskStore = defineStore('tasks', () => {
  const appStore = useAppStore();
  
  // State
  const tasks = ref<Task[]>([]);
  const priorityOptions = ref<PriorityOption[]>([]);
  const filter = ref<'all' | 'completed' | 'incomplete' | 'overdue'>('all');
  const sortBy = ref<'created_at' | 'due_date' | 'priority'>('created_at');
  const sortOrder = ref<'asc' | 'desc'>('desc');

  // Getters
  const filteredTasks = computed(() => {
    let filtered = [...tasks.value];

    // Apply filter
    switch (filter.value) {
      case 'completed':
        filtered = filtered.filter(task => task.completed);
        break;
      case 'incomplete':
        filtered = filtered.filter(task => !task.completed);
        break;
      case 'overdue':
        filtered = filtered.filter(task => 
          !task.completed && 
          task.due_date && 
          new Date(task.due_date) < new Date()
        );
        break;
    }

    // Apply sorting
    filtered.sort((a, b) => {
      let aValue: any;
      let bValue: any;

      switch (sortBy.value) {
        case 'due_date':
          aValue = a.due_date ? new Date(a.due_date).getTime() : 0;
          bValue = b.due_date ? new Date(b.due_date).getTime() : 0;
          break;
        case 'priority':
          aValue = a.priority;
          bValue = b.priority;
          break;
        case 'created_at':
        default:
          aValue = new Date(a.created_at).getTime();
          bValue = new Date(b.created_at).getTime();
          break;
      }

      if (sortOrder.value === 'asc') {
        return aValue - bValue;
      } else {
        return bValue - aValue;
      }
    });

    return filtered;
  });

  const completedTasks = computed(() => tasks.value.filter(task => task.completed));
  const incompleteTasks = computed(() => tasks.value.filter(task => !task.completed));
  const overdueTasks = computed(() => 
    tasks.value.filter(task => 
      !task.completed && 
      task.due_date && 
      new Date(task.due_date) < new Date()
    )
  );

  const taskStats = computed(() => ({
    total: tasks.value.length,
    completed: completedTasks.value.length,
    incomplete: incompleteTasks.value.length,
    overdue: overdueTasks.value.length,
  }));

  // Actions
  const initializeTasks = (initialTasks: Task[], initialPriorityOptions: PriorityOption[]) => {
    tasks.value = initialTasks;
    priorityOptions.value = initialPriorityOptions;
  };

  const addTask = async (taskData: {
    title: string;
    description?: string;
    priority: number;
    due_date?: string;
  }) => {
    try {
      appStore.setLoading(true);

      // Optimistic update
      const optimisticTask: Task = {
        id: Date.now(), // Temporary ID
        title: taskData.title,
        description: taskData.description || null,
        completed: false,
        priority: taskData.priority,
        due_date: taskData.due_date || null,
        completed_at: null,
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString(),
      };

      tasks.value.unshift(optimisticTask);

      // Make server request
      await new Promise<void>((resolve, reject) => {
        router.post('/tasks', taskData, {
          onSuccess: (page) => {
            // Replace optimistic task with real data from server
            const serverTasks = page.props.tasks as Task[];
            tasks.value = serverTasks;
            appStore.showSuccess('Task created successfully!');
            resolve();
          },
          onError: (errors) => {
            // Remove optimistic task on error
            tasks.value = tasks.value.filter(task => task.id !== optimisticTask.id);
            appStore.showError('Failed to create task');
            reject(errors);
          },
          onFinish: () => {
            appStore.setLoading(false);
          }
        });
      });
    } catch (error) {
      console.error('Error adding task:', error);
    }
  };

  const updateTask = async (taskId: number, taskData: Partial<Task>) => {
    try {
      appStore.setLoading(true);

      // Optimistic update
      const taskIndex = tasks.value.findIndex(task => task.id === taskId);
      if (taskIndex !== -1) {
        const originalTask = { ...tasks.value[taskIndex] };
        tasks.value[taskIndex] = { ...originalTask, ...taskData, updated_at: new Date().toISOString() };

        // Make server request
        await new Promise<void>((resolve, reject) => {
          router.patch(`/tasks/${taskId}`, taskData, {
            onSuccess: (page) => {
              const serverTasks = page.props.tasks as Task[];
              tasks.value = serverTasks;
              appStore.showSuccess('Task updated successfully!');
              resolve();
            },
            onError: (errors) => {
              // Revert optimistic update on error
              tasks.value[taskIndex] = originalTask;
              appStore.showError('Failed to update task');
              reject(errors);
            },
            onFinish: () => {
              appStore.setLoading(false);
            }
          });
        });
      }
    } catch (error) {
      console.error('Error updating task:', error);
    }
  };

  const toggleTaskComplete = async (taskId: number) => {
    try {
      const task = tasks.value.find(t => t.id === taskId);
      if (!task) return;

      // Optimistic update
      const originalTask = { ...task };
      task.completed = !task.completed;
      task.completed_at = task.completed ? new Date().toISOString() : null;
      task.updated_at = new Date().toISOString();

      // Make server request
      await new Promise<void>((resolve, reject) => {
        router.patch(`/tasks/${taskId}/toggle`, {}, {
          onSuccess: (page) => {
            const serverTasks = page.props.tasks as Task[];
            tasks.value = serverTasks;
            const message = task.completed ? 'Task completed!' : 'Task marked as incomplete!';
            appStore.showSuccess(message);
            resolve();
          },
          onError: (errors) => {
            // Revert optimistic update on error
            Object.assign(task, originalTask);
            appStore.showError('Failed to update task');
            reject(errors);
          }
        });
      });
    } catch (error) {
      console.error('Error toggling task:', error);
    }
  };

  const deleteTask = async (taskId: number) => {
    try {
      appStore.setLoading(true);

      // Optimistic update
      const taskIndex = tasks.value.findIndex(task => task.id === taskId);
      if (taskIndex === -1) return;

      const removedTask = tasks.value.splice(taskIndex, 1)[0];

      // Make server request
      await new Promise<void>((resolve, reject) => {
        router.delete(`/tasks/${taskId}`, {
          onSuccess: (page) => {
            const serverTasks = page.props.tasks as Task[];
            tasks.value = serverTasks;
            appStore.showSuccess('Task deleted successfully!');
            resolve();
          },
          onError: (errors) => {
            // Revert optimistic update on error
            tasks.value.splice(taskIndex, 0, removedTask);
            appStore.showError('Failed to delete task');
            reject(errors);
          },
          onFinish: () => {
            appStore.setLoading(false);
          }
        });
      });
    } catch (error) {
      console.error('Error deleting task:', error);
    }
  };

  const setFilter = (newFilter: typeof filter.value) => {
    filter.value = newFilter;
  };

  const setSorting = (newSortBy: typeof sortBy.value, newSortOrder: typeof sortOrder.value = 'desc') => {
    sortBy.value = newSortBy;
    sortOrder.value = newSortOrder;
  };

  const getPriorityLabel = (priority: number): string => {
    const option = priorityOptions.value.find(opt => opt.value === priority);
    return option ? option.label : 'Unknown';
  };

  const getPriorityColor = (priority: number): string => {
    switch (priority) {
      case 1: return 'green';
      case 2: return 'yellow';
      case 3: return 'red';
      default: return 'gray';
    }
  };

  return {
    // State
    tasks,
    priorityOptions,
    filter,
    sortBy,
    sortOrder,
    
    // Getters
    filteredTasks,
    completedTasks,
    incompleteTasks,
    overdueTasks,
    taskStats,
    
    // Actions
    initializeTasks,
    addTask,
    updateTask,
    toggleTaskComplete,
    deleteTask,
    setFilter,
    setSorting,
    getPriorityLabel,
    getPriorityColor,
  };
});
