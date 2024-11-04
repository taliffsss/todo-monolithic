<template>
  <Modal :show="true" @close="$emit('close')" size="lg">
    <div class="p-6">
      <div class="flex justify-between items-start">
        <div>
          <h3 class="text-lg font-medium text-gray-900">{{ task.title }}</h3>
          <span 
            :class="priorityClasses[task.priority]"
            class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
          >
            {{ task.priority }}
          </span>
        </div>

        <div class="flex items-center space-x-2">
          <button
            @click="$emit('edit', task)"
            class="text-gray-400 hover:text-gray-600"
          >
            <PencilIcon class="h-5 w-5" />
          </button>
          <button
            @click="$emit('close')"
            class="text-gray-400 hover:text-gray-600"
          >
            <XMarkIcon class="h-5 w-5" />
          </button>
        </div>
      </div>

      <div class="mt-4 space-y-4">
        <div>
          <h4 class="text-sm font-medium text-gray-700">Description</h4>
          <p class="mt-1 text-sm text-gray-500">{{ task.description }}</p>
        </div>
        
        <div class="grid grid-cols-2 gap-4">
          <div>
            <h4 class="text-sm font-medium text-gray-700">Due Date</h4>
            <p class="mt-1 text-sm text-gray-500">
              {{ task.due_date ? formatDate(task.due_date) : 'No due date' }}
            </p>
          </div>
          <div>
            <h4 class="text-sm font-medium text-gray-700">Created</h4>
            <p class="mt-1 text-sm text-gray-500">{{ formatDate(task.created_at) }}</p>
          </div>
        </div>

        <div v-if="task.tags?.length">
          <h4 class="text-sm font-medium text-gray-700">Tags</h4>
          <div class="mt-1 flex flex-wrap gap-2">
            <span
              v-for="tag in task.tags"
              :key="tag.id"
              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
            >
              {{ tag.name }}
            </span>
          </div>
        </div>

        <div v-if="task.attachments?.length">
          <h4 class="text-sm font-medium text-gray-700">Attachments</h4>
          <ul class="mt-2 divide-y divide-gray-200">
            <li
              v-for="attachment in task.attachments"
              :key="attachment.id"
              class="py-2 flex justify-between items-center"
            >
              <div class="flex items-center">
                <DocumentIcon class="h-5 w-5 text-gray-400 mr-2" />
                <span class="text-sm text-gray-600">{{ attachment.file_name }}</span>
              </div>
              <button
                @click="downloadAttachment(attachment)"
                class="text-sm text-blue-600 hover:text-blue-800"
              >
                Download
              </button>
            </li>
          </ul>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div v-if="task.completed_at">
            <h4 class="text-sm font-medium text-gray-700">Completed</h4>
            <p class="mt-1 text-sm text-gray-500">{{ formatDate(task.completed_at) }}</p>
          </div>
          <div v-if="task.archived_at">
            <h4 class="text-sm font-medium text-gray-700">Archived</h4>
            <p class="mt-1 text-sm text-gray-500">{{ formatDate(task.archived_at) }}</p>
          </div>
        </div>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { DocumentIcon, PencilIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import Modal from '@/components/shared/Modal.vue';
import dayjs from 'dayjs';
import { useStore } from 'vuex';

const props = defineProps({
  task: {
    type: Object,
    required: true
  }
});
const store = useStore();

const emit = defineEmits(['close', 'edit']);

const priorityClasses = {
  urgent: 'bg-red-100 text-red-800',
  high: 'bg-orange-100 text-orange-800',
  normal: 'bg-blue-100 text-blue-800',
  low: 'bg-green-100 text-green-800'
};

const formatDate = (date) => {
  return date ? dayjs(date).format('MMM D, YYYY h:mm A') : '';
};

const downloadAttachment = async (attachment) => {
  try {
        await store.dispatch('tasks/downloadAttachment', {
            taskId: props.task.id,
            attachmentId: attachment.id,
            fileName: attachment.file_name
        });
    } catch (e) {
        console.error('Download failed:', e);
    }
};
</script>