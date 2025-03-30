<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { ref, computed } from 'vue';
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
import { PlusCircle, Pencil, Trash2, Eye, Calendar } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { useToast } from '@/components/ui/toast';
import { format } from 'date-fns';

const { toast } = useToast();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Projects',
        href: '/projects/manage',
    },
];

interface Project {
    id: number;
    name: string;
    shortcode: string;
    start_date: string;
    end_date: string;
    description: string | null;
    status: 'upcoming' | 'active' | 'completed' | 'on_hold' | 'cancelled' | 'sale_pending' | 'delayed';
}

interface Props {
    projects: Project[];
}

const props = defineProps<Props>();

// Form for creating a new project
const createForm = useForm<{
    name: string;
    shortcode: string;
    start_date: string;
    end_date: string;
    description: string;
    status: 'upcoming' | 'active' | 'completed' | 'on_hold' | 'cancelled' | 'sale_pending' | 'delayed';
}>({
    name: '',
    shortcode: '',
    start_date: format(new Date(), 'yyyy-MM-dd'),
    end_date: '',
    description: '',
    status: 'upcoming',
});

// Form for editing a project
const editForm = useForm<{
    id: number;
    name: string;
    shortcode: string;
    start_date: string;
    end_date: string;
    description: string;
    status: 'upcoming' | 'active' | 'completed' | 'on_hold' | 'cancelled' | 'sale_pending' | 'delayed';
}>({
    id: 0,
    name: '',
    shortcode: '',
    start_date: '',
    end_date: '',
    description: '',
    status: 'upcoming',
});

const isCreateDialogOpen = ref(false);
const isEditDialogOpen = ref(false);
const isDeleteDialogOpen = ref(false);
const isViewDialogOpen = ref(false);
const projectToDelete = ref<Project | null>(null);
const projectToView = ref<Project | null>(null);

// Status options
const statusOptions = [
    { value: 'upcoming', label: 'Upcoming' },
    { value: 'active', label: 'Active' },
    { value: 'completed', label: 'Completed' },
    { value: 'on_hold', label: 'On Hold' },
    { value: 'cancelled', label: 'Cancelled' },
    { value: 'sale_pending', label: 'Sale Pending' },
    { value: 'delayed', label: 'Delayed' },
];

// Get status badge class based on status
const getStatusBadgeClass = (status: string) => {
    switch (status) {
        case 'upcoming':
            return 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400';
        case 'active':
            return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
        case 'completed':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
        case 'on_hold':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400';
        case 'cancelled':
            return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
        case 'sale_pending':
            return 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400';
        case 'delayed':
            return 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300';
    }
};

// Format date for display
const formatDate = (dateString: string | null | undefined) => {
    if (!dateString) return 'N/A';
    return format(new Date(dateString), 'MMM d, yyyy');
};

// Submit create form
const submitCreate = () => {
    createForm.post(route('project.store'), {
        onSuccess: () => {
            isCreateDialogOpen.value = false;
            createForm.reset();
            createForm.start_date = format(new Date(), 'yyyy-MM-dd');
            createForm.status = 'upcoming';
            toast({
                title: "Project Created",
                description: "The project has been successfully created.",
                duration: 5000,
            });
        },
    });
};

// Edit project
const editProject = (project: Project) => {
    editForm.id = project.id;
    editForm.name = project.name;
    editForm.shortcode = project.shortcode;
    editForm.start_date = project.start_date;
    editForm.end_date = project.end_date;
    editForm.description = project.description || '';
    editForm.status = project.status;
    isEditDialogOpen.value = true;
};

// View project
const viewProject = (project: Project) => {
    projectToView.value = project;
    isViewDialogOpen.value = true;
};

// Submit edit form
const submitEdit = () => {
    editForm.put(route('project.update', editForm.id), {
        onSuccess: () => {
            isEditDialogOpen.value = false;
            toast({
                title: "Project Updated",
                description: "The project has been successfully updated.",
                duration: 5000,
            });
        },
    });
};

// Delete project
const confirmDelete = (project: Project) => {
    projectToDelete.value = project;
    isDeleteDialogOpen.value = true;
};

// Submit delete
const submitDelete = () => {
    if (projectToDelete.value) {
        useForm({}).delete(route('project.destroy', { project: projectToDelete.value.id }), {
            onSuccess: () => {
                isDeleteDialogOpen.value = false;
                projectToDelete.value = null;
                toast({
                    title: "Project Deleted",
                    description: "The project has been successfully deleted.",
                    duration: 5000,
                });
            },
        });
    }
};
</script>

<template>
    <Head title="Project Management" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Project Management</h1>
                
                <!-- Create Dialog Trigger -->
                <Button @click="isCreateDialogOpen = true" class="futuristic-glow">
                    <PlusCircle class="mr-2 h-4 w-4" />
                    Create Project
                </Button>
                
                <!-- Create Dialog -->
                <Dialog v-model:open="isCreateDialogOpen">
                    <DialogContent class="max-w-3xl glass-card backdrop-blur-sm">
                        <DialogHeader>
                            <DialogTitle class="text-xl font-bold text-primary">Create Project</DialogTitle>
                            <DialogDescription>
                                Fill in the details to create a new project.
                            </DialogDescription>
                        </DialogHeader>
                        <form @submit.prevent="submitCreate">
                            <div class="grid gap-4 py-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="grid gap-2">
                                        <Label for="create-name">Project Name</Label>
                                        <Input id="create-name" v-model="createForm.name" required />
                                        <div v-if="createForm.errors.name" class="text-red-500 text-sm">
                                            {{ createForm.errors.name }}
                                        </div>
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="create-shortcode">Shortcode</Label>
                                        <Input id="create-shortcode" v-model="createForm.shortcode" required maxlength="10" placeholder="e.g. PRJ001" />
                                        <div v-if="createForm.errors.shortcode" class="text-red-500 text-sm">
                                            {{ createForm.errors.shortcode }}
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="grid gap-2">
                                        <Label for="create-start-date">Start Date</Label>
                                        <div class="relative">
                                            <Calendar class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                                            <Input id="create-start-date" type="date" v-model="createForm.start_date" required class="pl-10" />
                                        </div>
                                        <div v-if="createForm.errors.start_date" class="text-red-500 text-sm">
                                            {{ createForm.errors.start_date }}
                                        </div>
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="create-end-date">End Date (Optional)</Label>
                                        <div class="relative">
                                            <Calendar class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                                            <Input 
                                                id="create-end-date" 
                                                type="date" 
                                                v-model="createForm.end_date" 
                                                class="pl-10"
                                                :value="createForm.end_date || undefined"
                                            />
                                        </div>
                                        <div v-if="createForm.errors.end_date" class="text-red-500 text-sm">
                                            {{ createForm.errors.end_date }}
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="grid gap-2">
                                    <Label for="create-status">Status</Label>
                                    <div class="relative">
                                        <select 
                                            id="create-status" 
                                            v-model="createForm.status"
                                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                        >
                                            <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                                                {{ option.label }}
                                            </option>
                                        </select>
                                    </div>
                                    <div v-if="createForm.errors.status" class="text-red-500 text-sm">
                                        {{ createForm.errors.status }}
                                    </div>
                                </div>
                                
                                <div class="grid gap-2">
                                    <Label for="create-description">Description (Optional)</Label>
                                    <Textarea id="create-description" v-model="createForm.description" rows="3" />
                                    <div v-if="createForm.errors.description" class="text-red-500 text-sm">
                                        {{ createForm.errors.description }}
                                    </div>
                                </div>
                            </div>
                            <DialogFooter>
                                <Button type="button" variant="outline" @click="isCreateDialogOpen = false">Cancel</Button>
                                <Button type="submit" :disabled="createForm.processing" class="futuristic-glow">Create Project</Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>

            <!-- Projects Table -->
            <div class="bg-card shadow-sm rounded-lg border border-border/50 overflow-hidden">
                <Table>
                    <TableCaption>A list of all projects.</TableCaption>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Shortcode</TableHead>
                            <TableHead>Start Date</TableHead>
                            <TableHead>End Date</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="w-32">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="project in props.projects" :key="project.id" class="hover:bg-muted/50">
                            <TableCell class="font-medium">{{ project.name }}</TableCell>
                            <TableCell>
                                <code class="px-2 py-1 bg-muted rounded">{{ project.shortcode }}</code>
                            </TableCell>
                            <TableCell>{{ formatDate(project.start_date) }}</TableCell>
                            <TableCell>{{ formatDate(project.end_date) }}</TableCell>
                            <TableCell>
                                <span 
                                    class="px-2 py-1 rounded-full text-xs font-medium" 
                                    :class="getStatusBadgeClass(project.status)"
                                >
                                    {{ project.status.replace('_', ' ').charAt(0).toUpperCase() + project.status.replace('_', ' ').slice(1) }}
                                </span>
                            </TableCell>
                            <TableCell>
                                <div class="flex gap-2">
                                    <Button variant="outline" size="icon" @click="editProject(project)" class="hover:text-primary">
                                        <Pencil class="h-4 w-4" />
                                    </Button>
                                    <Button variant="outline" size="icon" @click="viewProject(project)" class="hover:text-primary">
                                        <Eye class="h-4 w-4" />
                                    </Button>
                                    <Button variant="destructive" size="icon" @click="confirmDelete(project)">
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="props.projects.length === 0">
                            <TableCell colspan="6" class="text-center py-4">No projects found. Create one to get started.</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Edit Dialog -->
            <Dialog v-model:open="isEditDialogOpen">
                <DialogContent class="max-w-3xl glass-card backdrop-blur-sm">
                    <DialogHeader>
                        <DialogTitle class="text-xl font-bold text-primary">Edit Project</DialogTitle>
                        <DialogDescription>
                            Update the project details.
                        </DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="submitEdit">
                        <div class="grid gap-4 py-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="grid gap-2">
                                    <Label for="edit-name">Project Name</Label>
                                    <Input id="edit-name" v-model="editForm.name" required />
                                    <div v-if="editForm.errors.name" class="text-red-500 text-sm">
                                        {{ editForm.errors.name }}
                                    </div>
                                </div>
                                <div class="grid gap-2">
                                    <Label for="edit-shortcode">Shortcode</Label>
                                    <Input id="edit-shortcode" v-model="editForm.shortcode" required maxlength="10" placeholder="e.g. PRJ001" />
                                    <div v-if="editForm.errors.shortcode" class="text-red-500 text-sm">
                                        {{ editForm.errors.shortcode }}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="grid gap-2">
                                    <Label for="edit-start-date">Start Date</Label>
                                    <div class="relative">
                                        <Calendar class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                                        <Input id="edit-start-date" type="date" v-model="editForm.start_date" required class="pl-10" />
                                    </div>
                                    <div v-if="editForm.errors.start_date" class="text-red-500 text-sm">
                                        {{ editForm.errors.start_date }}
                                    </div>
                                </div>
                                <div class="grid gap-2">
                                    <Label for="edit-end-date">End Date (Optional)</Label>
                                    <div class="relative">
                                        <Calendar class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                                        <Input 
                                            id="edit-end-date" 
                                            type="date" 
                                            v-model="editForm.end_date" 
                                            class="pl-10"
                                            :value="editForm.end_date || undefined"
                                        />
                                    </div>
                                    <div v-if="editForm.errors.end_date" class="text-red-500 text-sm">
                                        {{ editForm.errors.end_date }}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="grid gap-2">
                                <Label for="edit-status">Status</Label>
                                <div class="relative">
                                    <select 
                                        id="edit-status" 
                                        v-model="editForm.status"
                                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                                            {{ option.label }}
                                        </option>
                                    </select>
                                </div>
                                <div v-if="editForm.errors.status" class="text-red-500 text-sm">
                                    {{ editForm.errors.status }}
                                </div>
                            </div>
                            
                            <div class="grid gap-2">
                                <Label for="edit-description">Description (Optional)</Label>
                                <Textarea id="edit-description" v-model="editForm.description" rows="3" />
                                <div v-if="editForm.errors.description" class="text-red-500 text-sm">
                                    {{ editForm.errors.description }}
                                </div>
                            </div>
                        </div>
                        <DialogFooter>
                            <Button type="button" variant="outline" @click="isEditDialogOpen = false">Cancel</Button>
                            <Button type="submit" :disabled="editForm.processing" class="futuristic-glow">Update Project</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <!-- View Dialog -->
            <Dialog v-model:open="isViewDialogOpen">
                <DialogContent class="max-w-4xl glass-card backdrop-blur-sm">
                    <DialogHeader>
                        <DialogTitle class="text-xl font-bold text-primary">View Project</DialogTitle>
                        <DialogDescription>
                            View the project details.
                        </DialogDescription>
                    </DialogHeader>
                    <div v-if="projectToView" class="py-4">
                        <div class="flex flex-col gap-4">
                            <div class="gradient-border rounded-lg p-4 bg-card/50">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h2 class="text-xl font-bold mb-2 text-foreground">{{ projectToView.name }}</h2>
                                        <div class="flex items-center gap-2 mb-2">
                                            <code class="px-2 py-1 bg-muted rounded text-sm">{{ projectToView.shortcode }}</code>
                                            <span 
                                                class="px-2 py-1 rounded-full text-xs font-medium" 
                                                :class="getStatusBadgeClass(projectToView.status)"
                                            >
                                                {{ projectToView.status.replace('_', ' ').charAt(0).toUpperCase() + projectToView.status.replace('_', ' ').slice(1) }}
                                            </span>
                                        </div>
                                        <p class="text-muted-foreground" v-if="projectToView.description">{{ projectToView.description }}</p>
                                        <p class="text-muted-foreground italic" v-else>No description provided</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-card/80 border border-border/50 rounded-lg p-4 shadow-sm">
                                    <div class="flex items-center gap-2 mb-2">
                                        <Calendar class="h-4 w-4 text-primary" />
                                        <h3 class="text-sm font-medium text-foreground">Start Date</h3>
                                    </div>
                                    <p class="text-lg">{{ formatDate(projectToView.start_date) }}</p>
                                </div>
                                
                                <div class="bg-card/80 border border-border/50 rounded-lg p-4 shadow-sm">
                                    <div class="flex items-center gap-2 mb-2">
                                        <Calendar class="h-4 w-4 text-primary" />
                                        <h3 class="text-sm font-medium text-foreground">End Date</h3>
                                    </div>
                                    <p class="text-lg">{{ formatDate(projectToView.end_date) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="isViewDialogOpen = false" class="futuristic-glow">Close</Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <!-- Delete Dialog -->
            <Dialog v-model:open="isDeleteDialogOpen">
                <DialogContent class="glass-card backdrop-blur-sm">
                    <DialogHeader>
                        <DialogTitle class="text-xl font-bold text-primary">Delete Project</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to delete this project? This action cannot be undone.
                        </DialogDescription>
                    </DialogHeader>
                    <div v-if="projectToDelete" class="py-4">
                        <div class="bg-destructive/10 border border-destructive/30 rounded-lg p-4 mb-4">
                            <p class="font-medium mb-2">{{ projectToDelete.name }}</p>
                            <p class="text-sm text-muted-foreground">Shortcode: {{ projectToDelete.shortcode }}</p>
                            <p class="text-sm text-muted-foreground">Start Date: {{ formatDate(projectToDelete.start_date) }}</p>
                        </div>
                        <p class="text-sm text-destructive">Deleting this project will remove all associated data permanently.</p>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="isDeleteDialogOpen = false">Cancel</Button>
                        <Button type="button" variant="destructive" @click="submitDelete" :disabled="!projectToDelete">Delete Project</Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
