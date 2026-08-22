<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head } from '@inertiajs/vue3';

defineProps({ events: Array });
const formatDate = (date) => date ? new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : '-';
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Evacuations" />
        <div class="space-y-4">
            <div><h1 class="text-xl font-bold text-gray-900">Evacuations</h1><p class="text-sm text-gray-500 mt-1">Monitor active and completed evacuation events.</p></div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div v-if="events.length" class="divide-y divide-gray-100">
                    <article v-for="event in events" :key="event.id" class="p-5 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                        <div><h2 class="font-semibold text-gray-900">{{ event.evacuation_center?.name }}</h2><p class="text-xs font-mono text-gray-500 mt-1">{{ event.event_code }}</p><p class="text-sm text-gray-600 mt-2">{{ event.calamity?.name || 'Emergency evacuation' }}</p><p class="text-xs text-gray-500 mt-1">Started {{ formatDate(event.started_at) }} | {{ event.evacuation_center?.location }}</p><p v-if="event.notes" class="text-sm text-gray-700 mt-3">{{ event.notes }}</p></div>
                        <div class="sm:text-right"><StatusBadge :status="event.status" /><p class="text-xs text-gray-500 mt-2">{{ event.current_registrations_count }} residents currently registered</p></div>
                    </article>
                </div>
                <p v-else class="p-12 text-center text-sm text-gray-400">No evacuation events found.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
