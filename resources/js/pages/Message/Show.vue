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
import { Textarea } from '@/components/ui/textarea';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Separator } from '@/components/ui/separator';
import MultiSelect from '@/components/MultiSelect/index.vue';
import { useToast } from '@/components/ui/toast/use-toast';
import { 
    MessageSquare, 
    Users, 
    Calendar, 
    ArrowLeft,
    Send,
    Reply,
    UserCircle
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

interface MessageReply {
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
    replies: MessageReply[];
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
    message: Message;
    projectUsers: User[];
    allUsers: User[];
}

const props = defineProps<Props>();
const { toast } = useToast();

// Format date
const formatDate = (dateString: string) => {
    return format(new Date(dateString), 'MMM d, yyyy h:mm a');
};

// Get recipient names as a comma-separated string
const getRecipientNames = (recipients: MessageRecipient[]) => {
    return recipients.map(recipient => recipient.name).join(', ');
};

// Get user initials for avatar
const getUserInitials = (name: string) => {
    return name
        .split(' ')
        .map(part => part[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
};

// Quick reply form
const quickReplyForm = useForm({
    body: '',
});

// Submit quick reply
const submitQuickReply = () => {
    quickReplyForm.post(route('projects.messages.quick-reply', [props.project.id, props.message.id]), {
        onSuccess: () => {
            toast({
                title: 'Reply Sent',
                description: 'Your reply has been sent to all participants.',
                duration: 3000,
            });
            quickReplyForm.reset();
        },
    });
};

// Regular reply form with custom recipients
const showCustomReply = ref(false);
const selectedRecipientsIds = ref<number[]>([]);
const selectedRecipientsNames = ref<string[]>([]);
const newProjectMember = ref('');
const newAllUser = ref('');
const projectMemberOptions = computed(() => {
    return props.projectUsers?.filter(user => user && user.id && user.name) || [];
});

const allUserOptions = computed(() => {
    return props.allUsers?.filter(user => user && user.id && user.name && 
        !props.projectUsers?.some(pm => pm && pm.id === user.id)) || [];
});

// Initialize selected recipients with original recipients
const initializeRecipients = () => {
    selectedRecipientsIds.value = props.message.recipients.map(r => r.id);
    // Add original sender if not in recipients
    if (!selectedRecipientsIds.value.includes(props.message.sender.id)) {
        selectedRecipientsIds.value.push(props.message.sender.id);
    }
    selectedRecipientsNames.value = selectedRecipientsIds.value.map(id => {
        const user = props.projectUsers.find(user => user && user.id === id);
        return user ? user.name : '';
    });
};

// Submit custom reply
const replyForm = useForm({
    body: '',
    recipients: [] as number[],
});

const submitReply = () => {
    replyForm.recipients = selectedRecipientsIds.value;
    
    replyForm.post(route('projects.messages.reply', [props.project.id, props.message.id]), {
        onSuccess: () => {
            toast({
                title: 'Reply Sent',
                description: 'Your reply has been sent successfully.',
                duration: 3000,
            });
            replyForm.reset();
            showCustomReply.value = false;
        },
    });
};

// Toggle custom reply form
const toggleCustomReply = () => {
    showCustomReply.value = !showCustomReply.value;
    if (showCustomReply.value) {
        initializeRecipients();
    }
};

// Add recipient from project members
const addProjectMember = (id: string) => {
    const recipientId = parseInt(id);
    if (!isNaN(recipientId) && !selectedRecipientsIds.value.includes(recipientId)) {
        selectedRecipientsIds.value.push(recipientId);
        const user = projectMemberOptions.value.find(user => user && user.id === recipientId);
        if (user && user.name) {
            selectedRecipientsNames.value.push(user.name);
        }
        newProjectMember.value = '';
    }
};

// Add recipient from all users
const addAllUser = (id: string) => {
    const recipientId = parseInt(id);
    if (!isNaN(recipientId) && !selectedRecipientsIds.value.includes(recipientId)) {
        selectedRecipientsIds.value.push(recipientId);
        const user = allUserOptions.value.find(user => user && user.id === recipientId);
        if (user && user.name) {
            selectedRecipientsNames.value.push(user.name);
        }
        newAllUser.value = '';
    }
};

// Remove recipient
const removeRecipient = (id: number) => {
    const index = selectedRecipientsIds.value.indexOf(id);
    if (index !== -1) {
        selectedRecipientsIds.value.splice(index, 1);
        selectedRecipientsNames.value.splice(index, 1);
    }
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
        title: props.message.title,
        href: `/projects/${props.project.id}/messages/${props.message.id}`,
    },
];
</script>

<template>
    <Head :title="`${message.title} - ${project.name}`" />
    
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto p-6">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ message.title }}</h1>
                    <p class="text-muted-foreground">{{ message.subject }}</p>
                </div>
                <Link :href="`/projects/${project.id}/messages`">
                    <Button variant="outline">
                        <ArrowLeft class="h-4 w-4 mr-2" />
                        Back to Messages
                    </Button>
                </Link>
            </div>
            
            <!-- Message Thread -->
            <div class="space-y-6">
                <!-- Original Message -->
                <Card>
                    <CardHeader class="pb-2">
                        <div class="flex justify-between">
                            <div class="flex items-center space-x-3">
                                <Avatar>
                                    <AvatarFallback>{{ getUserInitials(message.sender.name) }}</AvatarFallback>
                                </Avatar>
                                <div>
                                    <CardTitle class="text-base">{{ message.sender.name }}</CardTitle>
                                    <CardDescription class="text-xs">
                                        <span class="flex items-center">
                                            <Calendar class="h-3 w-3 mr-1" />
                                            {{ formatDate(message.created_at) }}
                                        </span>
                                    </CardDescription>
                                </div>
                            </div>
                            <div class="text-xs text-muted-foreground">
                                <div class="flex items-center">
                                    <Users class="h-3.5 w-3.5 mr-1" />
                                    <span>To: {{ getRecipientNames(message.recipients) }}</span>
                                </div>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="prose prose-sm max-w-none dark:prose-invert">
                            <p class="whitespace-pre-line">{{ message.body }}</p>
                        </div>
                    </CardContent>
                </Card>
                
                <!-- Replies -->
                <div v-if="message.replies && message.replies.length > 0" class="space-y-4">
                    <h3 class="text-lg font-medium">Replies ({{ message.replies.length }})</h3>
                    
                    <Card v-for="reply in message.replies" :key="reply.id" class="border-l-4 border-l-primary/50">
                        <CardHeader class="pb-2">
                            <div class="flex justify-between">
                                <div class="flex items-center space-x-3">
                                    <Avatar>
                                        <AvatarFallback>{{ getUserInitials(reply.sender.name) }}</AvatarFallback>
                                    </Avatar>
                                    <div>
                                        <CardTitle class="text-base">{{ reply.sender.name }}</CardTitle>
                                        <CardDescription class="text-xs">
                                            <span class="flex items-center">
                                                <Calendar class="h-3 w-3 mr-1" />
                                                {{ formatDate(reply.created_at) }}
                                            </span>
                                        </CardDescription>
                                    </div>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="prose prose-sm max-w-none dark:prose-invert">
                                <p class="whitespace-pre-line">{{ reply.body }}</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
                
                <!-- Quick Reply Form -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-base">Quick Reply</CardTitle>
                        <CardDescription>
                            Your reply will be sent to all participants in this conversation.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submitQuickReply" class="space-y-4">
                            <Textarea 
                                v-model="quickReplyForm.body" 
                                :error="quickReplyForm.errors.body"
                                placeholder="Type your reply here..."
                                rows="4"
                                required
                            />
                            <p v-if="quickReplyForm.errors.body" class="text-destructive text-sm mt-1">{{ quickReplyForm.errors.body }}</p>
                            
                            <div class="flex justify-between items-center">
                                <Button type="button" variant="outline" @click="toggleCustomReply">
                                    <Users class="h-4 w-4 mr-2" />
                                    {{ showCustomReply ? 'Cancel Custom Reply' : 'Custom Reply' }}
                                </Button>
                                <Button type="submit" :disabled="quickReplyForm.processing">
                                    <Send class="h-4 w-4 mr-2" />
                                    Send to All
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
                
                <!-- Custom Reply Form -->
                <Card v-if="showCustomReply">
                    <CardHeader>
                        <CardTitle class="text-base">Custom Reply</CardTitle>
                        <CardDescription>
                            Select specific recipients for your reply.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submitReply" class="space-y-4">
                            <div>
                                <label class="text-sm font-medium mb-1 block">Recipients</label>
                                <div class="flex flex-wrap gap-2 mb-2">
                                    <div v-for="(name, index) in selectedRecipientsNames" :key="index" class="bg-gray-100 rounded-md py-1 px-2 text-sm text-black">
                                        {{ name }}
                                        <button @click="removeRecipient(selectedRecipientsIds[index])" class="ml-2 text-destructive hover:text-destructive-500">
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
                                        class="block w-full py-2 pl-3 text-sm text-gray-700 border border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500"
                                    >
                                        <option value="">Select a project member</option>
                                        <option v-for="user in projectMemberOptions" :key="user.id" :value="user.id">
                                            {{ user.name }}
                                        </option>
                                    </select>
                                    <select 
                                        id="all-users" 
                                        v-model="newAllUser" 
                                        @change="addAllUser(newAllUser)"
                                        class="block w-full py-2 pl-3 text-sm text-gray-700 border border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500"
                                    >
                                        <option value="">Select an all user</option>
                                        <option v-for="user in allUserOptions" :key="user.id" :value="user.id">
                                            {{ user.name }}
                                        </option>
                                    </select>
                                </div>
                                <p v-if="replyForm.errors.recipients" class="text-destructive text-sm mt-1">{{ replyForm.errors.recipients }}</p>
                            </div>
                            
                            <div>
                                <label class="text-sm font-medium mb-1 block">Message</label>
                                <Textarea 
                                    v-model="replyForm.body" 
                                    :error="replyForm.errors.body"
                                    placeholder="Type your reply here..."
                                    rows="4"
                                    required
                                />
                                <p v-if="replyForm.errors.body" class="text-destructive text-sm mt-1">{{ replyForm.errors.body }}</p>
                            </div>
                            
                            <div class="flex justify-end">
                                <Button type="submit" :disabled="replyForm.processing">
                                    <Reply class="h-4 w-4 mr-2" />
                                    Send Reply
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
