<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Pencil, Trash2 } from 'lucide-vue-next';

const props = defineProps({ requestTypes: Array });
const editingId = ref(null);
const form = useForm({ name: '', slug: '', description: '', fee: 0, is_active: true });

const edit = (requestType) => {
    editingId.value = requestType.id;
    form.name = requestType.name;
    form.slug = requestType.slug;
    form.description = requestType.description || '';
    form.fee = requestType.fee;
    form.is_active = requestType.is_active;
};

const reset = () => {
    editingId.value = null;
    form.reset();
    form.is_active = true;
};

const submit = () => {
    if (editingId.value) {
        form.put(`/request-types/${editingId.value}`, { onSuccess: reset });
    } else {
        form.post('/request-types', { onSuccess: reset });
    }
};

const remove = (requestType) => {
    if (confirm(`Delete ${requestType.name}?`)) router.delete(`/request-types/${requestType.id}`);
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Document Types" />
        <div class="space-y-4">
            <div><h1 class="text-xl font-bold text-foreground">Document Types</h1><p class="text-sm text-muted-foreground mt-1">Manage document names, descriptions, prices, and availability.</p></div>
            <form @submit.prevent="submit" class="bg-card rounded-xl border p-6 space-y-4">
                <h2 class="text-sm font-semibold text-foreground">{{ editingId ? 'Edit document type' : 'Add document type' }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-muted-foreground">Name *</label><Input v-model="form.name" required type="text" class="mt-1" /><p v-if="form.errors.name" class="text-xs text-destructive">{{ form.errors.name }}</p></div>
                    <div><label class="block text-sm font-medium text-muted-foreground">Slug *</label><Input v-model="form.slug" required type="text" class="mt-1" /><p v-if="form.errors.slug" class="text-xs text-destructive">{{ form.errors.slug }}</p></div>
                    <div><label class="block text-sm font-medium text-muted-foreground">Fee *</label><Input v-model="form.fee" required type="number" min="0" step="0.01" class="mt-1" /><p v-if="form.errors.fee" class="text-xs text-destructive">{{ form.errors.fee }}</p></div>
                    <label class="flex items-center gap-2 self-end text-sm text-foreground"><input v-model="form.is_active" type="checkbox" class="rounded border-input" /> Active</label>
                </div>
                <div><label class="block text-sm font-medium text-muted-foreground">Description</label><Textarea v-model="form.description" rows="3" class="mt-1" /></div>
                <div class="flex justify-end gap-3"><Button v-if="editingId" type="button" variant="outline" @click="reset">Cancel</Button><Button type="submit" :disabled="form.processing">{{ form.processing ? 'Saving...' : editingId ? 'Update Type' : 'Add Type' }}</Button></div>
            </form>
            <Card class="overflow-hidden">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Slug</TableHead>
                            <TableHead>Fee</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="text-right"></TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="requestType in props.requestTypes" :key="requestType.id">
                            <TableCell class="font-medium text-foreground">{{ requestType.name }}</TableCell>
                            <TableCell class="font-mono text-muted-foreground">{{ requestType.slug }}</TableCell>
                            <TableCell class="text-muted-foreground">{{ Number(requestType.fee).toFixed(2) }}</TableCell>
                            <TableCell>
                                <Badge :class="requestType.is_active ? 'bg-green-100 text-green-700 border-green-200' : 'bg-gray-100 text-gray-500 border-gray-200'">
                                    {{ requestType.is_active ? 'Active' : 'Inactive' }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right space-x-2">
                                <Button variant="ghost" size="sm" @click="edit(requestType)"><Pencil class="h-4 w-4" /> Edit</Button>
                                <Button variant="ghost" size="sm" class="text-destructive" @click="remove(requestType)"><Trash2 class="h-4 w-4" /> Delete</Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="!props.requestTypes.length">
                            <TableCell colspan="5" class="py-10 text-center text-muted-foreground">No document types found.</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>