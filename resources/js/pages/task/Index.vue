<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { 
    Card, 
    CardContent, 
    CardDescription, 
    CardFooter, 
    CardHeader, 
    CardTitle 
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { useToast } from '@/components/ui/toast/use-toast';
import { 
    ChevronDown, 
    ChevronRight, 
    CheckCircle2, 
    Clock, 
    Calendar, 
    Folder, 
    Tag, 
    ListChecks 
} from 'lucide-vue-next';
import { format } from 'date-fns';

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
    phase_id: number;
    task_id: number;
    completed: boolean;
    completion_date: string | null;
    completion_notes: string | null;
    completed_by: number | null;
    phase?: Phase;
    taskDefinition?: Task;
}

interface Phase {
    id: number;
    name: string;
    description: string;
    order: number;
    pivot: {
        order: number;
        start_date: string;
        end_date: string;
    };
}

interface Project {
    id: number;
    name: string;
    shortcode: string;
    description: string;
    start_date: string;
    end_date: string;
    status: string;
    phases: Phase[];
    phaseTasks: ProjectPhaseTask[];
}

const props = defineProps<{
    projects: Project[];
    taskTypes: TaskType[];
}>();

const { toast } = useToast();

// State for expanded/collapsed projects
const expandedProjects = ref<Record<number, boolean>>({});
const expandedPhases = ref<Record<string, boolean>>({});
const expandedTaskTypes = ref<Record<string, boolean>>({});

// Initialize all projects as expanded
props.projects.forEach(project => {
    expandedProjects.value[project.id] = true;
});

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
    return project.phaseTasks.filter(task => 
        task.phase?.id === phaseId
    );
};

const getTasksByTypeInPhase = (project: Project, phaseId: number, typeId: number) => {
    return project.phaseTasks.filter(task => 
        task.phase?.id === phaseId && 
        task.taskDefinition?.task_type_id === typeId
    );
};

// Get task types in a phase
const getTaskTypesInPhase = (project: Project, phaseId: number) => {
    const tasks = getTasksByPhase(project, phaseId);
    const typeIds = new Set(tasks.map(task => task.taskDefinition?.task_type_id).filter(Boolean));
    return props.taskTypes.filter(type => typeIds.has(type.id));
};

// Format dates
const formatDate = (dateString: string | null) => {
    if (!dateString) return 'N/A';
    return format(new Date(dateString), 'MMM d, yyyy');
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

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Tasks',
        href: '/tasks',
    },
];
</script>

<template>
    <Head title="Tasks" />
    
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto p-6">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">All Tasks</h1>
                    <p class="text-muted-foreground">View and manage tasks across all your projects</p>
                </div>
            </div>
            
            <!-- Projects and Tasks -->
            <div class="space-y-4">
                <div v-if="projects.length === 0" class="bg-card/80 border border-border/50 rounded-lg p-8 shadow-sm flex flex-col items-center justify-center text-center">
                    <ListChecks class="h-12 w-12 text-muted-foreground mb-2" />
                    <h3 class="text-lg font-medium">No Tasks Available</h3>
                    <p class="text-muted-foreground mt-1">You don't have any tasks assigned to you yet.</p>
                </div>
                
                <div v-for="project in projects" :key="`project-${project.id}`" class="bg-card rounded-lg border border-border shadow-sm overflow-hidden">
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
                                :class="{
                                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300': project.status === 'active',
                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300': project.status === 'on hold',
                                    'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300': project.status === 'completed',
                                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300': project.status === 'cancelled',
                                    'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300': !['active', 'on hold', 'completed', 'cancelled'].includes(project.status)
                                }"
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
                                            {{ phase.order }}
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
                                            v-for="task in getTasksByTypeInPhase(project, phase.id, taskType.id)" 
                                            :key="`task-${task.id}`"
                                            class="p-2 hover:bg-muted/10 transition-colors rounded-md flex items-start justify-between group"
                                        >
                                            <div class="flex items-start">
                                                <Checkbox 
                                                    :id="`task-${task.id}`" 
                                                    :checked="task.completed"
                                                    @update:checked="markTaskCompleted(task, $event)"
                                                    class="mt-0.5 mr-2"
                                                />
                                                <div>
                                                    <label 
                                                        :for="`task-${task.id}`"
                                                        class="text-sm cursor-pointer"
                                                        :class="{'line-through text-muted-foreground': task.completed}"
                                                    >
                                                        {{ task.taskDefinition?.name }}
                                                    </label>
                                                    <p class="text-xs text-muted-foreground mt-0.5">
                                                        {{ task.taskDefinition?.description || 'No description' }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div v-if="task.completed" class="flex items-center text-xs text-muted-foreground opacity-0 group-hover:opacity-100 transition-opacity">
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
    </AppLayout>
</template>
