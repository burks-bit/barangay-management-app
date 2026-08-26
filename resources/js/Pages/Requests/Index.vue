<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { Card, CardContent } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Plus, Eye } from 'lucide-vue-next';

const props = defineProps({
    requests: Object,
    filters: Object,
    requestTypes: Array,
});

const page = usePage();
const permissions = computed(() => page.props.auth?.permissions || []);
const canCreateWalkIn = computed(() => permissions.value.includes('process requests'));

const requesterName = (request) => {
    const profile = request.requester?.member_profile || request.resident;
    if (profile) return `${profile.first_name} ${profile.last_name}`.trim();
    return request.requester?.name || '-';
};

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const requestTypeId = ref(props.filters.request_type_id || '');

let debounceTimer = null;
watch([search, status, requestTypeId], () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get('/requests', {
            search: search.value,
            status: status.value,
            request_type_id: requestTypeId.value,
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
        <Head title="Service Requests" />

        <div class="space-y-4">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-foreground">Service Requests</h1>
                    <p class="text-sm text-muted-foreground">Process and track barangay document requests</p>
                </div>
                <Button
                    v-if="canCreateWalkIn"
                    as-child
                >
                    <Link href="/requests/create" class="inline-flex items-center gap-2">
                        <Plus class="h-4 w-4" /> New Walk-in Request
                    </Link>
                </Button>
            </div>

            <!-- Filters -->
            <Card>
                <CardContent class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <Input
                            v-model="search"
                            type="text"
                            placeholder="Search by tracking no., purpose, or requester..."
                        />
                        <Select v-model="status">
                            <option value="">All Statuses</option>
                            <option value="submitted">Submitted</option>
                            <option value="for_verification">For Verification</option>
                            <option value="approved">Approved</option>
                            <option value="processing">Processing</option>
                            <option value="ready_for_release">Ready for Release</option>
                            <option value="released">Released</option>
                            <option value="rejected">Rejected</option>
                            <option value="cancelled">Cancelled</option>
                        </Select>
                        <Select v-model="requestTypeId">
                            <option value="">All Request Types</option>
                            <option v-for="type in requestTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
                        </Select>
                    </div>
                </CardContent>
            </Card>

            <!-- Table -->
            <Card class="overflow-hidden">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Tracking No.</TableHead>
                            <TableHead>Requester</TableHead>
                            <TableHead>Type</TableHead>
                            <TableHead>Submitted</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="request in requests.data" :key="request.id">
                            <TableCell class="font-mono text-muted-foreground">
                                {{ request.tracking_number }}
                                <span
                                    v-if="request.source === 'walk_in'"
                                    class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wide bg-amber-100 text-amber-700"
                                >
                                    Walk-in
                                </span>
                            </TableCell>
                            <TableCell class="text-foreground">{{ requesterName(request) }}</TableCell>
                            <TableCell class="text-muted-foreground">{{ request.request_type?.name }}</TableCell>
                            <TableCell class="text-muted-foreground">{{ formatDate(request.submitted_at) }}</TableCell>
                            <TableCell><StatusBadge :status="request.status" /></TableCell>
                            <TableCell class="text-right">
                                <Button variant="ghost" size="sm" as-child>
                                    <Link :href="`/requests/${request.id}`"><Eye class="h-4 w-4" /> View</Link>
                                </Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="!requests.data.length">
                            <TableCell colspan="6" class="py-12 text-center text-muted-foreground">No requests found</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
                <Pagination :links="requests.links" />
            </Card>
        </div>
    </AuthenticatedLayout>
</template>