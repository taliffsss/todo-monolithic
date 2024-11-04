<template>
  <div class="bg-white p-4 rounded-lg shadow space-y-4">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <div>
        <input
          v-model="localFilters.search"
          type="text"
          placeholder="Search tasks..."
          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
        />
      </div>
      <div>
        <select
          v-model="localFilters.priority"
          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
        >
          <option value="">All Priorities</option>
          <option value="urgent">Urgent</option>
          <option value="high">High</option>
          <option value="normal">Normal</option>
          <option value="low">Low</option>
        </select>
      </div>
      <div>
        <select
          v-model="localFilters.status"
          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
        >
          <option value="">All Status</option>
          <option value="completed">Completed</option>
          <option value="todo">Todo</option>
          <option value="archived">Archived</option>
        </select>
      </div>
      <div>
        <select
          v-model="localFilters.sortBy"
          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
        >
          <option value="created_at">Created Date</option>
          <option value="due_date">Due Date</option>
          <option value="priority">Priority</option>
        </select>
      </div>
      
      <div class="sm:col-span-2">
        <div class="flex space-x-2">
          <input
            v-model="localFilters.dateFrom"
            type="date"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            placeholder="From"
          />
          <input
            v-model="localFilters.dateTo"
            type="date"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            placeholder="To"
          />
        </div>
      </div>
      
      <div>
        <select
          v-model="localFilters.sortDirection"
          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
        >
          <option value="desc">Descending</option>
          <option value="asc">Ascending</option>
        </select>
      </div>
    </div>
    
    <div class="flex justify-end">
      <button
        @click="applyFilters"
        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
      >
        Apply Filters
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  filters: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['update']);

const localFilters = ref({ ...props.filters });

const applyFilters = () => {
  emit('update', { ...localFilters.value });
};

watch(() => props.filters, (newFilters) => {
  localFilters.value = { ...newFilters };
}, { deep: true });
</script>