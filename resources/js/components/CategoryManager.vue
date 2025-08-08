<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
          Category Management
        </h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
          Organize your tasks with custom categories
        </p>
      </div>
      <Button @click="openCreateForm" class="flex items-center space-x-2">
        <Plus class="w-4 h-4" />
        <span>Add Category</span>
      </Button>
    </div>

    <!-- Create/Edit Form -->
    <div v-if="showForm" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
          {{ editingCategory ? 'Edit Category' : 'Create New Category' }}
        </h3>
        <Button variant="ghost" size="sm" @click="closeForm">
          <X class="w-4 h-4" />
        </Button>
      </div>

      <form @submit.prevent="submitForm" class="space-y-4">
        <div>
          <Label for="category-name" class="mb-2 block">Category Name *</Label>
          <Input
            id="category-name"
            v-model="form.name"
            type="text"
            placeholder="e.g., Work, Personal, Shopping"
            :class="{ 'border-red-500': formErrors.name }"
            required
          />
          <InputError :message="formErrors.name" />
        </div>

        <div>
          <Label for="category-description" class="mb-2 block">Description (Optional)</Label>
          <textarea
            id="category-description"
            v-model="form.description"
            rows="3"
            placeholder="Brief description of this category..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
            :class="{ 'border-red-500': formErrors.description }"
          ></textarea>
          <InputError :message="formErrors.description" />
        </div>

        <div>
          <Label class="mb-2 block">Category Color *</Label>
          <div class="flex items-center space-x-3">
            <div class="flex flex-wrap gap-2">
              <button
                v-for="color in predefinedColors"
                :key="color"
                type="button"
                @click="form.color = color"
                class="w-8 h-8 rounded-full border-2 hover:scale-110 transition-transform"
                :class="form.color === color ? 'border-gray-900 dark:border-white' : 'border-gray-300 dark:border-gray-600'"
                :style="{ backgroundColor: color }"
                :title="color"
              />
            </div>
            <div class="flex items-center space-x-2">
              <input
                v-model="form.color"
                type="color"
                class="w-8 h-8 rounded cursor-pointer border border-gray-300 dark:border-gray-600"
              />
              <span class="text-sm text-gray-500 dark:text-gray-400">Custom</span>
            </div>
          </div>
          <InputError :message="formErrors.color" />
        </div>

        <div class="flex justify-end space-x-3 pt-4">
          <Button type="button" variant="outline" @click="closeForm">
            Cancel
          </Button>
          <Button type="submit" :disabled="isLoading">
            <span v-if="isLoading" class="flex items-center space-x-2">
              <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
              <span>{{ editingCategory ? 'Updating...' : 'Creating...' }}</span>
            </span>
            <span v-else>{{ editingCategory ? 'Update Category' : 'Create Category' }}</span>
          </Button>
        </div>
      </form>
    </div>

    <!-- Categories List -->
    <div class="space-y-4">
      <div v-if="categoryStore.categories.length === 0" class="text-center py-12">
        <div class="w-16 h-16 mx-auto mb-4 text-gray-400">
          <Folder class="w-full h-full" />
        </div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No categories yet</h3>
        <p class="text-gray-500 dark:text-gray-400 mb-4">
          Create your first category to start organizing your tasks
        </p>
        <Button @click="openCreateForm">
          <Plus class="w-4 h-4 mr-2" />
          Create Category
        </Button>
      </div>

      <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="category in categoryStore.categoriesByName"
          :key="category.id"
          class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-shadow"
        >
          <div class="flex items-start justify-between mb-3">
            <div class="flex items-center space-x-3">
              <div
                class="w-4 h-4 rounded-full flex-shrink-0"
                :style="{ backgroundColor: category.color }"
              ></div>
              <div class="min-w-0 flex-1">
                <h4 class="text-sm font-medium text-gray-900 dark:text-white truncate">
                  {{ category.name }}
                </h4>
                <p v-if="category.description" class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">
                  {{ category.description }}
                </p>
              </div>
            </div>
            
            <div class="flex items-center space-x-1 flex-shrink-0">
              <Button variant="ghost" size="sm" @click="editCategory(category)">
                <Edit class="w-4 h-4" />
              </Button>
              <Button 
                variant="ghost" 
                size="sm" 
                @click="confirmDelete(category)"
                :disabled="(category.tasks_count ?? 0) > 0"
                :title="(category.tasks_count ?? 0) > 0 ? 'Cannot delete category with tasks' : 'Delete category'"
              >
                <Trash2 class="w-4 h-4" :class="(category.tasks_count ?? 0) > 0 ? 'text-gray-400' : 'text-red-500'" />
              </Button>
            </div>
          </div>

          <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
            <span>{{ category.tasks_count ?? 0 }} task{{ (category.tasks_count ?? 0) === 1 ? '' : 's' }}</span>
            <span>{{ formatDate(category.created_at) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <ConfirmationModal
      :open="showDeleteModal"
      :title="`Delete ${categoryToDelete?.name}?`"
      :description="categoryToDelete?.tasks_count ? 
        `This category has ${categoryToDelete.tasks_count} task(s). Please remove or reassign these tasks before deleting the category.` :
        'This action cannot be undone. The category will be permanently removed.'"
      :confirm-text="categoryToDelete?.tasks_count ? 'Cannot Delete' : 'Delete Category'"
      :loading="isDeleting"
      :disabled="(categoryToDelete?.tasks_count ?? 0) > 0"
      @confirm="handleDelete"
      @cancel="showDeleteModal = false"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Plus, X, Edit, Trash2, Folder } from 'lucide-vue-next';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import InputError from '@/components/InputError.vue';
import ConfirmationModal from '@/components/ConfirmationModal.vue';
import { useCategoryStore, useAppStore } from '@/stores';

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

interface CategoryFormData {
  name: string;
  description: string;
  color: string;
}

const props = defineProps<{
  categories: Category[];
}>();

const categoryStore = useCategoryStore();
const appStore = useAppStore();

// Form state
const showForm = ref(false);
const editingCategory = ref<Category | null>(null);
const form = ref<CategoryFormData>({
  name: '',
  description: '',
  color: '#6B7280',
});
const formErrors = ref<{[key: string]: string}>({});

// Delete state
const showDeleteModal = ref(false);
const categoryToDelete = ref<Category | null>(null);
const isDeleting = ref(false);

// Loading state
const isLoading = computed(() => appStore.isLoading);

// Predefined colors for easy selection
const predefinedColors = [
  '#EF4444', // Red
  '#F97316', // Orange  
  '#EAB308', // Yellow
  '#22C55E', // Green
  '#06B6D4', // Cyan
  '#3B82F6', // Blue
  '#8B5CF6', // Purple
  '#EC4899', // Pink
  '#64748B', // Slate
  '#DC2626', // Red-600
  '#EA580C', // Orange-600
  '#CA8A04', // Yellow-600
  '#16A34A', // Green-600
  '#0891B2', // Cyan-600
  '#2563EB', // Blue-600
  '#7C3AED', // Purple-600
  '#DB2777', // Pink-600
];

// Initialize store
onMounted(() => {
  categoryStore.initializeCategories(props.categories);
});

// Form methods
const openCreateForm = () => {
  editingCategory.value = null;
  form.value = {
    name: '',
    description: '',
    color: categoryStore.generateRandomColor(),
  };
  formErrors.value = {};
  showForm.value = true;
};

const editCategory = (category: Category) => {
  editingCategory.value = category;
  form.value = {
    name: category.name,
    description: category.description || '',
    color: category.color,
  };
  formErrors.value = {};
  showForm.value = true;
};

const closeForm = () => {
  showForm.value = false;
  editingCategory.value = null;
  formErrors.value = {};
};

const submitForm = async () => {
  // Basic validation
  formErrors.value = {};
  
  if (!form.value.name.trim()) {
    formErrors.value.name = 'Category name is required';
    return;
  }

  if (form.value.name.length > 255) {
    formErrors.value.name = 'Category name must be less than 255 characters';
    return;
  }

  try {
    if (editingCategory.value) {
      await categoryStore.updateCategory(editingCategory.value.id, {
        name: form.value.name.trim(),
        description: form.value.description.trim() || undefined,
        color: form.value.color,
      });
    } else {
      await categoryStore.createCategory({
        name: form.value.name.trim(),
        description: form.value.description.trim() || undefined,
        color: form.value.color,
      });
    }
    
    closeForm();
  } catch (error) {
    console.error('Error submitting form:', error);
  }
};

// Delete methods
const confirmDelete = (category: Category) => {
  categoryToDelete.value = category;
  showDeleteModal.value = true;
};

const handleDelete = async () => {
  if (!categoryToDelete.value) return;
  
  try {
    isDeleting.value = true;
    await categoryStore.deleteCategory(categoryToDelete.value.id);
    showDeleteModal.value = false;
    categoryToDelete.value = null;
  } catch (error) {
    console.error('Error deleting category:', error);
  } finally {
    isDeleting.value = false;
  }
};

// Utility methods
const formatDate = (dateString: string): string => {
  const date = new Date(dateString);
  return date.toLocaleDateString();
};
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
