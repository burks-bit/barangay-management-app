<script setup>
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';

const props = defineProps({
    status: {
        type: String,
        required: true,
    },
});

const statusConfig = {
    // Complaints
    submitted: { label: 'Submitted', class: 'bg-gray-100 text-gray-800 border-gray-200 hover:bg-gray-200' },
    under_review: { label: 'Under Review', class: 'bg-yellow-100 text-yellow-800 border-yellow-200 hover:bg-yellow-200' },
    verified: { label: 'Verified', class: 'bg-blue-100 text-blue-800 border-blue-200 hover:bg-blue-200' },
    assigned: { label: 'Assigned', class: 'bg-indigo-100 text-indigo-800 border-indigo-200 hover:bg-indigo-200' },
    under_investigation: { label: 'Under Investigation', class: 'bg-orange-100 text-orange-800 border-orange-200 hover:bg-orange-200' },
    for_mediation: { label: 'For Mediation', class: 'bg-amber-100 text-amber-800 border-amber-200 hover:bg-amber-200' },
    action_taken: { label: 'Action Taken', class: 'bg-teal-100 text-teal-800 border-teal-200 hover:bg-teal-200' },
    resolved: { label: 'Resolved', class: 'bg-green-100 text-green-800 border-green-200 hover:bg-green-200' },
    rejected: { label: 'Rejected', class: 'bg-red-100 text-red-800 border-red-200 hover:bg-red-200' },
    closed: { label: 'Closed', class: 'bg-slate-200 text-slate-700 border-slate-300 hover:bg-slate-300' },

    // Requests
    for_verification: { label: 'For Verification', class: 'bg-yellow-100 text-yellow-800 border-yellow-200 hover:bg-yellow-200' },
    approved: { label: 'Approved', class: 'bg-green-100 text-green-800 border-green-200 hover:bg-green-200' },
    processing: { label: 'Processing', class: 'bg-blue-100 text-blue-800 border-blue-200 hover:bg-blue-200' },
    ready_for_release: { label: 'Ready for Release', class: 'bg-purple-100 text-purple-800 border-purple-200 hover:bg-purple-200' },
    released: { label: 'Released', class: 'bg-emerald-100 text-emerald-800 border-emerald-200 hover:bg-emerald-200' },
    cancelled: { label: 'Cancelled', class: 'bg-gray-100 text-gray-500 border-gray-200 hover:bg-gray-200' },

    // Calamities / Incidents
    reported: { label: 'Reported', class: 'bg-gray-100 text-gray-800 border-gray-200 hover:bg-gray-200' },
    active: { label: 'Active', class: 'bg-red-100 text-red-800 border-red-200 hover:bg-red-200' },
    under_response: { label: 'Under Response', class: 'bg-orange-100 text-orange-800 border-orange-200 hover:bg-orange-200' },
    contained: { label: 'Contained', class: 'bg-yellow-100 text-yellow-800 border-yellow-200 hover:bg-yellow-200' },

    // Incident Blotters
    recorded: { label: 'Recorded', class: 'bg-gray-100 text-gray-800 border-gray-200 hover:bg-gray-200' },
    settled: { label: 'Settled', class: 'bg-green-100 text-green-800 border-green-200 hover:bg-green-200' },
    referred: { label: 'Referred', class: 'bg-purple-100 text-purple-800 border-purple-200 hover:bg-purple-200' },

    // Evacuation
    available: { label: 'Available', class: 'bg-green-100 text-green-800 border-green-200 hover:bg-green-200' },
    occupied: { label: 'Occupied', class: 'bg-yellow-100 text-yellow-800 border-yellow-200 hover:bg-yellow-200' },
    full: { label: 'Full', class: 'bg-red-100 text-red-800 border-red-200 hover:bg-red-200' },
    completed: { label: 'Completed', class: 'bg-green-100 text-green-800 border-green-200 hover:bg-green-200' },

    // Relief distribution
    planned: { label: 'Planned', class: 'bg-blue-100 text-blue-800 border-blue-200 hover:bg-blue-200' },
    ongoing: { label: 'Ongoing', class: 'bg-orange-100 text-orange-800 border-orange-200 hover:bg-orange-200' },

    // Assistance
    assessment: { label: 'Assessment', class: 'bg-yellow-100 text-yellow-800 border-yellow-200 hover:bg-yellow-200' },
    for_release: { label: 'For Release', class: 'bg-purple-100 text-purple-800 border-purple-200 hover:bg-purple-200' },

    // Verification
    pending: { label: 'Pending', class: 'bg-yellow-100 text-yellow-800 border-yellow-200 hover:bg-yellow-200' },
    inactive: { label: 'Inactive', class: 'bg-gray-100 text-gray-500 border-gray-200 hover:bg-gray-200' },

    // Announcements
    draft: { label: 'Draft', class: 'bg-gray-100 text-gray-600 border-gray-200 hover:bg-gray-200' },
    published: { label: 'Published', class: 'bg-green-100 text-green-800 border-green-200 hover:bg-green-200' },
    archived: { label: 'Archived', class: 'bg-slate-200 text-slate-700 border-slate-300 hover:bg-slate-300' },

    // Priority
    low: { label: 'Low', class: 'bg-green-100 text-green-800 border-green-200 hover:bg-green-200' },
    medium: { label: 'Medium', class: 'bg-yellow-100 text-yellow-800 border-yellow-200 hover:bg-yellow-200' },
    high: { label: 'High', class: 'bg-orange-100 text-orange-800 border-orange-200 hover:bg-orange-200' },
    urgent: { label: 'Urgent', class: 'bg-red-100 text-red-800 border-red-200 hover:bg-red-200' },

    // Severity
    moderate: { label: 'Moderate', class: 'bg-yellow-100 text-yellow-800 border-yellow-200 hover:bg-yellow-200' },
};

const config = computed(() => {
    return (
        statusConfig[props.status] || {
            label: props.status.replace(/_/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase()),
            class: 'bg-gray-100 text-gray-800 border-gray-200 hover:bg-gray-200',
        }
    );
});
</script>

<template>
    <Badge :class="config.class" class="whitespace-nowrap">
        {{ config.label }}
    </Badge>
</template>
