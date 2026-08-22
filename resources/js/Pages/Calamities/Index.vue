<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({ calamities: Object, filters: Object });

const statuses = ['reported', 'active', 'under_response', 'contained', 'resolved', 'archived'];
const filter = (event) => router.get('/calamities', { status: event.target.value }, { preserveState: true, replace: true });
const formatDate = (date) => date ? new Date(date).toLocaleDateString() : '-';

const destroy = (calamity) => {
    if (confirm('Delete this calamity?')) router.delete(`/calamities/${calamity.id}`);
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Calamities" />
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Calamities</h1>
                    <p class="text-sm text-gray-500 mt-1">Monitor reported and active calamities.</p>
                </div>
                <div class="flex items-center gap-2">
                    <Link v-if="$page.props.auth?.permissions?.includes('create calamities')" href="/calamities/create" class="px-4 py-2 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700">+ Add Calamity</Link>
                </div>
            </div>

            <select :value="filters.status || ''" @change="filter" class="px-3 py-2 w-full sm:w-auto rounded-lg border-gray-300 text-sm">
                <option value="">All statuses</option>
                <option v-for="status in statuses" :key="status" :value="status">{{ status.replaceAll('_',' ') }}</option>
            </select>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div v-if="calamities.data.length" class="divide-y divide-gray-100">
                    <article v-for="calamity in calamities.data" :key="calamity.id" class="p-5">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                            <div>
                                <h2 class="font-semibold text-gray-900">{{ calamity.name }}</h2>
                                <p class="text-xs font-mono text-gray-500 mt-1">{{ calamity.event_code }}</p>
                                <p class="text-sm text-gray-600 mt-1 capitalize">{{ calamity.type }} | Started {{ formatDate(calamity.started_at) }}</p>
                                <p v-if="calamity.description" class="text-sm text-gray-600 mt-2">{{ calamity.description }}</p>
                                <div class="flex flex-wrap gap-3 text-xs text-gray-500 mt-2">
                                    <span>{{ calamity.affected_households }} households</span>
                                    <span>{{ calamity.affected_residents }} residents</span>
                                </div>
                            </div>
                            <div class="text-left sm:text-right">
                                <StatusBadge :status="calamity.status" />
                                <div v-if="$page.props.auth?.permissions?.includes('update calamities') || $page.props.auth?.permissions?.includes('delete calamities')" class="mt-2 flex gap-2 sm:justify-end">
                                    <Link v-if="$page.props.auth?.permissions?.includes('update calamities')" :href="`/calamities/${calamity.id}/edit`" class="action-link text-indigo-700">Edit</Link>
                                    <button v-if="$page.props.auth?.permissions?.includes('delete calamities')" @click="destroy(calamity)" class="text-red-600 hover:text-red-900 text-sm">Delete</button>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>
                <p v-else class="p-12 text-center text-sm text-gray-400">No calamities found.</p>
                <Pagination :links="calamities.links" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
