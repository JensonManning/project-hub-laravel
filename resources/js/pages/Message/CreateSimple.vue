<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useToast } from '@/components/ui/toast/use-toast';
import { 
    MessageSquare, 
    Users, 
    Send
} from 'lucide-vue-next';

interface User {
    id: number;
    name: string;
    email: string;
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
    projectUsers: User[];
    allUsers: User[];
}

const props = defineProps<Props>();
const { toast } = useToast();

// Form state
const form = useForm({
    title: '',
    subject: '',
    body: '',
    recipients: [] as number[],
});

// Handle form submission
const submit = () => {
    form.post(route('projects.messages.store', props.project.id), {
        onSuccess: () => {
            toast({
                title: 'Message Sent',
                description: 'Your message has been sent successfully.',
                duration: 3000,
            });
        },
    });
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
    {
        title: 'New Message',
        href: `/projects/${props.project.id}/messages/create`,
    },
];

// Filter and prepare user lists
const validProjectUsers = props.projectUsers?.filter(user => user && user.id && user.name) || [];
const validAllUsers = props.allUsers?.filter(user => user && user.id && user.name) || [];

// Create a unique list of all users (to avoid duplicates)
const uniqueUserIds = new Set();
const allUniqueUsers = [];

// Add project users first
for (const user of validProjectUsers) {
    if (!uniqueUserIds.has(user.id)) {
        uniqueUserIds.add(user.id);
        allUniqueUsers.push({
            id: user.id,
            name: `${user.name} (Project Member)`,
            email: user.email
        });
    }
}

// Then add other users
for (const user of validAllUsers) {
    if (!uniqueUserIds.has(user.id)) {
        uniqueUserIds.add(user.id);
        allUniqueUsers.push({
            id: user.id,
            name: `${user.name}`,
            email: user.email
        });
    }
}
</script>

<template>
    <Head :title="`New Message - ${project.name}`" />
    
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto p-6">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">New Message</h1>
                    <p class="text-muted-foreground">Create a new message for {{ project.name }}</p>
                </div>
                <Link :href="`/projects/${project.id}/messages`">
                    <Button variant="outline">
                        <MessageSquare class="h-4 w-4 mr-2" />
                        Back to Messages
                    </Button>
                </Link>
            </div>
            
            <!-- Message Form -->
            <Card>
                <CardHeader>
                    <CardTitle>Compose Message</CardTitle>
                    <CardDescription>Fill out the form below to send a message to project members.</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="space-y-4">
                            <div>
                                <Label for="title">Title</Label>
                                <Input 
                                    id="title" 
                                    v-model="form.title" 
                                    :error="form.errors.title"
                                    placeholder="Enter message title"
                                    required
                                />
                                <p v-if="form.errors.title" class="text-destructive text-sm mt-1">{{ form.errors.title }}</p>
                            </div>
                            
                            <div>
                                <Label for="subject">Subject</Label>
                                <Input 
                                    id="subject" 
                                    v-model="form.subject" 
                                    :error="form.errors.subject"
                                    placeholder="Enter message subject"
                                    required
                                />
                                <p v-if="form.errors.subject" class="text-destructive text-sm mt-1">{{ form.errors.subject }}</p>
                            </div>
                            
                            <div>
                                <Label for="recipients">Recipients</Label>
                                <div class="border border-input rounded-md p-3 max-h-60 overflow-y-auto">
                                    <div v-for="user in allUniqueUsers" :key="user.id" class="flex items-center mb-2">
                                        <input 
                                            type="checkbox" 
                                            :id="`user-${user.id}`" 
                                            :value="user.id" 
                                            v-model="form.recipients"
                                            class="mr-2 h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                                        />
                                        <label :for="`user-${user.id}`" class="text-sm">
                                            {{ user.name }}
                                            <span class="text-xs text-muted-foreground ml-1">({{ user.email }})</span>
                                        </label>
                                    </div>
                                </div>
                                <p v-if="form.errors.recipients" class="text-destructive text-sm mt-1">{{ form.errors.recipients }}</p>
                            </div>
                            
                            <div>
                                <Label for="body">Message</Label>
                                <Textarea 
                                    id="body" 
                                    v-model="form.body" 
                                    :error="form.errors.body"
                                    placeholder="Enter your message"
                                    rows="8"
                                    required
                                />
                                <p v-if="form.errors.body" class="text-destructive text-sm mt-1">{{ form.errors.body }}</p>
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <Button type="submit" :disabled="form.processing" class="w-full sm:w-auto">
                                <Send class="h-4 w-4 mr-2" />
                                Send Message
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
