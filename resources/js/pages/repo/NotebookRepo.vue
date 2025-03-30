<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
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
import { PlusCircle, Pencil, Trash2, Eye } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Editor, EditorContent, useEditor } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import BulletList from '@tiptap/extension-bullet-list';
import OrderedList from '@tiptap/extension-ordered-list';
import ListItem from '@tiptap/extension-list-item';
import Bold from '@tiptap/extension-bold';
import Italic from '@tiptap/extension-italic';
import Heading from '@tiptap/extension-heading';
import { useToast } from '@/components/ui/toast';

const { toast } = useToast();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Repo',
        href: ''
    },
    {
        title: 'Notebooks',
        href: '/repo/notebooks',
    },
];

interface Notebook {
    id: number;
    name: string;
    description: string;
    content: string;
}

interface Props {
    notebooks: Notebook[];
}

const props = defineProps<Props>();

// Form for creating a new notebook
const createForm = useForm({
    name: '',
    description: '',
    content: '',
});

// Form for editing a notebook
const editForm = useForm({
    id: 0,
    name: '',
    description: '',
    content: '',
});

const isCreateDialogOpen = ref(false);
const isEditDialogOpen = ref(false);
const isDeleteDialogOpen = ref(false);
const isViewDialogOpen = ref(false);
const notebookToDelete = ref<Notebook | null>(null);
const notebookToView = ref<Notebook | null>(null);

// Create editor instances
const createEditor = useEditor({
    extensions: [
        StarterKit,
        BulletList,
        OrderedList,
        ListItem,
        Bold,
        Italic,
        Heading.configure({
            levels: [1, 2, 3],
        }),
    ],
    content: '',
    onUpdate: ({ editor }) => {
        createForm.content = editor.getHTML();
    },
});

const editEditor = useEditor({
    extensions: [
        StarterKit,
        BulletList,
        OrderedList,
        ListItem,
        Bold,
        Italic,
        Heading.configure({
            levels: [1, 2, 3],
        }),
    ],
    content: '',
    onUpdate: ({ editor }) => {
        editForm.content = editor.getHTML();
    },
});

// Reset editor content when dialog opens/closes
watch(() => isCreateDialogOpen.value, (isOpen) => {
    if (isOpen && createEditor.value) {
        createEditor.value.commands.setContent('');
    }
});

watch(() => isEditDialogOpen.value, (isOpen, prevIsOpen) => {
    if (isOpen && editEditor.value && editForm.content) {
        editEditor.value.commands.setContent(editForm.content);
    }
});

// Submit create form
const submitCreate = () => {
    createForm.post(route('repo.notebooks.store'), {
        onSuccess: () => {
            isCreateDialogOpen.value = false;
            createForm.reset();
            if (createEditor.value) {
                createEditor.value.commands.setContent('');
            }
            toast({
                title: "Notebook Created",
                description: "The notebook has been successfully created.",
                duration: 5000,
            });
        },
    });
};

// Edit notebook
const editNotebook = (notebook: Notebook) => {
    editForm.id = notebook.id;
    editForm.name = notebook.name;
    editForm.description = notebook.description;
    editForm.content = notebook.content;
    isEditDialogOpen.value = true;
    
    // Set editor content after dialog is open
    setTimeout(() => {
        if (editEditor.value) {
            editEditor.value.commands.setContent(notebook.content);
        }
    }, 100);
};

// View notebook
const viewNotebook = (notebook: Notebook) => {
    notebookToView.value = notebook;
    isViewDialogOpen.value = true;
};

// Submit edit form
const submitEdit = () => {
    editForm.put(route('repo.notebooks.update', editForm.id), {
        onSuccess: () => {
            isEditDialogOpen.value = false;
            toast({
                title: "Notebook Updated",
                description: "The notebook has been successfully updated.",
                duration: 5000,
            });
        },
    });
};

// Delete notebook
const confirmDelete = (notebook: Notebook) => {
    notebookToDelete.value = notebook;
    isDeleteDialogOpen.value = true;
};

// Submit delete
const submitDelete = () => {
    if (notebookToDelete.value) {
        useForm({}).delete(route('repo.notebooks.destroy', { notebook: notebookToDelete.value.id }), {
            onSuccess: () => {
                isDeleteDialogOpen.value = false;
                notebookToDelete.value = null;
                toast({
                    title: "Notebook Deleted",
                    description: "The notebook has been successfully deleted.",
                    variant: "destructive",
                    duration: 5000,
                });
            },
        });
    }
};

// Editor toolbar buttons
const editorButtons = [
    {
        icon: 'format_bold',
        action: (editor: Editor) => editor.chain().focus().toggleBold().run(),
        isActive: (editor: Editor) => editor.isActive('bold'),
        tooltip: 'Bold',
    },
    {
        icon: 'format_italic',
        action: (editor: Editor) => editor.chain().focus().toggleItalic().run(),
        isActive: (editor: Editor) => editor.isActive('italic'),
        tooltip: 'Italic',
    },
    {
        icon: 'format_list_bulleted',
        action: (editor: Editor) => editor.chain().focus().toggleBulletList().run(),
        isActive: (editor: Editor) => editor.isActive('bulletList'),
        tooltip: 'Bullet List',
    },
    {
        icon: 'format_list_numbered',
        action: (editor: Editor) => editor.chain().focus().toggleOrderedList().run(),
        isActive: (editor: Editor) => editor.isActive('orderedList'),
        tooltip: 'Numbered List',
    },
    {
        icon: 'title',
        action: (editor: Editor) => editor.chain().focus().toggleHeading({ level: 2 }).run(),
        isActive: (editor: Editor) => editor.isActive('heading', { level: 2 }),
        tooltip: 'Heading',
    },
];
</script>

<template>
    <Head title="Notebook Repository" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Notebook Repository</h1>
                
                <!-- Create Dialog -->
                <Dialog v-model:open="isCreateDialogOpen">
                    <DialogTrigger as-child>
                        <Button class="flex items-center gap-2">
                            <PlusCircle class="h-4 w-4" />
                            Add Notebook
                        </Button>
                    </DialogTrigger>
                    <DialogContent class="max-w-3xl">
                        <DialogHeader>
                            <DialogTitle>Add New Notebook</DialogTitle>
                            <DialogDescription>
                                Create a new notebook for your repository.
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
                                    <Label for="content">Content</Label>
                                    <div class="border border-input rounded-md overflow-hidden">
                                        <div class="bg-muted p-2 flex gap-2 border-b border-input">
                                            <button 
                                                v-for="button in editorButtons" 
                                                :key="button.tooltip"
                                                type="button"
                                                class="p-1 rounded hover:bg-background"
                                                :class="{ 'bg-background': createEditor && button.isActive(createEditor) }"
                                                @click="createEditor && button.action(createEditor)"
                                                :title="button.tooltip"
                                            >
                                                <span class="material-icons text-sm">{{ button.icon }}</span>
                                            </button>
                                        </div>
                                        <EditorContent :editor="createEditor" class="p-3 min-h-[200px]" />
                                    </div>
                                    <div v-if="createForm.errors.content" class="text-red-500 text-sm">
                                        {{ createForm.errors.content }}
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

            <!-- Notebooks Table -->
            <Table>
                <TableCaption>A list of all notebooks in your repository.</TableCaption>
                <TableHeader>
                    <TableRow>
                        <TableHead>Name</TableHead>
                        <TableHead>Description</TableHead>
                        <TableHead class="w-32">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="notebook in props.notebooks" :key="notebook.id">
                        <TableCell>{{ notebook.name }}</TableCell>
                        <TableCell>{{ notebook.description }}</TableCell>
                        <TableCell>
                            <div class="flex gap-2">
                                <Button variant="outline" size="icon" @click="editNotebook(notebook)">
                                    <Pencil class="h-4 w-4" />
                                </Button>
                                <Button variant="outline" size="icon" @click="viewNotebook(notebook)">
                                    <Eye class="h-4 w-4" />
                                </Button>
                                <Button variant="destructive" size="icon" @click="confirmDelete(notebook)">
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                    <TableRow v-if="props.notebooks.length === 0">
                        <TableCell colspan="3" class="text-center py-4">No notebooks found. Create one to get started.</TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <!-- Edit Dialog -->
            <Dialog v-model:open="isEditDialogOpen">
                <DialogContent class="max-w-3xl">
                    <DialogHeader>
                        <DialogTitle>Edit Notebook</DialogTitle>
                        <DialogDescription>
                            Update the notebook details.
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
                                <Label for="edit-content">Content</Label>
                                <div class="border border-input rounded-md overflow-hidden">
                                    <div class="bg-muted p-2 flex gap-2 border-b border-input">
                                        <button 
                                            v-for="button in editorButtons" 
                                            :key="button.tooltip"
                                            type="button"
                                            class="p-1 rounded hover:bg-background"
                                            :class="{ 'bg-background': editEditor && button.isActive(editEditor) }"
                                            @click="editEditor && button.action(editEditor)"
                                            :title="button.tooltip"
                                        >
                                            <span class="material-icons text-sm">{{ button.icon }}</span>
                                        </button>
                                    </div>
                                    <EditorContent :editor="editEditor" class="p-3 min-h-[200px]" />
                                </div>
                                <div v-if="editForm.errors.content" class="text-red-500 text-sm">
                                    {{ editForm.errors.content }}
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

            <!-- View Dialog -->
            <Dialog v-model:open="isViewDialogOpen">
                <DialogContent class="max-w-3xl">
                    <DialogHeader>
                        <DialogTitle>View Notebook</DialogTitle>
                        <DialogDescription>
                            View the notebook details.
                        </DialogDescription>
                    </DialogHeader>
                    <div v-if="notebookToView" class="py-4">
                        <h2 class="text-lg font-bold mb-2">Name: {{ notebookToView.name }}</h2>
                        <p class="mb-2">Description: {{ notebookToView.description }}</p>
                        <h2 class="text-lg font-bold mt-4">Content : </h2>
                        <div class="mt-4" v-html="notebookToView.content"></div>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="isViewDialogOpen = false">Close</Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <!-- Delete Dialog -->
            <Dialog v-model:open="isDeleteDialogOpen">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete Notebook</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to delete this notebook? This action cannot be undone.
                        </DialogDescription>
                    </DialogHeader>
                    <div v-if="notebookToDelete" class="py-4">
                        <p><strong>Name:</strong> {{ notebookToDelete.name }}</p>
                        <p><strong>Description:</strong> {{ notebookToDelete.description }}</p>
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

<style>
/* TipTap editor styles */
.ProseMirror {
    min-height: 150px;
    outline: none;
}

.ProseMirror p {
    margin-bottom: 0.75em;
}

.ProseMirror ul, .ProseMirror ol {
    padding-left: 1.5em;
    margin-bottom: 0.75em;
}

.ProseMirror h1 {
    font-size: 1.5em;
    font-weight: bold;
    margin-bottom: 0.5em;
}

.ProseMirror h2 {
    font-size: 1.25em;
    font-weight: bold;
    margin-bottom: 0.5em;
}

.ProseMirror h3 {
    font-size: 1.1em;
    font-weight: bold;
    margin-bottom: 0.5em;
}

.ProseMirror a {
    color: #3b82f6;
    text-decoration: underline;
}
</style>
