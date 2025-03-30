<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType } from '@/types';
import { Search, Moon, User, FolderOpen, ChevronDown } from 'lucide-vue-next';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { Link } from '@inertiajs/vue3';

defineProps<{
    breadcrumbs?: BreadcrumbItemType[];
}>();

interface Project {
    id: number;
    name: string;
    shortcode: string;
    status?: string;
}

const isProjectQuickAccessOpen = ref(false);
const activeProjects = ref<Project[]>([]);
const isLoading = ref(false);

// Project search functionality
const isSearchOpen = ref(false);
const searchQuery = ref('');
const searchResults = ref<Project[]>([]);
const isSearching = ref(false);
const searchInputRef = ref<HTMLInputElement | null>(null);

// Fetch active projects for the current user
async function fetchActiveProjects() {
    isLoading.value = true;
    try {
        const response = await axios.get('/projects/active');
        activeProjects.value = response.data;
    } catch (error) {
        console.error('Error fetching active projects:', error);
    } finally {
        isLoading.value = false;
    }
}

function toggleProjectQuickAccess() {
    isProjectQuickAccessOpen.value = !isProjectQuickAccessOpen.value;
    
    // Fetch projects when opening the dropdown if we don't have any yet
    if (isProjectQuickAccessOpen.value && activeProjects.value.length === 0) {
        fetchActiveProjects();
    }
}

function closeProjectQuickAccess() {
    isProjectQuickAccessOpen.value = false;
}

// Close dropdown when clicking outside
function handleClickOutside(event: MouseEvent) {
    const target = event.target as HTMLElement;
    if (!target.closest('.project-quick-access')) {
        closeProjectQuickAccess();
    }
    if (!target.closest('.project-search')) {
        closeSearch();
    }
}

// Project search methods
function toggleSearch() {
    isSearchOpen.value = !isSearchOpen.value;
    if (isSearchOpen.value) {
        setTimeout(() => {
            searchInputRef.value?.focus();
        }, 100);
    } else {
        searchQuery.value = '';
        searchResults.value = [];
    }
}

function closeSearch() {
    isSearchOpen.value = false;
    searchQuery.value = '';
    searchResults.value = [];
}

async function searchProjects() {
    if (!searchQuery.value.trim()) {
        searchResults.value = [];
        return;
    }
    
    isSearching.value = true;
    try {
        const response = await axios.get('/projects/search', {
            params: { query: searchQuery.value }
        });
        searchResults.value = response.data;
    } catch (error) {
        console.error('Error searching projects:', error);
    } finally {
        isSearching.value = false;
    }
}

// Debounce search to avoid too many requests
let searchTimeout: number | null = null;
function handleSearchInput() {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    
    searchTimeout = setTimeout(() => {
        searchProjects();
    }, 300) as unknown as number;
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    // Pre-fetch projects for faster access
    fetchActiveProjects();
});
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-[[data-collapsible=icon]]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>
        <div class="ml-auto flex items-center gap-3">
            <!-- Project Quick Access Dropdown -->
            <div class="relative project-quick-access">
                <button 
                    @click="toggleProjectQuickAccess"
                    class="flex items-center gap-1 px-3 py-1.5 rounded-md hover:bg-accent hover:text-accent-foreground"
                >
                    <FolderOpen class="h-4 w-4" />
                    <span class="text-sm font-medium">Projects</span>
                    <ChevronDown class="h-3 w-3" :class="{ 'transform rotate-180': isProjectQuickAccessOpen }" />
                </button>
                
                <div v-if="isProjectQuickAccessOpen" 
                    class="absolute right-0 mt-1 w-56 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1 z-50 border border-gray-200 dark:border-gray-700"
                >
                    <div v-if="isLoading" class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">
                        Loading projects...
                    </div>
                    <div v-else-if="activeProjects.length === 0" class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">
                        No active projects found
                    </div>
                    <template v-else>
                        <div class="px-4 py-1 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Active Projects
                        </div>
                        <Link 
                            v-for="project in activeProjects" 
                            :key="project.id" 
                            :href="`/projects/${project.id}`"
                            class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700"
                        >
                            {{ project.name }}
                            <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">({{ project.shortcode }})</span>
                        </Link>
                    </template>
                    <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>
                    <Link 
                        href="/projects/manage" 
                        class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700"
                    >
                        View All Projects
                    </Link>
                </div>
            </div>
            
            <!-- Project Search -->
            <div class="relative project-search">
                <button 
                    @click="toggleSearch" 
                    class="flex h-8 w-8 items-center justify-center rounded-md hover:bg-accent hover:text-accent-foreground"
                    :class="{ 'bg-accent text-accent-foreground': isSearchOpen }"
                >
                    <Search class="h-4 w-4" />
                </button>
                
                <div v-if="isSearchOpen" 
                    class="absolute right-0 mt-1 w-80 bg-white dark:bg-gray-800 rounded-md shadow-lg py-2 z-50 border border-gray-200 dark:border-gray-700"
                >
                    <div class="px-3 pb-2">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <Search class="h-4 w-4 text-gray-400" />
                            </div>
                            <input
                                ref="searchInputRef"
                                v-model="searchQuery"
                                @input="handleSearchInput"
                                type="text"
                                placeholder="Search projects..."
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md leading-5 bg-white dark:bg-gray-700 placeholder-gray-500 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                            />
                        </div>
                    </div>
                    
                    <div v-if="searchQuery && searchQuery.trim() !== ''">
                        <div v-if="isSearching" class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">
                            Searching...
                        </div>
                        <div v-else-if="searchResults.length === 0" class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">
                            No projects found matching "{{ searchQuery }}"
                        </div>
                        <template v-else>
                            <div class="max-h-60 overflow-y-auto">
                                <Link 
                                    v-for="project in searchResults" 
                                    :key="project.id" 
                                    :href="`/projects/${project.id}`"
                                    class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
                                >
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ project.name }}</div>
                                    <div class="flex items-center mt-1">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ project.shortcode }}</span>
                                        <span 
                                            class="ml-2 px-1.5 py-0.5 text-xs rounded-full"
                                            :class="{
                                                'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100': project.status === 'active',
                                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100': project.status === 'pending',
                                                'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100': project.status === 'inactive',
                                                'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100': !project.status || project.status === 'completed'
                                            }"
                                        >
                                            {{ project.status || 'unknown' }}
                                        </span>
                                    </div>
                                </Link>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
            
            <button class="flex h-8 w-8 items-center justify-center rounded-md hover:bg-accent hover:text-accent-foreground">
                <Moon class="h-4 w-4" />
            </button>
            <button class="flex h-8 w-8 items-center justify-center rounded-md hover:bg-accent hover:text-accent-foreground">
                <User class="h-4 w-4" />
            </button>
        </div>
    </header>
</template>
