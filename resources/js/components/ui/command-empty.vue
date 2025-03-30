<script setup lang="ts">
import { Command } from 'cmdk-vue';
import { cn } from '@/lib/utils';

const props = defineProps<{
  class?: string;
}>();
</script>

<template>
  <Command.Empty
    class="py-6 text-center text-sm"
    :class="cn('', props.class)"
    v-bind="$attrs"
  >
    <slot />
  </Command.Empty>
</template>
