<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Pencil, Trash2 } from 'lucide-vue-next';

const props = defineProps({ programs: Array });

const editingId = ref(null);
const form = useForm({
    name: '',
    code: '',
    description: '',
    category: '',
    funding_agency: '',
    budget: '',
    start_date: '',
    end_date: '',
    status: 'planning',
});

const edit = (program) => {
    editingId.value = program.id;
    form.name = program.name;
    form.code = program.code;
    form.description = program.description || '';
    form.category = program.category || '';
    form.funding_agency = program.funding_agency || '';
    form.budget = program.budget ?? '';
    form.start_date = program.start_date || '';
    form.end_date = program.end_date || '';
    form.status = program.status;
};

const reset = () => {
    editingId.value = null;
    form.reset();
    form.status = 'planning';
};

const submit = () => {
    if (editingId.value) {
        form.put(`/programs/${editingId.value}`, { onSuccess: reset });
    } else {
        form.post('/programs', { onSuccess: reset });
    }
};

const remove = (program) => {
    if (confirm(`Delete program "${program.name}"?`)) router.delete(`/programs/${program.id}`);
};

const statusClass = (status) => ({
    planning: 'bg-gray-100 text-gray-700 border-gray-200',
    active: 'bg-green-100 text-green-700 border-green-200',
    on_hold: 'bg-amber-100 text-amber-700 border-amber-200',
    completed: 'bg-blue-100 text-blue-700 border-blue-200',
}[status] || 'bg-gray-100 text-gray-700 border-gray-200');

const formatDate = (date) => date ? new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : '—';
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Programs" />

        <div class="space-y-4">
            <div>
                <h1 class="text-xl font-bold text-foreground">Programs</h1>
                <p class="text-sm text-muted-foreground mt-1">Manage barangay programs such as 4Ps, TUPAD, and other government assistance programs.</p>
            </div>

            <!-- Create / Edit form -->
            <form @submit.prevent="submit" class="bg-card rounded-xl border p-6 space-y-4">
                <h2 class="text-sm font-semibold text-foreground">{{ editingId ? 'Edit program' : 'Add program' }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground">Name *</label>
                        <Input v-model="form.name" required type="text" placeholder="e.g., Pantawid Pamilyang Pilipino Program" class="mt-1" />
                        <p v-if="form.errors.name" class="text-xs text-destructive mt-1">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground">Code *</label>
                        <Input v-model="form.code" required type="text" placeholder="e.g., 4PS" class="mt-1" />
                        <p v-if="form.errors.code" class="text-xs text-destructive mt-1">{{ form.errors.code }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground">Status *</label>
                        <Select v-model="form.status" required class="mt-1">
                            <option value="planning">Planning</option>
                            <option value="active">Active</option>
                            <option value="on_hold">On Hold</option>
                            <option value="completed">Completed</option>
                        </Select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground">Category</label>
                        <Input v-model="form.category" type="text" placeholder="e.g., Social Welfare, Livelihood" class="mt-1" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground">Funding Agency</label>
                        <Input v-model="form.funding_agency" type="text" placeholder="e.g., DSWD, DOLE" class="mt-1" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground">Budget (₱)</label>
                        <Input v-model="form.budget" type="number" min="0" step="0.01" class="mt-1" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground">Start Date</label>
                        <Input v-model="form.start_date" type="date" class="mt-1" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground">End Date</label>
                        <Input v-model="form.end_date" type="date" class="mt-1" />
                        <p v-if="form.errors.end_date" class="text-xs text-destructive mt-1">{{ form.errors.end_date }}</p>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-muted-foreground">Description</label>
                    <Textarea v-model="form.description" rows="2" placeholder="Brief description of the program..." class="mt-1" />
                </div>
                <div class="flex justify-end gap-3">
                    <Button v-if="editingId" type="button" variant="outline" @click="reset">Cancel</Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Saving...' : editingId ? 'Update Program' : 'Add Program' }}
                    </Button>
                </div>
            </form>

            <!-- Programs table -->
            <Card class="overflow-hidden">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Program</TableHead>
                            <TableHead>Category</TableHead>
                            <TableHead>Funding Agency</TableHead>
                            <TableHead>Period</TableHead>
                            <TableHead>Beneficiaries</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="program in props.programs" :key="program.id" class="hover:bg-muted/50">
                            <TableCell>
                                <Link :href="`/programs/${program.id}`" class="text-sm font-medium text-foreground hover:text-primary">{{ program.name }}</Link>
                                <p class="text-xs font-mono text-muted-foreground mt-0.5">{{ program.code }}</p>
                            </TableCell>
                            <TableCell class="text-muted-foreground">{{ program.category || '—' }}</TableCell>
                            <TableCell class="text-muted-foreground">{{ program.funding_agency || '—' }}</TableCell>
                            <TableCell class="text-muted-foreground whitespace-nowrap">{{ formatDate(program.start_date) }} – {{ formatDate(program.end_date) }}</TableCell>
                            <TableCell class="text-foreground">
                                {{ program.active_enrollments_count }} active
                                <span class="text-muted-foreground">/ {{ program.enrollments_count }} total</span>
                            </TableCell>
                            <TableCell>
                                <Badge :class="statusClass(program.status)" class="capitalize">{{ program.status.replace('_', ' ') }}</Badge>
                            </TableCell>
                            <TableCell class="text-right space-x-2 whitespace-nowrap">
                                <Button variant="ghost" size="sm" @click="edit(program)"><Pencil class="h-4 w-4" /> Edit</Button>
                                <Button variant="ghost" size="sm" class="text-destructive" @click="remove(program)"><Trash2 class="h-4 w-4" /> Delete</Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="!props.programs.length">
                            <TableCell colspan="7" class="py-10 text-center text-muted-foreground">No programs yet. Add your first program above.</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>