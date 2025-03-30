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
import { Switch } from '@/components/ui/switch';
import { PlusCircle, Pencil, Trash2, Eye, Plus, MinusCircle } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { useToast } from '@/components/ui/toast';

const { toast } = useToast();

interface SubTask {
    id?: number;
    name: string;
    description: string;
    task_repo_id?: number;
    [key: string]: string | number | undefined;
}

interface Task {
    id: number;
    name: string;
    description: string;
    phase_repo_id: number;
    category_repo_id: number;
    task_type_repo_id: number;
    resource_repo_id?: number | null;
    has_subtasks: boolean;
    subtasks: SubTask[];
    phase?: {
        id: number;
        name: string;
    };
    category?: {
        id: number;
        name: string;
    };
    taskType?: {
        id: number;
        name: string;
    };
    resource?: {
        id: number;
        name: string;
    };
}

interface Phase {
    id: number;
    name: string;
    description: string;
    order: number;
}

interface Category {
    id: number;
    name: string;
    description: string;
}

interface TaskType {
    id: number;
    name: string;
    description: string;
}

interface Resource {
    id: number;
    name: string;
    description: string;
}

interface Props {
    tasks: Task[];
    phases: Phase[];
    categories: Category[];
    taskTypes: TaskType[];
    resources: Resource[];
    task?: Task;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Repo',
        href: ''
    },
    {
        title: 'Tasks',
        href: '/repo/tasks',
    },
];

const props = defineProps<Props>();

// Form for creating a new task
const createForm = useForm<{
    name: string;
    description: string;
    phase_repo_id: number | null;
    category_repo_id: number | null;
    task_type_repo_id: number | null;
    resource_repo_id: number | null;
    has_subtasks: boolean;
    subtasks: SubTask[];
}>({
    name: '',
    description: '',
    phase_repo_id: null,
    category_repo_id: null,
    task_type_repo_id: null,
    resource_repo_id: null,
    has_subtasks: false,
    subtasks: [],
});

// Form for editing a task
const editForm = useForm<{
    id: number;
    name: string;
    description: string;
    phase_repo_id: number | null;
    category_repo_id: number | null;
    task_type_repo_id: number | null;
    resource_repo_id: number | null;
    has_subtasks: boolean;
    subtasks: SubTask[];
}>({
    id: 0,
    name: '',
    description: '',
    phase_repo_id: null,
    category_repo_id: null,
    task_type_repo_id: null,
    resource_repo_id: null,
    has_subtasks: false,
    subtasks: [],
});

const isCreateDialogOpen = ref(false);
const isEditDialogOpen = ref(false);
const isDeleteDialogOpen = ref(false);
const isViewDialogOpen = ref(false);
const taskToDelete = ref<Task | null>(null);
const taskToView = ref<Task | null>(null);

// Add a new subtask to the create form
const addSubtask = () => {
    if (Array.isArray(createForm.subtasks)) {
        createForm.subtasks.push({
            name: '',
            description: '',
        });
    }
};

// Remove a subtask from the create form
const removeSubtask = (index: number) => {
    if (Array.isArray(createForm.subtasks)) {
        createForm.subtasks.splice(index, 1);
    }
};

// Add a new subtask to the edit form
const addEditSubtask = () => {
    if (Array.isArray(editForm.subtasks)) {
        editForm.subtasks.push({
            name: '',
            description: '',
        });
    }
};

// Remove a subtask from the edit form
const removeEditSubtask = (index: number) => {
    if (Array.isArray(editForm.subtasks)) {
        editForm.subtasks.splice(index, 1);
    }
};

// Submit create form
const submitCreate = () => {
    createForm.post(route('repo.tasks.store'), {
        onSuccess: () => {
            isCreateDialogOpen.value = false;
            createForm.reset();
            toast({
                title: "Task Created",
                description: "The task has been successfully created.",
                duration: 5000,
            });
        },
    });
};

// Edit task
const editTask = (task: Task) => {
    editForm.id = task.id;
    editForm.name = task.name;
    editForm.description = task.description;
    editForm.phase_repo_id = task.phase_repo_id;
    editForm.category_repo_id = task.category_repo_id;
    editForm.task_type_repo_id = task.task_type_repo_id;
    editForm.resource_repo_id = task.resource_repo_id;
    editForm.has_subtasks = task.has_subtasks;
    editForm.subtasks = task.subtasks ? [...task.subtasks] : [];
    isEditDialogOpen.value = true;
};

// View task
const viewTask = (task: Task) => {
    taskToView.value = task;
    isViewDialogOpen.value = true;
};

// Submit edit form
const submitEdit = () => {
    editForm.put(route('repo.tasks.update', editForm.id), {
        onSuccess: () => {
            isEditDialogOpen.value = false;
            toast({
                title: "Task Updated",
                description: "The task has been successfully updated.",
                duration: 5000,
            });
        },
    });
};

// Delete task
const confirmDelete = (task: Task) => {
    taskToDelete.value = task;
    isDeleteDialogOpen.value = true;
};

// Submit delete
const submitDelete = () => {
    if (taskToDelete.value) {
        useForm({}).delete(route('repo.tasks.destroy', { task: taskToDelete.value.id }), {
            onSuccess: () => {
                isDeleteDialogOpen.value = false;
                taskToDelete.value = null;
                toast({
                    title: "Task Deleted",
                    description: "The task has been successfully deleted.",
                    variant: "destructive",
                    duration: 5000,
                });
            },
        });
    }
};

// Get phase name by ID
const getPhaseName = (phaseId: number) => {
    const phase = props.phases.find(p => p.id === phaseId);
    return phase ? phase.name : 'Unknown Phase';
};

// Get category name by ID
const getCategoryName = (categoryId: number) => {
    const category = props.categories.find(c => c.id === categoryId);
    return category ? category.name : 'Unknown Category';
};

// Get task type name by ID
const getTaskTypeName = (taskTypeId: number) => {
    const taskType = props.taskTypes.find(t => t.id === taskTypeId);
    return taskType ? taskType.name : 'Unknown Task Type';
};

// Get resource name by ID
const getResourceName = (resourceId: number | null | undefined) => {
    if (resourceId === null || resourceId === undefined) return 'None';
    const resource = props.resources.find(r => r.id === resourceId);
    return resource ? resource.name : 'Unknown Resource';
};

// Filters
const phaseFilter = ref<number | null>(null);
const categoryFilter = ref<number | null>(null);
const taskTypeFilter = ref<number | null>(null);
const resourceFilter = ref<number | null>(null);

// Filtered tasks
const filteredTasks = computed(() => {
    return props.tasks.filter(task => {
        const matchesPhase = phaseFilter.value === null || task.phase_repo_id === phaseFilter.value;
        const matchesCategory = categoryFilter.value === null || task.category_repo_id === categoryFilter.value;
        const matchesTaskType = taskTypeFilter.value === null || task.task_type_repo_id === taskTypeFilter.value;
        const matchesResource = resourceFilter.value === null || task.resource_repo_id === resourceFilter.value;
        
        return matchesPhase && matchesCategory && matchesTaskType && matchesResource;
    });
});

// Clear all filters
const clearFilters = () => {
    phaseFilter.value = null;
    categoryFilter.value = null;
    taskTypeFilter.value = null;
    resourceFilter.value = null;
};
</script>

<template>
    <Head title="Task Repository" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Task Repository</h1>
                
                <!-- Create Dialog -->
                <Dialog v-model:open="isCreateDialogOpen">
                    <DialogTrigger as-child>
                        <Button class="flex items-center gap-2">
                            <PlusCircle class="h-4 w-4" />
                            Add Task
                        </Button>
                    </DialogTrigger>
                    <DialogContent class="max-w-3xl max-h-[90vh] overflow-y-auto">
                        <DialogHeader>
                            <DialogTitle>Add New Task</DialogTitle>
                            <DialogDescription>
                                Create a new task for your repository.
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
                                
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div class="grid gap-2">
                                        <Label for="phase">Phase</Label>
                                        <div class="relative">
                                            <select 
                                                id="phase" 
                                                v-model="createForm.phase_repo_id"
                                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                                required
                                            >
                                                <option value="" disabled selected>Select a phase</option>
                                                <option v-for="phase in props.phases" :key="phase.id" :value="phase.id">
                                                    {{ phase.name }}
                                                </option>
                                                <option v-if="props.phases.length === 0" disabled>No phases available</option>
                                            </select>
                                        </div>
                                        <div v-if="createForm.errors.phase_repo_id" class="text-red-500 text-sm">
                                            {{ createForm.errors.phase_repo_id }}
                                        </div>
                                    </div>
                                    
                                    <div class="grid gap-2">
                                        <Label for="category">Category</Label>
                                        <div class="relative">
                                            <select 
                                                id="category" 
                                                v-model="createForm.category_repo_id"
                                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                                required
                                            >
                                                <option value="" disabled selected>Select a category</option>
                                                <option v-for="category in props.categories" :key="category.id" :value="category.id">
                                                    {{ category.name }}
                                                </option>
                                                <option v-if="props.categories.length === 0" disabled>No categories available</option>
                                            </select>
                                        </div>
                                        <div v-if="createForm.errors.category_repo_id" class="text-red-500 text-sm">
                                            {{ createForm.errors.category_repo_id }}
                                        </div>
                                    </div>
                                    
                                    <div class="grid gap-2">
                                        <Label for="taskType">Task Type</Label>
                                        <div class="relative">
                                            <select 
                                                id="taskType" 
                                                v-model="createForm.task_type_repo_id"
                                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                                required
                                            >
                                                <option value="" disabled selected>Select a task type</option>
                                                <option v-for="taskType in props.taskTypes" :key="taskType.id" :value="taskType.id">
                                                    {{ taskType.name }}
                                                </option>
                                                <option v-if="props.taskTypes.length === 0" disabled>No task types available</option>
                                            </select>
                                        </div>
                                        <div v-if="createForm.errors.task_type_repo_id" class="text-red-500 text-sm">
                                            {{ createForm.errors.task_type_repo_id }}
                                        </div>
                                    </div>
                                    
                                    <div class="grid gap-2">
                                        <Label for="resource">Resource</Label>
                                        <div class="relative">
                                            <select 
                                                id="resource" 
                                                v-model="createForm.resource_repo_id"
                                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                            >
                                                <option value="" disabled selected>Select a resource</option>
                                                <option v-for="resource in props.resources" :key="resource.id" :value="resource.id">
                                                    {{ resource.name }}
                                                </option>
                                                <option v-if="props.resources.length === 0" disabled>No resources available</option>
                                            </select>
                                        </div>
                                        <div v-if="createForm.errors.resource_repo_id" class="text-red-500 text-sm">
                                            {{ createForm.errors.resource_repo_id }}
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center space-x-2">
                                    <Switch id="has-subtasks" v-model="createForm.has_subtasks" />
                                    <Label for="has-subtasks">Has Subtasks</Label>
                                </div>
                                
                                <!-- Subtasks Section -->
                                <div v-if="createForm.has_subtasks" class="border border-gray-200 rounded-md p-4 mt-4">
                                    <div class="flex justify-between items-center mb-4">
                                        <h3 class="text-lg font-medium">Subtasks</h3>
                                        <Button type="button" variant="outline" size="sm" @click="addSubtask" class="flex items-center gap-1">
                                            <Plus class="h-4 w-4" />
                                            Add Subtask
                                        </Button>
                                    </div>
                                    
                                    <div v-if="createForm.subtasks.length === 0" class="text-center py-4 text-gray-500">
                                        No subtasks added. Click "Add Subtask" to create one.
                                    </div>
                                    
                                    <div v-for="(subtask, index) in createForm.subtasks" :key="index" class="border border-gray-200 rounded-md p-3 mb-3">
                                        <div class="flex justify-between items-start mb-2">
                                            <h4 class="font-medium">Subtask {{ index + 1 }}</h4>
                                            <Button type="button" variant="ghost" size="sm" @click="removeSubtask(index)" class="text-red-500 h-8 w-8 p-0">
                                                <MinusCircle class="h-4 w-4" />
                                            </Button>
                                        </div>
                                        
                                        <div class="grid gap-3">
                                            <div class="grid gap-1">
                                                <Label :for="`subtask-${index}-name`">Name</Label>
                                                <Input :id="`subtask-${index}-name`" v-model="subtask.name" required />
                                            </div>
                                            
                                            <div class="grid gap-1">
                                                <Label :for="`subtask-${index}-description`">Description</Label>
                                                <Textarea :id="`subtask-${index}-description`" v-model="subtask.description" required rows="2" />
                                            </div>
                                        </div>
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

            <!-- Tasks Table -->
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Tasks</h2>
            </div>

            <!-- Filters -->
            <div class="bg-muted/40 p-4 rounded-lg mb-4">
                <div class="flex flex-col space-y-4">
                    <h3 class="text-sm font-medium">Filters</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="grid gap-2">
                            <Label for="phase-filter">Phase</Label>
                            <div class="relative">
                                <select 
                                    id="phase-filter" 
                                    v-model="phaseFilter"
                                    class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option :value="null">All Phases</option>
                                    <option v-for="phase in props.phases" :key="phase.id" :value="phase.id">
                                        {{ phase.name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="grid gap-2">
                            <Label for="category-filter">Category</Label>
                            <div class="relative">
                                <select 
                                    id="category-filter" 
                                    v-model="categoryFilter"
                                    class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option :value="null">All Categories</option>
                                    <option v-for="category in props.categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="grid gap-2">
                            <Label for="task-type-filter">Task Type</Label>
                            <div class="relative">
                                <select 
                                    id="task-type-filter" 
                                    v-model="taskTypeFilter"
                                    class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option :value="null">All Task Types</option>
                                    <option v-for="taskType in props.taskTypes" :key="taskType.id" :value="taskType.id">
                                        {{ taskType.name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="grid gap-2">
                            <Label for="resource-filter">Resource</Label>
                            <div class="relative">
                                <select 
                                    id="resource-filter" 
                                    v-model="resourceFilter"
                                    class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option :value="null">All Resources</option>
                                    <option v-for="resource in props.resources" :key="resource.id" :value="resource.id">
                                        {{ resource.name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="flex items-end">
                            <Button variant="outline" @click="clearFilters" class="w-full">
                                Clear Filters
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <Table>
                <TableCaption>A list of all tasks in your repository.</TableCaption>
                <TableHeader>
                    <TableRow>
                        <TableHead>Name</TableHead>
                        <TableHead>Phase</TableHead>
                        <TableHead>Category</TableHead>
                        <TableHead>Task Type</TableHead>
                        <TableHead>Resource</TableHead>
                        <TableHead>Has Subtasks</TableHead>
                        <TableHead class="w-32">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="task in filteredTasks" :key="task.id">
                        <TableCell>{{ task.name }}</TableCell>
                        <TableCell>{{ task.phase ? task.phase.name : getPhaseName(task.phase_repo_id) }}</TableCell>
                        <TableCell>{{ task.category ? task.category.name : getCategoryName(task.category_repo_id) }}</TableCell>
                        <TableCell>{{ task.taskType ? task.taskType.name : getTaskTypeName(task.task_type_repo_id) }}</TableCell>
                        <TableCell>{{ task.resource ? task.resource.name : getResourceName(task.resource_repo_id) }}</TableCell>
                        <TableCell>{{ task.has_subtasks ? 'Yes' : 'No' }}</TableCell>
                        <TableCell>
                            <div class="flex gap-2">
                                <Button variant="outline" size="icon" @click="viewTask(task)">
                                    <Eye class="h-4 w-4" />
                                </Button>
                                <Button variant="outline" size="icon" @click="editTask(task)">
                                    <Pencil class="h-4 w-4" />
                                </Button>
                                <Button variant="destructive" size="icon" @click="confirmDelete(task)">
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                    <TableRow v-if="filteredTasks.length === 0">
                        <TableCell colspan="7" class="text-center py-4">No tasks found. Create one to get started.</TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <!-- View Dialog -->
            <Dialog v-model:open="isViewDialogOpen">
                <DialogContent class="max-w-4xl glass-card backdrop-blur-sm">
                    <DialogHeader>
                        <DialogTitle class="text-xl font-bold text-primary">View Task</DialogTitle>
                        <DialogDescription>
                            View the task details and subtasks.
                        </DialogDescription>
                    </DialogHeader>
                    <div v-if="taskToView" class="py-4">
                        <div class="flex flex-col gap-4">
                            <div class="gradient-border rounded-lg p-4 bg-card/50">
                                <h2 class="text-xl font-bold mb-2 text-foreground">{{ taskToView.name }}</h2>
                                <p class="text-muted-foreground">{{ taskToView.description }}</p>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-2">
                                <div class="bg-secondary/50 rounded-lg p-3 hover:bg-secondary/80 transition-colors">
                                    <h3 class="font-medium text-sm text-muted-foreground mb-1">Phase</h3>
                                    <p class="font-semibold">{{ taskToView.phase ? taskToView.phase.name : getPhaseName(taskToView.phase_repo_id) }}</p>
                                </div>
                                <div class="bg-secondary/50 rounded-lg p-3 hover:bg-secondary/80 transition-colors">
                                    <h3 class="font-medium text-sm text-muted-foreground mb-1">Category</h3>
                                    <p class="font-semibold">{{ taskToView.category ? taskToView.category.name : getCategoryName(taskToView.category_repo_id) }}</p>
                                </div>
                                <div class="bg-secondary/50 rounded-lg p-3 hover:bg-secondary/80 transition-colors">
                                    <h3 class="font-medium text-sm text-muted-foreground mb-1">Task Type</h3>
                                    <p class="font-semibold">{{ taskToView.taskType ? taskToView.taskType.name : getTaskTypeName(taskToView.task_type_repo_id) }}</p>
                                </div>
                                <div class="bg-secondary/50 rounded-lg p-3 hover:bg-secondary/80 transition-colors">
                                    <h3 class="font-medium text-sm text-muted-foreground mb-1">Resource</h3>
                                    <p class="font-semibold">{{ taskToView.resource ? taskToView.resource.name : getResourceName(taskToView.resource_repo_id) }}</p>
                                </div>
                            </div>
                            
                            <div v-if="taskToView.has_subtasks && taskToView.subtasks && taskToView.subtasks.length > 0" class="mt-4">
                                <div class="flex items-center gap-2 mb-4">
                                    <h3 class="text-lg font-bold text-foreground">Subtasks</h3>
                                    <div class="bg-primary/10 text-primary text-xs font-medium px-2 py-1 rounded-full">
                                        {{ taskToView.subtasks.length }} {{ taskToView.subtasks.length === 1 ? 'subtask' : 'subtasks' }}
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div 
                                        v-for="(subtask, index) in taskToView.subtasks" 
                                        :key="subtask.id || index" 
                                        class="futuristic-glow bg-card/80 border border-border/50 rounded-lg p-4 hover:border-primary/30 transition-all duration-200"
                                    >
                                        <div class="flex items-start gap-3">
                                            <div class="flex-shrink-0 w-6 h-6 bg-primary/10 text-primary rounded-full flex items-center justify-center mt-1">
                                                {{ index + 1 }}
                                            </div>
                                            <div class="flex-1">
                                                <h4 class="font-semibold text-foreground mb-2">{{ subtask.name }}</h4>
                                                <p class="text-muted-foreground text-sm">{{ subtask.description }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div v-else class="mt-4 text-center p-6 bg-muted/30 rounded-lg border border-border/50">
                                <p class="text-muted-foreground">This task has no subtasks.</p>
                            </div>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="isViewDialogOpen = false" class="futuristic-glow">Close</Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <!-- Edit Dialog -->
            <Dialog v-model:open="isEditDialogOpen">
                <DialogContent class="max-w-3xl max-h-[90vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>Edit Task</DialogTitle>
                        <DialogDescription>
                            Update the task details.
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
                            
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="grid gap-2">
                                    <Label for="edit-phase">Phase</Label>
                                    <div class="relative">
                                        <select 
                                            id="edit-phase" 
                                            v-model="editForm.phase_repo_id"
                                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                            required
                                        >
                                            <option value="" disabled selected>Select a phase</option>
                                            <option v-for="phase in props.phases" :key="phase.id" :value="phase.id">
                                                {{ phase.name }}
                                            </option>
                                            <option v-if="props.phases.length === 0" disabled>No phases available</option>
                                        </select>
                                    </div>
                                    <div v-if="editForm.errors.phase_repo_id" class="text-red-500 text-sm">
                                        {{ editForm.errors.phase_repo_id }}
                                    </div>
                                </div>
                                
                                <div class="grid gap-2">
                                    <Label for="edit-category">Category</Label>
                                    <div class="relative">
                                        <select 
                                            id="edit-category" 
                                            v-model="editForm.category_repo_id"
                                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                            required
                                        >
                                            <option value="" disabled selected>Select a category</option>
                                            <option v-for="category in props.categories" :key="category.id" :value="category.id">
                                                {{ category.name }}
                                            </option>
                                            <option v-if="props.categories.length === 0" disabled>No categories available</option>
                                        </select>
                                    </div>
                                    <div v-if="editForm.errors.category_repo_id" class="text-red-500 text-sm">
                                        {{ editForm.errors.category_repo_id }}
                                    </div>
                                </div>
                                
                                <div class="grid gap-2">
                                    <Label for="edit-taskType">Task Type</Label>
                                    <div class="relative">
                                        <select 
                                            id="edit-taskType" 
                                            v-model="editForm.task_type_repo_id"
                                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                            required
                                        >
                                            <option value="" disabled selected>Select a task type</option>
                                            <option v-for="taskType in props.taskTypes" :key="taskType.id" :value="taskType.id">
                                                {{ taskType.name }}
                                            </option>
                                            <option v-if="props.taskTypes.length === 0" disabled>No task types available</option>
                                        </select>
                                    </div>
                                    <div v-if="editForm.errors.task_type_repo_id" class="text-red-500 text-sm">
                                        {{ editForm.errors.task_type_repo_id }}
                                    </div>
                                </div>
                                
                                <div class="grid gap-2">
                                    <Label for="edit-resource">Resource</Label>
                                    <div class="relative">
                                        <select 
                                            id="edit-resource" 
                                            v-model="editForm.resource_repo_id"
                                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                        >
                                            <option value="" disabled selected>Select a resource</option>
                                            <option v-for="resource in props.resources" :key="resource.id" :value="resource.id">
                                                {{ resource.name }}
                                            </option>
                                            <option v-if="props.resources.length === 0" disabled>No resources available</option>
                                        </select>
                                    </div>
                                    <div v-if="editForm.errors.resource_repo_id" class="text-red-500 text-sm">
                                        {{ editForm.errors.resource_repo_id }}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                <Switch id="edit-has-subtasks" v-model="editForm.has_subtasks" />
                                <Label for="edit-has-subtasks">Has Subtasks</Label>
                            </div>
                            
                            <!-- Subtasks Section -->
                            <div v-if="editForm.has_subtasks" class="border border-gray-200 rounded-md p-4 mt-4">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-medium">Subtasks</h3>
                                    <Button type="button" variant="outline" size="sm" @click="addEditSubtask" class="flex items-center gap-1">
                                        <Plus class="h-4 w-4" />
                                        Add Subtask
                                    </Button>
                                </div>
                                
                                <div v-if="editForm.subtasks.length === 0" class="text-center py-4 text-gray-500">
                                    No subtasks added. Click "Add Subtask" to create one.
                                </div>
                                
                                <div v-for="(subtask, index) in editForm.subtasks" :key="subtask.id || index" class="border border-gray-200 rounded-md p-3 mb-3">
                                    <div class="flex justify-between items-start mb-2">
                                        <h4 class="font-medium">Subtask {{ index + 1 }}</h4>
                                        <Button type="button" variant="ghost" size="sm" @click="removeEditSubtask(index)" class="text-red-500 h-8 w-8 p-0">
                                            <MinusCircle class="h-4 w-4" />
                                        </Button>
                                    </div>
                                    
                                    <div class="grid gap-3">
                                        <div class="grid gap-1">
                                            <Label :for="`edit-subtask-${index}-name`">Name</Label>
                                            <Input :id="`edit-subtask-${index}-name`" v-model="subtask.name" required />
                                        </div>
                                        
                                        <div class="grid gap-1">
                                            <Label :for="`edit-subtask-${index}-description`">Description</Label>
                                            <Textarea :id="`edit-subtask-${index}-description`" v-model="subtask.description" required rows="2" />
                                        </div>
                                    </div>
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
                        <DialogTitle>Delete Task</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to delete this task? This action cannot be undone and will also delete all associated subtasks.
                        </DialogDescription>
                    </DialogHeader>
                    <div v-if="taskToDelete" class="py-4">
                        <p><strong>Name:</strong> {{ taskToDelete.name }}</p>
                        <p><strong>Description:</strong> {{ taskToDelete.description }}</p>
                        <p v-if="taskToDelete.has_subtasks"><strong>Warning:</strong> This task has subtasks that will also be deleted.</p>
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