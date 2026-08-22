<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    serviceRequest: Object,
});

const permissions = () => window.__inertia_page?.props?.auth?.permissions || [];
const canProcess = () => (window.__inertia_page?.props?.auth?.permissions || []).includes('process requests');

const showProcessForm = ref(false);
const processForm = useForm({
    status: '',
    remarks: '',
});

const submitProcess = () => {
    processForm.post(`/requests/${props.serviceRequest.id}/process`, {
        onSuccess: () => {
            showProcessForm.value = false;
            processForm.reset();
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
    { value: 'for_verification', label: 'For Verification' },
    { value: 'approved', label: 'Approved' },
    { value: 'processing', label: 'Processing' },
    { value: 'ready_for_release', label: 'Ready for Release' },
    { value: 'released', label: 'Released' },
    { value: 'rejected', label: 'Rejected' },
    { value: 'cancelled', label: 'Cancelled' },
];
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Request Details" />

        <div class="max-w-4xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <Link href="/requests" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to Requests</Link>
                <StatusBadge :status="serviceRequest.status" />
            </div>

            <!-- Request details -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100">
                    <p class="text-xs font-mono text-gray-500">{{ serviceRequest.tracking_number }}</p>
                    <h1 class="text-lg font-bold text-gray-900 mt-1">{{ serviceRequest.request_type?.name }}</h1>
                    <p v-if="serviceRequest.request_type?.fee > 0" class="text-sm text-gray-500 mt-1">Fee: ₱{{ serviceRequest.request_type.fee }}</p>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-gray-500">Requester</dt>
                            <dd class="font-medium text-gray-900">
                                {{ serviceRequest.requester?.member_profile?.first_name }}
                                {{ serviceRequest.requester?.member_profile?.last_name }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Purok</dt>
                            <dd class="font-medium text-gray-900">{{ serviceRequest.requester?.member_profile?.purok?.name || '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Purpose</dt>
                            <dd class="font-medium text-gray-900">{{ serviceRequest.purpose }}</dd>
                        </div>
                    </dl>
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-gray-500">Submitted</dt>
                            <dd class="font-medium text-gray-900">{{ formatDate(serviceRequest.submitted_at) }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Assigned Staff</dt>
                            <dd class="font-medium text-gray-900">{{ serviceRequest.assigned_staff?.name || 'Unassigned' }}</dd>
                        </div>
                        <div v-if="serviceRequest.released_at">
                            <dt class="text-gray-500">Released</dt>
                            <dd class="font-medium text-green-700">{{ formatDate(serviceRequest.released_at) }}</dd>
                        </div>
                    </dl>
                </div>

                <div v-if="serviceRequest.description" class="px-6 pb-6">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">Additional Details</h3>
                    <p class="text-sm text-gray-600 bg-gray-50 rounded-lg p-4">{{ serviceRequest.description }}</p>
                </div>
            </div>

            <!-- Process actions (staff only) -->
            <div
                v-if="$page.props.auth.permissions.includes('process requests') && !['released', 'cancelled'].includes(serviceRequest.status)"
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-6"
            >
                <button
                    @click="showProcessForm = !showProcessForm"
                    class="w-full flex items-center justify-between text-left"
                >
                    <span class="text-sm font-semibold text-gray-900">Update Request Status</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': showProcessForm }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <form v-if="showProcessForm" @submit.prevent="submitProcess" class="mt-4 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">New Status *</label>
                            <select v-model="processForm.status" required
                                class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="">Select status...</option>
                                <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Remarks</label>
                            <input v-model="processForm.remarks" type="text"
                                placeholder="Optional processing notes..."
                                class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" />
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" :disabled="processForm.processing"
                            class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50">
                            {{ processForm.processing ? 'Updating...' : 'Update Status' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Status history timeline -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Status History</h3>
                <ol v-if="serviceRequest.status_histories?.length" class="relative border-l border-gray-200 ml-3 space-y-6">
                    <li v-for="(history, index) in serviceRequest.status_histories" :key="history.id" class="ml-6">
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