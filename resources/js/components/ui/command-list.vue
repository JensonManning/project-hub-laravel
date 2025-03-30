<script setup lang="ts">
import { Command } from 'cmdk-vue';
import { cn } from '@/lib/utils';

const props = defineProps<{
  class?: string;
}>();
</script>

<template>
  <Command.List
    :class="cn('max-h-[300px] overflow-y-auto overflow-x-hidden', props.class)"
    v-bind="$attrs"
  >
    <slot />
  </Command.List>
</template>
