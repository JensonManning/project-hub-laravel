<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType } from '@/types';
import { Search, Moon, User } from 'lucide-vue-next';

defineProps<{
    breadcrumbs?: BreadcrumbItemType[];
}>();
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-[[data-collapsible=icon]]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>
        <div class="ml-auto flex items-center gap-3">
            <button class="flex h-8 w-8 items-center justify-center rounded-md hover:bg-accent hover:text-accent-foreground">
                <Search class="h-4 w-4" />
            </button>
            <button class="flex h-8 w-8 items-center justify-center rounded-md hover:bg-accent hover:text-accent-foreground">
                <Moon class="h-4 w-4" />
            </button>
            <button class="flex h-8 w-8 items-center justify-center rounded-md hover:bg-accent hover:text-accent-foreground">
                <User class="h-4 w-4" />
            </button>
        </div>
    </header>
</template>
