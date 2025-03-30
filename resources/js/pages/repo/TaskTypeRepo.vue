<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { ref } from 'vue';
import { 
    Dialog, 
    DialogContent, 
    DialogDescription, 
    DialogFooter, 
    DialogHeader, 
    DialogTitle, 
    DialogTrigger 
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
import { PlusCircle, Pencil, Trash2 } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { useToast } from '@/components/ui/toast';

const { toast } = useToast();

interface TaskType {
    id: number;
    name: string;
    description: string;
}

interface Props {
    taskTypes: TaskType[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Repo',
        href: ''
    },
    {
        title: 'Task Types',
        href: '/repo/task-types',
    },
];

const props = defineProps<Props>();

// Form for creating a new task type
const createForm = useForm({
    name: '',
    description: '',
});

// Form for editing a task type
const editForm = useForm({
    id: 0,
    name: '',
    description: '',
});

const isCreateDialogOpen = ref(false);
const isEditDialogOpen = ref(false);
const isDeleteDialogOpen = ref(false);
const taskTypeToDelete = ref<TaskType | null>(null);

// Submit create form
const submitCreate = () => {
    createForm.post(route('repo.task-types.store'), {
        onSuccess: () => {
            isCreateDialogOpen.value = false;
            createForm.reset();
            toast({
                title: "Task Type Created",
                description: "The task type has been successfully created.",
                duration: 5000,
            });
        },
    });
};

// Edit task type
const editTaskType = (taskType: TaskType) => {
    editForm.id = taskType.id;
    editForm.name = taskType.name;
    editForm.description = taskType.description;
    isEditDialogOpen.value = true;
};

// Submit edit form
const submitEdit = () => {
    editForm.put(route('repo.task-types.update', editForm.id), {
        onSuccess: () => {
            isEditDialogOpen.value = false;
            toast({
                title: "Task Type Updated",
                description: "The task type has been successfully updated.",
                duration: 5000,
            });
        },
    });
};

// Delete task type
const confirmDelete = (taskType: TaskType) => {
    taskTypeToDelete.value = taskType;
    isDeleteDialogOpen.value = true;
};

// Submit delete
const submitDelete = () => {
    if (taskTypeToDelete.value) {
        useForm({}).delete(route('repo.task-types.destroy', taskTypeToDelete.value.id), {
            onSuccess: () => {
                isDeleteDialogOpen.value = false;
                taskTypeToDelete.value = null;
                toast({
                    title: "Task Type Deleted",
                    description: "The task type has been successfully deleted.",
                    variant: "destructive",
                    duration: 5000,
                });
            },
        });
    }
};
</script>

<template>
    <Head title="Task Type Repository" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Task Type Repository</h1>
                
                <!-- Create Dialog -->
                <Dialog v-model:open="isCreateDialogOpen">
                    <DialogTrigger as-child>
                        <Button class="flex items-center gap-2">
                            <PlusCircle class="h-4 w-4" />
                            Add Task Type
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Add New Task Type</DialogTitle>
                            <DialogDescription>
                                Create a new task type for your repository.
                            </DialogDescription>
                        </DialogHeader>
                        <form @submit.prevent="submitCreate">
                            <div class="grid gap-4 py-4">
                                <div class="grid gap-2">
                                    <Label for="name">Name</Label>
                                    <Input id="name" v-model="createForm.name" required />
                                    <div v-if="createForm.errors.name" class="text-red-500 text-sm">
                                        {{ createForm.errors.name }}
                                    </div>
                                </div>
                                <div class="grid gap-2">
                                    <Label for="description">Description</Label>
                                    <Textarea id="description" v-model="createForm.description" required />
                                    <div v-if="createForm.errors.description" class="text-red-500 text-sm">
                                        {{ createForm.errors.description }}
                                    </div>
                                </div>
                            </div>
                            <DialogFooter>
                                <Button type="button" variant="outline" @click="isCreateDialogOpen = false">Cancel</Button>
                                <Button type="submit" :disabled="createForm.processing">Save</Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>

            <!-- Task Types Table -->
            <Table>
                <TableCaption>A list of all task types in your repository.</TableCaption>
                <TableHeader>
                    <TableRow>
                        <TableHead>Name</TableHead>
                        <TableHead>Description</TableHead>
                        <TableHead class="w-32">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="taskType in props.taskTypes" :key="taskType.id">
                        <TableCell>{{ taskType.name }}</TableCell>
                        <TableCell>{{ taskType.description }}</TableCell>
                        <TableCell>
                            <div class="flex gap-2">
                                <Button variant="outline" size="icon" @click="editTaskType(taskType)">
                                    <Pencil class="h-4 w-4" />
                                </Button>
                                <Button variant="destructive" size="icon" @click="confirmDelete(taskType)">
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                    <TableRow v-if="props.taskTypes.length === 0">
                        <TableCell colspan="3" class="text-center py-4">No task types found. Create one to get started.</TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <!-- Edit Dialog -->
            <Dialog v-model:open="isEditDialogOpen">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Edit Task Type</DialogTitle>
                        <DialogDescription>
                            Update the task type details.
                        </DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="submitEdit">
                        <div class="grid gap-4 py-4">
                            <div class="grid gap-2">
                                <Label for="edit-name">Name</Label>
                                <Input id="edit-name" v-model="editForm.name" required />
                                <div v-if="editForm.errors.name" class="text-red-500 text-sm">
                                    {{ editForm.errors.name }}
                                </div>
                            </div>
                            <div class="grid gap-2">
                                <Label for="edit-description">Description</Label>
                                <Textarea id="edit-description" v-model="editForm.description" required />
                                <div v-if="editForm.errors.description" class="text-red-500 text-sm">
                                    {{ editForm.errors.description }}
                                </div>
                            </div>
                        </div>
                        <DialogFooter>
                            <Button type="button" variant="outline" @click="isEditDialogOpen = false">Cancel</Button>
                            <Button type="submit" :disabled="editForm.processing">Update</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <!-- Delete Dialog -->
            <Dialog v-model:open="isDeleteDialogOpen">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete Task Type</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to delete this task type? This action cannot be undone.
                        </DialogDescription>
                    </DialogHeader>
                    <div v-if="taskTypeToDelete" class="py-4">
                        <p><strong>Name:</strong> {{ taskTypeToDelete.name }}</p>
                        <p><strong>Description:</strong> {{ taskTypeToDelete.description }}</p>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="isDeleteDialogOpen = false">Cancel</Button>
                        <Button type="button" variant="destructive" @click="submitDelete">Delete</Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>