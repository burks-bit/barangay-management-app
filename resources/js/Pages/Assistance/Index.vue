<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    requests: Object,
    filters: Object,
});

const formatDate = (date) => date ? new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : '-';
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Assistance Requests" />

        <div class="space-y-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Assistance Requests</h1>
                <p class="text-sm text-gray-500 mt-1">Review assistance requests from residents.</p>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div v-if="requests.data.length" class="divide-y divide-gray-100">
                    <div v-for="request in requests.data" :key="request.id" class="px-6 py-4 flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ request.assistanceType?.name }}</p>
                            <p class="text-xs font-mono text-gray-500 mt-1">{{ request.assistance_code }}</p>
                            <p class="text-sm text-gray-700 mt-2">{{ request.applicant?.member_profile?.first_name }} {{ request.applicant?.member_profile?.last_name }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ request.reason }}</p>
                            <p class="text-xs text-gray-400 mt-2">Submitted {{ formatDate(request.created_at) }}</p>
                        </div>
                        <StatusBadge :status="request.status" />
                    </div>
                </div>
                <p v-else class="text-sm text-gray-400 text-center py-12">No assistance requests found.</p>
                <Pagination :links="requests.links" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
