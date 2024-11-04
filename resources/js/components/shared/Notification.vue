<template>
  <TransitionRoot appear :show="show" as="template">
    <div class="fixed bottom-4 right-4 z-50">
      <TransitionChild
        as="template"
        enter="transform ease-out duration-300 transition"
        enter-from="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        enter-to="translate-y-0 opacity-100 sm:translate-x-0"
        leave="transition ease-in duration-100"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div
          v-if="message"
          class="max-w-sm w-full shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden"
          :class="{
            'bg-green-500': type === 'success',
            'bg-red-500': type === 'error',
            'bg-yellow-500': type === 'warning',
            'bg-blue-500': type === 'info'
          }"
        >
          <div class="p-4">
            <div class="flex items-center">
              <div class="flex-1 text-sm font-medium text-white">
                {{ message }}
              </div>
              <div class="ml-4 flex-shrink-0 flex">
                <button
                  @click="handleClose"
                  class="inline-flex text-white hover:text-gray-200 focus:outline-none focus:ring-2 focus:ring-white"
                >
                  <span class="sr-only">Close</span>
                  <XMarkIcon class="h-5 w-5" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </TransitionChild>
    </div>
  </TransitionRoot>
</template>

<script setup>
import { TransitionRoot, TransitionChild } from '@headlessui/vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';
import { onMounted, onUnmounted } from 'vue';

const props = defineProps({
  show: {
    type: Boolean,
    required: true
  },
  message: {
    type: String,
    required: true
  },
  type: {
    type: String,
    default: 'success',
    validator: (value) => ['success', 'error', 'warning', 'info'].includes(value)
  },
  duration: {
    type: Number,
    default: 3000
  }
});

const emit = defineEmits(['close']);

let timeout = null;

const handleClose = () => {
  if (timeout) {
    clearTimeout(timeout);
  }
  emit('close');
};

onMounted(() => {
  if (props.duration > 0 && props.show) {
    timeout = setTimeout(() => {
      handleClose();
    }, props.duration);
  }
});

onUnmounted(() => {
  if (timeout) {
    clearTimeout(timeout);
  }
});
</script>