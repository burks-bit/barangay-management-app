<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    residents: Object,
    filters: Object,
    puroks: Array,
});

const search = ref(props.filters.search || '');
const purokId = ref(props.filters.purok_id || '');
const verificationStatus = ref(props.filters.verification_status || '');

let debounceTimer = null;
watch([search, purokId, verificationStatus], () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get('/residents', {
            search: search.value,
            purok_id: purokId.value,
            verification_status: verificationStatus.value,
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
        <Head title="Residents" />

        <div class="space-y-4">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Residents</h1>
                    <p class="text-sm text-gray-500">Manage resident records and verifications</p>
                </div>
                <Link
                    v-if="$page.props.auth?.permissions?.includes('create residents')"
                    href="/residents/create"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-wider hover:bg-blue-700 transition-colors"
                >
                    + Add Resident
                </Link>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search by name, ID, or contact..."
                            class="w-full px-3 py-2 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                        />
                    </div>
                    <select
                        v-model="purokId"
                        class="px-3 py-2 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                    >
                        <option value="">All Puroks</option>
                        <option v-for="purok in puroks" :key="purok.id" :value="purok.id">{{ purok.name }}</option>
                    </select>
                    <select
                        v-model="verificationStatus"
                        class="px-3 py-2 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                    >
                        <option value="">All Verification Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="verified">Verified</option>
                        <option value="rejected">Rejected</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resident ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purok</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Verification</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <tr v-for="resident in residents.data" :key="resident.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-600">{{ resident.resident_id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-700 text-xs font-semibold mr-3">
                                            {{ (resident.first_name[0] || '') + (resident.last_name[0] || '') }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ resident.first_name }} {{ resident.middle_name ? resident.middle_name.charAt(0) + '. ' : '' }}{{ resident.last_name }}
                                            </div>
                                            <div class="text-xs text-gray-400 capitalize">{{ resident.sex }} &middot; {{ resident.age ?? '-' }} yrs</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ resident.purok?.name || '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ resident.contact_number || '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap"><StatusBadge :status="resident.verification_status" /></td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <Link :href="`/residents/${resident.id}`" class="action-link text-blue-700">View</Link>
                                    <Link
                                        v-if="$page.props.auth?.permissions?.includes('update residents')"
                                        :href="`/residents/${resident.id}/edit`"
                                        class="action-link text-indigo-700"
                                    >Edit</Link>
                                    <button
                                        v-if="$page.props.auth?.permissions?.includes('verify residents') && resident.verification_status === 'pending'"
                                        @click="router.post(`/residents/${resident.id}/verify`)"
                                        class="text-green-600 hover:text-green-900"
                                    >Verify</button>
                                </td>
                            </tr>
                            <tr v-if="!residents.data.length">
                                <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-400">No residents found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination :links="residents.links" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>