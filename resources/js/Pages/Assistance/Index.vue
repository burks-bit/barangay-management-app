<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Select } from '@/components/ui/select';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';

const props = defineProps({
    requests: Object,
    filters: Object,
});

const page = usePage();
const permissions = computed(() => page.props.auth?.permissions || []);
const canProcess = computed(() => permissions.value.includes('process assistance'));
const canApprove = computed(() => permissions.value.includes('approve assistance'));

const statusFilter = ref(props.filters.status || '');
let filterTimer = null;
const applyFilter = () => {
    clearTimeout(filterTimer);
    filterTimer = setTimeout(() => {
        router.get('/assistance', { status: statusFilter.value }, {
            preserveState: true,
            replace: true,
        });
    }, 300);
};

const TRANSITIONS = {
    submitted: [
        { value: 'for_verification', label: 'Mark for Verification', permission: 'process assistance' },
        { value: 'rejected', label: 'Reject', permission: 'approve assistance' },
        { value: 'cancelled', label: 'Cancel', permission: 'process assistance' },
    ],
    for_verification: [
        { value: 'under_assessment', label: 'Start Assessment', permission: 'process assistance' },
        { value: 'rejected', label: 'Reject', permission: 'approve assistance' },
        { value: 'cancelled', label: 'Cancel', permission: 'process assistance' },
    ],
    under_assessment: [
        { value: 'approved', label: 'Approve', permission: 'approve assistance' },
        { value: 'rejected', label: 'Reject', permission: 'approve assistance' },
        { value: 'cancelled', label: 'Cancel', permission: 'process assistance' },
    ],
    approved: [
        { value: 'for_release', label: 'Mark for Release', permission: 'approve assistance' },
        { value: 'released', label: 'Release', permission: 'approve assistance' },
        { value: 'rejected', label: 'Reject', permission: 'approve assistance' },
    ],
    for_release: [
        { value: 'released', label: 'Release', permission: 'approve assistance' },
        { value: 'rejected', label: 'Reject', permission: 'approve assistance' },
    ],
};

const allowedTransitions = (request) =>
    (TRANSITIONS[request.status] || []).filter((t) => permissions.value.includes(t.permission));

const activeRowId = ref(null);
const actionForm = ref({ status: '', remarks: '' });

const toggleActions = (request) => {
    if (activeRowId.value === request.id) {
        activeRowId.value = null;
        return;
    }
    const options = allowedTransitions(request);
    activeRowId.value = request.id;
    actionForm.value = { status: options[0]?.value || '', remarks: '' };
};

const submitAction = (request) => {
    router.post(`/assistance/${request.id}/status`, {
        status: actionForm.value.status,
        remarks: actionForm.value.remarks,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            activeRowId.value = null;
            actionForm.value = { status: '', remarks: '' };
        },
    });
};

const formatDate = (date) => date ? new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : '-';
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Assistance Requests" />

        <div class="space-y-4">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-foreground">Assistance Requests</h1>
                    <p class="text-sm text-muted-foreground mt-1">Review and process assistance requests from residents.</p>
                </div>
            </div>

            <!-- Status filter -->
            <Card>
                <CardContent class="p-4">
                    <Select v-model="statusFilter" @change="applyFilter" class="w-full md:w-64">
                        <option value="">All Statuses</option>
                        <option value="submitted">Submitted</option>
                        <option value="for_verification">For Verification</option>
                        <option value="under_assessment">Under Assessment</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="for_release">For Release</option>
                        <option value="released">Released</option>
                        <option value="cancelled">Cancelled</option>
                    </Select>
                </CardContent>
            </Card>

            <Card class="overflow-hidden">
                <CardContent v-if="requests.data.length" class="divide-y divide-border p-0">
                    <div v-for="request in requests.data" :key="request.id" class="px-6 py-4">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-foreground">{{ request.assistanceType?.name }}</p>
                                <p class="text-xs font-mono text-muted-foreground mt-1">{{ request.assistance_code }}</p>
                                <p class="text-sm text-muted-foreground mt-2">{{ request.applicant?.member_profile?.first_name }} {{ request.applicant?.member_profile?.last_name }}</p>
                                <p class="text-xs text-muted-foreground mt-1">{{ request.reason }}</p>
                                <p v-if="request.amount" class="text-xs text-muted-foreground mt-1">Requested amount: ₱{{ Number(request.amount).toFixed(2) }}</p>
                                <p class="text-xs text-muted-foreground mt-2">Submitted {{ formatDate(request.created_at) }}</p>
                            </div>
                            <StatusBadge :status="request.status" />
                        </div>

                        <!-- Actions -->
                        <div v-if="(canProcess || canApprove) && allowedTransitions(request).length" class="mt-3">
                            <Button
                                type="button"
                                variant="secondary"
                                size="sm"
                                @click="toggleActions(request)"
                            >
                                {{ activeRowId === request.id ? 'Close Actions' : 'Update Status' }}
                            </Button>

                            <form v-if="activeRowId === request.id" @submit.prevent="submitAction(request)" class="mt-3 flex flex-col sm:flex-row gap-3 sm:items-end bg-muted rounded-lg p-3">
                                <div class="flex-1">
                                    <label class="block text-xs font-medium text-muted-foreground mb-1">New Status *</label>
                                    <Select v-model="actionForm.status" required>
                                        <option v-for="opt in allowedTransitions(request)" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                    </Select>
                                </div>
                                <div class="flex-1">
                                    <label class="block text-xs font-medium text-muted-foreground mb-1">Remarks</label>
                                    <Input v-model="actionForm.remarks" type="text" placeholder="Optional notes..." />
                                </div>
                                <Button type="submit" :disabled="!actionForm.status" class="whitespace-nowrap">
                                    Apply
                                </Button>
                            </form>
                        </div>
                    </div>
                </CardContent>
                <p v-else class="text-sm text-muted-foreground text-center py-12">No assistance requests found.</p>
                <Pagination :links="requests.links" />
            </Card>
        </div>
    </AuthenticatedLayout>
</template>