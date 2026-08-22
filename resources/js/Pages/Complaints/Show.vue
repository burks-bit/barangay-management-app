<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

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
                <Link :href="props.backUrl" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to Complaints</Link>
                <div class="flex items-center gap-2">
                    <StatusBadge :status="complaint.priority" />
                    <StatusBadge :status="complaint.status" />
                </div>
            </div>

            <!-- Complaint details -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100">
                    <p class="text-xs font-mono text-gray-500">{{ complaint.complaint_code }}</p>
                    <h1 class="text-lg font-bold text-gray-900 mt-1">{{ complaint.subject }}</h1>
                    <p class="text-sm text-gray-500 mt-1">{{ complaint.category?.name }}</p>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-gray-500">Complainant</dt>
                            <dd class="font-medium text-gray-900">
                                {{ complaint.complainant?.member_profile?.first_name }}
                                {{ complaint.complainant?.member_profile?.last_name }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Location</dt>
                            <dd class="font-medium text-gray-900">{{ complaint.location }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Incident Date & Time</dt>
                            <dd class="font-medium text-gray-900">{{ formatDate(complaint.incident_datetime) }}</dd>
                        </div>
                    </dl>
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-gray-500">Assigned Moderator</dt>
                            <dd class="font-medium text-gray-900">{{ complaint.assigned_moderator?.name || 'Unassigned' }}</dd>
                        </div>
                        <div v-if="complaint.resolution_date">
                            <dt class="text-gray-500">Resolution Date</dt>
                            <dd class="font-medium text-green-700">{{ formatDate(complaint.resolution_date) }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="px-6 pb-6">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">Description</h3>
                    <p class="text-sm text-gray-600 bg-gray-50 rounded-lg p-4 whitespace-pre-line">{{ complaint.description }}</p>
                </div>

                <div v-if="complaint.resolution" class="px-6 pb-6">
                    <h3 class="text-sm font-semibold text-green-800 mb-2">Resolution</h3>
                    <p class="text-sm text-green-700 bg-green-50 border border-green-100 rounded-lg p-4 whitespace-pre-line">{{ complaint.resolution }}</p>
                </div>
            </div>

            <!-- Workflow actions (staff only) -->
            <div
                v-if="$page.props.auth.permissions.includes('process complaints') && !isFinal"
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-6"
            >
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Workflow Actions</h3>

                <!-- Assign -->
                <div
                    v-if="$page.props.auth.permissions.includes('assign complaints') && !complaint.assigned_moderator"
                    class="mb-4 p-4 bg-blue-50 border border-blue-100 rounded-lg"
                >
                    <button @click="showAssignForm = !showAssignForm" class="text-sm font-medium text-blue-700 hover:text-blue-900">
                        {{ showAssignForm ? '- Cancel Assignment' : '+ Assign to Moderator' }}
                    </button>
                    <form v-if="showAssignForm" @submit.prevent="submitAssign" class="mt-3 flex gap-3">
                        <select v-model="assignForm.assigned_to" required
                            class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">Select moderator...</option>
                            <option v-for="mod in moderators" :key="mod.id" :value="mod.id">{{ mod.name }}</option>
                        </select>
                        <button type="submit" :disabled="assignForm.processing"
                            class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50">
                            Assign
                        </button>
                    </form>
                </div>

                <!-- Process / Update status -->
                <div class="mb-4 p-4 bg-yellow-50 border border-yellow-100 rounded-lg">
                    <button @click="showProcessForm = !showProcessForm" class="text-sm font-medium text-yellow-700 hover:text-yellow-900">
                        {{ showProcessForm ? '- Cancel Status Update' : '~ Update Status' }}
                    </button>
                    <form v-if="showProcessForm" @submit.prevent="submitProcess" class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3">
                        <select v-model="processForm.status" required
                            class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">Select new status...</option>
                            <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                        </select>
                        <input v-model="processForm.remarks" type="text" placeholder="Remarks..."
                            class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" />
                        <div class="md:col-span-2 flex justify-end">
                            <button type="submit" :disabled="processForm.processing"
                                class="px-4 py-2 bg-yellow-600 text-white text-sm font-medium rounded-lg hover:bg-yellow-700 disabled:opacity-50">
                                Update Status
                            </button>
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
                        <textarea v-model="resolveForm.resolution" rows="3" required placeholder="Describe the resolution..."
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"></textarea>
                        <div class="flex justify-end">
                            <button type="submit" :disabled="resolveForm.processing"
                                class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 disabled:opacity-50">
                                Mark as Resolved
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Status history timeline -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Status History</h3>
                <ol v-if="complaint.status_histories?.length" class="relative border-l border-gray-200 ml-3 space-y-6">
                    <li v-for="(history, index) in complaint.status_histories" :key="history.id" class="ml-6">
                        <span
                            class="absolute flex items-center justify-center w-6 h-6 rounded-full -left-3 ring-4 ring-white"
                            :class="index === 0 ? 'bg-blue-600' : 'bg-gray-300'"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-white" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </span>
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-900 capitalize">{{ (history.to_status || '').replace(/_/g, ' ') }}</p>
                                <p v-if="history.remarks" class="text-xs text-gray-500 mt-0.5">{{ history.remarks }}</p>
                                <p class="text-xs text-gray-400 mt-1">by {{ history.user?.name || 'System' }}</p>
                            </div>
                            <span class="text-xs text-gray-400 whitespace-nowrap">{{ formatDate(history.created_at) }}</span>
                        </div>
                    </li>
                </ol>
                <p v-else class="text-sm text-gray-400">No status history yet.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>