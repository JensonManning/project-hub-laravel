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
  error?: string;
}>();

const emit = defineEmits(['update:modelValue']);

const selectedValues = ref<(number | string)[]>([]);

// Initialize selectedValues from modelValue
watch(() => props.modelValue, (newValue) => {
  selectedValues.value = [...newValue];
}, { immediate: true });

// Update modelValue when selectedValues changes
watch(selectedValues, (newValue) => {
  emit('update:modelValue', newValue);
});

// Get selected options with labels
const selectedOptions = computed(() => {
  return props.options.filter(option => selectedValues.value.includes(option.value));
});
</script>

<template>
  <div 
    class="relative w-full"
    :class="{ 'border-destructive ring-destructive': error }"
  >
    <select 
      multiple
      class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
      :class="{ 'border-destructive': error }"
      @change="e => {
        const select = e.target as HTMLSelectElement;
        const values = Array.from(select.selectedOptions).map(option => 
          isNaN(Number(option.value)) ? option.value : Number(option.value)
        );
        emit('update:modelValue', values);
      }"
    >
      <option 
        v-for="option in props.options" 
        :key="option.value" 
        :value="option.value"
        :selected="selectedValues.includes(option.value)"
      >
        {{ option.label }}
      </option>
    </select>
    <p v-if="error" class="text-destructive text-sm mt-1">{{ error }}</p>
  </div>
</template>
