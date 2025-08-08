<template>
  <div class="space-y-6">
    <!-- Task Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm border border-sidebar-border/70">
        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ taskStore.taskStats.total }}</div>
        <div class="text-sm text-gray-600 dark:text-gray-400">Total Tasks</div>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm border border-sidebar-border/70">
        <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ taskStore.taskStats.completed }}</div>
        <div class="text-sm text-gray-600 dark:text-gray-400">Completed</div>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm border border-sidebar-border/70">
        <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ taskStore.taskStats.incomplete }}</div>
        <div class="text-sm text-gray-600 dark:text-gray-400">Incomplete</div>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm border border-sidebar-border/70">
        <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ taskStore.taskStats.overdue }}</div>
        <div class="text-sm text-gray-600 dark:text-gray-400">Overdue</div>
      </div>
    </div>

    <!-- Add Task Form -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-sidebar-border/70">
      <!-- Collapsible Header -->
      <button
        @click="isAddFormOpen = !isAddFormOpen"
        class="w-full p-6 text-left hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors rounded-lg"
        type="button"
      >
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
            Add New Task
          </h2>
          <div class="flex items-center gap-2">
            <span class="text-sm text-gray-500 dark:text-gray-400">
              {{ isAddFormOpen ? 'Hide form' : 'Click to add task' }}
            </span>
            <kbd class="hidden sm:inline-flex px-2 py-1 text-xs font-semibold text-gray-500 bg-gray-100 border border-gray-300 rounded dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600">
              Ctrl+N
            </kbd>
            <svg 
              class="w-5 h-5 text-gray-500 dark:text-gray-400 transform transition-transform duration-200"
              :class="{ 'rotate-180': isAddFormOpen }"
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </div>
      </button>

      <!-- Collapsible Form Content -->
      <Transition
        enter-active-class="transition-all duration-300 ease-in-out"
        enter-from-class="opacity-0 max-h-0"
        enter-to-class="opacity-100 max-h-[1000px]"
        leave-active-class="transition-all duration-300 ease-in-out"
        leave-from-class="opacity-100 max-h-[1000px]"
        leave-to-class="opacity-0 max-h-0"
      >
        <div 
          v-show="isAddFormOpen"
          class="border-t border-gray-200 dark:border-gray-700 overflow-hidden"
        >
          <div class="p-6">
          <form @submit.prevent="submitTask" class="space-y-4">
        <div>
          <Label for="title" class="mb-2 block">Title</Label>
          <Input
            id="title"
            v-model="form.title"
            type="text"
            placeholder="Enter task title..."
            required
          />
          <InputError :message="formErrors.title" />
        </div>

        <div>
          <Label for="description" class="mb-2 block">Description (Optional)</Label>
          <textarea
            id="description"
            v-model="form.description"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            placeholder="Enter task description..."
          ></textarea>
          <InputError :message="formErrors.description" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <Label for="priority" class="mb-2 block">Priority</Label>
                      <select
            id="priority"
            v-model="form.priority"
            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            required
          >
            <option v-for="option in taskStore.priorityOptions" :key="option.value" :value="option.value">
              {{ option.label }}
            </option>
          </select>
            <InputError :message="formErrors.priority" />
          </div>

          <div>
            <Label for="due_date" class="mb-2 block">Due Date (Optional)</Label>
            <Input
              id="due_date"
              v-model="form.due_date"
              type="date"
            />
            <InputError :message="formErrors.due_date" />
          </div>

          <div>
            <Label class="mb-2 block">Categories (Optional)</Label>
            <div class="space-y-2 max-h-32 overflow-y-auto">
              <div 
                v-for="category in categories" 
                :key="category.id"
                class="flex items-center space-x-2"
              >
                <input
                  :id="`category-${category.id}`"
                  v-model="form.categories"
                  :value="category.id"
                  type="checkbox"
                  class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                />
                <label 
                  :for="`category-${category.id}`"
                  class="flex items-center space-x-2 cursor-pointer"
                >
                  <span 
                    class="w-3 h-3 rounded-full" 
                    :style="{ backgroundColor: category.color }"
                  ></span>
                  <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    {{ category.name }}
                  </span>
                </label>
              </div>
            </div>
            <p v-if="categories.length === 0" class="text-sm text-gray-500 dark:text-gray-400 mt-1">
              No categories available. Create categories first to organize your tasks.
            </p>
            <InputError :message="formErrors.categories" />
          </div>
        </div>

            <div class="flex justify-end">
              <Button type="submit">
                Add Task
              </Button>
            </div>
          </form>
          </div>
        </div>
      </Transition>
    </div>

    <!-- Filter and Sort Controls -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm border border-sidebar-border/70">
      <div class="flex flex-wrap gap-4 items-center">
        <div class="flex gap-2">
          <button
            @click="taskStore.setFilter('all')"
            :class="[
              'px-3 py-1 rounded-md text-sm font-medium transition-colors',
              taskStore.filter === 'all' 
                ? 'bg-blue-600 text-white' 
                : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'
            ]"
          >
            All
          </button>
          <button
            @click="taskStore.setFilter('incomplete')"
            :class="[
              'px-3 py-1 rounded-md text-sm font-medium transition-colors',
              taskStore.filter === 'incomplete' 
                ? 'bg-yellow-600 text-white' 
                : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'
            ]"
          >
            Incomplete
          </button>
          <button
            @click="taskStore.setFilter('completed')"
            :class="[
              'px-3 py-1 rounded-md text-sm font-medium transition-colors',
              taskStore.filter === 'completed' 
                ? 'bg-green-600 text-white' 
                : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'
            ]"
          >
            Completed
          </button>
          <button
            @click="taskStore.setFilter('overdue')"
            :class="[
              'px-3 py-1 rounded-md text-sm font-medium transition-colors',
              taskStore.filter === 'overdue' 
                ? 'bg-red-600 text-white' 
                : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'
            ]"
          >
            Overdue
          </button>
        </div>
        
        <div class="flex gap-2 items-center">
          <span class="text-sm text-gray-600 dark:text-gray-400">Sort by:</span>
          <select
            v-model="taskStore.sortBy"
            @change="taskStore.setSorting(taskStore.sortBy, taskStore.sortOrder)"
            class="px-2 py-1 border border-gray-300 rounded-md text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
          >
            <option value="created_at">Date Created</option>
            <option value="due_date">Due Date</option>
            <option value="priority">Priority</option>
          </select>
          <button
            @click="taskStore.setSorting(taskStore.sortBy, taskStore.sortOrder === 'asc' ? 'desc' : 'asc')"
            class="p-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
            :title="taskStore.sortOrder === 'asc' ? 'Sort Descending' : 'Sort Ascending'"
          >
            <svg class="w-4 h-4" :class="{ 'rotate-180': taskStore.sortOrder === 'desc' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Tasks List -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm border border-sidebar-border/70">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
        My Tasks ({{ taskStore.filteredTasks.length }})
      </h2>

      <div v-if="taskStore.filteredTasks.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
        <p v-if="taskStore.tasks.length === 0">No tasks yet. Add your first task above!</p>
        <p v-else>No tasks match the current filter.</p>
      </div>

      <div v-else class="space-y-3">
        <TodoItem
          v-for="task in taskStore.filteredTasks"
          :key="task.id"
          :task="task"
          :categories="categories"
          @toggle-complete="taskStore.toggleTaskComplete"
          @update="handleUpdateTask"
          @delete="handleDeleteTask"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { useTaskStore } from '@/stores';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import InputError from '@/components/InputError.vue';
import TodoItem from '@/components/TodoItem.vue';

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
  categories: Category[];
}

interface Category {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  color: string;
  user_id: number;
  created_at: string;
  updated_at: string;
}

interface PriorityOption {
  value: number;
  label: string;
}

const props = defineProps<{
  tasks: Task[];
  categories: Category[];
  priorityOptions: PriorityOption[];
}>();

const taskStore = useTaskStore();

// Initialize store with props data
onMounted(() => {
  taskStore.initializeTasks(props.tasks, props.priorityOptions);
  
  // Initialize filters from URL query parameters
  initializeFiltersFromURL();
  
  // Add keyboard shortcut for opening add form
  document.addEventListener('keydown', handleKeydown);
});

// Initialize filters from URL query parameters
const initializeFiltersFromURL = () => {
  const url = new URL(window.location.href);
  const params = url.searchParams;
  
  // Set filter from URL
  const filterParam = params.get('filter');
  if (filterParam && ['all', 'completed', 'incomplete', 'overdue'].includes(filterParam)) {
    taskStore.setFilter(filterParam as any);
  }
  
  // Set sort parameters from URL
  const sortBy = params.get('sortBy');
  const sortOrder = params.get('sortOrder');
  
  if (sortBy && ['created_at', 'due_date', 'priority'].includes(sortBy)) {
    const order = sortOrder === 'asc' ? 'asc' : 'desc';
    taskStore.setSorting(sortBy as any, order);
  }
};

// Update URL when filters change
const updateURL = (filter: string, sortBy: string, sortOrder: string) => {
  const url = new URL(window.location.href);
  
  // Update query parameters
  if (filter !== 'all') {
    url.searchParams.set('filter', filter);
  } else {
    url.searchParams.delete('filter');
  }
  
  if (sortBy !== 'created_at') {
    url.searchParams.set('sortBy', sortBy);
  } else {
    url.searchParams.delete('sortBy');
  }
  
  if (sortOrder !== 'desc') {
    url.searchParams.set('sortOrder', sortOrder);
  } else {
    url.searchParams.delete('sortOrder');
  }
  
  // Update URL without page reload
  window.history.replaceState({}, '', url.toString());
};

// Watch for filter and sort changes to update URL
watch([() => taskStore.filter, () => taskStore.sortBy, () => taskStore.sortOrder], 
  ([newFilter, newSortBy, newSortOrder]) => {
    updateURL(newFilter, newSortBy, newSortOrder);
  }
);

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown);
});

const handleKeydown = (event: KeyboardEvent) => {
  // Ctrl+N or Cmd+N to open/close add form
  if ((event.ctrlKey || event.metaKey) && event.key === 'n') {
    event.preventDefault();
    isAddFormOpen.value = !isAddFormOpen.value;
    
    // Focus on title input if opening
    if (isAddFormOpen.value) {
      setTimeout(() => {
        const titleInput = document.getElementById('title');
        titleInput?.focus();
      }, 100);
    }
  }
};



const isAddFormOpen = ref(false);

const form = ref({
  title: '',
  description: '',
  priority: 1, // Default to Low priority
  due_date: '',
  categories: [] as number[],
});

const formErrors = ref<{[key: string]: string}>({});

const submitTask = async () => {
  if (!form.value.title.trim()) {
    formErrors.value.title = 'Title is required';
    return;
  }
  
  formErrors.value = {};
  
  try {
    await taskStore.addTask({
      title: form.value.title,
      description: form.value.description || undefined,
      priority: form.value.priority,
      due_date: form.value.due_date || undefined,
      categories: form.value.categories,
    });
    
    // Reset form and close the form
    form.value = {
      title: '',
      description: '',
      priority: 1,
      due_date: '',
      categories: [],
    };
    isAddFormOpen.value = false;
  } catch (error) {
    console.error('Error submitting task:', error);
  }
};

const handleUpdateTask = async (task: Task, data: any) => {
  await taskStore.updateTask(task.id, data);
};

const handleDeleteTask = async (task: Task) => {
  await taskStore.deleteTask(task.id);
};
</script>
