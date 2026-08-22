<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    requests: Object,
    filters: Object,
    requestTypes: Array,
});

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
            <div>
                <h1 class="text-xl font-bold text-gray-900">Service Requests</h1>
                <p class="text-sm text-gray-500">Process and track barangay document requests</p>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search by tracking no., purpose, or requester..."
                        class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                    />
                    <select v-model="status" class="rounded-lg px-3 py-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        <option value="">All Statuses</option>
                        <option value="submitted">Submitted</option>
                        <option value="for_verification">For Verification</option>
                        <option value="approved">Approved</option>
                        <option value="processing">Processing</option>
                        <option value="ready_for_release">Ready for Release</option>
                        <option value="released">Released</option>
                        <option value="rejected">Rejected</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <select v-model="requestTypeId" class="rounded-lg px-3 py-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        <option value="">All Request Types</option>
                        <option v-for="type in requestTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tracking No.</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requester</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <tr v-for="request in requests.data" :key="request.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-600">{{ request.tracking_number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ request.requester?.member_profile?.first_name }} {{ request.requester?.member_profile?.last_name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ request.request_type?.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(request.submitted_at) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap"><StatusBadge :status="request.status" /></td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <Link :href="`/requests/${request.id}`" class="text-blue-600 hover:text-blue-900">View</Link>
                                </td>
                            </tr>
                            <tr v-if="!requests.data.length">
                                <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-400">No requests found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination :links="requests.links" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>