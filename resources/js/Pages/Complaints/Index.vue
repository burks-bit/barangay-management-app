<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

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
                <h1 class="text-xl font-bold text-gray-900">Complaints</h1>
                <p class="text-sm text-gray-500">Review, assign, and resolve community complaints</p>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search by code, subject, or complainant..."
                        class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                    />
                    <select v-model="status" class="rounded-lg px-3 py-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
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
                    <select v-model="categoryId" class="rounded-lg px-3 py-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        <option value="">All Categories</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                    </select>
                    <select v-model="priority" class="rounded-lg px-3 py-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        <option value="">All Priorities</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Complainant</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <tr v-for="complaint in complaints.data" :key="complaint.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-600">{{ complaint.complaint_code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ complaint.subject }}</div>
                                    <div class="text-xs text-gray-400">{{ complaint.category?.name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ complaint.complainant?.member_profile?.first_name }} {{ complaint.complainant?.member_profile?.last_name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap"><StatusBadge :status="complaint.priority" /></td>
                                <td class="px-6 py-4 whitespace-nowrap"><StatusBadge :status="complaint.status" /></td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <Link :href="`/complaints/${complaint.id}`" class="text-blue-600 hover:text-blue-900">View</Link>
                                </td>
                            </tr>
                            <tr v-if="!complaints.data.length">
                                <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-400">No complaints found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination :links="complaints.links" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>