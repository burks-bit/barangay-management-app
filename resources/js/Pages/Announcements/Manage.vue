<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Pencil, Send, Archive, Trash2 } from 'lucide-vue-next';

const props = defineProps({
    announcements: Array,
});

const types = [
    { value: 'barangay_announcement', label: 'Barangay Announcement' },
    { value: 'calamity_warning', label: 'Calamity Warning' },
    { value: 'evacuation_notice', label: 'Evacuation Notice' },
    { value: 'community_event', label: 'Community Event' },
    { value: 'service_interruption', label: 'Service Interruption' },
    { value: 'emergency_instruction', label: 'Emergency Instruction' },
    { value: 'general', label: 'General' },
];

const typeLabel = (value) => types.find((t) => t.value === value)?.label || value;

const editingId = ref(null);
const form = useForm({
    title: '',
    type: 'barangay_announcement',
    priority: 'normal',
    content: '',
    publish: false,
});

const edit = (announcement) => {
    editingId.value = announcement.id;
    form.title = announcement.title;
    form.type = announcement.type;
    form.priority = announcement.priority;
    form.content = announcement.content || '';
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const reset = () => {
    editingId.value = null;
    form.reset();
    form.type = 'barangay_announcement';
    form.priority = 'normal';
    form.publish = false;
};

const submit = (action) => {
    if (action === 'update') {
        form.put(`/announcements/${editingId.value}`, { onSuccess: reset });
        return;
    }
    form.publish = action === 'publish';
    form.post('/announcements', { onSuccess: reset });
};

const publish = (announcement) => {
    router.post(`/announcements/${announcement.id}/publish`, {}, { preserveScroll: true });
};

const archive = (announcement) => {
    router.post(`/announcements/${announcement.id}/archive`, {}, { preserveScroll: true });
};

const remove = (announcement) => {
    if (confirm(`Delete announcement "${announcement.title}"?`)) {
        router.delete(`/announcements/${announcement.id}`);
    }
};

const statusClass = (status) => ({
    draft: 'bg-gray-100 text-gray-600 border-gray-200',
    published: 'bg-green-100 text-green-700 border-green-200',
    archived: 'bg-amber-100 text-amber-700 border-amber-200',
}[status] || 'bg-gray-100 text-gray-600 border-gray-200');

const priorityClass = (priority) => ({
    normal: 'bg-blue-50 text-blue-700 border-blue-200',
    important: 'bg-amber-100 text-amber-700 border-amber-200',
    emergency: 'bg-red-100 text-red-700 border-red-200',
}[priority] || 'bg-gray-100 text-gray-600 border-gray-200');

const formatDate = (date) => date
    ? new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
    : '—';
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Manage Announcements" />

        <div class="space-y-4">
            <div>
                <h1 class="text-xl font-bold text-foreground">Manage Announcements</h1>
                <p class="text-sm text-muted-foreground mt-1">Create, publish, and archive announcements shown to community members.</p>
            </div>

            <!-- Create / Edit form -->
            <form @submit.prevent="submit(editingId ? 'update' : 'draft')" class="bg-card rounded-xl border p-6 space-y-4">
                <h2 class="text-sm font-semibold text-foreground">{{ editingId ? 'Edit announcement' : 'New announcement' }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-muted-foreground">Title *</label>
                        <Input v-model="form.title" required type="text" maxlength="200" placeholder="e.g., Water service interruption on Friday" class="mt-1" />
                        <p v-if="form.errors.title" class="text-xs text-destructive mt-1">{{ form.errors.title }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground">Type *</label>
                        <Select v-model="form.type" required class="mt-1">
                            <option v-for="type in types" :key="type.value" :value="type.value">{{ type.label }}</option>
                        </Select>
                        <p v-if="form.errors.type" class="text-xs text-destructive mt-1">{{ form.errors.type }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground">Priority *</label>
                        <Select v-model="form.priority" required class="mt-1">
                            <option value="normal">Normal</option>
                            <option value="important">Important</option>
                            <option value="emergency">Emergency</option>
                        </Select>
                        <p v-if="form.errors.priority" class="text-xs text-destructive mt-1">{{ form.errors.priority }}</p>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-muted-foreground">Content *</label>
                    <Textarea v-model="form.content" required rows="4" placeholder="Write the announcement details here..." class="mt-1" />
                    <p v-if="form.errors.content" class="text-xs text-destructive mt-1">{{ form.errors.content }}</p>
                </div>
                <div class="flex justify-end gap-3">
                    <Button v-if="editingId" type="button" variant="outline" @click="reset">Cancel</Button>
                    <template v-if="editingId">
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Saving...' : 'Update Announcement' }}
                        </Button>
                    </template>
                    <template v-else>
                        <Button type="submit" variant="outline" :disabled="form.processing">
                            Save Draft
                        </Button>
                        <Button type="button" :disabled="form.processing" @click="submit('publish')">
                            Publish Now
                        </Button>
                    </template>
                </div>
            </form>

            <!-- Announcements table -->
            <Card class="overflow-hidden">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Announcement</TableHead>
                            <TableHead>Type</TableHead>
                            <TableHead>Priority</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Published</TableHead>
                            <TableHead>Created By</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="announcement in props.announcements" :key="announcement.id" class="hover:bg-muted/50">
                            <TableCell class="max-w-xs">
                                <p class="text-sm font-medium text-foreground">{{ announcement.title }}</p>
                                <p class="text-xs text-muted-foreground mt-0.5 line-clamp-2">{{ announcement.content }}</p>
                            </TableCell>
                            <TableCell class="text-muted-foreground whitespace-nowrap">{{ typeLabel(announcement.type) }}</TableCell>
                            <TableCell>
                                <Badge :class="priorityClass(announcement.priority)" class="capitalize">{{ announcement.priority }}</Badge>
                            </TableCell>
                            <TableCell>
                                <Badge :class="statusClass(announcement.status)" class="capitalize">{{ announcement.status }}</Badge>
                            </TableCell>
                            <TableCell class="text-muted-foreground whitespace-nowrap">{{ formatDate(announcement.published_at) }}</TableCell>
                            <TableCell class="text-muted-foreground whitespace-nowrap">{{ announcement.creator?.name || '—' }}</TableCell>
                            <TableCell class="text-right space-x-2 whitespace-nowrap">
                                <Button variant="ghost" size="sm" @click="edit(announcement)"><Pencil class="h-4 w-4" /> Edit</Button>
                                <Button v-if="announcement.status !== 'published'" variant="ghost" size="sm" class="text-green-700" @click="publish(announcement)"><Send class="h-4 w-4" /> Publish</Button>
                                <Button v-if="announcement.status === 'published'" variant="ghost" size="sm" class="text-amber-700" @click="archive(announcement)"><Archive class="h-4 w-4" /> Archive</Button>
                                <Button variant="ghost" size="sm" class="text-destructive" @click="remove(announcement)"><Trash2 class="h-4 w-4" /> Delete</Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="!props.announcements.length">
                            <TableCell colspan="7" class="py-10 text-center text-muted-foreground">No announcements yet. Create your first one above.</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>