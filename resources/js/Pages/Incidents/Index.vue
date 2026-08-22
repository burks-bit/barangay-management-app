<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    incidents: Array,
});

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Active Incidents" />

        <div class="max-w-5xl mx-auto space-y-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Active Incidents</h1>
                <p class="text-sm text-gray-500 mt-1">Current incidents reported in the barangay.</p>
            </div>

            <div v-if="incidents.length" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="divide-y divide-gray-100">
                    <article v-for="incident in incidents" :key="incident.id" class="p-5">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                            <div>
                                <h2 class="text-base font-semibold text-gray-900">{{ incident.incident_code }}</h2>
                                <p class="text-sm text-gray-700 mt-1 capitalize">{{ incident.type }} at {{ incident.location }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ incident.purok?.name || 'Barangay-wide' }} | {{ formatDate(incident.incident_datetime) }}</p>
                            </div>
                            <StatusBadge :status="incident.severity || incident.status" />
                        </div>
                        <p class="text-sm text-gray-700 mt-3">{{ incident.description }}</p>
                    </article>
                </div>
            </div>
            <p v-else class="bg-white rounded-xl border border-gray-100 p-8 text-center text-sm text-gray-400">No active incidents</p>
        </div>
    </AuthenticatedLayout>
</template>
