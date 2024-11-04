<template>
  <Modal :show="true" @close="$emit('close')">
    <div class="p-6">
      <h3 class="text-lg font-medium leading-6 text-gray-900">
        {{ task ? 'Edit Task' : 'Create New Task' }}
      </h3>

      <form @submit.prevent="handleSubmit" class="mt-4 space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Title</label>
          <input
            v-model="form.title"
            type="text"
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700">Description</label>
          <textarea
            v-model="form.description"
            rows="3"
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          ></textarea>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Due Date</label>
          <input
            v-model="form.due_date"
            type="date"
            :min="minDate"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700">Priority</label>
          <select
            v-model="form.priority"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option value="urgent">Urgent</option>
            <option value="high">High</option>
            <option value="normal">Normal</option>
            <option value="low">Low</option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700">Tags</label>
          <TagInput 
            v-model="form.tags"
            @update:modelValue="handleTagsChange"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700">Attachments</label>
          <FileUpload 
            v-model="form.attachments"
            @update:modelValue="handleAttachmentsChange"
          />
        </div>
        
        <div class="mt-6 flex justify-end space-x-3">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="isSubmitting"
            class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
          >
            {{ isSubmitting ? 'Saving...' : (task ? 'Update' : 'Create') }}
          </button>
        </div>
      </form>
    </div>
  </Modal>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import Modal from '@/components/shared/Modal.vue';
import TagInput from '@/components/shared/TagInput.vue';
import FileUpload from '@/components/shared/FileUpload.vue';

const props = defineProps({
  task: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['close', 'save']);

const isSubmitting = ref(false);
const form = ref({
  title: props.task?.title || '',
  description: props.task?.description || '',
  due_date: props.task?.due_date || '',
  priority: props.task?.priority || 'normal',
  tags: [],
  attachments: []
});

onMounted(() => {
  if (props.task?.tags) {
    form.value.tags = props.task.tags.map(tag => tag.name);
  }
});

const minDate = computed(() => {
  const today = new Date();
  return today.toISOString().split('T')[0];
});

const handleTagsChange = (newTags) => {
  form.value.tags = newTags;
  console.log(newTags);
};

const handleAttachmentsChange = (newFiles) => {
  form.value.attachments = newFiles;
};

const handleSubmit = async () => {
  if (isSubmitting.value) return;

  isSubmitting.value = true;
  try {
    const formData = new FormData();
    
    formData.append('title', form.value.title);
    formData.append('description', form.value.description);
    formData.append('priority', form.value.priority);
    
    if (form.value.due_date) {
      formData.append('due_date', form.value.due_date);
    }

    if (form.value.tags.length > 0) {
      form.value.tags.forEach((tag) => {
        formData.append('tags[]', tag);
      });
    }

    if (form.value.attachments.length > 0) {
      form.value.attachments.forEach((file) => {
        formData.append('attachments[]', file);
      });
    }

    await emit('save', formData);
  } catch (error) {
    console.error('Error saving task:', error);
  } finally {
    isSubmitting.value = false;
  }
};
</script>