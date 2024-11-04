<template>
  <div class="space-y-2">
    <input
      type="file"
      @change="handleFileChange"
      multiple
      class="block w-full text-sm text-gray-500
        file:mr-4 file:py-2 file:px-4
        file:rounded-full file:border-0
        file:text-sm file:font-semibold
        file:bg-blue-50 file:text-blue-700
        hover:file:bg-blue-100"
      :accept="acceptedTypes"
    />
    
    <div v-if="modelValue?.length" class="space-y-2">
      <div
        v-for="(file, index) in modelValue"
        :key="index"
        class="flex items-center justify-between rounded-lg bg-gray-50 p-2"
      >
        <div class="flex items-center space-x-2">
          <span class="text-sm text-gray-600">{{ file.name }}</span>
          <span class="text-xs text-gray-400">({{ formatFileSize(file.size) }})</span>
        </div>
        <button
          type="button"
          @click="removeFile(index)"
          class="text-red-500 hover:text-red-700"
        >
          Remove
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['update:modelValue']);

const acceptedTypes = '.jpg,.jpeg,.png,.svg,.mp4,.csv,.txt,.doc,.docx';

const handleFileChange = (event) => {
  const files = Array.from(event.target.files);
  emit('update:modelValue', [...props.modelValue, ...files]);
};

const removeFile = (index) => {
  const files = [...props.modelValue];
  files.splice(index, 1);
  emit('update:modelValue', files);
};

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};
</script>