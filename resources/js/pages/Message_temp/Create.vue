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
import { 
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import MultiSelect from '@/components/MultiSelect/index.vue';
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

// Selected recipients
const selectedRecipients = ref<number[]>([]);

// Handle form submission
const submit = () => {
    form.recipients = selectedRecipients.value;
    
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
                                <MultiSelect
                                    v-model="selectedRecipients"
                                    :options="projectUsers.map(user => ({ value: user.id, label: user.name }))"
                                    placeholder="Select recipients"
                                    :error="form.errors.recipients"
                                    required
                                />
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
