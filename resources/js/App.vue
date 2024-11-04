<template>
  <div class="min-h-screen bg-gray-100">
    <nav v-if="isAuthenticated" class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex">
            <router-link to="/" class="flex items-center">
              <span class="text-xl font-bold text-gray-900">TODO App</span>
            </router-link>
          </div>
          
          <div class="flex items-center space-x-4">
            <button
              @click="openCreateModal"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <PlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
              New Task
            </button>
            
            <!-- <button
              @click="toggleShowArchived"
              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <ArchiveBoxIcon v-if="!showArchived" class="h-5 w-5 mr-2" aria-hidden="true" />
              <ArchiveBoxArrowDownIcon v-else class="h-5 w-5 mr-2" aria-hidden="true" />
              {{ showArchived ? 'Show Active' : 'Show Archived' }}
            </button> -->
            
            <div class="ml-3 relative">
              <div class="flex items-center space-x-3">
                <span v-if="user" class="text-sm font-medium text-gray-700">
                  {{ user.name }}
                </span>
                <button
                  @click="handleLogout"
                  class="text-gray-700 hover:text-gray-900 font-medium text-sm"
                >
                  Logout
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div v-if="!initialized" class="flex justify-center py-12">
        <LoadingSpinner size="lg" />
      </div>
      <router-view v-else :show-archived="showArchived" />
    </main>
    
    <CreateTaskModal
      v-if="showCreateModal"
      @close="closeCreateModal"
      @save="handleTaskCreate"
    />
    
    <Notification
      v-if="notification.message"
      :message="notification.message"
      :type="notification.type"
      :show="notification.show"
      @close="closeNotification"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useStore } from 'vuex';
import { useRouter } from 'vue-router';
import { PlusIcon, ArchiveBoxIcon, ArchiveBoxArrowDownIcon } from '@heroicons/vue/24/outline';
import CreateTaskModal from '@/components/tasks/CreateTaskModal.vue';
import LoadingSpinner from '@/components/shared/LoadingSpinner.vue';
import Notification from '@/components/shared/Notification.vue';

const store = useStore();
const router = useRouter();

const initialized = ref(false);
const showCreateModal = ref(false);
const showArchived = ref(false);
const notification = ref({
  show: false,
  message: '',
  type: 'success'
});

let notificationTimeout = null;

const isAuthenticated = computed(() => store.getters['auth/isAuthenticated']);
const user = computed(() => store.getters['auth/user']);

const handleLogout = async () => {
  try {
    await store.dispatch('auth/logout');
    router.push('/login');
    showNotification('Logged out successfully');
  } catch (e) {
    showNotification('Failed to logout', 'error');
  }
};

const toggleShowArchived = () => {
  showArchived.value = !showArchived.value;
  store.dispatch('tasks/fetchTasks', { archived: showArchived.value });
};

const openCreateModal = () => {
  showCreateModal.value = true;
};

const closeCreateModal = () => {
  showCreateModal.value = false;
};

const handleTaskCreate = async (taskData) => {
  try {
    await store.dispatch('tasks/createTask', taskData);
    closeCreateModal();
    showNotification('Task created successfully');
    if (router.currentRoute.value.name === 'tasks') {
      await store.dispatch('tasks/fetchTasks', { archived: showArchived.value });
    }
  } catch (e) {
    showNotification(e.response?.data?.message || 'Failed to create task', 'error');
  }
};

const showNotification = (message, type = 'success') => {
  if (notificationTimeout) {
    clearTimeout(notificationTimeout);
  }

  notification.value = {
    show: true,
    message,
    type
  };

  notificationTimeout = setTimeout(() => {
    closeNotification();
  }, 3000);
};

const closeNotification = () => {
  notification.value.show = false;
  notification.value.message = '';
  
  if (notificationTimeout) {
    clearTimeout(notificationTimeout);
    notificationTimeout = null;
  }
};

const initializeApp = async () => {
  if (isAuthenticated.value) {
    try {
      await store.dispatch('auth/checkAuth');
      await store.dispatch('tags/fetchTags');
      await store.dispatch('tasks/fetchTasks', { archived: showArchived.value });
    } catch (e) {
      console.error('Auth check failed:', e);
      if (router.currentRoute.value.meta.requiresAuth) {
        router.push('/login');
      }
    }
  }
  initialized.value = true;
};

const handleError = (error) => {
  console.error('Application error:', error);
  showNotification(
    error.response?.data?.message || 'An error occurred',
    'error'
  );
};

watch(() => isAuthenticated.value, (newValue) => {
  if (!newValue && router.currentRoute.value.meta.requiresAuth) {
    router.push('/login');
  }
}, { immediate: true });

onMounted(async () => {
  try {
    await initializeApp();
  } catch (e) {
    handleError(e);
  }
});

onUnmounted(() => {
  if (notificationTimeout) {
    clearTimeout(notificationTimeout);
  }
});
</script>

<style>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>