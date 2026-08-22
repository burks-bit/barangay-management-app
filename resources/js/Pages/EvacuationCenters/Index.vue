<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head } from '@inertiajs/vue3';

defineProps({ centers: Array });
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Evacuation Centers" />
        <div class="space-y-4">
            <div><h1 class="text-xl font-bold text-gray-900">Evacuation Centers</h1><p class="text-sm text-gray-500 mt-1">View available evacuation facilities and capacity.</p></div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <article v-for="center in centers" :key="center.id" class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-start justify-between gap-3"><div><h2 class="font-semibold text-gray-900">{{ center.name }}</h2><p class="text-sm text-gray-500 mt-1">{{ center.location }}</p></div><StatusBadge :status="center.status" /></div>
                    <dl class="grid grid-cols-2 gap-3 mt-5 text-sm"><div><dt class="text-gray-500">Capacity</dt><dd class="font-medium text-gray-900">{{ center.capacity }}</dd></div><div><dt class="text-gray-500">Current occupancy</dt><dd class="font-medium text-gray-900">{{ center.current_occupancy }}</dd></div><div><dt class="text-gray-500">Available spaces</dt><dd class="font-medium text-green-700">{{ Math.max(0, center.capacity - center.current_occupancy) }}</dd></div><div><dt class="text-gray-500">Active evacuations</dt><dd class="font-medium text-gray-900">{{ center.active_events_count }}</dd></div></dl>
                    <p v-if="center.contact_person || center.contact_number" class="text-xs text-gray-500 mt-4">Contact: {{ center.contact_person || '-' }} {{ center.contact_number ? `(${center.contact_number})` : '' }}</p>
                </article>
            </div>
            <p v-if="!centers.length" class="bg-white rounded-xl border border-gray-100 p-12 text-center text-sm text-gray-400">No evacuation centers found.</p>
        </div>
    </AuthenticatedLayout>
</template>
