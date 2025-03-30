<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import type {
ColumnDef,
ColumnFiltersState,
ExpandedState,
SortingState,
VisibilityState,
} from '@tanstack/vue-table'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import {
DropdownMenu,
DropdownMenuCheckboxItem,
DropdownMenuContent,
DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'

import { Input } from '@/components/ui/input'
import {
Table,
TableBody,
TableCell,
TableHead,
TableHeader,
TableRow,
} from '@/components/ui/table'
import {
FlexRender,
getCoreRowModel,
getExpandedRowModel,
getFilteredRowModel,
getPaginationRowModel,
getSortedRowModel,
useVueTable,
} from '@tanstack/vue-table'
import { ArrowUpDown, ChevronDown, LucidePencil, LucideTrash } from 'lucide-vue-next'
import { h, ref, onMounted, type Ref } from 'vue'
import { 
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle 
} from '@/components/ui/dialog'
import { useForm } from '@inertiajs/vue3'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Management',
        href: ''
    },
    {
        title: 'Roles',
        href: '/management/roles',
    },
];

// Get users and roles from props
const props = defineProps<{
    initialUsers?: any[];
    availableRoles?: any[];
}>();

// Create reactive data arrays
const users = ref(props.initialUsers || []);
const roles = ref(props.availableRoles || []);

// Define column types
const columns: ColumnDef<any>[] = [
    {
        id: 'select',
        header: ({ table }) => h(Checkbox, {
            'modelValue': table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && 'indeterminate'),
            'onUpdate:modelValue': (value: boolean) => table.toggleAllPageRowsSelected(!!value),
            'ariaLabel': 'Select all',
        }),
        cell: ({ row }) => h(Checkbox, {
            'modelValue': row.getIsSelected(),
            'onUpdate:modelValue': (value: boolean) => row.toggleSelected(!!value),
            'ariaLabel': 'Select row',
        }),
        enableSorting: false,
        enableHiding: false,
    },
    {
        accessorKey: 'name',
        header: 'Name',
        cell: ({ row }) => h('div', { class: 'capitalize' }, row.getValue('name')),
    },
    {
        accessorKey: 'email',
        header: 'Email',
        cell: ({ row }) => h('div', {}, row.getValue('email')),
    },
    {
        id: 'roleName',
        accessorFn: (row) => row.role?.name || 'No Role',
        header: ({ column }) => {
            return h(Button, {
                variant: 'ghost',
                onClick: () => column.toggleSorting(column.getIsSorted() === 'asc' ),
            }, () => ['Role', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => h('div', { class: 'capitalize' }, row.getValue('roleName')),
    },
    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row }) => {
            const rowData = row.original
            return h('div', { class: 'flex items-center justify-end gap-2' }, [
                h(Button, { 
                    variant: 'ghost', 
                    size: 'icon',
                    onClick: () => openEditUserDialog(rowData)
                }, {
                    default: () => h(LucidePencil, { class: 'h-4 w-4' })
                })
            ])
        },
    },
]

const sorting = ref<SortingState>([])
const columnFilters = ref<ColumnFiltersState>([])
const columnVisibility = ref<VisibilityState>({})
const rowSelection = ref({})
const expanded = ref<ExpandedState>({})

// Dialog open states
const isDialogOpen = ref(false)
const isCreateDialogOpen = ref(false)
const isEditUserDialogOpen = ref(false)
const isManageRolesDialogOpen = ref(false)

// Create form
const createForm = useForm({
    name: '',
});

// Edit form
const editForm = useForm({
    id: 0,
    name: '',
});

// Edit user role form
const editUserRoleForm = useForm({
    id: 0,
    name: '',
    email: '',
    role_id: '',
});

// Function to open edit dialog
const openEditDialog = (rowData: any) => {
    // Close the manage roles dialog first
    isManageRolesDialogOpen.value = false;
    
    // Then set up and open the edit dialog
    editForm.id = rowData.id;
    editForm.name = rowData.name;
    
    // Add a small delay to ensure the first dialog is fully closed
    setTimeout(() => {
        isDialogOpen.value = true;
    }, 100);
};

// Function to open edit user role dialog
const openEditUserDialog = (rowData: any) => {
    // Close any other open dialogs first
    isDialogOpen.value = false;
    isCreateDialogOpen.value = false;
    isManageRolesDialogOpen.value = false;
    
    // Then set up and open the edit user role dialog
    editUserRoleForm.id = rowData.id;
    editUserRoleForm.name = rowData.name;
    editUserRoleForm.email = rowData.email;
    editUserRoleForm.role_id = rowData.role?.id || '';
    isEditUserDialogOpen.value = true;
}

// Function to open create dialog
const openCreateDialog = () => {
    createForm.reset();
    isCreateDialogOpen.value = true;
}

// Function to open manage roles dialog
const openManageRolesDialog = () => {
    isManageRolesDialogOpen.value = true;
}

// Function to update role name with proper type handling
const updateRoleName = (value: string) => {
    editForm.name = value;
}

// Function to update new role name
const updateNewRoleName = (value: string): void => {
    createForm.name = value;
}

// Function to update user role
const updateUserRole = (value: string): void => {
    editUserRoleForm.role_id = value;
}

// Function to save role changes
const saveRole = () => {
    editForm.put(route('management.roles.update', editForm.id), {
        onSuccess: () => {
            isDialogOpen.value = false;
            // Refresh data
            window.location.reload();
        }
    });
}

// Function to save user role changes
const saveUserRole = () => {
    editUserRoleForm.put(route('management.users.update-role', editUserRoleForm.id), {
        onSuccess: () => {
            isEditUserDialogOpen.value = false;
            // Refresh data
            window.location.reload();
        }
    });
}

// Function to create new role
const createRole = () => {
    createForm.post(route('management.roles.store'), {
        onSuccess: () => {
            isCreateDialogOpen.value = false;
            // Refresh data
            window.location.reload();
        }
    });
}

// Function to delete role
const deleteRole = (id: number) => {
    if (confirm('Are you sure you want to delete this role?')) {
        useForm({}).delete(route('management.roles.destroy', id), {
            onSuccess: () => {
                // Refresh data
                window.location.reload();
            }
        });
    }
}

// Function to delete role with confirmation
const confirmDeleteRole = (id: number) => {
    if (confirm('Are you sure you want to delete this role? This cannot be undone.')) {
        deleteRole(id);
    }
}

const table = useVueTable({
    data: users,
    columns,
    state: {
        sorting: sorting.value,
        columnFilters: columnFilters.value,
        columnVisibility: columnVisibility.value,
        rowSelection: rowSelection.value,
        expanded: expanded.value,
    },
    enableRowSelection: true,
    enableMultiRowSelection: true,
    onSortingChange: updater => {
        sorting.value = typeof updater === 'function' ? updater(sorting.value) : updater;
    },
    onColumnFiltersChange: updater => {
        columnFilters.value = typeof updater === 'function' ? updater(columnFilters.value) : updater;
    },
    onColumnVisibilityChange: updater => {
        columnVisibility.value = typeof updater === 'function' ? updater(columnVisibility.value) : updater;
    },
    onRowSelectionChange: updater => {
        rowSelection.value = typeof updater === 'function' ? updater(rowSelection.value) : updater;
    },
    onExpandedChange: updater => {
        expanded.value = typeof updater === 'function' ? updater(expanded.value) : updater;
    },
    getCoreRowModel: getCoreRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getExpandedRowModel: getExpandedRowModel(),
})
</script>

<template>
    <Head title="RoleManagement" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="relative min-h-[100vh] flex-1 p-4 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border md:min-h-min">
                <div class="w-92">
                    <div class="flex gap-4 items-center py-4">
                        <Input
                        class="max-w-sm"
                        placeholder="Filter by name"
                        :model-value="table.getColumn('name')?.getFilterValue() as string"
                        @update:model-value=" table.getColumn('name')?.setFilterValue($event)"
                        />
                        <Button @click="openCreateDialog" class="ml-auto mr-2">
                            Add Role
                        </Button>
                        <Button variant="outline" @click="openManageRolesDialog">
                            Edit Roles
                        </Button>
                    </div>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                                    <TableHead v-for="header in headerGroup.headers" :key="header.id">
                                        <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <template v-if="table.getRowModel().rows?.length">
                                    <template v-for="row in table.getRowModel().rows" :key="row.id">
                                        <TableRow :data-state="row.getIsSelected() && 'selected'">
                                            <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                                                <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                                            </TableCell>
                                        </TableRow>
                                        <TableRow v-if="row.getIsExpanded()">
                                            <TableCell :colspan="row.getAllCells().length">
                                                {{  JSON.stringify(row.original) }}
                                            </TableCell>
                                        </TableRow>
                                    </template>
                                </template>
                                <TableRow v-else>
                                    <TableCell :colspan="columns.length" class="h-24 text-center">
                                        <span class="text-sm text-muted-foreground">No results.</span>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                    <div class="flex items-center rounded-xl justify-end space-x-2 py-4">
                        <div class="flex-1 text-sm text-muted-foreground">
                            {{ table.getFilteredSelectedRowModel().rows.length }} of
                            {{ table.getFilteredRowModel().rows.length }} row(s) selected.
                        </div>
                        <div class="space-x-2">
                            <Button
                            variant="outline"
                            size="sm"
                            :disabled="!table.getCanPreviousPage()"
                            @click="table.previousPage()"
                            >
                            Previous
                            </Button>
                            <Button
                            variant="outline"
                            size="sm"
                            :disabled="!table.getCanNextPage()"
                            @click="table.nextPage()"
                            >
                            Next
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <Dialog :open="isDialogOpen" @update:open="isDialogOpen = $event">
            <DialogContent class="bg-background">
                <DialogHeader>
                    <DialogTitle>Edit Role</DialogTitle>
                    <DialogDescription>
                        Edit role details
                    </DialogDescription>
                </DialogHeader>
                <div class="grid grid-cols-4 items-center gap-4">
                    <div class="col-span-4">
                        <Input
                            :model-value="editForm.name"
                            @update:model-value="updateRoleName(String($event))"
                            type="text"
                            placeholder="Role name"
                            class="w-full"
                        />
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="isDialogOpen = false">Cancel</Button>
                    <Button @click="saveRole" :disabled="editForm.processing">Save changes</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <Dialog :open="isCreateDialogOpen" @update:open="isCreateDialogOpen = $event">
            <DialogContent class="bg-background">
                <DialogHeader>
                    <DialogTitle>Create Role</DialogTitle>
                    <DialogDescription>
                        Add a new role
                    </DialogDescription>
                </DialogHeader>
                <div class="grid grid-cols-4 items-center gap-4">
                    <div class="col-span-4">
                        <Input
                            :model-value="createForm.name"
                            @update:model-value="updateNewRoleName(String($event))"
                            type="text"
                            placeholder="Role name"
                            class="w-full"
                        />
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="isCreateDialogOpen = false">Cancel</Button>
                    <Button @click="createRole" :disabled="createForm.processing">Create</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <Dialog :open="isEditUserDialogOpen" @update:open="isEditUserDialogOpen = $event">
            <DialogContent class="bg-background">
                <DialogHeader>
                    <DialogTitle>Edit User Role</DialogTitle>
                    <DialogDescription>
                        Change role for {{ editUserRoleForm.name }}
                    </DialogDescription>
                </DialogHeader>
                <div class="grid grid-cols-4 items-center gap-4">
                    <div class="col-span-4">
                        <div class="mb-2">
                            <span class="text-sm font-medium">Email: {{ editUserRoleForm.email }}</span>
                        </div>
                        <div class="mb-4">
                            <label class="text-sm font-medium mb-1 block">Select Role</label>
                            <select 
                                :value="editUserRoleForm.role_id" 
                                @change="(e: Event) => updateUserRole((e.target as HTMLSelectElement).value)"
                                class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                            >
                                <option value="" disabled>Select a role</option>
                                <option v-for="role in roles" :key="role.id" :value="role.id">
                                    {{ role.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="isEditUserDialogOpen = false">Cancel</Button>
                    <Button @click="saveUserRole" :disabled="editUserRoleForm.processing">Save changes</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <Dialog :open="isManageRolesDialogOpen" @update:open="isManageRolesDialogOpen = $event">
            <DialogContent class="sm:max-w-[625px] bg-background">
                <DialogHeader>
                    <DialogTitle>Manage Roles</DialogTitle>
                    <DialogDescription>
                        Edit or delete existing roles
                    </DialogDescription>
                </DialogHeader>
                <div class="max-h-[60vh] overflow-y-auto">
                    <div v-if="roles.length === 0" class="py-6 text-center text-sm text-muted-foreground">
                        No roles found
                    </div>
                    <div v-else class="space-y-4 py-4">
                        <div v-for="role in roles" :key="role.id" class="flex items-center justify-between space-x-4 rounded-lg border p-4">
                            <div>
                                <h4 class="text-sm font-medium leading-none">{{ role.name }}</h4>
                                <p class="text-sm text-muted-foreground">ID: {{ role.id }}</p>
                            </div>
                            <div class="flex space-x-2">
                                <Button 
                                    variant="outline" 
                                    size="sm"
                                    @click="openEditDialog(role)"
                                >
                                    <LucidePencil class="h-4 w-4 mr-2" />
                                    Edit
                                </Button>
                                <Button 
                                    variant="destructive" 
                                    size="sm"
                                    @click="confirmDeleteRole(role.id)"
                                >
                                    <LucideTrash class="h-4 w-4 mr-2" />
                                    Delete
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="isManageRolesDialogOpen = false">Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
