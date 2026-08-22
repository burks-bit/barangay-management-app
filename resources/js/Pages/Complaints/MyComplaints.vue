<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    complaints: Object,
    filters: Object,
});

const status = ref(props.filters.status || '');

let debounceTimer = null;
watch(status, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get('/my-complaints', { status: status.value }, { preserveState: true, replace: true });
    }, 300);
});

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="My Complaints" />

        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">My Complaints</h1>
                    <p class="text-sm text-gray-500">Track the complaints you have filed</p>
                </div>
                <Link
                    href="/my-complaints/create"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-wider hover:bg-blue-700 transition-colors"
                >
                    + File Complaint
                </Link>
            </div>

            <!-- Filter -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <select v-model="status" class="px-3 py-2 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
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
                </select>
            </div>

            <!-- List -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div v-if="complaints.data.length" class="divide-y divide-gray-100">
                    <div v-for="complaint in complaints.data" :key="complaint.id" class="px-6 py-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900">{{ complaint.subject }}</p>
                                <p class="text-xs text-gray-500 mt-0.5 font-mono">{{ complaint.complaint_code }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ complaint.category?.name }} &middot; {{ complaint.location }}</p>
                                <p v-if="complaint.resolution" class="text-xs text-green-700 mt-2 bg-green-50 rounded p-2">
                                    <span class="font-medium">Resolution:</span> {{ complaint.resolution }}
                                </p>
                            </div>
                            <div class="text-right flex-shrink-0 space-y-2">
                                <StatusBadge :status="complaint.status" />
                                <p class="text-xs text-gray-400">{{ formatDate(complaint.created_at) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <p v-else class="text-sm text-gray-400 text-center py-12">No complaints filed yet.</p>
                <Pagination :links="complaints.links" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>