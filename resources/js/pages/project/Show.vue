<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { computed } from 'vue';
import { 
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Calendar, ArrowLeft, Zap, Clock } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { format, differenceInDays } from 'date-fns';
import { Link } from '@inertiajs/vue3';

interface Project {
    id: number;
    name: string;
    shortcode: string;
    start_date: string;
    end_date: string | null;
    description: string | null;
    status: 'active' | 'completed' | 'on_hold' | 'cancelled' | 'upcoming' | 'sale_pending' | 'delayed';
}

interface Props {
    project: Project;
}

const props = defineProps<Props>();

// Format date for display
const formatDate = (dateString: string | null) => {
    if (!dateString) return 'Not specified';
    return format(new Date(dateString), 'MMMM d, yyyy');
};

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

// Format status for display
const formattedStatus = computed(() => {
    return props.project.status.replace('_', ' ').charAt(0).toUpperCase() + props.project.status.replace('_', ' ').slice(1);
});

// Calculate project duration
const projectDuration = computed(() => {
    if (!props.project.start_date) return 'Unknown';
    
    const startDate = new Date(props.project.start_date);
    const endDate = props.project.end_date ? new Date(props.project.end_date) : new Date();
    
    const days = differenceInDays(endDate, startDate);
    
    if (days < 30) {
        return `${days} day${days !== 1 ? 's' : ''}`;
    } else if (days < 365) {
        const months = Math.floor(days / 30);
        return `${months} month${months !== 1 ? 's' : ''}`;
    } else {
        const years = Math.floor(days / 365);
        const remainingMonths = Math.floor((days % 365) / 30);
        return `${years} year${years !== 1 ? 's' : ''}${remainingMonths > 0 ? ` ${remainingMonths} month${remainingMonths !== 1 ? 's' : ''}` : ''}`;
    }
});

// Breadcrumbs
const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: 'Projects',
        href: '/projects/manage',
    },
    {
        title: props.project.name,
        href: `/projects/${props.project.id}`,
    },
]);
</script>

<template>
    <Head :title="project.name" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto p-6">
            <div class="mb-6">
                <Link :href="route('project.index')" class="flex items-center text-muted-foreground hover:text-primary transition-colors">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Back to Projects
                </Link>
            </div>
            
            <div class="mb-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-foreground">{{ project.name }}</h1>
                        <div class="flex items-center gap-2 mt-2">
                            <code class="px-2 py-1 bg-muted rounded text-sm">{{ project.shortcode }}</code>
                            <span 
                                class="px-2 py-1 rounded-full text-xs font-medium" 
                                :class="getStatusBadgeClass(project.status)"
                            >
                                {{ formattedStatus }}
                            </span>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <Link :href="`/projects/${project.id}/edit`" as="button" class="futuristic-glow inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                            Edit Project
                        </Link>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Project Info Card -->
                <Card class="col-span-1 md:col-span-2 glass-card backdrop-blur-sm">
                    <CardHeader>
                        <CardTitle class="text-xl text-primary">Project Information</CardTitle>
                        <CardDescription>Details about the project</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="gradient-border rounded-lg p-4 bg-card/50 mb-4">
                            <h3 class="text-sm font-medium text-muted-foreground mb-2">Description</h3>
                            <p class="text-foreground" v-if="project.description">{{ project.description }}</p>
                            <p class="text-muted-foreground italic" v-else>No description provided</p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-card/80 border border-border/50 rounded-lg p-4 shadow-sm">
                                <div class="flex items-center gap-2 mb-2">
                                    <Calendar class="h-4 w-4 text-primary" />
                                    <h3 class="text-sm font-medium text-foreground">Start Date</h3>
                                </div>
                                <p class="text-lg">{{ formatDate(project.start_date) }}</p>
                            </div>
                            
                            <div class="bg-card/80 border border-border/50 rounded-lg p-4 shadow-sm">
                                <div class="flex items-center gap-2 mb-2">
                                    <Calendar class="h-4 w-4 text-primary" />
                                    <h3 class="text-sm font-medium text-foreground">End Date</h3>
                                </div>
                                <p class="text-lg">{{ formatDate(project.end_date) }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                
                <!-- Project Stats Card -->
                <Card class="glass-card backdrop-blur-sm">
                    <CardHeader>
                        <CardTitle class="text-xl text-primary">Project Stats</CardTitle>
                        <CardDescription>Key metrics</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div class="bg-card/80 border border-border/50 rounded-lg p-4 shadow-sm">
                                <div class="flex items-center gap-2 mb-2">
                                    <Zap class="h-4 w-4 text-primary" />
                                    <h3 class="text-sm font-medium text-foreground">Status</h3>
                                </div>
                                <div class="flex items-center">
                                    <span 
                                        class="px-2 py-1 rounded-full text-xs font-medium" 
                                        :class="getStatusBadgeClass(project.status)"
                                    >
                                        {{ formattedStatus }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="bg-card/80 border border-border/50 rounded-lg p-4 shadow-sm">
                                <div class="flex items-center gap-2 mb-2">
                                    <Clock class="h-4 w-4 text-primary" />
                                    <h3 class="text-sm font-medium text-foreground">Duration</h3>
                                </div>
                                <p class="text-lg">{{ projectDuration }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
            
            <!-- Placeholder for future project details sections -->
            <div class="grid grid-cols-1 gap-6">
                <Card class="glass-card backdrop-blur-sm">
                    <CardHeader>
                        <CardTitle class="text-xl text-primary">Project Tasks</CardTitle>
                        <CardDescription>Tasks associated with this project</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="bg-card/80 border border-border/50 rounded-lg p-8 shadow-sm flex flex-col items-center justify-center text-center">
                            <p class="text-muted-foreground mb-4">No tasks have been created for this project yet.</p>
                            <Button disabled class="futuristic-glow">
                                Add Task
                            </Button>
                            <p class="text-xs text-muted-foreground mt-4">Task management will be implemented in the next phase.</p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
