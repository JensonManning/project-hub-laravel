<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { ref } from 'vue';
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
import { PlusCircle, Pencil, Trash2 } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { useToast } from '@/components/ui/toast';

const { toast } = useToast();

interface Resource {
    id: number;
    name: string;
    description: string;
}

interface Props {
    resources: Resource[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Repo',
        href: ''
    },
    {
        title: 'Resources',
        href: '/repo/resources',
    },
];

const props = defineProps<Props>();

// Form for creating a new resource
const createForm = useForm({
    name: '',
    description: '',
});

// Form for editing a resource
const editForm = useForm({
    id: 0,
    name: '',
    description: '',
});

const isCreateDialogOpen = ref(false);
const isEditDialogOpen = ref(false);
const isDeleteDialogOpen = ref(false);
const resourceToDelete = ref<Resource | null>(null);

// Submit create form
const submitCreate = () => {
    createForm.post(route('repo.resources.store'), {
        onSuccess: () => {
            isCreateDialogOpen.value = false;
            createForm.reset();
            toast({
                title: "Resource Created",
                description: "The resource has been successfully created.",
                duration: 5000,
            });
        },
    });
};

// Edit resource
const editResource = (resource: Resource) => {
    editForm.id = resource.id;
    editForm.name = resource.name;
    editForm.description = resource.description;
    isEditDialogOpen.value = true;
};

// Submit edit form
const submitEdit = () => {
    editForm.put(route('repo.resources.update', editForm.id), {
        onSuccess: () => {
            isEditDialogOpen.value = false;
            toast({
                title: "Resource Updated",
                description: "The resource has been successfully updated.",
                duration: 5000,
            });
        },
    });
};

// Delete resource
const confirmDelete = (resource: Resource) => {
    resourceToDelete.value = resource;
    isDeleteDialogOpen.value = true;
};

// Submit delete
const submitDelete = () => {
    if (resourceToDelete.value) {
        useForm({}).delete(route('repo.resources.destroy', resourceToDelete.value.id), {
            onSuccess: () => {
                isDeleteDialogOpen.value = false;
                resourceToDelete.value = null;
                toast({
                    title: "Resource Deleted",
                    description: "The resource has been successfully deleted.",
                    variant: "destructive",
                    duration: 5000,
                });
            },
        });
    }
};
</script>

<template>
    <Head title="Resource Repository" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Resource Repository</h1>
                
                <!-- Create Dialog -->
                <Dialog v-model:open="isCreateDialogOpen">
                    <DialogTrigger as-child>
                        <Button class="flex items-center gap-2">
                            <PlusCircle class="h-4 w-4" />
                            Add Resource
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Add New Resource</DialogTitle>
                            <DialogDescription>
                                Create a new resource for your repository.
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
                            </div>
                            <DialogFooter>
                                <Button type="button" variant="outline" @click="isCreateDialogOpen = false">Cancel</Button>
                                <Button type="submit" :disabled="createForm.processing">Save</Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>

            <!-- Resources Table -->
            <Table>
                <TableCaption>A list of all resources in your repository.</TableCaption>
                <TableHeader>
                    <TableRow>
                        <TableHead>Name</TableHead>
                        <TableHead>Description</TableHead>
                        <TableHead class="w-32">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="resource in props.resources" :key="resource.id">
                        <TableCell>{{ resource.name }}</TableCell>
                        <TableCell>{{ resource.description }}</TableCell>
                        <TableCell>
                            <div class="flex gap-2">
                                <Button variant="outline" size="icon" @click="editResource(resource)">
                                    <Pencil class="h-4 w-4" />
                                </Button>
                                <Button variant="destructive" size="icon" @click="confirmDelete(resource)">
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                    <TableRow v-if="props.resources.length === 0">
                        <TableCell colspan="3" class="text-center py-4">No resources found. Create one to get started.</TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <!-- Edit Dialog -->
            <Dialog v-model:open="isEditDialogOpen">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Edit Resource</DialogTitle>
                        <DialogDescription>
                            Update the resource details.
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
                        <DialogTitle>Delete Resource</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to delete this resource? This action cannot be undone.
                        </DialogDescription>
                    </DialogHeader>
                    <div v-if="resourceToDelete" class="py-4">
                        <p><strong>Name:</strong> {{ resourceToDelete.name }}</p>
                        <p><strong>Description:</strong> {{ resourceToDelete.description }}</p>
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
