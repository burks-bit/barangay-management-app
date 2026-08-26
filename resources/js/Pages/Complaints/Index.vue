<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { Card, CardContent } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Eye } from 'lucide-vue-next';

const props = defineProps({
    complaints: Object,
    filters: Object,
    categories: Array,
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const categoryId = ref(props.filters.category_id || '');
const priority = ref(props.filters.priority || '');

let debounceTimer = null;
watch([search, status, categoryId, priority], () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get('/complaints', {
            search: search.value,
            status: status.value,
            category_id: categoryId.value,
            priority: priority.value,
        }, {
            preserveState: true,
            replace: true,
        });
    }, 300);
});

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Complaints" />

        <div class="space-y-4">
            <div>
                <h1 class="text-xl font-bold text-foreground">Complaints</h1>
                <p class="text-sm text-muted-foreground">Review, assign, and resolve community complaints</p>
            </div>

            <!-- Filters -->
            <Card>
                <CardContent class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <Input
                            v-model="search"
                            type="text"
                            placeholder="Search by code, subject, or complainant..."
                        />
                        <Select v-model="status">
                            <option value="">All Statuses</option>
                            <option value="submitted">Submitted</option>
                            <option value="under_review">Under Review</option>
                            <option value="verified">Verified</option>
                            <option value="assigned">Assigned</option>
                            <option value="under_investigation">Under Investigation</option>
                            <option value="for_mediation">For Mediation</option>
                            <option value="action_taken">Action Taken</option>
                            <option value="resolved">Resolved</option>
                            <option value="rejected">Rejected</option>
                            <option value="closed">Closed</option>
                        </Select>
                        <Select v-model="categoryId">
                            <option value="">All Categories</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                        </Select>
                        <Select v-model="priority">
                            <option value="">All Priorities</option>
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </Select>
                    </div>
                </CardContent>
            </Card>

            <!-- Table -->
            <Card class="overflow-hidden">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Code</TableHead>
                            <TableHead>Subject</TableHead>
                            <TableHead>Complainant</TableHead>
                            <TableHead>Priority</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="complaint in complaints.data" :key="complaint.id">
                            <TableCell class="font-mono text-muted-foreground">{{ complaint.complaint_code }}</TableCell>
                            <TableCell>
                                <div class="text-sm font-medium text-foreground">{{ complaint.subject }}</div>
                                <div class="text-xs text-muted-foreground">{{ complaint.category?.name }}</div>
                            </TableCell>
                            <TableCell class="text-muted-foreground">
                                {{ complaint.complainant?.member_profile?.first_name }} {{ complaint.complainant?.member_profile?.last_name }}
                            </TableCell>
                            <TableCell><StatusBadge :status="complaint.priority" /></TableCell>
                            <TableCell><StatusBadge :status="complaint.status" /></TableCell>
                            <TableCell class="text-right">
                                <Button variant="ghost" size="sm" as-child>
                                    <Link :href="`/complaints/${complaint.id}`"><Eye class="h-4 w-4" /> View</Link>
                                </Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="!complaints.data.length">
                            <TableCell colspan="6" class="py-12 text-center text-muted-foreground">No complaints found</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
                <Pagination :links="complaints.links" />
            </Card>
        </div>
    </AuthenticatedLayout>
</template>