<template>
  <div class="space-y-2">
    <div class="flex flex-wrap gap-2">
      <span
        v-for="tag in modelValue"
        :key="tag"
        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-blue-100 text-blue-800"
      >
        {{ tag }}
        <button
          type="button"
          @click="removeTag(tag)"
          class="ml-1.5 inline-flex items-center justify-center w-4 h-4 rounded-full hover:bg-blue-200"
        >
          ×
        </button>
      </span>
    </div>
    
    <div class="flex">
      <input
        v-model="newTag"
        @keydown.enter.prevent="addTag"
        type="text"
        placeholder="Add tag..."
        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
      />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['update:modelValue']);

const newTag = ref('');

const addTag = () => {
  const tag = newTag.value.trim();
  if (tag && !props.modelValue.includes(tag)) {
    emit('update:modelValue', [...props.modelValue, tag]);
  }
  newTag.value = '';
};

const removeTag = (tagToRemove) => {
  emit('update:modelValue', props.modelValue.filter(tag => tag !== tagToRemove));
};
</script>