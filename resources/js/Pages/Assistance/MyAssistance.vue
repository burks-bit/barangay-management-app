<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    requests: Object,
    filters: Object,
});

const formatDate = (date) => date ? new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : '-';
</script>

<template>
    <AuthenticatedLayout>
        <Head title="My Assistance Requests" />

        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">My Assistance Requests</h1>
                    <p class="text-sm text-gray-500 mt-1">Track assistance requests you have submitted.</p>
                </div>
                <Link href="/my-assistance/create" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium">+ Request Assistance</Link>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <select :value="filters.status || ''" @change="router.get('/my-assistance', { status: $event.target.value }, { preserveState: true, replace: true })" class="px-3 py-2 rounded-lg border-gray-300 text-sm">
                    <option value="">All Statuses</option>
                    <option v-for="status in ['submitted', 'for_verification', 'under_assessment', 'approved', 'rejected', 'for_release', 'released', 'cancelled']" :key="status" :value="status">{{ status.replaceAll('_', ' ') }}</option>
                </select>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div v-if="requests.data.length" class="divide-y divide-gray-100">
                    <div v-for="request in requests.data" :key="request.id" class="px-6 py-4 flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ request.assistanceType?.name }}</p>
                            <p class="text-xs font-mono text-gray-500 mt-1">{{ request.assistance_code }}</p>
                            <p class="text-sm text-gray-700 mt-2">{{ request.reason }}</p>
                            <p class="text-xs text-gray-400 mt-2">Submitted {{ formatDate(request.created_at) }}</p>
                        </div>
                        <StatusBadge :status="request.status" />
                    </div>
                </div>
                <p v-else class="text-sm text-gray-400 text-center py-12">No assistance requests yet.</p>
                <Pagination :links="requests.links" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
