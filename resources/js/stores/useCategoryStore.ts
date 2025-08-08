import { defineStore } from 'pinia';
import { router } from '@inertiajs/vue3';
import { useAppStore } from './useAppStore';

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
  description?: string;
  color: string;
}

export const useCategoryStore = defineStore('category', {
  state: () => ({
    categories: [] as Category[],
  }),

  getters: {
    getCategoryById: (state) => (id: number) => {
      return state.categories.find(category => category.id === id);
    },

    getCategoryBySlug: (state) => (slug: string) => {
      return state.categories.find(category => category.slug === slug);
    },

    categoriesByName: (state) => {
      return [...state.categories].sort((a, b) => a.name.localeCompare(b.name));
    },

    categoriesWithTasks: (state) => {
      return state.categories.filter(category => (category.tasks_count ?? 0) > 0);
    },

    unusedCategories: (state) => {
      return state.categories.filter(category => (category.tasks_count ?? 0) === 0);
    },
  },

  actions: {
    initializeCategories(categories: Category[]) {
      this.categories = categories;
    },

    async createCategory(data: CategoryFormData) {
      const appStore = useAppStore();
      
      try {
        appStore.setLoading(true);

        // Optimistic update
        const tempCategory: Category = {
          id: Date.now(), // Temporary ID
          name: data.name,
          slug: data.name.toLowerCase().replace(/\s+/g, '-'),
          description: data.description || null,
          color: data.color,
          user_id: 0, // Will be set by server
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString(),
          tasks_count: 0,
        };

        this.categories.push(tempCategory);

        const response = await new Promise<any>((resolve, reject) => {
          router.post('/categories', data, {
            onSuccess: (page) => resolve(page),
            onError: (errors) => reject(errors),
            preserveState: true,
            preserveScroll: true,
          });
        });

        // Replace temp category with real one from server
        const categoryIndex = this.categories.findIndex(c => c.id === tempCategory.id);
        if (categoryIndex !== -1) {
          // The server should return the created category in the response
          // For now, we'll update the temp category with a proper ID
          this.categories[categoryIndex].id = response.props?.category?.id || tempCategory.id;
        }

        appStore.addNotification({
          type: 'success',
          message: 'Category created successfully!',
        });

      } catch (error) {
        // Remove optimistic update on error
        const tempIndex = this.categories.findIndex(c => c.id === Date.now());
        if (tempIndex !== -1) {
          this.categories.splice(tempIndex, 1);
        }

        appStore.addNotification({
          type: 'error',
          message: 'Failed to create category. Please try again.',
        });

        throw error;
      } finally {
        appStore.setLoading(false);
      }
    },

    async updateCategory(id: number, data: Partial<CategoryFormData>) {
      const appStore = useAppStore();
      
      // Store original for rollback
      const categoryIndex = this.categories.findIndex(c => c.id === id);
      if (categoryIndex === -1) throw new Error('Category not found');

      const originalCategory = { ...this.categories[categoryIndex] };

      try {
        appStore.setLoading(true);

        // Optimistic update
        Object.assign(this.categories[categoryIndex], {
          ...data,
          updated_at: new Date().toISOString(),
        });

        await new Promise<void>((resolve, reject) => {
          router.patch(`/categories/${id}`, data, {
            onSuccess: () => resolve(),
            onError: (errors) => reject(errors),
            preserveState: true,
            preserveScroll: true,
          });
        });

        appStore.addNotification({
          type: 'success',
          message: 'Category updated successfully!',
        });

      } catch (error) {
        // Rollback on error
        this.categories[categoryIndex] = originalCategory;

        appStore.addNotification({
          type: 'error',
          message: 'Failed to update category. Please try again.',
        });

        throw error;
      } finally {
        appStore.setLoading(false);
      }
    },

    async deleteCategory(id: number) {
      const appStore = useAppStore();
      
      // Store original for rollback
      const categoryIndex = this.categories.findIndex(c => c.id === id);
      if (categoryIndex === -1) throw new Error('Category not found');

      const originalCategory = this.categories[categoryIndex];

      try {
        appStore.setLoading(true);

        // Optimistic update
        this.categories.splice(categoryIndex, 1);

        await new Promise<void>((resolve, reject) => {
          router.delete(`/categories/${id}`, {
            onSuccess: () => resolve(),
            onError: (errors) => reject(errors),
            preserveState: true,
            preserveScroll: true,
          });
        });

        appStore.addNotification({
          type: 'success',
          message: 'Category deleted successfully!',
        });

      } catch (error) {
        // Rollback on error
        if (originalCategory) {
          this.categories.push(originalCategory);
        }

        appStore.addNotification({
          type: 'error',
          message: 'Failed to delete category. Please try again.',
        });

        throw error;
      } finally {
        appStore.setLoading(false);
      }
    },

    // Helper method to generate random colors
    generateRandomColor(): string {
      const colors = [
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
      return colors[Math.floor(Math.random() * colors.length)];
    },
  },
});
