<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, FastForward, NotebookText, UsersRound, ShieldCheck, SquareCheckBig, SquareStack, Zap, FilePlus } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
];

const projectNavItems: NavItem[] = [
    {
        title: 'Manage Projects',
        href: '/projects/manage',
        icon: Zap,
    },
];

const managementNavItems: NavItem[] = [
    {
        title: 'Roles',
        href: '/management/roles',
        icon: LayoutGrid,
    },
    {
        title: 'User Management',
        href: '/management/users',
        icon: UsersRound,
    },
    {
        title: 'User Approvals',
        href: '/management/user-approvals',
        icon: ShieldCheck,
    },
];

const repoNavItems: NavItem[] = [
    {
        title: 'Phases',
        href: '/repo/phases',
        icon: FastForward,
    },
    {
        title: 'Notebooks',
        href: '/repo/notebooks',
        icon: NotebookText,
    },
    {
        title: 'Resources',
        href: '/repo/resources',
        icon: UsersRound,
    },
    {
        title: 'Categories',
        href: '/repo/categories',
        icon: SquareStack,
    },
    {
        title: 'Task Types',
        href: '/repo/task-types',
        icon: ShieldCheck,
    },
    {
        title: 'Tasks',
        href: '/repo/tasks',
        icon: SquareCheckBig,
    }
];

const footerNavItems: NavItem[] = [
    {
        title: 'My Github',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset" class="backdrop-blur-md bg-sidebar/80 border-r border-sidebar-border/50 shadow-lg">
        <SidebarHeader class="gradient-border">
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child class="futuristic-glow">
                        <Link :href="route('dashboard')" class="flex items-center justify-center">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent class="scrollbar-thin scrollbar-thumb-primary/20 scrollbar-track-transparent hover:scrollbar-thumb-primary/30">
            <NavMain :items="mainNavItems" :management-items="managementNavItems" :repo-items="repoNavItems" :project-items="projectNavItems" />
        </SidebarContent>

        <SidebarFooter class="border-t border-sidebar-border/50 bg-sidebar-accent/30">
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
