<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, router } from '@inertiajs/vue3';

defineProps({ calamities: Object, filters: Object });

const statuses = ['reported', 'active', 'under_response', 'contained', 'resolved', 'archived'];
const filter = (event) => router.get('/calamities', { status: event.target.value }, { preserveState: true, replace: true });
const formatDate = (date) => date ? new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : '-';
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Calamities" />
        <div class="space-y-4">
            <div class="flex items-center justify-between gap-3">
                <div><h1 class="text-xl font-bold text-gray-900">Calamities</h1><p class="text-sm text-gray-500 mt-1">Monitor reported and active calamities.</p></div>
                <select :value="filters.status || ''" @change="filter" class="px-3 py-2 rounded-lg border-gray-300 text-sm">
                    <option value="">All statuses</option><option v-for="status in statuses" :key="status" :value="status">{{ status.replaceAll('_', ' ') }}</option>
                </select>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div v-if="calamities.data.length" class="divide-y divide-gray-100">
                    <article v-for="calamity in calamities.data" :key="calamity.id" class="p-5">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                            <div><h2 class="font-semibold text-gray-900">{{ calamity.name }}</h2><p class="text-xs font-mono text-gray-500 mt-1">{{ calamity.event_code }}</p><p class="text-sm text-gray-600 mt-2 capitalize">{{ calamity.type }} | Started {{ formatDate(calamity.started_at) }}</p></div>
                            <StatusBadge :status="calamity.status" />
                        </div>
                        <p v-if="calamity.description" class="text-sm text-gray-700 mt-3">{{ calamity.description }}</p>
                        <div class="flex flex-wrap gap-4 text-xs text-gray-500 mt-3"><span>{{ calamity.affected_households }} households affected</span><span>{{ calamity.affected_residents }} residents affected</span><span v-if="calamity.puroks?.length">{{ calamity.puroks.map((purok) => purok.name).join(', ') }}</span></div>
                    </article>
                </div>
                <p v-else class="p-12 text-center text-sm text-gray-400">No calamities found.</p>
                <Pagination :links="calamities.links" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
