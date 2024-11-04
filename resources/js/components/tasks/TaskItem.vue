<template>
  <div class="p-4 hover:bg-gray-50">
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-3">
        <input
          type="checkbox"
          :checked="!!task.completed_at"
          @change="toggleComplete"
          class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
        />
        <div class="min-w-0 flex-1">
          <div class="flex items-center space-x-2">
            <h3 
              class="text-sm font-medium truncate"
              :class="task.completed_at ? 'text-gray-400 line-through' : 'text-gray-900'"
            >
              {{ task.title }}
            </h3>
            <span
              v-if="task.priority"
              :class="priorityClasses[task.priority]"
              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
            >
              {{ task.priority }}
            </span>
          </div>
          <div class="mt-1 flex items-center space-x-4 text-xs text-gray-500">
            <div v-if="task.due_date" class="flex items-center">
              <CalendarIcon class="mr-1.5 h-4 w-4" />
              <span :class="{ 'text-red-600': isOverdue }">
                {{ formatDate(task.due_date) }}
              </span>
            </div>
            
            <div v-if="task.tags?.length" class="flex items-center">
              <TagIcon class="mr-1.5 h-4 w-4" />
              <span>{{ task.tags.length }} tags</span>
            </div>

            <div v-if="task.attachments?.length" class="flex items-center">
              <PaperClipIcon class="mr-1.5 h-4 w-4" />
              <span>{{ task.attachments.length }} files</span>
            </div>

            <div v-if="task.archived_at" class="flex items-center text-gray-500">
              <ArchiveBoxIcon class="mr-1.5 h-4 w-4" />
              <span>Archived {{ formatDate(task.archived_at) }}</span>
            </div>
          </div>
        </div>
      </div>
      <div class="ml-4 flex items-center space-x-4">
        <button
          @click="$emit('view', task)"
          class="text-gray-400 hover:text-gray-600"
          title="View Details"
        >
          <EyeIcon class="h-5 w-5" />
        </button>

        <button
          @click="handleArchiveToggle"
          class="text-gray-400 hover:text-gray-600"
          :title="task.archived_at ? 'Restore Task' : 'Archive Task'"
        >
          <ArchiveBoxArrowDownIcon v-if="task.archived_at" class="h-5 w-5" />
          <ArchiveBoxIcon v-else class="h-5 w-5" />
        </button>

        <button
          @click="confirmDelete"
          class="text-gray-400 hover:text-red-600"
          title="Delete Task"
        >
          <TrashIcon class="h-5 w-5" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { 
  CalendarIcon, 
  TagIcon, 
  PaperClipIcon,
  EyeIcon,
  TrashIcon,
  ArchiveBoxIcon,
  ArchiveBoxArrowDownIcon
} from '@heroicons/vue/24/outline';
import dayjs from 'dayjs';
import { useStore } from 'vuex';

const props = defineProps({
  task: {
    type: Object,
    required: true
  }
});
const store = useStore();

const emit = defineEmits(['update', 'delete', 'view', 'archive', 'restore']);

const priorityClasses = {
  urgent: 'bg-red-100 text-red-800',
  high: 'bg-orange-100 text-orange-800',
  normal: 'bg-blue-100 text-blue-800',
  low: 'bg-green-100 text-green-800'
};

const isOverdue = computed(() => {
  if (!props.task.due_date || props.task.completed_at) return false;
  return dayjs(props.task.due_date).isBefore(dayjs(), 'day');
});

const formatDate = (date) => {
  return dayjs(date).format('MMM D, YYYY');
};

const handleArchiveToggle = async () => {
  try {
    if (props.task.archived_at) {
      await store.dispatch('tasks/restoreTask', props.task.id);
      emit('restore', props.task.id);
    } else {
      await store.dispatch('tasks/archiveTask', props.task.id);
      emit('archive', props.task.id);
    }
  } catch (e) {
    if (e.response?.status === 404) {
      console.error('Task not found');
    } else if (e.response?.status === 401) {
      console.error('Unauthorized to modify this task');
    } else {
      console.error('Failed to toggle archive status:', e);
    }
  }
};

const toggleComplete = async () => {
  try {
    await store.dispatch('tasks/completeTask', {
      id: props.task.id,
      completed_at: !props.task.completed_at
    });
  } catch (e) {
    console.error('Failed to toggle task completion:', e);
  }
};

const confirmDelete = () => {
  if (confirm('Are you sure you want to delete this task?')) {
    emit('delete', props.task.id);
  }
};
</script>