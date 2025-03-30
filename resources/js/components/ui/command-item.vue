<script setup lang="ts">
import { Command } from 'cmdk-vue';
import { cn } from '@/lib/utils';

const props = defineProps<{
  class?: string;
  disabled?: boolean;
}>();
</script>

<template>
  <Command.Item
    :class="
      cn(
        'relative flex cursor-default select-none items-center rounded-sm px-2 py-1.5 text-sm outline-none aria-selected:bg-accent aria-selected:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50',
        props.class
      )
    "
    :disabled="props.disabled"
    v-bind="$attrs"
  >
    <slot />
  </Command.Item>
</template>
