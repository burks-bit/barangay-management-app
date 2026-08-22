<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
        required: true,
    },
});

const statusConfig = {
    // Complaints
    submitted: { label: 'Submitted', class: 'bg-gray-100 text-gray-800' },
    under_review: { label: 'Under Review', class: 'bg-yellow-100 text-yellow-800' },
    verified: { label: 'Verified', class: 'bg-blue-100 text-blue-800' },
    assigned: { label: 'Assigned', class: 'bg-indigo-100 text-indigo-800' },
    under_investigation: { label: 'Under Investigation', class: 'bg-orange-100 text-orange-800' },
    for_mediation: { label: 'For Mediation', class: 'bg-amber-100 text-amber-800' },
    action_taken: { label: 'Action Taken', class: 'bg-teal-100 text-teal-800' },
    resolved: { label: 'Resolved', class: 'bg-green-100 text-green-800' },
    rejected: { label: 'Rejected', class: 'bg-red-100 text-red-800' },
    closed: { label: 'Closed', class: 'bg-slate-200 text-slate-700' },

    // Requests
    for_verification: { label: 'For Verification', class: 'bg-yellow-100 text-yellow-800' },
    approved: { label: 'Approved', class: 'bg-green-100 text-green-800' },
    processing: { label: 'Processing', class: 'bg-blue-100 text-blue-800' },
    ready_for_release: { label: 'Ready for Release', class: 'bg-purple-100 text-purple-800' },
    released: { label: 'Released', class: 'bg-emerald-100 text-emerald-800' },
    cancelled: { label: 'Cancelled', class: 'bg-gray-100 text-gray-500' },

    // Calamities / Incidents
    reported: { label: 'Reported', class: 'bg-gray-100 text-gray-800' },
    active: { label: 'Active', class: 'bg-red-100 text-red-800' },
    under_response: { label: 'Under Response', class: 'bg-orange-100 text-orange-800' },
    contained: { label: 'Contained', class: 'bg-yellow-100 text-yellow-800' },

    // Evacuation
    available: { label: 'Available', class: 'bg-green-100 text-green-800' },
    occupied: { label: 'Occupied', class: 'bg-yellow-100 text-yellow-800' },
    full: { label: 'Full', class: 'bg-red-100 text-red-800' },
    completed: { label: 'Completed', class: 'bg-green-100 text-green-800' },

    // Relief distribution
    planned: { label: 'Planned', class: 'bg-blue-100 text-blue-800' },
    ongoing: { label: 'Ongoing', class: 'bg-orange-100 text-orange-800' },

    // Assistance
    assessment: { label: 'Assessment', class: 'bg-yellow-100 text-yellow-800' },
    for_release: { label: 'For Release', class: 'bg-purple-100 text-purple-800' },

    // Verification
    pending: { label: 'Pending', class: 'bg-yellow-100 text-yellow-800' },
    inactive: { label: 'Inactive', class: 'bg-gray-100 text-gray-500' },

    // Announcements
    draft: { label: 'Draft', class: 'bg-gray-100 text-gray-600' },
    published: { label: 'Published', class: 'bg-green-100 text-green-800' },
    archived: { label: 'Archived', class: 'bg-slate-200 text-slate-700' },

    // Priority
    low: { label: 'Low', class: 'bg-green-100 text-green-800' },
    medium: { label: 'Medium', class: 'bg-yellow-100 text-yellow-800' },
    high: { label: 'High', class: 'bg-orange-100 text-orange-800' },
    urgent: { label: 'Urgent', class: 'bg-red-100 text-red-800' },

    // Severity
    moderate: { label: 'Moderate', class: 'bg-yellow-100 text-yellow-800' },
};

const config = computed(() => {
    return (
        statusConfig[props.status] || {
            label: props.status.replace(/_/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase()),
            class: 'bg-gray-100 text-gray-800',
        }
    );
});
</script>

<template>
    <span
        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium whitespace-nowrap"
        :class="config.class"
    >
        {{ config.label }}
    </span>
</template>