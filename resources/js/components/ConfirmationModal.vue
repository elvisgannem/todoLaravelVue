<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent class="sm:max-w-md">
      <DialogHeader>
        <DialogTitle class="flex items-center gap-2">
          <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/20">
            <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
          </div>
          {{ title }}
        </DialogTitle>
        <DialogDescription class="text-left">
          {{ description }}
        </DialogDescription>
      </DialogHeader>
      
      <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end sm:gap-2">
        <Button 
          variant="outline" 
          @click="$emit('cancel')"
          class="sm:w-auto"
        >
          Cancel
        </Button>
        <Button 
          variant="destructive" 
          @click="$emit('confirm')"
          :disabled="loading"
          class="sm:w-auto"
        >
          <svg v-if="loading" class="mr-2 h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ confirmText }}
        </Button>
      </div>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import Button from '@/components/ui/button/Button.vue';

interface Props {
  open: boolean;
  title?: string;
  description?: string;
  confirmText?: string;
  loading?: boolean;
}

withDefaults(defineProps<Props>(), {
  title: 'Are you sure?',
  description: 'This action cannot be undone.',
  confirmText: 'Delete',
  loading: false,
});

defineEmits<{
  'update:open': [value: boolean];
  confirm: [];
  cancel: [];
}>();
</script>
