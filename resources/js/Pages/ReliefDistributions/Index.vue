<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head } from '@inertiajs/vue3';

defineProps({ events: Array });
const formatDate = (date) => date ? new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : '-';
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Relief Distribution" />
        <div class="space-y-4">
            <div><h1 class="text-xl font-bold text-gray-900">Relief Distribution</h1><p class="text-sm text-gray-500 mt-1">View scheduled and completed relief distributions.</p></div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div v-if="events.length" class="divide-y divide-gray-100"><article v-for="event in events" :key="event.id" class="p-5"><div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3"><div><h2 class="font-semibold text-gray-900">{{ event.name }}</h2><p class="text-xs font-mono text-gray-500 mt-1">{{ event.event_code }}</p><p class="text-sm text-gray-600 mt-2">{{ event.location }} | {{ formatDate(event.distribution_date) }}</p><p v-if="event.calamity" class="text-xs text-gray-500 mt-1">Related calamity: {{ event.calamity.name }}</p></div><div class="sm:text-right"><StatusBadge :status="event.status" /><p class="text-xs text-gray-500 mt-2">{{ event.recipients_count }} recipients</p></div></div><div v-if="event.items.length" class="flex flex-wrap gap-2 mt-4"><span v-for="item in event.items" :key="item.id" class="rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-700">{{ item.inventory_item?.name }}: {{ item.quantity }}</span></div><p v-if="event.notes" class="text-sm text-gray-700 mt-3">{{ event.notes }}</p></article></div>
                <p v-else class="p-12 text-center text-sm text-gray-400">No relief distributions found.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
