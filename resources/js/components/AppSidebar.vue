<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, FastForward, NotebookText, UsersRound, ShieldCheck, SquareCheckBig, SquareStack } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
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
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" :management-items="managementNavItems" :repo-items="repoNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
