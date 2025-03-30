<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { type BreadcrumbItem } from '@/types';
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

interface Phase {
    id: number;
    name: string;
    description: string;
    order: number;
}

interface Props {
    phases: Phase[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Repo',
        href: ''
    },
    {
        title: 'Phases',
        href: '/repo/phases',
    },
];

const props = defineProps<Props>();

// Form for creating a new phase
const createForm = useForm({
    name: '',
    description: '',
    order: 1,
});

// Form for editing a phase
const editForm = useForm({
    id: 0,
    name: '',
    description: '',
    order: 1,
});

const isCreateDialogOpen = ref(false);
const isEditDialogOpen = ref(false);
const isDeleteDialogOpen = ref(false);
const phaseToDelete = ref<Phase | null>(null);

// Submit create form
const submitCreate = () => {
    createForm.post(route('repo.phases.store'), {
        onSuccess: () => {
            isCreateDialogOpen.value = false;
            createForm.reset();
            toast({
                title: "Phase Created",
                description: "The phase has been successfully created.",
                duration: 5000,
            });
        },
    });
};

// Edit phase
const editPhase = (phase: Phase) => {
    editForm.id = phase.id;
    editForm.name = phase.name;
    editForm.description = phase.description;
    editForm.order = phase.order;
    isEditDialogOpen.value = true;
};

// Submit edit form
const submitEdit = () => {
    editForm.put(route('repo.phases.update', editForm.id), {
        onSuccess: () => {
            isEditDialogOpen.value = false;
            toast({
                title: "Phase Updated",
                description: "The phase has been successfully updated.",
                duration: 5000,
            });
        },
    });
};

// Delete phase
const confirmDelete = (phase: Phase) => {
    phaseToDelete.value = phase;
    isDeleteDialogOpen.value = true;
};

// Submit delete
const submitDelete = () => {
    if (phaseToDelete.value) {
        useForm({}).delete(route('repo.phases.destroy', phaseToDelete.value.id), {
            onSuccess: () => {
                isDeleteDialogOpen.value = false;
                phaseToDelete.value = null;
                toast({
                    title: "Phase Deleted",
                    description: "The phase has been successfully deleted.",
                    variant: "destructive",
                    duration: 5000,
                });
            },
        });
    }
};

// Sort phases by order
const sortedPhases = computed(() => {
    return [...props.phases].sort((a, b) => a.order - b.order);
});
</script>

<template>
    <Head title="Phase Repository" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Phase Repository</h1>
                
                <!-- Create Dialog -->
                <Dialog v-model:open="isCreateDialogOpen">
                    <DialogTrigger as-child>
                        <Button class="flex items-center gap-2">
                            <PlusCircle class="h-4 w-4" />
                            Add Phase
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Add New Phase</DialogTitle>
                            <DialogDescription>
                                Create a new phase for your repository.
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
                                <div class="grid gap-2">
                                    <Label for="order">Order</Label>
                                    <Input id="order" type="number" v-model="createForm.order" min="1" required />
                                    <div v-if="createForm.errors.order" class="text-red-500 text-sm">
                                        {{ createForm.errors.order }}
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

            <!-- Phases Table -->
            <Table>
                <TableCaption>A list of all phases in your repository.</TableCaption>
                <TableHeader>
                    <TableRow>
                        <TableHead class="w-16">Order</TableHead>
                        <TableHead>Name</TableHead>
                        <TableHead>Description</TableHead>
                        <TableHead class="w-32">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="phase in sortedPhases" :key="phase.id">
                        <TableCell>{{ phase.order }}</TableCell>
                        <TableCell>{{ phase.name }}</TableCell>
                        <TableCell>{{ phase.description }}</TableCell>
                        <TableCell>
                            <div class="flex gap-2">
                                <Button variant="outline" size="icon" @click="editPhase(phase)">
                                    <Pencil class="h-4 w-4" />
                                </Button>
                                <Button variant="destructive" size="icon" @click="confirmDelete(phase)">
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                    <TableRow v-if="sortedPhases.length === 0">
                        <TableCell colspan="4" class="text-center py-4">No phases found. Create one to get started.</TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <!-- Edit Dialog -->
            <Dialog v-model:open="isEditDialogOpen">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Edit Phase</DialogTitle>
                        <DialogDescription>
                            Update the phase details.
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
                            <div class="grid gap-2">
                                <Label for="edit-order">Order</Label>
                                <Input id="edit-order" type="number" v-model="editForm.order" min="1" required />
                                <div v-if="editForm.errors.order" class="text-red-500 text-sm">
                                    {{ editForm.errors.order }}
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
                        <DialogTitle>Delete Phase</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to delete this phase? This action cannot be undone.
                        </DialogDescription>
                    </DialogHeader>
                    <div v-if="phaseToDelete" class="py-4">
                        <p><strong>Name:</strong> {{ phaseToDelete.name }}</p>
                        <p><strong>Description:</strong> {{ phaseToDelete.description }}</p>
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
