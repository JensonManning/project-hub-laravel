<script setup lang="ts">
import { Command } from 'cmdk-vue';
import { cn } from '@/lib/utils';

const props = defineProps<{
  class?: string;
}>();
</script>

<template>
  <div
    class="flex items-center border-b px-3"
    :class="cn('', props.class)"
    cmdk-input-wrapper=""
  >
    <Search class="mr-2 h-4 w-4 shrink-0 opacity-50" />
    <Command.Input
      class="flex h-10 w-full rounded-md bg-transparent py-3 text-sm outline-none placeholder:text-muted-foreground disabled:cursor-not-allowed disabled:opacity-50"
      v-bind="$attrs"
    />
  </div>
</template>

<script lang="ts">
import { Search } from 'lucide-vue-next';
</script>
