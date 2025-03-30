<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    Dialog, 
    DialogContent, 
    DialogDescription, 
    DialogFooter, 
    DialogHeader, 
    DialogTitle
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { 
    Table, 
    TableBody, 
    TableCaption, 
    TableCell, 
    TableHead, 
    TableHeader, 
    TableRow 
} from '@/components/ui/table';
import { useToast } from '@/components/ui/toast';
import { 
    CheckCircle, 
    XCircle, 
    Edit, 
    Trash, 
    UserCog, 
    Key, 
    Mail, 
    User as UserIcon 
} from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Management',
        href: ''
    },
    {
        title: 'User Management',
        href: '/management/users',
    },
];

const { toast } = useToast();

interface User {
    id: number;
    name: string;
    email: string;
    approval_status: string;
    rejection_reason?: string;
    created_at: string;
    role?: {
        id: number;
        name: string;
    };
}

interface Role {
    id: number;
    name: string;
}

interface Props {
    users: User[];
    roles: Role[];
}

const props = defineProps<Props>();

// Form for editing a user
const editForm = useForm({
    id: 0,
    name: '',
    email: '',
    role_id: '',
    approval_status: '',
    rejection_reason: '',
    password: '',
    password_confirmation: '',
});

// Form for deleting a user
const deleteForm = useForm({
    id: 0,
});

const isEditDialogOpen = ref(false);
const isDeleteDialogOpen = ref(false);
const isChangingPassword = ref(false);

// Function to open edit dialog
const openEditDialog = (user: User) => {
    editForm.id = user.id;
    editForm.name = user.name;
    editForm.email = user.email;
    editForm.role_id = user.role?.id.toString() || '';
    editForm.approval_status = user.approval_status;
    editForm.rejection_reason = user.rejection_reason || '';
    editForm.password = '';
    editForm.password_confirmation = '';
    isChangingPassword.value = false;
    isEditDialogOpen.value = true;
};

// Function to open delete dialog
const openDeleteDialog = (user: User) => {
    deleteForm.id = user.id;
    isDeleteDialogOpen.value = true;
};

// Function to update a user
const updateUser = () => {
    // If not changing password, remove password fields
    if (!isChangingPassword.value) {
        editForm.password = '';
        editForm.password_confirmation = '';
    }
    
    // If not rejecting, clear rejection reason
    if (editForm.approval_status !== 'rejected') {
        editForm.rejection_reason = '';
    }
    
    editForm.put(route('management.users.update', editForm.id), {
        onSuccess: () => {
            isEditDialogOpen.value = false;
            toast({
                title: "User Updated",
                description: "The user has been successfully updated.",
                duration: 5000,
            });
        },
    });
};

// Function to delete a user
const deleteUser = () => {
    deleteForm.delete(route('management.users.destroy', deleteForm.id), {
        onSuccess: () => {
            isDeleteDialogOpen.value = false;
            toast({
                title: "User Deleted",
                description: "The user has been successfully deleted.",
                duration: 5000,
            });
        },
    });
};

// Function to toggle password change
const togglePasswordChange = () => {
    isChangingPassword.value = !isChangingPassword.value;
    if (!isChangingPassword.value) {
        editForm.password = '';
        editForm.password_confirmation = '';
    }
};

// Function to get status badge class
const getStatusBadgeClass = (status: string) => {
    switch (status) {
        case 'approved':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
        case 'rejected':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
    }
};
</script>

<template>
    <Head title="User Management" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">User Management</h1>
            </div>

            <!-- Users Table -->
            <div>
                <Table>
                    <TableCaption>A list of all users in the system.</TableCaption>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead>Role</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Registration Date</TableHead>
                            <TableHead class="w-32">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="user in props.users" :key="user.id">
                            <TableCell>{{ user.name }}</TableCell>
                            <TableCell>{{ user.email }}</TableCell>
                            <TableCell>{{ user.role?.name || 'None' }}</TableCell>
                            <TableCell>
                                <span 
                                    class="px-2 py-1 rounded-full text-xs font-medium" 
                                    :class="getStatusBadgeClass(user.approval_status)"
                                >
                                    {{ user.approval_status.charAt(0).toUpperCase() + user.approval_status.slice(1) }}
                                </span>
                            </TableCell>
                            <TableCell>{{ new Date(user.created_at).toLocaleDateString() }}</TableCell>
                            <TableCell>
                                <div class="flex gap-2">
                                    <Button variant="outline" size="sm" @click="openEditDialog(user)">
                                        <Edit class="h-4 w-4 mr-2" />
                                        Edit
                                    </Button>
                                    <Button variant="destructive" size="sm" @click="openDeleteDialog(user)">
                                        <Trash class="h-4 w-4 mr-2" />
                                        Delete
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="props.users.length === 0">
                            <TableCell colspan="6" class="text-center py-4">No users found.</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Edit User Dialog -->
            <Dialog :open="isEditDialogOpen" @update:open="isEditDialogOpen = $event">
                <DialogContent class="bg-background max-w-2xl">
                    <DialogHeader>
                        <DialogTitle>Edit User</DialogTitle>
                        <DialogDescription>
                            Update user information, role, and approval status.
                        </DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="updateUser">
                        <div class="grid gap-4 py-4">
                            <!-- User Information Section -->
                            <div class="border-b pb-4 mb-4">
                                <h3 class="text-lg font-medium mb-4 flex items-center">
                                    <UserIcon class="h-5 w-5 mr-2" />
                                    User Information
                                </h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="grid gap-2">
                                        <Label for="name">Name</Label>
                                        <Input 
                                            id="name" 
                                            v-model="editForm.name" 
                                            placeholder="Enter user name"
                                            required
                                        />
                                        <div v-if="editForm.errors.name" class="text-red-500 text-sm">
                                            {{ editForm.errors.name }}
                                        </div>
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="email">Email</Label>
                                        <Input 
                                            id="email" 
                                            v-model="editForm.email" 
                                            type="email"
                                            placeholder="Enter user email"
                                            required
                                        />
                                        <div v-if="editForm.errors.email" class="text-red-500 text-sm">
                                            {{ editForm.errors.email }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Password Section -->
                            <div class="border-b pb-4 mb-4">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-medium flex items-center">
                                        <Key class="h-5 w-5 mr-2" />
                                        Password
                                    </h3>
                                    <Button 
                                        type="button" 
                                        variant="outline" 
                                        size="sm"
                                        @click="togglePasswordChange"
                                    >
                                        {{ isChangingPassword ? 'Cancel Password Change' : 'Change Password' }}
                                    </Button>
                                </div>
                                <div v-if="isChangingPassword" class="grid grid-cols-2 gap-4">
                                    <div class="grid gap-2">
                                        <Label for="password">New Password</Label>
                                        <Input 
                                            id="password" 
                                            v-model="editForm.password" 
                                            type="password"
                                            placeholder="Enter new password"
                                            required
                                        />
                                        <div v-if="editForm.errors.password" class="text-red-500 text-sm">
                                            {{ editForm.errors.password }}
                                        </div>
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="password_confirmation">Confirm Password</Label>
                                        <Input 
                                            id="password_confirmation" 
                                            v-model="editForm.password_confirmation" 
                                            type="password"
                                            placeholder="Confirm new password"
                                            required
                                        />
                                    </div>
                                </div>
                                <div v-else class="text-sm text-gray-500">
                                    Password will not be changed. Click the button above to set a new password.
                                </div>
                            </div>

                            <!-- Role and Status Section -->
                            <div>
                                <h3 class="text-lg font-medium mb-4 flex items-center">
                                    <UserCog class="h-5 w-5 mr-2" />
                                    Role & Approval Status
                                </h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="grid gap-2">
                                        <Label for="role">Role</Label>
                                        <div class="relative">
                                            <select 
                                                id="role" 
                                                v-model="editForm.role_id"
                                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                                required
                                            >
                                                <option value="" disabled>Select a role</option>
                                                <option v-for="role in props.roles" :key="role.id" :value="role.id">
                                                    {{ role.name }}
                                                </option>
                                            </select>
                                        </div>
                                        <div v-if="editForm.errors.role_id" class="text-red-500 text-sm">
                                            {{ editForm.errors.role_id }}
                                        </div>
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="approval_status">Approval Status</Label>
                                        <div class="relative">
                                            <select 
                                                id="approval_status" 
                                                v-model="editForm.approval_status"
                                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                                required
                                            >
                                                <option value="pending">Pending</option>
                                                <option value="approved">Approved</option>
                                                <option value="rejected">Rejected</option>
                                            </select>
                                        </div>
                                        <div v-if="editForm.errors.approval_status" class="text-red-500 text-sm">
                                            {{ editForm.errors.approval_status }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Rejection Reason (only shown when status is rejected) -->
                                <div v-if="editForm.approval_status === 'rejected'" class="mt-4">
                                    <Label for="rejection_reason">Rejection Reason</Label>
                                    <Textarea 
                                        id="rejection_reason" 
                                        v-model="editForm.rejection_reason" 
                                        placeholder="Enter reason for rejection"
                                        required
                                    />
                                    <div v-if="editForm.errors.rejection_reason" class="text-red-500 text-sm">
                                        {{ editForm.errors.rejection_reason }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <DialogFooter>
                            <Button type="button" variant="outline" @click="isEditDialogOpen = false">Cancel</Button>
                            <Button type="submit" :disabled="editForm.processing">Save Changes</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <!-- Delete User Dialog -->
            <Dialog :open="isDeleteDialogOpen" @update:open="isDeleteDialogOpen = $event">
                <DialogContent class="bg-background">
                    <DialogHeader>
                        <DialogTitle>Delete User</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to delete this user? This action cannot be undone.
                        </DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="deleteUser">
                        <DialogFooter>
                            <Button type="button" variant="outline" @click="isDeleteDialogOpen = false">Cancel</Button>
                            <Button type="submit" variant="destructive" :disabled="deleteForm.processing">Delete</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
