<script setup lang="ts">
import { ref, computed, watch } from 'vue';

interface Option {
  value: number | string;
  label: string;
}

const props = defineProps<{
  modelValue: (number | string)[];
  options: Option[];
  placeholder?: string;
  label?: string;
  error?: string;
}>();

const emit = defineEmits(['update:modelValue']);

const selectedValues = ref<(number | string)[]>([]);
const isOpen = ref(false);

// Initialize selectedValues from modelValue
watch(() => props.modelValue, (newValue) => {
  selectedValues.value = [...newValue];
}, { immediate: true });

// Update modelValue when selectedValues changes
watch(selectedValues, (newValue) => {
  emit('update:modelValue', newValue);
});

// Toggle dropdown
const toggleDropdown = () => {
  isOpen.value = !isOpen.value;
};

// Toggle selection of an option
const toggleOption = (value: number | string) => {
  const index = selectedValues.value.indexOf(value);
  if (index === -1) {
    selectedValues.value.push(value);
  } else {
    selectedValues.value.splice(index, 1);
  }
};

// Check if an option is selected
const isSelected = (value: number | string) => {
  return selectedValues.value.includes(value);
};

// Get display text for the dropdown
const displayText = computed(() => {
  if (selectedValues.value.length === 0) {
    return props.placeholder || 'Select options';
  }
  return `${selectedValues.value.length} selected`;
});

// Close dropdown when clicking outside
const dropdownRef = ref<HTMLDivElement | null>(null);
const handleClickOutside = (event: MouseEvent) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
    isOpen.value = false;
  }
};

// Add click outside listener
onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

// Remove click outside listener
onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
  <div ref="dropdownRef" class="relative w-full">
    <label v-if="label" class="block text-sm font-medium text-gray-700 mb-1">{{ label }}</label>
    <button 
      type="button"
      @click="toggleDropdown"
      class="w-full flex justify-between items-center px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm text-sm"
      :class="{ 'border-red-500': error }"
    >
      <span>{{ displayText }}</span>
      <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
      </svg>
    </button>
    
    <div v-if="isOpen" class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base overflow-auto focus:outline-none sm:text-sm">
      <div v-for="option in options" :key="option.value" class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-gray-100" @click="toggleOption(option.value)">
        <div class="flex items-center">
          <input 
            type="checkbox" 
            :checked="isSelected(option.value)" 
            class="h-4 w-4 text-indigo-600 border-gray-300 rounded"
            @click.stop
          />
          <span class="ml-3 block truncate">{{ option.label }}</span>
        </div>
        <span v-if="isSelected(option.value)" class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600">
          <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
          </svg>
        </span>
      </div>
    </div>
    
    <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
  </div>
</template>

<script lang="ts">
import { onMounted, onUnmounted } from 'vue';
</script>
