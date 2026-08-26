<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Plus, Eye, Trash2 } from 'lucide-vue-next';

defineProps({
    reports: Array,
});

const remove = (report) => {
    if (confirm(`Delete report "${report.name}"?`)) {
        router.delete(`/reports/census/${report.id}`);
    }
};

const datasetLabel = (value) => ({
    service_requests: 'Service Requests',
    complaints: 'Complaints',
    assistance_requests: 'Assistance Requests',
}[value] || value);

const groupLabel = (dataset, group) => ({
    status: 'Status',
    type: 'Type',
    priority: 'Priority',
    category: 'Category',
    month: 'Month',
    source: 'Source',
}[group] || group);
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Census Reports" />

        <div class="space-y-4">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-foreground">Census Reports</h1>
                    <p class="text-sm text-muted-foreground mt-1">Saved analytics over raised requests, complaints, and assistance — filtered by statuses and other dimensions.</p>
                </div>
                <Button as-child>
                    <Link href="/reports/census/builder">
                        <Plus class="h-4 w-4" /> Build New Report
                    </Link>
                </Button>
            </div>

            <Card class="overflow-hidden">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Report</TableHead>
                            <TableHead>Dataset</TableHead>
                            <TableHead>Grouped By</TableHead>
                            <TableHead>Status Filters</TableHead>
                            <TableHead>Created By</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="report in reports" :key="report.id" class="hover:bg-muted/50">
                            <TableCell>
                                <Link :href="`/reports/census/${report.id}`" class="text-sm font-medium text-foreground hover:text-primary">{{ report.name }}</Link>
                                <p v-if="report.description" class="text-xs text-muted-foreground mt-0.5 max-w-sm truncate">{{ report.description }}</p>
                            </TableCell>
                            <TableCell class="text-muted-foreground">{{ datasetLabel(report.dataset) }}</TableCell>
                            <TableCell class="text-muted-foreground">{{ groupLabel(report.dataset, report.group_by) }}</TableCell>
                            <TableCell class="text-muted-foreground">
                                <template v-if="report.filters?.statuses?.length">
                                    <span
                                        v-for="s in report.filters.statuses.slice(0, 3)"
                                        :key="s"
                                        class="inline-flex items-center mr-1 mb-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-primary/10 text-primary capitalize"
                                    >{{ s.replace(/_/g, ' ') }}</span>
                                    <span v-if="report.filters.statuses.length > 3" class="text-xs text-muted-foreground">+{{ report.filters.statuses.length - 3 }}</span>
                                </template>
                                <span v-else class="text-muted-foreground">All statuses</span>
                            </TableCell>
                            <TableCell class="text-muted-foreground">{{ report.creator?.name || '—' }}</TableCell>
                            <TableCell class="text-right space-x-2 whitespace-nowrap">
                                <Button variant="ghost" size="sm" as-child>
                                    <Link :href="`/reports/census/${report.id}`"><Eye class="h-4 w-4" /> View</Link>
                                </Button>
                                <Button variant="ghost" size="sm" class="text-destructive" @click="remove(report)">
                                    <Trash2 class="h-4 w-4" /> Delete
                                </Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="!reports.length">
                            <TableCell colspan="6" class="py-10 text-center text-muted-foreground">
                                No saved reports yet. Use the builder to create your first census report.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>