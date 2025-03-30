<script setup lang="ts">
import { ref, computed } from 'vue';
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

// Selected recipients
const selectedRecipients = ref<number[]>([]);
const newProjectMember = ref('');
const newAllUser = ref('');

// Computed properties to prepare recipient options
const projectMemberOptions = computed(() => {
    if (!props.projectUsers) return [];
    return props.projectUsers.filter(user => user !== null && user !== undefined).map(user => ({ value: user.id, label: `${user.name} (Project Member)` }));
});

const allUserOptions = computed(() => {
    if (!props.allUsers || !props.projectUsers) return [];
    return props.allUsers.filter(user => user !== null && user !== undefined && !props.projectUsers.find(pm => pm !== null && pm !== undefined && pm.id === user.id)).map(user => ({ value: user.id, label: `${user.name} (All Users)` }));
});

const allRecipientOptions = computed(() => [...projectMemberOptions.value, ...allUserOptions.value]);

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

// Add recipient
const addProjectMember = (id: string) => {
    const recipientId = parseInt(id);
    if (!isNaN(recipientId) && !selectedRecipients.value.includes(recipientId)) {
        selectedRecipients.value.push(recipientId);
        newProjectMember.value = '';
    }
};

const addAllUser = (id: string) => {
    const recipientId = parseInt(id);
    if (!isNaN(recipientId) && !selectedRecipients.value.includes(recipientId)) {
        selectedRecipients.value.push(recipientId);
        newAllUser.value = '';
    }
};

// Remove recipient
const removeRecipient = (id: number) => {
    selectedRecipients.value = selectedRecipients.value.filter(r => r !== id);
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
                                <div class="flex flex-wrap gap-2 mb-2">
                                    <div v-for="(recipient, index) in selectedRecipients" :key="index" class="bg-gray-100 rounded-md py-1 px-2 text-sm text-gray-900">
                                        {{ allRecipientOptions.find(option => option.value === recipient)?.label }}
                                        <button @click="removeRecipient(recipient)" class="ml-2 text-destructive hover:text-destructive-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <select 
                                        id="project-members" 
                                        v-model="newProjectMember" 
                                        @change="addProjectMember(newProjectMember)"
                                        class="block w-full py-2 pl-10 text-sm text-gray-700 border border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500"
                                    >
                                        <option value="">Select a project member</option>
                                        <option v-for="(option, index) in projectMemberOptions" :key="index" :value="option.value">
                                            {{ option.label }}
                                        </option>
                                    </select>
                                    <select 
                                        id="all-users" 
                                        v-model="newAllUser" 
                                        @change="addAllUser(newAllUser)"
                                        class="block w-full py-2 pl-10 text-sm text-gray-700 border border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500"
                                    >
                                        <option value="">Select an all user</option>
                                        <option v-for="(option, index) in allUserOptions" :key="index" :value="option.value">
                                            {{ option.label }}
                                        </option>
                                    </select>
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
