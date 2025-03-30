<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
  variant?: 'default' | 'secondary' | 'destructive' | 'outline' | 'success' | 'warning';
}>();

const variantClasses = computed(() => {
  switch (props.variant) {
    case 'default':
      return 'bg-primary text-primary-foreground hover:bg-primary/80';
    case 'secondary':
      return 'bg-secondary text-secondary-foreground hover:bg-secondary/80';
    case 'destructive':
      return 'bg-destructive text-destructive-foreground hover:bg-destructive/80';
    case 'outline':
      return 'border border-input bg-background hover:bg-accent hover:text-accent-foreground';
    case 'success':
      return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
    case 'warning':
      return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
    default:
      return 'bg-primary text-primary-foreground hover:bg-primary/80';
  }
});
</script>

<template>
  <div
    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
    :class="variantClasses"
  >
    <slot />
  </div>
</template>
