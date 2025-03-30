<script setup lang="ts">
import { Command } from 'cmdk-vue';
import { cn } from '@/lib/utils';

const props = defineProps<{
  class?: string;
  heading?: string;
}>();
</script>

<template>
  <Command.Group
    :class="cn('overflow-hidden p-1 text-foreground', props.class)"
    :heading="heading"
    v-bind="$attrs"
  >
    <slot />
  </Command.Group>
</template>
