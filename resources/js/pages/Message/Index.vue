<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
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
import { 
    MessageSquare, 
    Users, 
    Calendar, 
    Plus, 
    ChevronRight 
} from 'lucide-vue-next';
import { format } from 'date-fns';

interface User {
    id: number;
    name: string;
    email: string;
}

interface MessageRecipient {
    id: number;
    name: string;
    email: string;
    pivot: {
        read: boolean;
        read_at: string | null;
    };
}

interface Message {
    id: number;
    project_id: number;
    sender_id: number;
    title: string;
    subject: string;
    body: string;
    is_thread_starter: boolean;
    parent_id: number | null;
    created_at: string;
    updated_at: string;
    sender: User;
    recipients: MessageRecipient[];
}

interface Project {
    id: number;
    name: string;
    shortcode: string;
    description: string;
    start_date: string;
    end_date: string;
    status: string;
}

interface Props {
    project: Project;
    messages: {
        data: Message[];
        links: any[];
        meta: {
            current_page: number;
            from: number;
            last_page: number;
            links: any[];
            path: string;
            per_page: number;
            to: number;
            total: number;
        };
    };
}

const props = defineProps<Props>();

// Format date
const formatDate = (dateString: string) => {
    return format(new Date(dateString), 'MMM d, yyyy h:mm a');
};

// Get recipient names as a comma-separated string
const getRecipientNames = (recipients: MessageRecipient[]) => {
    return recipients.map(recipient => recipient.name).join(', ');
};

// Count unread messages
const countUnreadMessages = (recipients: MessageRecipient[]) => {
    return recipients.filter(recipient => !recipient.pivot.read).length;
};

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Projects',
        href: '/projects/manage',
    },
    {
        title: props.project.name,
        href: `/projects/${props.project.id}`,
    },
    {
        title: 'Messages',
        href: `/projects/${props.project.id}/messages`,
    },
];
</script>

<template>
    <Head :title="`Messages - ${project.name}`" />
    
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto p-6">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Project Messages</h1>
                    <p class="text-muted-foreground">View and manage messages for {{ project.name }}</p>
                </div>
                <Link :href="`/projects/${project.id}/messages/create`">
                    <Button>
                        <Plus class="h-4 w-4 mr-2" />
                        New Message
                    </Button>
                </Link>
            </div>
            
            <!-- Messages List -->
            <div class="space-y-4">
                <div v-if="messages.data.length === 0" class="bg-card/80 border border-border/50 rounded-lg p-8 shadow-sm flex flex-col items-center justify-center text-center">
                    <MessageSquare class="h-12 w-12 text-muted-foreground mb-2" />
                    <h3 class="text-lg font-medium">No Messages Yet</h3>
                    <p class="text-muted-foreground mt-1">Start a conversation by creating a new message.</p>
                    <Link :href="`/projects/${project.id}/messages/create`" class="mt-4">
                        <Button>
                            <Plus class="h-4 w-4 mr-2" />
                            New Message
                        </Button>
                    </Link>
                </div>
                
                <div v-else>
                    <Card v-for="message in messages.data" :key="message.id" class="mb-4 hover:shadow-md transition-shadow">
                        <Link :href="`/projects/${project.id}/messages/${message.id}`" class="block">
                            <CardHeader class="pb-2">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <CardTitle class="text-lg">{{ message.title }}</CardTitle>
                                        <CardDescription>{{ message.subject }}</CardDescription>
                                    </div>
                                    <div class="flex items-center">
                                        <span v-if="countUnreadMessages(message.recipients) > 0" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary text-primary-foreground mr-2">
                                            {{ countUnreadMessages(message.recipients) }} unread
                                        </span>
                                        <ChevronRight class="h-5 w-5 text-muted-foreground" />
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent class="pb-2">
                                <p class="text-sm text-muted-foreground line-clamp-2">{{ message.body }}</p>
                            </CardContent>
                            <CardFooter class="pt-0 text-xs text-muted-foreground">
                                <div class="flex flex-wrap justify-between w-full">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex items-center">
                                            <Users class="h-3.5 w-3.5 mr-1" />
                                            <span>From: {{ message.sender.name }}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <Users class="h-3.5 w-3.5 mr-1" />
                                            <span>To: {{ getRecipientNames(message.recipients) }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center">
                                        <Calendar class="h-3.5 w-3.5 mr-1" />
                                        <span>{{ formatDate(message.created_at) }}</span>
                                    </div>
                                </div>
                            </CardFooter>
                        </Link>
                    </Card>
                    
                    <!-- Pagination -->
                    <div v-if="messages.meta.last_page > 1" class="flex justify-center mt-6">
                        <div class="flex space-x-2">
                            <Link v-for="(link, i) in messages.meta.links" 
                                  :key="i" 
                                  :href="link.url" 
                                  v-html="link.label"
                                  class="px-3 py-1 rounded border border-border text-sm"
                                  :class="{ 
                                      'bg-primary text-primary-foreground': link.active,
                                      'bg-card hover:bg-muted/50 cursor-pointer': !link.active && link.url,
                                      'bg-muted/20 text-muted-foreground cursor-not-allowed': !link.url
                                  }"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
