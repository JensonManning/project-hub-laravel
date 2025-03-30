<script setup lang="ts">
import { SidebarProvider } from '@/components/ui/sidebar';
import { onMounted, ref } from 'vue';

interface Props {
    variant?: 'header' | 'sidebar';
}

defineProps<Props>();

const isOpen = ref(true);

onMounted(() => {
    isOpen.value = localStorage.getItem('sidebar') !== 'false';
});

const handleSidebarChange = (open: boolean) => {
    isOpen.value = open;
    localStorage.setItem('sidebar', String(open));
};
</script>

<template>
    <div v-if="variant === 'header'" class="flex min-h-screen w-full flex-col bg-gradient-to-br from-background to-background/95 backdrop-blur-sm">
        <div class="fixed inset-0 bg-primary/5 dark:bg-primary/3 -z-10 bg-[radial-gradient(circle_at_30%_20%,rgba(var(--primary),0.1)_0%,transparent_50%),radial-gradient(circle_at_80%_70%,rgba(var(--primary),0.05)_0%,transparent_60%)]"></div>
        <slot />
    </div>
    <SidebarProvider v-else :default-open="isOpen" :open="isOpen" @update:open="handleSidebarChange">
        <div class="fixed inset-0 bg-primary/5 dark:bg-primary/3 -z-10 bg-[radial-gradient(circle_at_30%_20%,rgba(var(--primary),0.1)_0%,transparent_50%),radial-gradient(circle_at_80%_70%,rgba(var(--primary),0.05)_0%,transparent_60%)]"></div>
        <slot />
    </SidebarProvider>
</template>
