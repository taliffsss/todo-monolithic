<template>
  <Modal :show="true" @close="$emit('close')">
    <div class="p-6">
      <h3 class="text-lg font-medium leading-6 text-gray-900">
        {{ task ? 'Edit Task' : 'Create Task' }}
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
          <TagInput v-model="form.tags" />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700">Attachments</label>
          <div class="mt-1">
            <div v-if="task?.attachments?.length" class="mb-2 space-y-2">
              <div
                v-for="attachment in task.attachments"
                :key="attachment.id"
                class="flex items-center justify-between p-2 bg-gray-50 rounded-md"
              >
                <span class="text-sm text-gray-600">{{ attachment.file_name }}</span>
                <button
                  type="button"
                  @click="removeExistingAttachment(attachment.id)"
                  class="text-red-500 hover:text-red-700"
                >
                  Remove
                </button>
              </div>
            </div>
            
            <FileUpload v-model="form.attachments" />
          </div>
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
import { ref, computed } from 'vue';
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
  tags: props.task?.tags?.map(t => t.name) || [],
  attachments: [],
  removed_attachments: []
});

const minDate = computed(() => {
  const today = new Date();
  return today.toISOString().split('T')[0];
});

const removeExistingAttachment = (attachmentId) => {
  form.value.removed_attachments.push(attachmentId);
};

const handleSubmit = async () => {
  if (isSubmitting.value) return;
  
  isSubmitting.value = true;
  try {
    const formData = new FormData();
    
    Object.keys(form.value).forEach(key => {
      if (key !== 'attachments' && key !== 'tags' && key !== 'removed_attachments') {
        formData.append(key, form.value[key]);
      }
    });

    form.value.tags.forEach(tag => {
      formData.append('tags[]', tag);
    });

    form.value.attachments.forEach(file => {
      formData.append('attachments[]', file);
    });

    form.value.removed_attachments.forEach(id => {
      formData.append('removed_attachments[]', id);
    });

    await emit('save', formData);
  } catch (e) {
    console.error('Error submitting form:', e);
  } finally {
    isSubmitting.value = false;
  }
};

watch(() => props.task, (newTask) => {
  if (newTask) {
    form.value = {
      title: newTask.title,
      description: newTask.description,
      due_date: newTask.due_date || '',
      priority: newTask.priority || 'normal',
      tags: newTask.tags?.map(t => t.name) || [],
      attachments: [],
      removed_attachments: []
    };
  }
}, { immediate: true });
</script>