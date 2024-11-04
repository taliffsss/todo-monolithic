<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div class="flex items-center space-x-4">
        <h1 class="text-2xl font-semibold text-gray-900">
          {{ showArchived ? 'Archived Tasks' : 'My Tasks' }}
        </h1>
        <button
          @click="toggleArchiveView"
          class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          <ArchiveBoxIcon v-if="!showArchived" class="h-4 w-4 mr-2" />
          <ArchiveBoxArrowDownIcon v-else class="h-4 w-4 mr-2" />
          {{ showArchived ? 'View Active Tasks' : 'View Archived' }}
        </button>
      </div>
    </div>

    <TaskFilters
      :filters="filters"
      @update="handleFiltersUpdate"
    />

    <div class="bg-white shadow rounded-lg">
      <div v-if="loading" class="text-center py-12">
        <LoadingSpinner size="lg" />
        <p class="mt-2 text-sm text-gray-500">Loading tasks...</p>
      </div>

      <div
        v-else-if="!tasks?.length"
        class="text-center py-12"
      >
        <DocumentIcon class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">
          {{ showArchived ? 'No archived tasks' : 'No tasks' }}
        </h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ showArchived ? 'Archived tasks will appear here' : 'Get started by creating a new task' }}
        </p>
      </div>

      <template v-else>
        <div class="divide-y divide-gray-200">
          <TaskItem
            v-for="task in tasks"
            :key="task.id"
            :task="task"
            @update="handleTaskUpdate"
            @delete="handleTaskDelete"
            @view="showTaskDetails"
            @archive="handleTaskArchive"
            @restore="handleTaskRestore"
          />
        </div>

        <div
          v-if="totalPages > 1"
          class="flex justify-center space-x-2 p-4 border-t border-gray-200"
        >
          <button
            v-for="page in totalPages"
            :key="page"
            @click="changePage(page)"
            :class="[
              'px-3 py-1 rounded-md text-sm',
              currentPage === page
                ? 'bg-blue-600 text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            ]"
          >
            {{ page }}
          </button>
        </div>
      </template>
    </div>

    <TaskDetails
      v-if="selectedTask"
      :task="selectedTask"
      @close="closeTaskDetails"
      @edit="showEditTask"
      @archive="handleTaskArchive"
      @restore="handleTaskRestore"
    />

    <CreateTaskModal
      v-if="showTaskModal"
      :task="editingTask"
      @close="closeTaskModal"
      @save="handleTaskSave"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useStore } from 'vuex';
import { DocumentIcon, ArchiveBoxIcon, ArchiveBoxArrowDownIcon } from '@heroicons/vue/24/outline';
import TaskItem from '@/components/tasks/TaskItem.vue';
import TaskFilters from '@/components/tasks/TaskFilters.vue';
import TaskDetails from '@/components/tasks/TaskDetails.vue';
import CreateTaskModal from '@/components/tasks/CreateTaskModal.vue';
import LoadingSpinner from '@/components/shared/LoadingSpinner.vue';

const store = useStore();

const filters = ref({
  search: '',
  priority: '',
  status: '',
  dateFrom: '',
  dateTo: '',
  sortBy: 'created_at',
  sortDirection: 'desc'
});

const showTaskModal = ref(false);
const selectedTask = ref(null);
const editingTask = ref(null);
const showArchived = ref(false);

const tasks = computed(() => {
  const allTasks = store.getters['tasks/getTasks'];
  return showArchived.value 
    ? allTasks
    : allTasks.filter(task => task.archived_at !== null && task.archived_at !== '');
});

const loading = computed(() => store.getters['tasks/isLoading']);
const pagination = computed(() => store.getters['tasks/getPagination']);
const currentPage = computed(() => pagination.value.currentPage);
const totalPages = computed(() => pagination.value.totalPages);

const toggleArchiveView = () => {
  showArchived.value = !showArchived.value;
  fetchTasks(1);
};

const handleTaskArchive = async (taskId) => {
  try {
    await store.dispatch('tasks/archiveTask', taskId);
    await store.dispatch('tasks/fetchTasks', {
      ...filters.value,
      page: currentPage.value,
      archived: showArchived.value
    });
    if (selectedTask.value?.id === taskId) {
      closeTaskDetails();
    }
  } catch (e) {
    if (e.response?.status === 404) {
      console.error('Task not found');
    } else if (e.response?.status === 401) {
      console.error('Unauthorized to archive this task');
    } else {
      console.error('Error archiving task:', e);
    }
  }
};

const handleTaskRestore = async (taskId) => {
  try {
    await store.dispatch('tasks/restoreTask', taskId);
    await store.dispatch('tasks/fetchTasks', {
      ...filters.value,
      page: currentPage.value,
      archived: showArchived.value
    });
    if (selectedTask.value?.id === taskId) {
      closeTaskDetails();
    }
  } catch (e) {
    if (e.response?.status === 404) {
      console.error('Task not found');
    } else if (e.response?.status === 401) {
      console.error('Unauthorized to restore this task');
    } else {
      console.error('Error restoring task:', e);
    }
  }
};

const handleFiltersUpdate = async (newFilters) => {
  filters.value = { ...filters.value, ...newFilters };
  await fetchTasks(1);
};

const handleTaskUpdate = async (task) => {
  try {
    await store.dispatch('tasks/updateTask', {
      taskId: task.id,
      data: task
    });
  } catch (e) {
    console.error('Error updating task:', e);
  }
};

const handleTaskDelete = async (taskId) => {
  try {
    await store.dispatch('tasks/deleteTask', taskId);
  } catch (e) {
    console.error('Error deleting task:', e);
  }
};

const handleTaskSave = async (taskData) => {
  try {
    if (editingTask.value) {
      await store.dispatch('tasks/updateTask', {
        taskId: editingTask.value.id,
        data: taskData
      });
    } else {
      await store.dispatch('tasks/createTask', taskData);
    }
    closeTaskModal();
    await fetchTasks();
  } catch (e) {
    console.error('Error saving task:', e);
  }
};

const showTaskDetails = (task) => {
  selectedTask.value = task;
};

const closeTaskDetails = () => {
  selectedTask.value = null;
};

const showEditTask = (task) => {
  editingTask.value = task;
  showTaskModal.value = true;
  closeTaskDetails();
};

const closeTaskModal = () => {
  showTaskModal.value = false;
  editingTask.value = null;
};

const changePage = (page) => {
  fetchTasks(page);
};

const fetchTasks = async (page = 1) => {
  try {
    await store.dispatch('tasks/fetchTasks', {
      ...filters.value,
      page,
      archived: showArchived.value
    });
  } catch (e) {
    console.error('Error fetching tasks:', e);
  }
};

watch(showArchived, () => {
  fetchTasks(1);
});

onMounted(() => {
  fetchTasks();
});
</script>