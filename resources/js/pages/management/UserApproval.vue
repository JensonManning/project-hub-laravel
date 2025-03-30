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
import { CheckCircle, XCircle } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Management',
        href: ''
    },
    {
        title: 'User Approvals',
        href: '/management/user-approvals',
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
}

interface Role {
    id: number;
    name: string;
}

interface Props {
    pendingUsers: User[];
    approvedUsers: User[];
    rejectedUsers: User[];
    roles: Role[];
}

const props = defineProps<Props>();

// Form for approving a user
const approveForm = useForm({
    id: 0,
    role_id: '',
});

// Form for rejecting a user
const rejectForm = useForm({
    id: 0,
    rejection_reason: '',
});

const isApproveDialogOpen = ref(false);
const isRejectDialogOpen = ref(false);

// Function to open approve dialog
const openApproveDialog = (user: User) => {
    approveForm.id = user.id;
    approveForm.role_id = '';
    isApproveDialogOpen.value = true;
};

// Function to open reject dialog
const openRejectDialog = (user: User) => {
    rejectForm.id = user.id;
    rejectForm.rejection_reason = '';
    isRejectDialogOpen.value = true;
};

// Function to approve a user
const approveUser = () => {
    approveForm.post(route('management.user-approvals.approve', approveForm.id), {
        onSuccess: () => {
            isApproveDialogOpen.value = false;
            toast({
                title: "User Approved",
                description: "The user has been successfully approved.",
                duration: 5000,
            });
        },
    });
};

// Function to reject a user
const rejectUser = () => {
    rejectForm.post(route('management.user-approvals.reject', rejectForm.id), {
        onSuccess: () => {
            isRejectDialogOpen.value = false;
            toast({
                title: "User Rejected",
                description: "The user has been successfully rejected.",
                duration: 5000,
            });
        },
    });
};
</script>

<template>
    <Head title="User Approvals" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">User Approvals</h1>
            </div>

            <!-- Pending Users Table -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold mb-4">Pending Approvals</h2>
                <Table>
                    <TableCaption>A list of all users pending approval.</TableCaption>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead>Registration Date</TableHead>
                            <TableHead class="w-32">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="user in props.pendingUsers" :key="user.id">
                            <TableCell>{{ user.name }}</TableCell>
                            <TableCell>{{ user.email }}</TableCell>
                            <TableCell>{{ new Date(user.created_at).toLocaleDateString() }}</TableCell>
                            <TableCell>
                                <div class="flex gap-2">
                                    <Button variant="outline" size="sm" @click="openApproveDialog(user)">
                                        <CheckCircle class="h-4 w-4 mr-2" />
                                        Approve
                                    </Button>
                                    <Button variant="destructive" size="sm" @click="openRejectDialog(user)">
                                        <XCircle class="h-4 w-4 mr-2" />
                                        Reject
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="props.pendingUsers.length === 0">
                            <TableCell colspan="4" class="text-center py-4">No pending users found.</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Approved Users Table -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold mb-4">Approved Users</h2>
                <Table>
                    <TableCaption>A list of all approved users.</TableCaption>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead>Registration Date</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="user in props.approvedUsers" :key="user.id">
                            <TableCell>{{ user.name }}</TableCell>
                            <TableCell>{{ user.email }}</TableCell>
                            <TableCell>{{ new Date(user.created_at).toLocaleDateString() }}</TableCell>
                        </TableRow>
                        <TableRow v-if="props.approvedUsers.length === 0">
                            <TableCell colspan="3" class="text-center py-4">No approved users found.</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Rejected Users Table -->
            <div>
                <h2 class="text-xl font-semibold mb-4">Rejected Users</h2>
                <Table>
                    <TableCaption>A list of all rejected users.</TableCaption>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead>Rejection Reason</TableHead>
                            <TableHead class="w-32">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="user in props.rejectedUsers" :key="user.id">
                            <TableCell>{{ user.name }}</TableCell>
                            <TableCell>{{ user.email }}</TableCell>
                            <TableCell>{{ user.rejection_reason }}</TableCell>
                            <TableCell>
                                <Button variant="outline" size="sm" @click="openApproveDialog(user)">
                                    <CheckCircle class="h-4 w-4 mr-2" />
                                    Approve
                                </Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="props.rejectedUsers.length === 0">
                            <TableCell colspan="4" class="text-center py-4">No rejected users found.</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Approve Dialog -->
            <Dialog :open="isApproveDialogOpen" @update:open="isApproveDialogOpen = $event">
                <DialogContent class="bg-background">
                    <DialogHeader>
                        <DialogTitle>Approve User</DialogTitle>
                        <DialogDescription>
                            Assign a role to this user to approve them.
                        </DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="approveUser">
                        <div class="grid gap-4 py-4">
                            <div class="grid gap-2">
                                <Label for="role">Role</Label>
                                <div class="relative">
                                    <select 
                                        id="role" 
                                        v-model="approveForm.role_id"
                                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                        required
                                    >
                                        <option value="" disabled selected>Select a role</option>
                                        <option v-for="role in props.roles" :key="role.id" :value="role.id">
                                            {{ role.name }}
                                        </option>
                                    </select>
                                </div>
                                <div v-if="approveForm.errors.role_id" class="text-red-500 text-sm">
                                    {{ approveForm.errors.role_id }}
                                </div>
                            </div>
                        </div>
                        <DialogFooter>
                            <Button type="button" variant="outline" @click="isApproveDialogOpen = false">Cancel</Button>
                            <Button type="submit" :disabled="approveForm.processing">Approve</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <!-- Reject Dialog -->
            <Dialog :open="isRejectDialogOpen" @update:open="isRejectDialogOpen = $event">
                <DialogContent class="bg-background">
                    <DialogHeader>
                        <DialogTitle>Reject User</DialogTitle>
                        <DialogDescription>
                            Provide a reason for rejecting this user.
                        </DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="rejectUser">
                        <div class="grid gap-4 py-4">
                            <div class="grid gap-2">
                                <Label for="rejection_reason">Rejection Reason</Label>
                                <Textarea 
                                    id="rejection_reason" 
                                    v-model="rejectForm.rejection_reason" 
                                    placeholder="Enter reason for rejection"
                                    required
                                />
                                <div v-if="rejectForm.errors.rejection_reason" class="text-red-500 text-sm">
                                    {{ rejectForm.errors.rejection_reason }}
                                </div>
                            </div>
                        </div>
                        <DialogFooter>
                            <Button type="button" variant="outline" @click="isRejectDialogOpen = false">Cancel</Button>
                            <Button type="submit" variant="destructive" :disabled="rejectForm.processing">Reject</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
