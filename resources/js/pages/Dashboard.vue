<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    Card, 
    CardContent, 
    CardDescription, 
    CardFooter, 
    CardHeader, 
    CardTitle 
} from '@/components/ui/card';
import { format } from 'date-fns';
import { 
    MoreVertical, 
    ExternalLink, 
    Eye, 
    ChevronDown, 
    ChevronRight, 
    CheckCircle2, 
    Clock, 
    Calendar, 
    Folder, 
    Tag, 
    ListChecks 
} from 'lucide-vue-next';
import { 
    DropdownMenu, 
    DropdownMenuContent, 
    DropdownMenuItem, 
    DropdownMenuTrigger 
} from '@/components/ui/dropdown-menu';
import { 
    Dialog, 
    DialogContent, 
    DialogHeader, 
    DialogTitle, 
    DialogDescription 
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { useToast } from '@/components/ui/toast/use-toast';
import { ref } from 'vue';

interface Phase {
    id: number;
    name: string;
    description: string;
    color: string;
    order: number;
}

interface Project {
    id: number;
    name: string;
    shortcode: string;
    description: string;
    start_date: string;
    end_date: string;
    status: string;
    phases: {
        id: number;
        name: string;
        pivot: {
            order: number;
            start_date: string;
            end_date: string;
        }
    }[];
    phaseTasks?: ProjectPhaseTask[];
}

interface ProjectsByPhase {
    [key: number]: {
        phase: Phase;
        projects: Project[];
    }
}

interface TaskType {
    id: number;
    name: string;
    description: string;
}

interface Task {
    id: number;
    name: string;
    description: string;
    phase_repo_id: number;
    task_type_id: number;
    taskType?: TaskType;
}

interface ProjectPhaseTask {
    id: number;
    project_id: number;
    phase_repo_id: number;
    task_repo_id: number;
    completed: boolean;
    completion_date: string | null;
    completion_notes: string | null;
    completed_by: number | null;
    start_date: string | null;
    end_date: string | null;
    status: string;
    notes: string | null;
    phase?: Phase;
    taskDefinition?: Task;
}

const props = defineProps<{
    phases: Phase[];
    projectsByPhase: ProjectsByPhase;
    taskTypes: TaskType[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

// Function to format dates
const formatDate = (dateString: string) => {
    return format(new Date(dateString), 'MMM d, yyyy');
};

// Function to get project status badge color
const getStatusColor = (status: string) => {
    switch (status.toLowerCase()) {
        case 'active':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 'on hold':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
        case 'completed':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
        case 'cancelled':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
    }
};

// Dialog state for quick view
const quickViewProject = ref<Project | null>(null);
const isQuickViewOpen = ref(false);

// State for expanded/collapsed projects in task view
const expandedProjects = ref<Record<number, boolean>>({});
const expandedPhases = ref<Record<string, boolean>>({});
const expandedTaskTypes = ref<Record<string, boolean>>({});
const activeTab = ref('projects');

// Task filtering options
const taskFilters = ref({
    status: 'all', // all, upcoming, active, late
    hideCompleted: false
});

// Initialize all projects as expanded
props.projectsByPhase && Object.values(props.projectsByPhase).forEach(phaseData => {
    phaseData.projects.forEach((project: Project) => {
        expandedProjects.value[project.id] = true;
    });
});

const openQuickView = (project: Project) => {
    quickViewProject.value = project;
    isQuickViewOpen.value = true;
};

// Function to open project in new tab
const openInNewTab = (projectId: number) => {
    const url = `/projects/${projectId}`;
    window.open(url, '_blank');
};

// Toggle project expansion
const toggleProject = (projectId: number) => {
    expandedProjects.value[projectId] = !expandedProjects.value[projectId];
};

// Toggle phase expansion
const togglePhase = (projectId: number, phaseId: number) => {
    const key = `${projectId}-${phaseId}`;
    expandedPhases.value[key] = !expandedPhases.value[key];
};

// Toggle task type expansion
const toggleTaskType = (projectId: number, phaseId: number, typeId: number) => {
    const key = `${projectId}-${phaseId}-${typeId}`;
    expandedTaskTypes.value[key] = !expandedTaskTypes.value[key];
};

// Group tasks by phase and task type
const getTasksByPhase = (project: Project, phaseId: number) => {
    return project.phaseTasks?.filter(task => 
        task.phase?.id === phaseId
    ) || [];
};

const getTasksByTypeInPhase = (project: Project, phaseId: number, typeId: number) => {
    return project.phaseTasks?.filter(task => 
        task.phase?.id === phaseId && 
        task.taskDefinition?.task_type_id === typeId
    ) || [];
};

// Get task types in a phase
const getTaskTypesInPhase = (project: Project, phaseId: number) => {
    const tasks = getTasksByPhase(project, phaseId);
    const typeIds = new Set(tasks.map(task => task.taskDefinition?.task_type_id).filter(Boolean));
    return props.taskTypes.filter(type => typeIds.has(type.id));
};

// Filter tasks based on current filter settings
const filterTasks = (tasks: ProjectPhaseTask[]) => {
    return tasks.filter(task => {
        // Filter by completion status
        if (taskFilters.value.hideCompleted && task.completed) {
            return false;
        }
        
        // Filter by task status
        if (taskFilters.value.status !== 'all') {
            const today = new Date();
            const startDate = task.start_date ? new Date(task.start_date) : null;
            const endDate = task.end_date ? new Date(task.end_date) : null;
            
            switch (taskFilters.value.status) {
                case 'upcoming':
                    // Task hasn't started yet
                    return startDate && startDate > today;
                case 'active':
                    // Task has started but not ended yet
                    return (!startDate || startDate <= today) && 
                           (endDate && endDate >= today) && 
                           !task.completed;
                case 'late':
                    // Task end date has passed but not completed
                    return endDate && endDate < today && !task.completed;
                default:
                    return true;
            }
        }
        
        return true;
    });
};

// Apply filters to tasks by phase
const getFilteredTasksByPhase = (project: Project, phaseId: number) => {
    const tasks = getTasksByPhase(project, phaseId);
    return filterTasks(tasks);
};

// Apply filters to tasks by type in phase
const getFilteredTasksByTypeInPhase = (project: Project, phaseId: number, typeId: number) => {
    const tasks = getTasksByTypeInPhase(project, phaseId, typeId);
    return filterTasks(tasks);
};

// Check if a phase has any tasks after filtering
const phaseHasFilteredTasks = (project: Project, phaseId: number) => {
    return getFilteredTasksByPhase(project, phaseId).length > 0;
};

// Check if a task type has any tasks after filtering
const taskTypeHasFilteredTasks = (project: Project, phaseId: number, typeId: number) => {
    return getFilteredTasksByTypeInPhase(project, phaseId, typeId).length > 0;
};

// Mark task as completed
const markTaskCompleted = (task: ProjectPhaseTask, completed: boolean) => {
    const form = useForm({
        completed: completed,
        completion_notes: '',
    });
    
    form.put(route('tasks.complete', task.id), {
        onSuccess: () => {
            task.completed = completed;
            task.completion_date = completed ? new Date().toISOString() : null;
            
            toast({
                title: completed ? "Task Completed" : "Task Reopened",
                description: completed 
                    ? "The task has been marked as completed." 
                    : "The task has been reopened.",
                duration: 3000,
            });
        },
        onError: () => {
            toast({
                title: "Error",
                description: "There was a problem updating the task status.",
                variant: "destructive",
                duration: 5000,
            });
        }
    });
};

const { toast } = useToast();
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="mb-4">
                <h1 class="text-2xl font-bold tracking-tight">Your Projects Dashboard</h1>
                <p class="text-muted-foreground">View your projects organized by their current phase.</p>
            </div>
            
            <!-- Dashboard Tabs -->
            <div class="border-b border-border mb-4">
                <div class="flex space-x-6">
                    <button 
                        class="py-2 px-1 border-b-2" 
                        :class="activeTab === 'projects' ? 'border-primary text-sm font-medium' : 'border-transparent text-sm font-medium text-muted-foreground hover:text-foreground'"
                        @click="activeTab = 'projects'"
                    >
                        Projects
                    </button>
                    <button 
                        class="py-2 px-1 border-b-2" 
                        :class="activeTab === 'tasks' ? 'border-primary text-sm font-medium' : 'border-transparent text-sm font-medium text-muted-foreground hover:text-foreground'"
                        @click="activeTab = 'tasks'"
                    >
                        Tasks
                    </button>
                </div>
            </div>
            
            <!-- Projects View -->
            <div v-if="activeTab === 'projects'">
                <!-- Project Columns -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6 overflow-x-auto pb-4">
                    <div 
                        v-for="(phaseData, phaseId) in props.projectsByPhase" 
                        :key="`phase-${phaseId}`"
                        class="min-w-[220px] max-w-[250px]"
                    >
                        <div class="bg-card rounded-lg shadow-sm border border-border h-full flex flex-col"
                            :style="{
                                backgroundColor: phaseData.phase.color ? `${phaseData.phase.color}10` : undefined,
                                borderColor: phaseData.phase.color ? `${phaseData.phase.color}30` : undefined
                            }"
                        >
                            <!-- Phase Header -->
                            <div class="p-2 border-b flex items-center justify-between"
                                :style="{
                                    backgroundColor: phaseData.phase.color ? `${phaseData.phase.color}20` : undefined,
                                    borderColor: phaseData.phase.color ? `${phaseData.phase.color}40` : undefined
                                }"
                            >
                                <div>
                                    <h3 class="font-medium text-sm flex items-center">
                                        <span class="h-3 w-3 rounded-full mr-2" :style="{ backgroundColor: phaseData.phase.color }"></span>
                                        {{ phaseData.phase.name }}
                                    </h3>
                                    <p class="text-xs text-muted-foreground">{{ phaseData.projects.length }} projects</p>
                                </div>
                            </div>
                            
                            <!-- Project Cards -->
                            <div class="p-2 overflow-y-auto flex-grow" style="max-height: calc(100vh - 180px);">
                                <div v-if="phaseData.projects.length === 0" class="p-3 text-center text-muted-foreground text-xs">
                                    No projects in this phase
                                </div>
                                
                                <div v-for="project in phaseData.projects" :key="`project-${project.id}`" class="mb-2">
                                    <Card class="shadow-sm hover:shadow-md transition-shadow border-border overflow-hidden relative"
                                        :style="{
                                            borderLeft: `3px solid ${phaseData.phase.color || '#64748b'}`
                                        }"
                                    >
                                        <!-- Project status indicator -->
                                        <div 
                                            class="absolute top-0 left-0 h-1 bg-gradient-to-r"
                                            :style="{
                                                width: '100%',
                                                background: `linear-gradient(to right, ${phaseData.phase.color || '#64748b'}80, transparent)`,
                                                opacity: 0.5
                                            }"
                                        ></div>
                                        
                                        <CardHeader class="p-2 pb-1 relative">
                                            <div class="flex items-start justify-between">
                                                <CardTitle class="text-xs font-medium">
                                                    <Link :href="`/projects/${project.id}`" class="hover:text-primary transition-colors flex items-center">
                                                        <span class="h-2 w-2 rounded-full mr-1.5" :style="{ backgroundColor: phaseData.phase.color }"></span>
                                                        {{ project.name }}
                                                    </Link>
                                                </CardTitle>
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger class="h-6 w-6 flex items-center justify-center rounded-full hover:bg-muted">
                                                        <MoreVertical class="h-3 w-3" />
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent align="end">
                                                        <DropdownMenuItem @click="openQuickView(project)">
                                                            <Eye class="h-3.5 w-3.5 mr-2" />
                                                            Quick View
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem @click="openInNewTab(project.id)">
                                                            <ExternalLink class="h-3.5 w-3.5 mr-2" />
                                                            View in New Tab
                                                        </DropdownMenuItem>
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            </div>
                                            <CardDescription class="text-[10px] flex items-center">
                                                <Folder class="h-3 w-3 mr-1 text-muted-foreground/70" />
                                                {{ project.shortcode }}
                                            </CardDescription>
                                        </CardHeader>
                                        <CardContent class="p-2 pt-0 pb-1">
                                            <p class="text-[10px] text-muted-foreground line-clamp-2">
                                                {{ project.description || 'No description available' }}
                                            </p>
                                        </CardContent>
                                        <CardFooter class="p-2 pt-0 flex items-center justify-between bg-muted/10">
                                            <span :class="getStatusColor(project.status)" class="text-[10px] px-1.5 py-0.5 rounded-full">
                                                {{ project.status }}
                                            </span>
                                            <div class="flex items-center">
                                                <Calendar class="h-3 w-3 mr-1 text-muted-foreground/70" />
                                                <span class="text-[10px] text-muted-foreground">
                                                    {{ formatDate(project.end_date) }}
                                                </span>
                                            </div>
                                        </CardFooter>
                                    </Card>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tasks View -->
            <div v-else-if="activeTab === 'tasks'" class="space-y-4">
                <!-- Task Filters -->
                <div class="bg-card rounded-lg border border-border p-4 mb-4">
                    <h3 class="text-sm font-medium mb-3">Filter Tasks</h3>
                    <div class="flex flex-wrap gap-4">
                        <div>
                            <label class="text-xs text-muted-foreground mb-1 block">Task Status</label>
                            <div class="flex space-x-1">
                                <button 
                                    @click="taskFilters.status = 'all'" 
                                    class="px-2 py-1 text-xs rounded-md"
                                    :class="taskFilters.status === 'all' ? 'bg-primary text-primary-foreground' : 'bg-muted hover:bg-muted/80'"
                                >
                                    All
                                </button>
                                <button 
                                    @click="taskFilters.status = 'upcoming'" 
                                    class="px-2 py-1 text-xs rounded-md"
                                    :class="taskFilters.status === 'upcoming' ? 'bg-primary text-primary-foreground' : 'bg-muted hover:bg-muted/80'"
                                >
                                    Upcoming
                                </button>
                                <button 
                                    @click="taskFilters.status = 'active'" 
                                    class="px-2 py-1 text-xs rounded-md"
                                    :class="taskFilters.status === 'active' ? 'bg-primary text-primary-foreground' : 'bg-muted hover:bg-muted/80'"
                                >
                                    Active
                                </button>
                                <button 
                                    @click="taskFilters.status = 'late'" 
                                    class="px-2 py-1 text-xs rounded-md"
                                    :class="taskFilters.status === 'late' ? 'bg-primary text-primary-foreground' : 'bg-muted hover:bg-muted/80'"
                                >
                                    Late
                                </button>
                            </div>
                        </div>
                        <div class="flex items-end">
                            <div class="flex items-center space-x-2">
                                <Checkbox 
                                    id="hide-completed" 
                                    v-model="taskFilters.hideCompleted"
                                />
                                <label for="hide-completed" class="text-xs cursor-pointer">
                                    Hide Completed Tasks
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div v-if="!Object.values(props.projectsByPhase).some(phaseData => phaseData.projects.length > 0)" 
                    class="bg-card/80 border border-border/50 rounded-lg p-8 shadow-sm flex flex-col items-center justify-center text-center">
                    <ListChecks class="h-12 w-12 text-muted-foreground mb-2" />
                    <h3 class="text-lg font-medium">No Tasks Available</h3>
                    <p class="text-muted-foreground mt-1">You don't have any tasks assigned to you yet.</p>
                </div>
                
                <div v-else>
                    <div v-for="(phaseData, phaseId) in props.projectsByPhase" :key="`phase-${phaseId}`">
                        <div v-for="project in phaseData.projects" :key="`project-${project.id}`" 
                            class="bg-card rounded-lg border border-border shadow-sm overflow-hidden mb-4">
                            <!-- Project Header -->
                            <div 
                                class="p-4 bg-muted/30 border-b border-border flex items-center justify-between cursor-pointer"
                                @click="toggleProject(project.id)"
                            >
                                <div class="flex items-center">
                                    <Button variant="ghost" size="icon" class="h-6 w-6 mr-2">
                                        <ChevronDown v-if="expandedProjects[project.id]" class="h-4 w-4" />
                                        <ChevronRight v-else class="h-4 w-4" />
                                    </Button>
                                    <div>
                                        <h3 class="font-medium">{{ project.name }}</h3>
                                        <p class="text-xs text-muted-foreground">{{ project.shortcode }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span 
                                        class="text-xs px-2 py-0.5 rounded-full"
                                        :class="getStatusColor(project.status)"
                                    >
                                        {{ project.status }}
                                    </span>
                                    <Link 
                                        :href="`/projects/${project.id}`" 
                                        class="text-xs text-muted-foreground hover:text-primary transition-colors"
                                    >
                                        View Project
                                    </Link>
                                </div>
                            </div>
                            
                            <!-- Project Content (Phases) -->
                            <div v-if="expandedProjects[project.id]" class="divide-y divide-border/50">
                                <div v-for="phase in project.phases" :key="`phase-${project.id}-${phase.id}`" class="bg-card">
                                    <!-- Phase Header -->
                                    <div 
                                        class="p-3 pl-8 bg-muted/10 flex items-center justify-between cursor-pointer hover:bg-muted/30 transition-colors"
                                        @click="togglePhase(project.id, phase.id)"
                                    >
                                        <div class="flex items-center">
                                            <Button variant="ghost" size="icon" class="h-5 w-5 mr-2">
                                                <ChevronDown v-if="expandedPhases[`${project.id}-${phase.id}`]" class="h-3 w-3" />
                                                <ChevronRight v-else class="h-3 w-3" />
                                            </Button>
                                            <div class="flex items-center">
                                                <div class="h-5 w-5 rounded-full bg-primary/20 flex items-center justify-center text-primary text-xs font-medium mr-2">
                                                    {{ phase.pivot.order }}
                                                </div>
                                                <span class="text-sm font-medium">{{ phase.name }}</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center text-xs text-muted-foreground">
                                            <Calendar class="h-3 w-3 mr-1" />
                                            {{ formatDate(phase.pivot.start_date) }} - {{ formatDate(phase.pivot.end_date) }}
                                        </div>
                                    </div>
                                    
                                    <!-- Phase Content (Task Types) -->
                                    <div v-if="expandedPhases[`${project.id}-${phase.id}`]" class="pl-12">
                                        <div v-for="taskType in getTaskTypesInPhase(project, phase.id)" :key="`type-${project.id}-${phase.id}-${taskType.id}`">
                                            <!-- Task Type Header -->
                                            <div 
                                                class="p-2 flex items-center cursor-pointer hover:bg-muted/20 transition-colors"
                                                @click="toggleTaskType(project.id, phase.id, taskType.id)"
                                            >
                                                <Button variant="ghost" size="icon" class="h-4 w-4 mr-2">
                                                    <ChevronDown v-if="expandedTaskTypes[`${project.id}-${phase.id}-${taskType.id}`]" class="h-3 w-3" />
                                                    <ChevronRight v-else class="h-3 w-3" />
                                                </Button>
                                                <div class="flex items-center">
                                                    <Tag class="h-3 w-3 mr-1 text-primary" />
                                                    <span class="text-xs font-medium">{{ taskType.name }}</span>
                                                </div>
                                            </div>
                                            
                                            <!-- Task Type Content (Tasks) -->
                                            <div v-if="expandedTaskTypes[`${project.id}-${phase.id}-${taskType.id}`]" class="pl-8 pb-2">
                                                <div 
                                                    v-for="task in getFilteredTasksByTypeInPhase(project, phase.id, taskType.id)" 
                                                    :key="`task-${task.id}`"
                                                    class="p-2 hover:bg-muted/10 transition-colors rounded-md flex items-start justify-between group relative overflow-hidden"
                                                    :style="{
                                                        backgroundColor: task.completed ? 'rgba(236, 253, 245, 0.4)' : 'transparent',
                                                        borderLeft: task.completed ? '2px solid #10b981' : 
                                                                  task.status === 'late' ? '2px solid #ef4444' : 
                                                                  task.status === 'active' ? '2px solid #3b82f6' : 
                                                                  task.status === 'upcoming' ? '2px solid #8b5cf6' : 
                                                                  '2px solid transparent'
                                                    }"
                                                >
                                                    <!-- Task progress indicator -->
                                                    <div 
                                                        class="absolute left-0 top-0 bottom-0 bg-gradient-to-r from-primary/20 to-transparent"
                                                        :style="{
                                                            width: task.completed ? '100%' : '0%',
                                                            opacity: task.completed ? '0.2' : '0'
                                                        }"
                                                    ></div>
                                                    
                                                    <div class="flex items-start relative z-10">
                                                        <Checkbox 
                                                            :id="`task-${task.id}`" 
                                                            :checked="task.completed"
                                                            @update:checked="markTaskCompleted(task, $event)"
                                                            class="mt-0.5 mr-2"
                                                        />
                                                        <div>
                                                            <div class="flex items-center">
                                                                <label 
                                                                    :for="`task-${task.id}`"
                                                                    class="text-sm cursor-pointer"
                                                                    :class="{'line-through text-muted-foreground': task.completed}"
                                                                >
                                                                    {{ task.taskDefinition?.name }}
                                                                </label>
                                                                <span 
                                                                    v-if="task.status" 
                                                                    class="text-xs px-1.5 py-0.5 rounded-full ml-2"
                                                                    :class="{
                                                                        'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300': task.status === 'upcoming',
                                                                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300': task.status === 'active',
                                                                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300': task.status === 'late',
                                                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300': task.status === 'delayed',
                                                                        'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300': task.status === 'cancelled',
                                                                        'bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-300': task.completed
                                                                    }"
                                                                >
                                                                    {{ task.completed ? 'Completed' : task.status }}
                                                                </span>
                                                            </div>
                                                            <p class="text-xs text-muted-foreground mt-0.5">
                                                                {{ task.taskDefinition?.description || 'No description' }}
                                                            </p>
                                                            <div class="flex items-center mt-1 text-xs text-muted-foreground">
                                                                <Calendar class="h-3 w-3 mr-1" />
                                                                <span>{{ task.end_date ? formatDate(task.end_date) : 'No due date' }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div v-if="task.completed" class="flex items-center text-xs text-muted-foreground opacity-0 group-hover:opacity-100 transition-opacity relative z-10">
                                                        <CheckCircle2 class="h-3 w-3 mr-1 text-green-600" />
                                                        <span>Completed {{ task.completion_date ? formatDate(task.completion_date) : '' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick View Dialog -->
        <Dialog :open="isQuickViewOpen" @update:open="isQuickViewOpen = $event">
            <DialogContent class="sm:max-w-[800px] max-h-[90vh] overflow-y-auto">
                <DialogHeader v-if="quickViewProject">
                    <DialogTitle class="text-xl font-bold">{{ quickViewProject.name }}</DialogTitle>
                    <DialogDescription class="text-sm">
                        {{ quickViewProject.shortcode }} · {{ quickViewProject.status }}
                    </DialogDescription>
                </DialogHeader>
                
                <div v-if="quickViewProject" class="mt-4">
                    <!-- Project Tabs -->
                    <div class="border-b border-border">
                        <div class="flex space-x-6">
                            <button 
                                class="py-2 px-1 border-b-2" 
                                :class="activeTab === 'projects' ? 'border-primary text-sm font-medium' : 'border-transparent text-sm font-medium text-muted-foreground hover:text-foreground'"
                                @click="activeTab = 'projects'"
                            >
                                Projects
                            </button>
                            <button 
                                class="py-2 px-1 border-b-2" 
                                :class="activeTab === 'tasks' ? 'border-primary text-sm font-medium' : 'border-transparent text-sm font-medium text-muted-foreground hover:text-foreground'"
                                @click="activeTab = 'tasks'"
                            >
                                Tasks
                            </button>
                        </div>
                    </div>
                    
                    <!-- Project Overview Content -->
                    <div v-if="activeTab === 'projects'" class="py-4">
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <h3 class="text-sm font-medium mb-1">Start Date</h3>
                                <p class="text-sm">{{ formatDate(quickViewProject.start_date) }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium mb-1">End Date</h3>
                                <p class="text-sm">{{ formatDate(quickViewProject.end_date) }}</p>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h3 class="text-sm font-medium mb-1">Description</h3>
                            <p class="text-sm">{{ quickViewProject.description || 'No description available' }}</p>
                        </div>
                        
                        <div class="flex justify-end mt-6">
                            <Link 
                                :href="`/projects/${quickViewProject.id}`" 
                                class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md bg-primary text-white hover:bg-primary/90"
                            >
                                View Full Project
                                <ExternalLink class="ml-1.5 h-3 w-3" />
                            </Link>
                        </div>
                    </div>
                    
                    <!-- Task Management Content -->
                    <div v-else-if="activeTab === 'tasks'" class="py-4">
                        <!-- Task Filters -->
                        <div class="mb-4 border-b border-border pb-3">
                            <div class="flex flex-wrap gap-4">
                                <div>
                                    <label class="text-xs text-muted-foreground mb-1 block">Task Status</label>
                                    <div class="flex space-x-1">
                                        <button 
                                            @click="taskFilters.status = 'all'" 
                                            class="px-2 py-1 text-xs rounded-md"
                                            :class="taskFilters.status === 'all' ? 'bg-primary text-primary-foreground' : 'bg-muted hover:bg-muted/80'"
                                        >
                                            All
                                        </button>
                                        <button 
                                            @click="taskFilters.status = 'upcoming'" 
                                            class="px-2 py-1 text-xs rounded-md"
                                            :class="taskFilters.status === 'upcoming' ? 'bg-primary text-primary-foreground' : 'bg-muted hover:bg-muted/80'"
                                        >
                                            Upcoming
                                        </button>
                                        <button 
                                            @click="taskFilters.status = 'active'" 
                                            class="px-2 py-1 text-xs rounded-md"
                                            :class="taskFilters.status === 'active' ? 'bg-primary text-primary-foreground' : 'bg-muted hover:bg-muted/80'"
                                        >
                                            Active
                                        </button>
                                        <button 
                                            @click="taskFilters.status = 'late'" 
                                            class="px-2 py-1 text-xs rounded-md"
                                            :class="taskFilters.status === 'late' ? 'bg-primary text-primary-foreground' : 'bg-muted hover:bg-muted/80'"
                                        >
                                            Late
                                        </button>
                                    </div>
                                </div>
                                <div class="flex items-end">
                                    <div class="flex items-center space-x-2">
                                        <Checkbox 
                                            id="hide-completed-dialog" 
                                            v-model="taskFilters.hideCompleted"
                                        />
                                        <label for="hide-completed-dialog" class="text-xs cursor-pointer">
                                            Hide Completed Tasks
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div v-for="phase in quickViewProject.phases" :key="`phase-${phase.id}`" class="mb-4">
                            <h3 class="text-sm font-medium mb-1">{{ phase.name }}</h3>
                            <div v-if="expandedPhases[`${quickViewProject.id}-${phase.id}`]" class="mb-2">
                                <div v-for="taskType in getTaskTypesInPhase(quickViewProject, phase.id)" :key="`task-type-${taskType.id}`" class="mb-2">
                                    <div v-if="taskTypeHasFilteredTasks(quickViewProject, phase.id, taskType.id)" class="flex justify-between mb-1">
                                        <h4 class="text-xs font-medium">{{ taskType.name }}</h4>
                                        <button @click="toggleTaskType(quickViewProject.id, phase.id, taskType.id)" class="text-xs text-muted-foreground hover:text-foreground">
                                            {{ expandedTaskTypes[`${quickViewProject.id}-${phase.id}-${taskType.id}`] ? 'Hide' : 'Show' }}
                                        </button>
                                    </div>
                                    <div v-if="expandedTaskTypes[`${quickViewProject.id}-${phase.id}-${taskType.id}`]" class="mb-2">
                                        <div v-for="task in getFilteredTasksByTypeInPhase(quickViewProject, phase.id, taskType.id)" :key="`task-${task.id}`" class="mb-1">
                                            <div class="flex items-center justify-between p-1.5 rounded-md hover:bg-muted/10 transition-colors relative overflow-hidden"
                                                :style="{
                                                    backgroundColor: task.completed ? 'rgba(236, 253, 245, 0.4)' : 'transparent',
                                                    borderLeft: task.completed ? '2px solid #10b981' : 
                                                              task.status === 'late' ? '2px solid #ef4444' : 
                                                              task.status === 'active' ? '2px solid #3b82f6' : 
                                                              task.status === 'upcoming' ? '2px solid #8b5cf6' : 
                                                              '2px solid transparent'
                                                }"
                                            >
                                                <!-- Task progress indicator -->
                                                <div 
                                                    class="absolute left-0 top-0 bottom-0 bg-gradient-to-r from-primary/20 to-transparent"
                                                    :style="{
                                                        width: task.completed ? '100%' : '0%',
                                                        opacity: task.completed ? '0.2' : '0'
                                                    }"
                                                ></div>
                                                
                                                <div class="flex items-center relative z-10">
                                                    <Checkbox 
                                                        :modelValue="task.completed" 
                                                        @update:modelValue="markTaskCompleted(task, $event)"
                                                        class="mr-2"
                                                    />
                                                    <div>
                                                        <p class="text-sm" :class="{'line-through text-muted-foreground': task.completed}">
                                                            {{ task.taskDefinition?.name }}
                                                        </p>
                                                        <div class="flex items-center mt-0.5 text-xs text-muted-foreground">
                                                            <Calendar class="h-3 w-3 mr-1" />
                                                            <span>{{ task.end_date ? formatDate(task.end_date) : 'No due date' }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex items-center relative z-10">
                                                    <span 
                                                        v-if="task.status" 
                                                        class="text-xs px-1.5 py-0.5 rounded-full mr-2"
                                                        :class="{
                                                            'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300': task.status === 'upcoming',
                                                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300': task.status === 'active',
                                                            'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300': task.status === 'late',
                                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300': task.status === 'delayed',
                                                            'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300': task.status === 'cancelled',
                                                            'bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-300': task.completed
                                                        }"
                                                    >
                                                        {{ task.completed ? 'Completed' : task.status }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button 
                                v-if="phaseHasFilteredTasks(quickViewProject, phase.id)"
                                @click="togglePhase(quickViewProject.id, phase.id)" 
                                class="text-xs text-muted-foreground hover:text-foreground"
                            >
                                {{ expandedPhases[`${quickViewProject.id}-${phase.id}`] ? 'Hide' : 'Show' }} tasks
                            </button>
                            <p v-else class="text-xs text-muted-foreground">No tasks match your filters</p>
                        </div>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
