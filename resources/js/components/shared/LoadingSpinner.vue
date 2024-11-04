<template>
  <div class="flex justify-center items-center">
    <div 
      class="animate-spin rounded-full h-8 w-8 border-4 border-blue-500 border-t-transparent"
      :class="[size === 'sm' ? 'h-4 w-4' : size === 'lg' ? 'h-12 w-12' : 'h-8 w-8']"
    ></div>
  </div>
</template>

<script setup>
defineProps({
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  }
});
</script>