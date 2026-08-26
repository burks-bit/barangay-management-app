<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent } from '@/components/ui/card';

const props = defineProps({
    complaint: Object,
    moderators: Array,
    backUrl: { type: String, default: '/complaints' },
});

const showAssignForm = ref(false);
const showProcessForm = ref(false);
const showResolveForm = ref(false);

const assignForm = useForm({ assigned_to: '' });
const processForm = useForm({ status: '', remarks: '' });
const resolveForm = useForm({ resolution: '' });

const submitAssign = () => {
    assignForm.post(`/complaints/${props.complaint.id}/assign`, {
        onSuccess: () => {
            showAssignForm.value = false;
            assignForm.reset();
        },
    });
};

const submitProcess = () => {
    processForm.post(`/complaints/${props.complaint.id}/process`, {
        onSuccess: () => {
            showProcessForm.value = false;
            processForm.reset();
        },
    });
};

const submitResolve = () => {
    resolveForm.post(`/complaints/${props.complaint.id}/resolve`, {
        onSuccess: () => {
            showResolveForm.value = false;
            resolveForm.reset();
        },
    });
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleString('en-US', {
        year: 'numeric', month: 'short', day: 'numeric',
        hour: 'numeric', minute: '2-digit',
    });
};

const statusOptions = [
    { value: 'under_review', label: 'Under Review' },
    { value: 'verified', label: 'Verified' },
    { value: 'under_investigation', label: 'Under Investigation' },
    { value: 'for_mediation', label: 'For Mediation' },
    { value: 'action_taken', label: 'Action Taken' },
    { value: 'rejected', label: 'Rejected' },
    { value: 'closed', label: 'Closed' },
];

const isFinal = ['resolved', 'closed', 'rejected'].includes(props.complaint.status);
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Complaint Details" />

        <div class="max-w-4xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <Link :href="props.backUrl" class="text-sm text-muted-foreground hover:text-foreground">&larr; Back to Complaints</Link>
                <div class="flex items-center gap-2">
                    <StatusBadge :status="complaint.priority" />
                    <StatusBadge :status="complaint.status" />
                </div>
            </div>

            <!-- Complaint details -->
            <Card class="overflow-hidden">
                <div class="px-6 py-5 border-b">
                    <p class="text-xs font-mono text-muted-foreground">{{ complaint.complaint_code }}</p>
                    <h1 class="text-lg font-bold text-foreground mt-1">{{ complaint.subject }}</h1>
                    <p class="text-sm text-muted-foreground mt-1">{{ complaint.category?.name }}</p>
                </div>

                <CardContent class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6">
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-muted-foreground">Complainant</dt>
                            <dd class="font-medium text-foreground">
                                {{ complaint.complainant?.member_profile?.first_name }}
                                {{ complaint.complainant?.member_profile?.last_name }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Location</dt>
                            <dd class="font-medium text-foreground">{{ complaint.location }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Incident Date & Time</dt>
                            <dd class="font-medium text-foreground">{{ formatDate(complaint.incident_datetime) }}</dd>
                        </div>
                    </dl>
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-muted-foreground">Assigned Moderator</dt>
                            <dd class="font-medium text-foreground">{{ complaint.assigned_moderator?.name || 'Unassigned' }}</dd>
                        </div>
                        <div v-if="complaint.resolution_date">
                            <dt class="text-muted-foreground">Resolution Date</dt>
                            <dd class="font-medium text-green-700">{{ formatDate(complaint.resolution_date) }}</dd>
                        </div>
                    </dl>
                </CardContent>

                <div class="px-6 pb-6">
                    <h3 class="text-sm font-semibold text-foreground mb-2">Description</h3>
                    <p class="text-sm text-muted-foreground bg-muted rounded-lg p-4 whitespace-pre-line">{{ complaint.description }}</p>
                </div>

                <div v-if="complaint.resolution" class="px-6 pb-6">
                    <h3 class="text-sm font-semibold text-green-800 mb-2">Resolution</h3>
                    <p class="text-sm text-green-700 bg-green-50 border border-green-100 rounded-lg p-4 whitespace-pre-line">{{ complaint.resolution }}</p>
                </div>
            </Card>

            <!-- Workflow actions (staff only) -->
            <Card
                v-if="$page.props.auth.permissions.includes('process complaints') && !isFinal"
            >
                <CardContent class="pt-6">
                    <h3 class="text-sm font-semibold text-foreground mb-4">Workflow Actions</h3>

                    <!-- Assign -->
                    <div
                        v-if="$page.props.auth.permissions.includes('assign complaints') && !complaint.assigned_moderator"
                        class="mb-4 p-4 bg-primary/5 border rounded-lg"
                    >
                        <button @click="showAssignForm = !showAssignForm" class="text-sm font-medium text-primary hover:text-primary/80">
                            {{ showAssignForm ? '- Cancel Assignment' : '+ Assign to Moderator' }}
                        </button>
                        <form v-if="showAssignForm" @submit.prevent="submitAssign" class="mt-3 flex gap-3">
                            <Select v-model="assignForm.assigned_to" required class="flex-1">
                                <option value="">Select moderator...</option>
                                <option v-for="mod in moderators" :key="mod.id" :value="mod.id">{{ mod.name }}</option>
                            </Select>
                            <Button type="submit" :disabled="assignForm.processing">
                                Assign
                            </Button>
                        </form>
                    </div>

                    <!-- Process / Update status -->
                    <div class="mb-4 p-4 bg-yellow-50 border border-yellow-100 rounded-lg">
                        <button @click="showProcessForm = !showProcessForm" class="text-sm font-medium text-yellow-700 hover:text-yellow-900">
                            {{ showProcessForm ? '- Cancel Status Update' : '~ Update Status' }}
                        </button>
                        <form v-if="showProcessForm" @submit.prevent="submitProcess" class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3">
                            <Select v-model="processForm.status" required>
                                <option value="">Select new status...</option>
                                <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                            </Select>
                            <Input v-model="processForm.remarks" type="text" placeholder="Remarks..." />
                            <div class="md:col-span-2 flex justify-end">
                                <Button type="submit" class="bg-yellow-600 hover:bg-yellow-700" :disabled="processForm.processing">
                                    Update Status
                                </Button>
                            </div>
                        </form>
                    </div>

                    <!-- Resolve -->
                    <div
                        v-if="$page.props.auth.permissions.includes('resolve complaints')"
                        class="p-4 bg-green-50 border border-green-100 rounded-lg"
                    >
                        <button @click="showResolveForm = !showResolveForm" class="text-sm font-medium text-green-700 hover:text-green-900">
                            {{ showResolveForm ? '- Cancel Resolution' : '✓ Resolve Complaint' }}
                        </button>
                        <form v-if="showResolveForm" @submit.prevent="submitResolve" class="mt-3 space-y-3">
                            <Textarea v-model="resolveForm.resolution" rows="3" required placeholder="Describe the resolution..." />
                            <div class="flex justify-end">
                                <Button type="submit" class="bg-green-600 hover:bg-green-700" :disabled="resolveForm.processing">
                                    Mark as Resolved
                                </Button>
                            </div>
                        </form>
                    </div>
                </CardContent>
            </Card>

            <!-- Status history timeline -->
            <Card>
                <CardContent class="pt-6">
                    <h3 class="text-sm font-semibold text-foreground mb-4">Status History</h3>
                    <ol v-if="complaint.status_histories?.length" class="relative border-l ml-3 space-y-6">
                        <li v-for="(history, index) in complaint.status_histories" :key="history.id" class="ml-6">
                            <span
                                class="absolute flex items-center justify-center w-6 h-6 rounded-full -left-3 ring-4 ring-card"
                                :class="index === 0 ? 'bg-primary' : 'bg-muted'"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-white" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-medium text-foreground capitalize">{{ (history.to_status || '').replace(/_/g, ' ') }}</p>
                                    <p v-if="history.remarks" class="text-xs text-muted-foreground mt-0.5">{{ history.remarks }}</p>
                                    <p class="text-xs text-muted-foreground mt-1">by {{ history.user?.name || 'System' }}</p>
                                </div>
                                <span class="text-xs text-muted-foreground whitespace-nowrap">{{ formatDate(history.created_at) }}</span>
                            </div>
                        </li>
                    </ol>
                    <p v-else class="text-sm text-muted-foreground">No status history yet.</p>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>