<script setup lang="ts">
import { Command } from 'cmdk-vue';
import { cn } from '@/lib/utils';

const props = defineProps<{
  class?: string;
}>();
</script>

<template>
  <Command.Separator
    :class="cn('-mx-1 h-px bg-border', props.class)"
    v-bind="$attrs"
  />
</template>
