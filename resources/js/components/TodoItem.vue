<template>
  <div
    class="flex items-center gap-4 p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
    :class="[
      task.completed ? 'opacity-75' : '',
      getPriorityBorderClass(task.priority)
    ]"
  >
    <!-- Checkbox -->
    <div class="flex-shrink-0">
      <input
        type="checkbox"
        :checked="task.completed"
        @change="$emit('toggleComplete', task.id)"
        class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
      />
    </div>

    <!-- Task Content -->
    <div class="flex-1 min-w-0">
      <div class="flex items-start justify-between">
        <div class="flex-1">
          <h3
            class="text-sm font-medium text-gray-900 dark:text-gray-100"
            :class="{ 'line-through': task.completed }"
          >
            {{ task.title }}
          </h3>
          
          <p
            v-if="task.description"
            class="text-sm text-gray-600 dark:text-gray-400 mt-1"
            :class="{ 'line-through': task.completed }"
          >
            {{ task.description }}
          </p>

          <div class="flex items-center gap-4 mt-2 text-xs text-gray-500 dark:text-gray-400">
            <!-- Priority Badge -->
            <span
              class="px-2 py-1 rounded-full text-xs font-medium"
              :class="getPriorityBadgeClass(task.priority)"
            >
              {{ getPriorityLabel(task.priority) }}
            </span>

            <!-- Due Date -->
            <span v-if="task.due_date" class="flex items-center gap-1">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              {{ formatDate(task.due_date) }}
              <span v-if="isOverdue(task)" class="text-red-500 font-medium">(Overdue)</span>
            </span>

            <!-- Categories -->
            <div v-if="task.categories && task.categories.length > 0" class="flex items-center gap-1 flex-wrap">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
              </svg>
              <span
                v-for="category in task.categories"
                :key="category.id"
                class="px-2 py-1 rounded-full text-xs font-medium"
                :style="{ backgroundColor: category.color + '20', color: category.color, border: '1px solid ' + category.color + '40' }"
              >
                {{ category.name }}
              </span>
            </div>

            <!-- Completion Date -->
            <span v-if="task.completed_at" class="flex items-center gap-1 text-green-600">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              Completed {{ formatDate(task.completed_at) }}
            </span>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-2 ml-4">
          <button
            @click="toggleEdit"
            class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
            title="Edit task"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
          </button>

          <button
            @click="showDeleteModal = true"
            class="p-1 text-gray-400 hover:text-red-600 dark:hover:text-red-400"
            title="Delete task"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Form Modal -->
  <div v-if="isEditing" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md mx-4">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
        Edit Task
      </h3>

      <form @submit.prevent="submitEdit" class="space-y-4">
        <div>
          <Label for="edit-title" class="mb-2 block">Title</Label>
          <Input
            id="edit-title"
            v-model="editForm.title"
            type="text"
            required
          />
        </div>

        <div>
          <Label for="edit-description" class="mb-2 block">Description</Label>
          <textarea
            id="edit-description"
            v-model="editForm.description"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
          ></textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <Label for="edit-priority" class="mb-2 block">Priority</Label>
            <select
              id="edit-priority"
              v-model="editForm.priority"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
              required
            >
            <option v-for="option in taskStore.priorityOptions" :key="option.value" :value="option.value">
              {{ option.label }}
            </option>
            </select>
          </div>

          <div>
            <Label for="edit-due-date" class="mb-2 block">Due Date</Label>
            <Input
              id="edit-due-date"
              v-model="editForm.due_date"
              type="date"
            />
          </div>
        </div>

        <div>
          <Label class="mb-2 block">Categories</Label>
          <div class="space-y-2">
            <label
              v-for="category in categories"
              :key="category.id"
              class="flex items-center gap-2 cursor-pointer"
            >
              <input
                type="checkbox"
                :value="category.id"
                v-model="editForm.categories"
                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
              />
              <span
                class="px-2 py-1 rounded-full text-xs font-medium"
                :style="{ backgroundColor: category.color + '20', color: category.color, border: '1px solid ' + category.color + '40' }"
              >
                {{ category.name }}
              </span>
            </label>
          </div>
        </div>

        <div class="flex justify-end gap-2">
          <Button type="button" variant="outline" @click="cancelEdit">
            Cancel
          </Button>
          <Button type="submit">
            Update Task
          </Button>
        </div>
      </form>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <ConfirmationModal
    v-model:open="showDeleteModal"
    title="Delete Task"
    :description="`Are you sure you want to delete '${task.title}'? This action cannot be undone.`"
    confirm-text="Delete Task"
    :loading="isDeleting"
    @confirm="handleDelete"
    @cancel="showDeleteModal = false"
  />
</template>

<script setup lang="ts">
import { ref } from 'vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import ConfirmationModal from '@/components/ConfirmationModal.vue';
import { useTaskStore } from '@/stores';

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

interface EditFormData {
  title: string;
  description: string;
  priority: number;
  due_date: string;
  categories: number[];
}

const props = defineProps<{
  task: Task;
  categories: Category[];
}>();

const emit = defineEmits<{
  toggleComplete: [taskId: number];
  update: [task: Task, data: EditFormData];
  delete: [task: Task];
}>();

const taskStore = useTaskStore();

const isEditing = ref(false);
const showDeleteModal = ref(false);
const isDeleting = ref(false);

const editForm = ref<EditFormData>({
  title: '',
  description: '',
  priority: 1,
  due_date: '',
  categories: [],
});

const toggleEdit = () => {
  isEditing.value = true;
  editForm.value = {
    title: props.task.title,
    description: props.task.description || '',
    priority: props.task.priority,
    due_date: props.task.due_date || '',
    categories: props.task.categories.map(cat => cat.id),
  };
};

const cancelEdit = () => {
  isEditing.value = false;
};

const submitEdit = () => {
  emit('update', props.task, editForm.value);
  isEditing.value = false;
};

const handleDelete = async () => {
  isDeleting.value = true;
  try {
    emit('delete', props.task);
    showDeleteModal.value = false;
  } finally {
    isDeleting.value = false;
  }
};

const getPriorityLabel = (priority: number): string => {
  return taskStore.getPriorityLabel(priority);
};

const getPriorityBadgeClass = (priority: number): string => {
  switch (priority) {
    case 1: return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
    case 2: return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
    case 3: return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
    default: return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
  }
};

const getPriorityBorderClass = (priority: number): string => {
  switch (priority) {
    case 3: return 'border-l-4 border-l-red-500';
    case 2: return 'border-l-4 border-l-yellow-500';
    default: return 'border-l-4 border-l-green-500';
  }
};

const formatDate = (dateString: string): string => {
  const date = new Date(dateString);
  return date.toLocaleDateString();
};

const isOverdue = (task: Task): boolean => {
  if (!task.due_date || task.completed) return false;
  return new Date(task.due_date) < new Date();
};
</script>
