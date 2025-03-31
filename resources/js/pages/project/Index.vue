<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { ref, reactive, computed, watch } from 'vue';
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
import { PlusCircle, Pencil, Trash2, Eye, Calendar, Book, CheckSquare } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { useToast } from '@/components/ui/toast';
import { format, addDays, nextMonday, previousFriday, isFriday, isMonday, parseISO } from 'date-fns';

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
    phases?: Phase[];
    notebooks?: Notebook[];
    resources?: ProjectResource[];
    phase_tasks?: ProjectPhaseTask[];
}

interface Phase {
    id: number;
    name: string;
    description: string;
    order: number;
    pivot?: {
        order: number;
        start_date: string;
        end_date: string;
    };
}

interface Notebook {
    id: number;
    name: string;
    description: string;
    content: string;
    pivot?: {
        created_at: string;
        updated_at: string;
    };
}

interface Resource {
    id: number;
    name: string;
    description: string;
}

interface User {
    id: number;
    name: string;
    email: string;
    role_id: number;
}

interface ProjectResource {
    id?: number;
    resource_id: number | null;
    user_id: number | null;
    [key: string]: any;
}

interface Task {
    id: number;
    name: string;
    description: string;
    phase_repo_id: number;
    task_type_repo_id: number;
}

interface TaskType {
    id: number;
    name: string;
    description: string;
}

interface ProjectPhaseTask {
    id?: number;
    project_id: number;
    phase_repo_id: number;
    task_repo_id: number;
    start_date: string;
    end_date: string;
    status: string;
    task_definition?: Task;
}

interface PhaseTask {
    phase_id: number;
    task_ids: number[];
}

interface Props {
    projects: Project[];
    phases: Phase[];
    notebooks: Notebook[];
    resources: Resource[];
    users: User[];
    tasks: Task[];
    taskTypes: TaskType[];
}

const props = defineProps<Props>();

// Form for creating a new project
interface CreateForm {
    name: string;
    shortcode: string;
    start_date: string;
    end_date: string;
    description: string;
    status: string;
    phase_ids: number[];
    phase_dates: {
        id: number;
        start_date: string;
        end_date: string;
    }[];
    notebook_ids: number[];
    resources: ProjectResource[];
    task_ids: number[];
    [key: string]: any;
}

const createForm = useForm<CreateForm>({
    name: '',
    shortcode: '',
    start_date: format(new Date(), 'yyyy-MM-dd'),
    end_date: '',
    description: '',
    status: 'upcoming',
    phase_ids: [],
    phase_dates: [],
    notebook_ids: [],
    resources: [{
        resource_id: null,
        user_id: null
    }],
    task_ids: [],
});

// Form for editing a project
interface EditForm {
    id: number;
    name: string;
    shortcode: string;
    start_date: string;
    end_date: string;
    description: string;
    status: string;
    phase_ids: number[];
    phase_dates: {
        id: number;
        start_date: string;
        end_date: string;
    }[];
    notebook_ids: number[];
    resources: ProjectResource[];
    task_ids: number[];
    [key: string]: any;
}

const editForm = useForm<EditForm>({
    id: 0,
    name: '',
    shortcode: '',
    start_date: '',
    end_date: '',
    description: '',
    status: 'upcoming',
    phase_ids: [],
    phase_dates: [],
    notebook_ids: [],
    resources: [{ resource_id: null, user_id: null }],
    task_ids: [],
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

// Calculate phase dates based on project start date and selected phases
const calculatePhaseDates = (startDate: string, phaseIds: number[]) => {
    if (!startDate || !phaseIds.length) return [];
    
    const phaseDates = [];
    let currentDate = parseISO(startDate);
    
    for (let i = 0; i < phaseIds.length; i++) {
        // If this is not the first phase, start on the next Monday
        if (i > 0) {
            currentDate = nextMonday(addDays(currentDate, 3)); // Move to next Monday
        }
        
        // End date is the Friday of the same week
        let endDate = currentDate;
        if (!isFriday(currentDate)) {
            endDate = previousFriday(addDays(currentDate, 7)); // Get the next Friday
        }
        
        phaseDates.push({
            id: phaseIds[i],
            start_date: format(currentDate, 'yyyy-MM-dd'),
            end_date: format(endDate, 'yyyy-MM-dd')
        });
        
        // Move to the next phase start date
        currentDate = endDate;
    }
    
    return phaseDates;
};

// Watch for changes to start date or phase selections to auto-calculate dates
watch(
    [() => createForm.start_date, () => createForm.phase_ids],
    ([newStartDate, newPhaseIds]) => {
        if (newStartDate && newPhaseIds.length) {
            createForm.phase_dates = calculatePhaseDates(newStartDate, newPhaseIds);
        }
    },
    { deep: true }
);

watch(
    [() => editForm.start_date, () => editForm.phase_ids],
    ([newStartDate, newPhaseIds]) => {
        if (newStartDate && newPhaseIds.length) {
            editForm.phase_dates = calculatePhaseDates(newStartDate, newPhaseIds);
        }
    },
    { deep: true }
);

// Helper functions for selecting all phases and notebooks
const isAllPhasesSelected = (form: any) => {
    return props.phases && props.phases.length > 0 && form.phase_ids.length === props.phases.length;
};

const selectAllPhases = (form: any) => {
    if (isAllPhasesSelected(form)) {
        form.phase_ids = [];
        form.phase_dates = [];
    } else {
        form.phase_ids = props.phases.map(phase => phase.id);
        // This will trigger the watcher to calculate dates
    }
};

const isAllNotebooksSelected = (form: any) => {
    return props.notebooks && props.notebooks.length > 0 && form.notebook_ids.length === props.notebooks.length;
};

const selectAllNotebooks = (form: any) => {
    if (isAllNotebooksSelected(form)) {
        form.notebook_ids = [];
    } else {
        form.notebook_ids = props.notebooks.map(notebook => notebook.id);
    }
};

// Helper functions for managing resources
const addResource = (form: any) => {
    form.resources.push({
        resource_id: null,
        user_id: null
    });
};

const removeResource = (form: any, index: number) => {
    form.resources.splice(index, 1);
};

// Helper functions for managing phases and tasks
const getTasksForPhase = (phaseId: number) => {
    return props.tasks.filter(task => task.phase_repo_id === phaseId);
};

const updatePhaseTasks = (form: any, phaseId: number, taskIds: number[]) => {
    // Find if there's already an entry for this phase
    const existingIndex = form.phase_tasks.findIndex((pt: PhaseTask) => pt.phase_id === phaseId);
    
    if (existingIndex >= 0) {
        // Update existing entry
        form.phase_tasks[existingIndex].task_ids = taskIds;
    } else {
        // Add new entry
        form.phase_tasks.push({
            phase_id: phaseId,
            task_ids: taskIds
        });
    }
};

const getSelectedTasksForPhase = (form: any, phaseId: number): number[] => {
    const phaseTask = form.phase_tasks.find((pt: PhaseTask) => pt.phase_id === phaseId);
    return phaseTask ? phaseTask.task_ids : [];
};

const isAllTasksSelectedForPhase = (form: any, phaseId: number) => {
    const phaseTasks = getTasksForPhase(phaseId);
    const selectedTasks = getSelectedTasksForPhase(form, phaseId);
    return phaseTasks.length > 0 && selectedTasks.length === phaseTasks.length;
};

const selectAllTasksForPhase = (form: any, phaseId: number) => {
    const phaseTasks = getTasksForPhase(phaseId);
    if (isAllTasksSelectedForPhase(form, phaseId)) {
        // Deselect all tasks for this phase
        updatePhaseTasks(form, phaseId, []);
    } else {
        // Select all tasks for this phase
        updatePhaseTasks(form, phaseId, phaseTasks.map(task => task.id));
    }
};

// Helper functions for task selection
const getTasksByType = (typeId: number) => {
    return props.tasks.filter(task => task.task_type_repo_id === typeId);
};

const isAllTasksSelectedForType = (form: any, typeId: number) => {
    const tasksOfType = getTasksByType(typeId);
    if (tasksOfType.length === 0) return false;
    
    return tasksOfType.every(task => form.task_ids.includes(task.id));
};

const selectAllTasksForType = (form: any, typeId: number) => {
    const tasksOfType = getTasksByType(typeId);
    const currentTaskIds = [...form.task_ids];
    
    if (isAllTasksSelectedForType(form, typeId)) {
        // Deselect all tasks of this type
        form.task_ids = currentTaskIds.filter(id => 
            !tasksOfType.some(task => task.id === id)
        );
    } else {
        // Select all tasks of this type
        const taskIdsToAdd = tasksOfType
            .filter(task => !currentTaskIds.includes(task.id))
            .map(task => task.id);
        
        form.task_ids = [...currentTaskIds, ...taskIdsToAdd];
    }
};

// Helper functions for task type selection
const selectTaskType = (form: any, typeId: number, selected: boolean) => {
    const tasksOfType = getTasksByType(typeId);
    const taskIds = tasksOfType.map(task => task.id);
    
    if (selected) {
        // Add all tasks of this type
        const currentTaskIds = [...form.task_ids];
        const newTaskIds = taskIds.filter(id => !currentTaskIds.includes(id));
        form.task_ids = [...currentTaskIds, ...newTaskIds];
    } else {
        // Remove all tasks of this type
        form.task_ids = form.task_ids.filter(id => 
            !taskIds.includes(id)
        );
    }
};

const isTaskTypeSelected = (form: any, typeId: number) => {
    const tasksOfType = getTasksByType(typeId);
    if (tasksOfType.length === 0) return false;
    
    // A task type is considered selected if ALL tasks of that type are selected
    return tasksOfType.every(task => form.task_ids.includes(task.id));
};

// Watch for phase changes to update task dates
watch(() => createForm.phase_ids, (newPhaseIds) => {
    // This watcher is now only needed for phase dates
    // No need to filter tasks as we're using a flat task_ids array now
}, { deep: true });

watch(() => editForm.phase_ids, (newPhaseIds) => {
    // This watcher is now only needed for phase dates
    // No need to filter tasks as we're using a flat task_ids array now
}, { deep: true });

// Submit create form
const submitCreate = () => {
    // Validate shortcode before submission
    validateShortcode(createForm);
    
    // Check if there are any validation errors
    if (createForm.errors.shortcode) {
        toast({
            title: "Validation Error",
            description: createForm.errors.shortcode,
            variant: "destructive",
            duration: 5000,
        });
        return;
    }
    
    createForm.post(route('projects.store'), {
        onSuccess: () => {
            isCreateDialogOpen.value = false;
            createForm.reset();
            createForm.start_date = format(new Date(), 'yyyy-MM-dd');
            createForm.status = 'upcoming';
            createForm.resources = [{ resource_id: null, user_id: null }];
            toast({
                title: "Project Created",
                description: "The project has been successfully created.",
                duration: 5000,
            });
        },
        onError: (errors) => {
            console.error('Form submission errors:', errors);
            toast({
                title: "Error",
                description: "There was a problem creating the project. Please check the form for errors.",
                variant: "destructive",
                duration: 5000,
            });
        }
    });
};

// Edit project
const editProject = (project: Project) => {
    editForm.id = project.id;
    editForm.name = project.name;
    editForm.shortcode = project.shortcode;
    editForm.start_date = project.start_date;
    editForm.end_date = project.end_date || '';
    editForm.description = project.description || '';
    editForm.status = project.status;
    editForm.phase_ids = project.phases?.map(phase => phase.id) || [];
    editForm.phase_dates = project.phases?.map(phase => ({
        id: phase.id,
        start_date: phase.pivot?.start_date || '',
        end_date: phase.pivot?.end_date || '',
    })) || [];
    editForm.notebook_ids = project.notebooks?.map(notebook => notebook.id) || [];
    
    // Map project resources
    editForm.resources = project.resources?.map(resource => ({
        resource_id: resource.resource_type?.id || null,
        user_id: resource.user?.id || null
    })) || [{
        resource_id: null,
        user_id: null
    }];
    
    // Map project phase tasks
    editForm.task_ids = project.phase_tasks?.map(task => task.task_repo_id) || [];
    
    isEditDialogOpen.value = true;
};

// View project
const viewProject = (project: Project) => {
    projectToView.value = project;
    isViewDialogOpen.value = true;
};

// Submit edit form
const submitEdit = () => {
    // Validate shortcode before submission
    validateShortcode(editForm);
    
    // Check if there are any validation errors
    if (editForm.errors.shortcode) {
        toast({
            title: "Validation Error",
            description: editForm.errors.shortcode,
            variant: "destructive",
            duration: 5000,
        });
        return;
    }
    
    editForm.put(route('project.update', editForm.id), {
        onSuccess: () => {
            isEditDialogOpen.value = false;
            toast({
                title: "Project Updated",
                description: "The project has been successfully updated.",
                duration: 5000,
            });
        },
        onError: (errors) => {
            console.error('Form submission errors:', errors);
            toast({
                title: "Error",
                description: "There was a problem updating the project. Please check the form for errors.",
                variant: "destructive",
                duration: 5000,
            });
        }
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
        useForm({}).delete(route('projects.destroy', projectToDelete.value.id), {
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

const validateShortcode = (form: any) => {
    const shortcode = form.shortcode;
    if (!shortcode) return;
    
    // Convert to uppercase to ensure case doesn't cause validation issues
    const uppercaseShortcode = shortcode.toUpperCase();
    form.shortcode = uppercaseShortcode;
    
    // Check if the shortcode matches the pattern: 4 letters + 2 numbers + 1 letter
    const regex = /^[A-Z]{4}\d{2}[A-Z]$/;
    if (!regex.test(uppercaseShortcode)) {
        console.log('Shortcode validation failed:', uppercaseShortcode);
        form.errors.shortcode = 'Invalid shortcode format. Please use 4 letters + 2 numbers + 1 letter (e.g. PROJ01A)';
    } else {
        console.log('Shortcode validation passed:', uppercaseShortcode);
        form.errors.shortcode = null;
    }
};
</script>

<template>
    <AppLayout title="Projects">
        <div class="container mx-auto py-8 space-y-6">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold">Projects</h1>
                
                <!-- Create Dialog Trigger -->
                <Button @click="isCreateDialogOpen = true" class="futuristic-glow">
                    <PlusCircle class="mr-2 h-4 w-4" />
                    Create Project
                </Button>
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

            <!-- Create Dialog -->
            <Dialog v-model:open="isCreateDialogOpen">
                <DialogContent class="max-w-6xl glass-card backdrop-blur-sm max-h-[90vh] overflow-hidden flex flex-col">
                    <DialogHeader>
                        <DialogTitle class="text-xl font-bold text-primary">Create Project</DialogTitle>
                        <DialogDescription>
                            Fill in the details to create a new project.
                        </DialogDescription>
                    </DialogHeader>
                    <div class="overflow-y-auto pr-2 -mr-2">
                        <form @submit.prevent="submitCreate" class="py-4 space-y-6">
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
                                        <div class="relative">
                                            <Input 
                                                id="create-shortcode" 
                                                v-model="createForm.shortcode" 
                                                required 
                                                maxlength="7" 
                                                placeholder="e.g. PROJ01A" 
                                                class="uppercase"
                                                @input="validateShortcode(createForm)"
                                            />
                                            <div class="text-xs text-muted-foreground mt-1">
                                                Format: 4 letters + 2 numbers + 1 letter (e.g. PROJ01A)
                                            </div>
                                        </div>
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
                                
                                <div class="grid gap-2 mt-4">
                                    <Label for="create-phases">Project Phases</Label>
                                    <div class="border border-input rounded-md p-4 bg-card/50">
                                        <div class="flex items-center justify-between mb-2 pb-2 border-b border-border/50">
                                            <span class="text-sm font-medium">Available Phases</span>
                                            <Button 
                                                type="button" 
                                                variant="ghost" 
                                                size="sm" 
                                                class="h-7 text-xs"
                                                @click="selectAllPhases(createForm)"
                                            >
                                                {{ isAllPhasesSelected(createForm) ? 'Deselect All' : 'Select All' }}
                                            </Button>
                                        </div>
                                        <div class="text-sm text-muted-foreground mb-3">
                                            Select phases for this project. The order you select them will be maintained.
                                        </div>
                                        <div v-if="props.phases && props.phases.length > 0" class="space-y-2 max-h-60 overflow-y-auto pr-2">
                                            <div 
                                                v-for="phase in props.phases" 
                                                :key="phase.id"
                                                class="flex items-start space-x-2 p-2 hover:bg-muted/50 rounded-md transition-colors"
                                            >
                                                <input 
                                                    type="checkbox"
                                                    :id="`create-phase-${phase.id}`"
                                                    :value="phase.id"
                                                    v-model="createForm.phase_ids"
                                                    class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                                                />
                                                <div>
                                                    <label 
                                                        :for="`create-phase-${phase.id}`"
                                                        class="text-sm font-medium cursor-pointer"
                                                    >
                                                        {{ phase.name }}
                                                    </label>
                                                    <p class="text-xs text-muted-foreground">{{ phase.description }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else class="bg-card/80 border border-border/50 rounded-lg p-4 shadow-sm flex flex-col items-center justify-center text-center">
                                            <p class="text-muted-foreground">No Phases In Repo</p>
                                            <p class="text-xs text-muted-foreground mt-2">Add phases in the Phase Repository first.</p>
                                        </div>
                                        <div v-if="createForm.phase_ids.length > 0" class="mt-3 pt-3 border-t border-border/50">
                                            <div class="text-sm font-medium mb-2">Selected phases ({{ createForm.phase_ids.length }}):</div>
                                            <div class="space-y-3">
                                                <div 
                                                    v-for="(phaseId, index) in createForm.phase_ids" 
                                                    :key="phaseId"
                                                    class="bg-card/80 border border-border/50 rounded-lg p-3 shadow-sm"
                                                >
                                                    <div class="flex items-center gap-2 mb-2">
                                                        <div class="bg-primary text-primary-foreground w-5 h-5 rounded-full flex items-center justify-center text-xs font-medium">
                                                            {{ index + 1 }}
                                                        </div>
                                                        <h4 class="text-sm font-medium">{{ props.phases.find(p => p.id === phaseId)?.name }}</h4>
                                                    </div>
                                                    
                                                    <div class="ml-7 grid grid-cols-1 md:grid-cols-2 gap-3">
                                                        <div>
                                                            <Label :for="`create-phase-${phaseId}-start`" class="text-xs">Start Date</Label>
                                                            <Input 
                                                                :id="`create-phase-${phaseId}-start`"
                                                                type="date"
                                                                v-model="createForm.phase_dates.find(p => p.id === phaseId)!.start_date"
                                                                class="h-8 text-xs"
                                                            />
                                                        </div>
                                                        <div>
                                                            <Label :for="`create-phase-${phaseId}-end`" class="text-xs">End Date</Label>
                                                            <Input 
                                                                :id="`create-phase-${phaseId}-end`"
                                                                type="date"
                                                                v-model="createForm.phase_dates.find(p => p.id === phaseId)!.end_date"
                                                                class="h-8 text-xs"
                                                            />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-2 text-xs text-muted-foreground">
                                                <p>Phase dates are automatically calculated based on the project start date:</p>
                                                <ul class="list-disc ml-4 mt-1">
                                                    <li>Phase 1 starts on the project start date</li>
                                                    <li>Each phase ends on a Friday</li>
                                                    <li>Next phase starts on the following Monday</li>
                                                </ul>
                                                <p class="mt-1">You can adjust the dates if needed.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="createForm.errors.phase_ids" class="text-red-500 text-sm">
                                        {{ createForm.errors.phase_ids }}
                                    </div>
                                </div>
                                
                                <!-- Task Type Selection -->
                                <div class="grid gap-2 mt-4">
                                    <Label for="create-tasks">Project Tasks</Label>
                                    <div class="border border-input rounded-md p-4 bg-card/50">
                                        <div class="flex items-center justify-between mb-2 pb-2 border-b border-border/50">
                                            <span class="text-sm font-medium">Available Task Types</span>
                                        </div>
                                        <div class="text-sm text-muted-foreground mb-3">
                                            Select task types for this project. All tasks of the selected types will be automatically assigned to their respective phases.
                                        </div>
                                        
                                        <div v-if="props.taskTypes && props.taskTypes.length > 0" class="space-y-4">
                                            <div 
                                                v-for="taskType in props.taskTypes" 
                                                :key="`task-type-${taskType.id}`"
                                                class="bg-card/50 border border-input rounded-md p-4"
                                            >
                                                <div class="flex items-center justify-between mb-2">
                                                    <div class="flex items-start space-x-2">
                                                        <input 
                                                            type="checkbox"
                                                            :id="`create-task-type-${taskType.id}`"
                                                            :checked="isTaskTypeSelected(createForm, taskType.id)"
                                                            @change="(e) => {
                                                                const target = e.target as HTMLInputElement;
                                                                selectTaskType(createForm, taskType.id, target.checked);
                                                            }"
                                                            class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary mt-1"
                                                        />
                                                        <div>
                                                            <label 
                                                                :for="`create-task-type-${taskType.id}`"
                                                                class="text-sm font-medium cursor-pointer"
                                                            >
                                                                {{ taskType.name }}
                                                            </label>
                                                            <p class="text-xs text-muted-foreground">{{ taskType.description }}</p>
                                                            <div class="mt-2 text-xs text-muted-foreground">
                                                                <span class="font-medium">Tasks in this type:</span> {{ getTasksByType(taskType.id).length }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else class="bg-card/80 border border-border/50 rounded-lg p-4 shadow-sm flex flex-col items-center justify-center text-center">
                                            <p class="text-muted-foreground">No Task Types Available</p>
                                            <p class="text-xs text-muted-foreground mt-2">Add task types in the Task Repository first.</p>
                                        </div>
                                        
                                        <div v-if="createForm.task_ids.length > 0" class="mt-3 pt-3 border-t border-border/50">
                                            <div class="text-sm font-medium mb-2">
                                                Selected tasks: <span class="bg-primary/10 text-primary px-2 py-0.5 rounded-full">{{ createForm.task_ids.length }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="grid gap-2 mt-4">
                                    <Label for="create-notebooks">Project Notebooks</Label>
                                    <div class="border border-input rounded-md p-4 bg-card/50">
                                        <div class="flex items-center justify-between mb-2 pb-2 border-b border-border/50">
                                            <span class="text-sm font-medium">Available Notebooks</span>
                                            <Button 
                                                type="button" 
                                                variant="ghost" 
                                                size="sm" 
                                                class="h-7 text-xs"
                                                @click="selectAllNotebooks(createForm)"
                                            >
                                                {{ isAllNotebooksSelected(createForm) ? 'Deselect All' : 'Select All' }}
                                            </Button>
                                        </div>
                                        <div class="text-sm text-muted-foreground mb-3">
                                            Select notebooks for this project.
                                        </div>
                                        <div v-if="props.notebooks && props.notebooks.length > 0" class="space-y-2 max-h-60 overflow-y-auto pr-2">
                                            <div 
                                                v-for="notebook in props.notebooks" 
                                                :key="notebook.id"
                                                class="flex items-start space-x-2 p-2 hover:bg-muted/50 rounded-md transition-colors"
                                            >
                                                <input 
                                                    type="checkbox"
                                                    :id="`create-notebook-${notebook.id}`"
                                                    :value="notebook.id"
                                                    v-model="createForm.notebook_ids"
                                                    class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                                                />
                                                <div>
                                                    <label 
                                                        :for="`create-notebook-${notebook.id}`"
                                                        class="text-sm font-medium cursor-pointer"
                                                    >
                                                        {{ notebook.name }}
                                                    </label>
                                                    <p class="text-xs text-muted-foreground">{{ notebook.description }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else class="bg-card/80 border border-border/50 rounded-lg p-4 shadow-sm flex flex-col items-center justify-center text-center">
                                            <p class="text-muted-foreground">No Notebooks In Repo</p>
                                            <p class="text-xs text-muted-foreground mt-2">Add notebooks in the Notebook Repository first.</p>
                                        </div>
                                        <div v-if="createForm.notebook_ids.length > 0" class="mt-3 pt-3 border-t border-border/50">
                                            <div class="text-sm font-medium mb-2">Selected notebooks ({{ createForm.notebook_ids.length }}):</div>
                                            <div class="flex flex-wrap gap-2">
                                                <div 
                                                    v-for="notebookId in createForm.notebook_ids" 
                                                    :key="notebookId"
                                                    class="bg-primary/10 text-primary text-xs px-2 py-1 rounded-full flex items-center"
                                                >
                                                    <span>{{ props.notebooks.find(n => n.id === notebookId)?.name }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="createForm.errors.notebook_ids" class="text-red-500 text-sm">
                                        {{ createForm.errors.notebook_ids }}
                                    </div>
                                </div>
                                
                                <div class="grid gap-2 mt-4">
                                    <Label for="create-resources">Project Resources</Label>
                                    <div class="border border-input rounded-md p-4 bg-card/50">
                                        <div class="flex items-center justify-between mb-2 pb-2 border-b border-border/50">
                                            <span class="text-sm font-medium">Available Resources</span>
                                            <Button 
                                                type="button" 
                                                variant="ghost" 
                                                size="sm" 
                                                class="h-7 text-xs"
                                                @click="addResource(createForm)"
                                            >
                                                Add Resource
                                            </Button>
                                        </div>
                                        <div class="text-sm text-muted-foreground mb-3">
                                            Select resources for this project.
                                        </div>
                                        <div v-if="props.resources && props.resources.length > 0" class="space-y-2 max-h-60 overflow-y-auto pr-2">
                                            <div 
                                                v-for="(resource, index) in createForm.resources" 
                                                :key="index"
                                                class="flex items-start space-x-2 p-2 hover:bg-muted/50 rounded-md transition-colors"
                                            >
                                                <div class="flex items-center gap-2 w-1/2">
                                                    <select 
                                                        v-model="resource.resource_id"
                                                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                                    >
                                                        <option :value="null">Select Resource</option>
                                                        <option v-for="r in props.resources" :key="r.id" :value="r.id">{{ r.name }}</option>
                                                    </select>
                                                </div>
                                                <div class="flex items-center gap-2 w-1/2">
                                                    <select 
                                                        v-model="resource.user_id"
                                                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                                    >
                                                        <option :value="null">Select User</option>
                                                        <option v-for="u in props.users" :key="u.id" :value="u.id">{{ u.name }}</option>
                                                    </select>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <Button 
                                                        type="button" 
                                                        variant="destructive" 
                                                        size="icon" 
                                                        @click="removeResource(createForm, index)"
                                                        :disabled="createForm.resources.length === 1"
                                                    >
                                                        <Trash2 class="h-4 w-4" />
                                                    </Button>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else class="bg-card/80 border border-border/50 rounded-lg p-4 shadow-sm flex flex-col items-center justify-center text-center">
                                            <p class="text-muted-foreground">No Resources In Repo</p>
                                            <p class="text-xs text-muted-foreground mt-2">Add resources in the Resource Repository first.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <DialogFooter class="mt-2 pt-2 border-t border-border/50">
                                <Button type="button" variant="outline" @click="isCreateDialogOpen = false">Cancel</Button>
                                <Button type="submit" :disabled="createForm.processing" class="futuristic-glow">Create Project</Button>
                            </DialogFooter>
                        </form>
                    </div>
                </DialogContent>
            </Dialog>

            <!-- Edit Dialog -->
            <Dialog v-model:open="isEditDialogOpen">
                <DialogContent class="max-w-6xl glass-card backdrop-blur-sm max-h-[90vh] overflow-hidden flex flex-col">
                    <DialogHeader>
                        <DialogTitle class="text-xl font-bold text-primary">Edit Project</DialogTitle>
                        <DialogDescription>
                            Update the project details below.
                        </DialogDescription>
                    </DialogHeader>
                    <div class="overflow-y-auto pr-2 -mr-2">
                        <form @submit.prevent="submitEdit" class="py-4 space-y-6">
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
                                        <div class="relative">
                                            <Input 
                                                id="edit-shortcode" 
                                                v-model="editForm.shortcode" 
                                                required 
                                                maxlength="7" 
                                                placeholder="e.g. PROJ01A" 
                                                class="uppercase"
                                                @input="validateShortcode(editForm)"
                                            />
                                            <div class="text-xs text-muted-foreground mt-1">
                                                Format: 4 letters + 2 numbers + 1 letter (e.g. PROJ01A)
                                            </div>
                                        </div>
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
                                
                                <div class="grid gap-2 mt-4">
                                    <Label for="edit-phases">Project Phases</Label>
                                    <div class="border border-input rounded-md p-4 bg-card/50">
                                        <div class="flex items-center justify-between mb-2 pb-2 border-b border-border/50">
                                            <span class="text-sm font-medium">Available Phases</span>
                                            <Button 
                                                type="button" 
                                                variant="ghost" 
                                                size="sm" 
                                                class="h-7 text-xs"
                                                @click="selectAllPhases(editForm)"
                                            >
                                                {{ isAllPhasesSelected(editForm) ? 'Deselect All' : 'Select All' }}
                                            </Button>
                                        </div>
                                        <div class="text-sm text-muted-foreground mb-3">
                                            Select phases for this project. The order you select them will be maintained.
                                        </div>
                                        <div v-if="props.phases && props.phases.length > 0" class="space-y-2 max-h-60 overflow-y-auto pr-2">
                                            <div 
                                                v-for="phase in props.phases" 
                                                :key="phase.id"
                                                class="flex items-start space-x-2 p-2 hover:bg-muted/50 rounded-md transition-colors"
                                            >
                                                <input 
                                                    type="checkbox"
                                                    :id="`edit-phase-${phase.id}`"
                                                    :value="phase.id"
                                                    v-model="editForm.phase_ids"
                                                    class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                                                />
                                                <div>
                                                    <label 
                                                        :for="`edit-phase-${phase.id}`"
                                                        class="text-sm font-medium cursor-pointer"
                                                    >
                                                        {{ phase.name }}
                                                    </label>
                                                    <p class="text-xs text-muted-foreground">{{ phase.description }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else class="bg-card/80 border border-border/50 rounded-lg p-4 shadow-sm flex flex-col items-center justify-center text-center">
                                            <p class="text-muted-foreground">No Phases In Repo</p>
                                            <p class="text-xs text-muted-foreground mt-2">Add phases in the Phase Repository first.</p>
                                        </div>
                                        <div v-if="editForm.phase_ids.length > 0" class="mt-3 pt-3 border-t border-border/50">
                                            <div class="text-sm font-medium mb-2">Selected phases ({{ editForm.phase_ids.length }}):</div>
                                            <div class="space-y-3">
                                                <div 
                                                    v-for="(phaseId, index) in editForm.phase_ids" 
                                                    :key="phaseId"
                                                    class="bg-card/80 border border-border/50 rounded-lg p-3 shadow-sm"
                                                >
                                                    <div class="flex items-center gap-2 mb-2">
                                                        <div class="bg-primary text-primary-foreground w-5 h-5 rounded-full flex items-center justify-center text-xs font-medium">
                                                            {{ index + 1 }}
                                                        </div>
                                                        <h4 class="text-sm font-medium">{{ props.phases.find(p => p.id === phaseId)?.name }}</h4>
                                                    </div>
                                                    
                                                    <div class="ml-7 grid grid-cols-1 md:grid-cols-2 gap-3">
                                                        <div>
                                                            <Label :for="`edit-phase-${phaseId}-start`" class="text-xs">Start Date</Label>
                                                            <Input 
                                                                :id="`edit-phase-${phaseId}-start`"
                                                                type="date"
                                                                v-model="editForm.phase_dates.find(p => p.id === phaseId)!.start_date"
                                                                class="h-8 text-xs"
                                                            />
                                                        </div>
                                                        <div>
                                                            <Label :for="`edit-phase-${phaseId}-end`" class="text-xs">End Date</Label>
                                                            <Input 
                                                                :id="`edit-phase-${phaseId}-end`"
                                                                type="date"
                                                                v-model="editForm.phase_dates.find(p => p.id === phaseId)!.end_date"
                                                                class="h-8 text-xs"
                                                            />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-2 text-xs text-muted-foreground">
                                                <p>Phase dates are automatically calculated based on the project start date:</p>
                                                <ul class="list-disc ml-4 mt-1">
                                                    <li>Phase 1 starts on the project start date</li>
                                                    <li>Each phase ends on a Friday</li>
                                                    <li>Next phase starts on the following Monday</li>
                                                </ul>
                                                <p class="mt-1">You can adjust the dates if needed.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="editForm.errors.phase_ids" class="text-red-500 text-sm">
                                        {{ editForm.errors.phase_ids }}
                                    </div>
                                </div>
                                
                                <!-- Task Type Selection -->
                                <div class="grid gap-2 mt-4">
                                    <Label for="edit-tasks">Project Tasks</Label>
                                    <div class="border border-input rounded-md p-4 bg-card/50">
                                        <div class="flex items-center justify-between mb-2 pb-2 border-b border-border/50">
                                            <span class="text-sm font-medium">Available Task Types</span>
                                        </div>
                                        <div class="text-sm text-muted-foreground mb-3">
                                            Select task types for this project. All tasks of the selected types will be automatically assigned to their respective phases.
                                        </div>
                                        
                                        <div v-if="props.taskTypes && props.taskTypes.length > 0" class="space-y-4">
                                            <div 
                                                v-for="taskType in props.taskTypes" 
                                                :key="`task-type-${taskType.id}`"
                                                class="bg-card/50 border border-input rounded-md p-4"
                                            >
                                                <div class="flex items-center justify-between mb-2">
                                                    <div class="flex items-start space-x-2">
                                                        <input 
                                                            type="checkbox"
                                                            :id="`edit-task-type-${taskType.id}`"
                                                            :checked="isTaskTypeSelected(editForm, taskType.id)"
                                                            @change="(e) => {
                                                                const target = e.target as HTMLInputElement;
                                                                selectTaskType(editForm, taskType.id, target.checked);
                                                            }"
                                                            class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary mt-1"
                                                        />
                                                        <div>
                                                            <label 
                                                                :for="`edit-task-type-${taskType.id}`"
                                                                class="text-sm font-medium cursor-pointer"
                                                            >
                                                                {{ taskType.name }}
                                                            </label>
                                                            <p class="text-xs text-muted-foreground">{{ taskType.description }}</p>
                                                            <div class="mt-2 text-xs text-muted-foreground">
                                                                <span class="font-medium">Tasks in this type:</span> {{ getTasksByType(taskType.id).length }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else class="bg-card/80 border border-border/50 rounded-lg p-4 shadow-sm flex flex-col items-center justify-center text-center">
                                            <p class="text-muted-foreground">No Task Types Available</p>
                                            <p class="text-xs text-muted-foreground mt-2">Add task types in the Task Repository first.</p>
                                        </div>
                                        
                                        <div v-if="editForm.task_ids.length > 0" class="mt-3 pt-3 border-t border-border/50">
                                            <div class="text-sm font-medium mb-2">
                                                Selected tasks: <span class="bg-primary/10 text-primary px-2 py-0.5 rounded-full">{{ editForm.task_ids.length }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="grid gap-2 mt-4">
                                    <Label for="edit-notebooks">Project Notebooks</Label>
                                    <div class="border border-input rounded-md p-4 bg-card/50">
                                        <div class="flex items-center justify-between mb-2 pb-2 border-b border-border/50">
                                            <span class="text-sm font-medium">Available Notebooks</span>
                                            <Button 
                                                type="button" 
                                                variant="ghost" 
                                                size="sm" 
                                                class="h-7 text-xs"
                                                @click="selectAllNotebooks(editForm)"
                                            >
                                                {{ isAllNotebooksSelected(editForm) ? 'Deselect All' : 'Select All' }}
                                            </Button>
                                        </div>
                                        <div class="text-sm text-muted-foreground mb-3">
                                            Select notebooks for this project.
                                        </div>
                                        <div v-if="props.notebooks && props.notebooks.length > 0" class="space-y-2 max-h-60 overflow-y-auto pr-2">
                                            <div 
                                                v-for="notebook in props.notebooks" 
                                                :key="notebook.id"
                                                class="flex items-start space-x-2 p-2 hover:bg-muted/50 rounded-md transition-colors"
                                            >
                                                <input 
                                                    type="checkbox"
                                                    :id="`edit-notebook-${notebook.id}`"
                                                    :value="notebook.id"
                                                    v-model="editForm.notebook_ids"
                                                    class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                                                />
                                                <div>
                                                    <label 
                                                        :for="`edit-notebook-${notebook.id}`"
                                                        class="text-sm font-medium cursor-pointer"
                                                    >
                                                        {{ notebook.name }}
                                                    </label>
                                                    <p class="text-xs text-muted-foreground">{{ notebook.description }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else class="bg-card/80 border border-border/50 rounded-lg p-4 shadow-sm flex flex-col items-center justify-center text-center">
                                            <p class="text-muted-foreground">No Notebooks In Repo</p>
                                            <p class="text-xs text-muted-foreground mt-2">Add notebooks in the Notebook Repository first.</p>
                                        </div>
                                        <div v-if="editForm.notebook_ids.length > 0" class="mt-3 pt-3 border-t border-border/50">
                                            <div class="text-sm font-medium mb-2">Selected notebooks ({{ editForm.notebook_ids.length }}):</div>
                                            <div class="flex flex-wrap gap-2">
                                                <div 
                                                    v-for="notebookId in editForm.notebook_ids" 
                                                    :key="notebookId"
                                                    class="bg-primary/10 text-primary text-xs px-2 py-1 rounded-full flex items-center"
                                                >
                                                    <span>{{ props.notebooks.find(n => n.id === notebookId)?.name }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="editForm.errors.notebook_ids" class="text-red-500 text-sm">
                                        {{ editForm.errors.notebook_ids }}
                                    </div>
                                </div>
                                
                                <div class="grid gap-2 mt-4">
                                    <Label for="edit-resources">Project Resources</Label>
                                    <div class="border border-input rounded-md p-4 bg-card/50">
                                        <div class="flex items-center justify-between mb-2 pb-2 border-b border-border/50">
                                            <span class="text-sm font-medium">Available Resources</span>
                                            <Button 
                                                type="button" 
                                                variant="ghost" 
                                                size="sm" 
                                                class="h-7 text-xs"
                                                @click="addResource(editForm)"
                                            >
                                                Add Resource
                                            </Button>
                                        </div>
                                        <div class="text-sm text-muted-foreground mb-3">
                                            Select resources for this project.
                                        </div>
                                        <div v-if="props.resources && props.resources.length > 0" class="space-y-2 max-h-60 overflow-y-auto pr-2">
                                            <div 
                                                v-for="(resource, index) in editForm.resources" 
                                                :key="index"
                                                class="flex items-start space-x-2 p-2 hover:bg-muted/50 rounded-md transition-colors"
                                            >
                                                <div class="flex items-center gap-2 w-1/2">
                                                    <select 
                                                        v-model="resource.resource_id"
                                                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                                    >
                                                        <option :value="null">Select Resource</option>
                                                        <option v-for="r in props.resources" :key="r.id" :value="r.id">{{ r.name }}</option>
                                                    </select>
                                                </div>
                                                <div class="flex items-center gap-2 w-1/2">
                                                    <select 
                                                        v-model="resource.user_id"
                                                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                                    >
                                                        <option :value="null">Select User</option>
                                                        <option v-for="u in props.users" :key="u.id" :value="u.id">{{ u.name }}</option>
                                                    </select>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <Button 
                                                        type="button" 
                                                        variant="destructive" 
                                                        size="icon" 
                                                        @click="removeResource(editForm, index)"
                                                        :disabled="editForm.resources.length === 1"
                                                    >
                                                        <Trash2 class="h-4 w-4" />
                                                    </Button>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else class="bg-card/80 border border-border/50 rounded-lg p-4 shadow-sm flex flex-col items-center justify-center text-center">
                                            <p class="text-muted-foreground">No Resources In Repo</p>
                                            <p class="text-xs text-muted-foreground mt-2">Add resources in the Resource Repository first.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <DialogFooter class="mt-2 pt-2 border-t border-border/50">
                                <Button type="button" variant="outline" @click="isEditDialogOpen = false">Cancel</Button>
                                <Button type="submit" :disabled="editForm.processing" class="futuristic-glow">Update Project</Button>
                            </DialogFooter>
                        </form>
                    </div>
                </DialogContent>
            </Dialog>

            <!-- View Dialog -->
            <Dialog v-model:open="isViewDialogOpen">
                <DialogContent class="max-w-6xl glass-card backdrop-blur-sm max-h-[90vh] overflow-hidden flex flex-col">
                    <DialogHeader>
                        <DialogTitle class="text-xl font-bold text-primary">Project Details</DialogTitle>
                        <DialogDescription>
                            View detailed information about this project.
                        </DialogDescription>
                    </DialogHeader>
                    <div class="overflow-y-auto pr-2 -mr-2">
                        <div v-if="projectToView" class="py-4">
                            <div class="flex flex-col gap-4">
                                <!-- Project Header -->
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
                                
                                <!-- Project Details -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="bg-card/80 border border-border/50 rounded-lg p-4 shadow-sm futuristic-glow">
                                        <div class="flex items-center gap-2 mb-2">
                                            <Calendar class="h-4 w-4 text-primary" />
                                            <h3 class="text-sm font-medium text-foreground">Start Date</h3>
                                        </div>
                                        <p class="text-lg">{{ formatDate(projectToView.start_date) }}</p>
                                    </div>
                                    
                                    <div class="bg-card/80 border border-border/50 rounded-lg p-4 shadow-sm futuristic-glow">
                                        <div class="flex items-center gap-2 mb-2">
                                            <Calendar class="h-4 w-4 text-primary" />
                                            <h3 class="text-sm font-medium text-foreground">End Date</h3>
                                        </div>
                                        <p class="text-lg">{{ formatDate(projectToView.end_date) }}</p>
                                    </div>
                                </div>
                                
                                <!-- Project Phases -->
                                <div v-if="projectToView.phases && projectToView.phases.length > 0" class="mt-2">
                                    <div class="flex items-center gap-2 mb-3">
                                        <h3 class="text-lg font-bold text-foreground">Project Phases</h3>
                                        <div class="bg-primary/10 text-primary text-xs font-medium px-2 py-1 rounded-full">
                                            {{ projectToView.phases.length }} {{ projectToView.phases.length === 1 ? 'Phase' : 'Phases' }}
                                        </div>
                                    </div>
                                    
                                    <div class="space-y-3 max-h-60 overflow-y-auto pr-2">
                                        <div 
                                            v-for="(phase, index) in projectToView.phases" 
                                            :key="phase.id"
                                            class="bg-card/80 border border-border/50 rounded-lg p-3 shadow-sm hover:shadow-md transition-shadow"
                                        >
                                            <div class="flex items-center gap-2 mb-1">
                                                <div class="bg-primary text-primary-foreground w-5 h-5 rounded-full flex items-center justify-center text-xs font-medium">
                                                    {{ index + 1 }}
                                                </div>
                                                <h4 class="text-sm font-medium">{{ phase.name }}</h4>
                                            </div>
                                            <div class="ml-7">
                                                <p class="text-xs text-muted-foreground mb-1">{{ phase.description }}</p>
                                                <div class="flex flex-wrap gap-2 mt-2">
                                                    <div class="bg-primary/10 text-primary text-xs px-2 py-1 rounded-full flex items-center">
                                                        <Calendar class="h-3 w-3 mr-1" />
                                                        <span>Start: {{ formatDate(phase.pivot?.start_date) }}</span>
                                                    </div>
                                                    <div class="bg-primary/10 text-primary text-xs px-2 py-1 rounded-full flex items-center">
                                                        <Calendar class="h-3 w-3 mr-1" />
                                                        <span>End: {{ formatDate(phase.pivot?.end_date) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div v-else class="mt-2">
                                    <div class="flex items-center gap-2 mb-3">
                                        <h3 class="text-lg font-bold text-foreground">Project Phases</h3>
                                    </div>
                                    <div class="bg-card/80 border border-border/50 rounded-lg p-4 shadow-sm flex flex-col items-center justify-center text-center">
                                        <p class="text-muted-foreground">No phases have been assigned to this project.</p>
                                    </div>
                                </div>
                                
                                <!-- Project Tasks -->
                                <div v-if="projectToView.phase_tasks && projectToView.phase_tasks.length > 0" class="mt-4">
                                    <div class="flex items-center gap-2 mb-3">
                                        <h3 class="text-lg font-bold text-foreground">Project Tasks</h3>
                                        <div class="bg-primary/10 text-primary text-xs font-medium px-2 py-1 rounded-full">
                                            {{ projectToView.phase_tasks.length }} {{ projectToView.phase_tasks.length === 1 ? 'Task' : 'Tasks' }}
                                        </div>
                                    </div>
                                    
                                    <!-- Group tasks by phase -->
                                    <div v-for="phase in projectToView.phases" :key="`phase-tasks-${phase.id}`" class="mb-4">
                                        <div class="flex items-center gap-2 mb-2">
                                            <h4 class="text-md font-semibold text-foreground">{{ phase.name }}</h4>
                                            <div class="text-xs text-muted-foreground">
                                                {{ formatDate(phase.pivot?.start_date) }} - {{ formatDate(phase.pivot?.end_date) }}
                                            </div>
                                        </div>
                                        
                                        <div class="space-y-3 max-h-60 overflow-y-auto pr-2">
                                            <div 
                                                v-for="task in projectToView.phase_tasks.filter(t => t.phase_repo_id === phase.id)" 
                                                :key="`task-${task.id}`"
                                                class="bg-card/80 border border-border/50 rounded-lg p-3 shadow-sm hover:shadow-md transition-shadow"
                                            >
                                                <div class="flex items-center gap-2 mb-1">
                                                    <div class="bg-primary/20 text-primary p-1.5 rounded-md">
                                                        <CheckSquare class="h-4 w-4" />
                                                    </div>
                                                    <h4 class="text-sm font-medium">{{ task.task_definition?.name }}</h4>
                                                    <div class="ml-auto text-xs px-2 py-0.5 rounded-full" 
                                                        :class="{
                                                            'bg-yellow-100 text-yellow-800': task.status === 'pending',
                                                            'bg-blue-100 text-blue-800': task.status === 'in_progress',
                                                            'bg-green-100 text-green-800': task.status === 'completed',
                                                            'bg-red-100 text-red-800': task.status === 'blocked',
                                                        }"
                                                    >
                                                        {{ task.status.charAt(0).toUpperCase() + task.status.slice(1).replace('_', ' ') }}
                                                    </div>
                                                </div>
                                                <p class="text-xs text-muted-foreground ml-8">{{ task.task_definition?.description }}</p>
                                                <p class="text-xs text-muted-foreground ml-8">{{ formatDate(task.start_date) }} - {{ formatDate(task.end_date) }}</p>
                                            </div>
                                            
                                            <div v-if="!projectToView.phase_tasks.some(t => t.phase_repo_id === phase.id)" class="bg-card/80 border border-border/50 rounded-lg p-4 shadow-sm flex flex-col items-center justify-center text-center">
                                                <p class="text-muted-foreground">No tasks assigned to this phase.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div v-else-if="projectToView.phases && projectToView.phases.length > 0" class="mt-4">
                                    <div class="flex items-center gap-2 mb-3">
                                        <h3 class="text-lg font-bold text-foreground">Project Tasks</h3>
                                    </div>
                                    <div class="bg-card/80 border border-border/50 rounded-lg p-4 shadow-sm flex flex-col items-center justify-center text-center">
                                        <p class="text-muted-foreground">No tasks have been assigned to this project.</p>
                                    </div>
                                </div>
                                
                                <!-- Project Resources -->
                                <div v-if="projectToView.resources && projectToView.resources.length > 0" class="mt-4">
                                    <div class="flex items-center gap-2 mb-3">
                                        <h3 class="text-lg font-bold text-foreground">Project Resources</h3>
                                        <div class="bg-primary/10 text-primary text-xs font-medium px-2 py-1 rounded-full">
                                            {{ projectToView.resources.length }} {{ projectToView.resources.length === 1 ? 'Resource' : 'Resources' }}
                                        </div>
                                    </div>
                                    
                                    <div class="space-y-3 max-h-60 overflow-y-auto pr-2">
                                        <div 
                                            v-for="resource in projectToView.resources" 
                                            :key="resource.id"
                                            class="bg-card/80 border border-border/50 rounded-lg p-3 shadow-sm hover:shadow-md transition-shadow"
                                        >
                                            <div class="flex items-center gap-2 mb-1">
                                                <div class="bg-primary/20 text-primary p-1.5 rounded-md">
                                                    <Book class="h-4 w-4" />
                                                </div>
                                                <h4 class="text-sm font-medium">{{ resource.resource_type.name }}</h4>
                                            </div>
                                            <p class="text-xs text-muted-foreground ml-8">{{ resource.resource_type.description }}</p>
                                            <p class="text-xs text-muted-foreground ml-8">Assigned to: {{ resource.user?.name || 'Unassigned' }}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div v-else class="mt-4">
                                    <div class="flex items-center gap-2 mb-3">
                                        <h3 class="text-lg font-bold text-foreground">Project Resources</h3>
                                    </div>
                                    <div class="bg-card/80 border border-border/50 rounded-lg p-4 shadow-sm flex flex-col items-center justify-center text-center">
                                        <p class="text-muted-foreground">No resources have been assigned to this project.</p>
                                    </div>
                                </div>
                                
                                <!-- Project Notebooks -->
                                <div v-if="projectToView.notebooks && projectToView.notebooks.length > 0" class="mt-4">
                                    <div class="flex items-center gap-2 mb-3">
                                        <h3 class="text-lg font-bold text-foreground">Project Notebooks</h3>
                                        <div class="bg-primary/10 text-primary text-xs font-medium px-2 py-1 rounded-full">
                                            {{ projectToView.notebooks.length }} {{ projectToView.notebooks.length === 1 ? 'Notebook' : 'Notebooks' }}
                                        </div>
                                    </div>
                                    
                                    <div class="space-y-3 max-h-60 overflow-y-auto pr-2">
                                        <div 
                                            v-for="notebook in projectToView.notebooks" 
                                            :key="notebook.id"
                                            class="bg-card/80 border border-border/50 rounded-lg p-3 shadow-sm hover:shadow-md transition-shadow"
                                        >
                                            <div class="flex items-center gap-2 mb-1">
                                                <div class="bg-primary/20 text-primary p-1.5 rounded-md">
                                                    <Book class="h-4 w-4" />
                                                </div>
                                                <h4 class="text-sm font-medium">{{ notebook.name }}</h4>
                                            </div>
                                            <p class="text-xs text-muted-foreground ml-8">{{ notebook.description }}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div v-else class="mt-4">
                                    <div class="flex items-center gap-2 mb-3">
                                        <h3 class="text-lg font-bold text-foreground">Project Notebooks</h3>
                                    </div>
                                    <div class="bg-card/80 border border-border/50 rounded-lg p-4 shadow-sm flex flex-col items-center justify-center text-center">
                                        <p class="text-muted-foreground">No notebooks have been assigned to this project.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <DialogFooter class="mt-2 pt-2 border-t border-border/50">
                            <div class="flex gap-2">
                                <Button 
                                    type="button" 
                                    variant="outline" 
                                    @click="isViewDialogOpen = false" 
                                    class="futuristic-glow"
                                >
                                    Close
                                </Button>
                                <Button 
                                    type="button" 
                                    variant="default" 
                                    @click="editProject(projectToView as Project); isViewDialogOpen = false" 
                                    class="futuristic-glow"
                                >
                                    Edit Project
                                </Button>
                            </div>
                        </DialogFooter>
                    </div>
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
