<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import WeatherCard from '@/Components/WeatherCard.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    stats: Object,
    urgentComplaints: Array,
    recentRequests: Array,
});

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Moderator Dashboard" />

        <div class="space-y-6">
            <!-- Welcome banner -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl p-6 text-white">
                <h1 class="text-2xl font-bold">Operations Dashboard</h1>
                <p class="text-blue-100 mt-1">Your assigned tasks and urgent items requiring attention</p>
            </div>

            <WeatherCard />

            <!-- Stats grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <StatCard title="Assigned Complaints" :value="stats.assigned_complaints" icon="clipboard" color="orange" href="/complaints" />
                <StatCard title="Pending Requests" :value="stats.pending_requests" icon="clipboard" color="purple" href="/requests" />
                <StatCard title="Unverified Residents" :value="stats.unverified_residents" icon="users" color="yellow" href="/residents?verification_status=pending" />
                <StatCard title="Active Incidents" :value="stats.active_incidents" icon="alert" color="red" href="/incidents" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <StatCard title="Active Calamities" :value="stats.active_calamities" icon="alert" color="red" href="/calamities" />
                <StatCard title="Current Evacuees" :value="stats.current_evacuees" icon="home" color="teal" href="/evacuations" />
                <StatCard title="Pending Assistance" :value="stats.pending_assistance" icon="check" color="green" href="/assistance" />
                <StatCard title="Distributions (Month)" :value="stats.recent_distributions" icon="box" color="indigo" href="/relief-distributions" />
            </div>

            <!-- Urgent items -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Urgent complaints -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-base font-semibold text-gray-900">Urgent Complaints</h3>
                        <Link href="/complaints" class="text-sm text-blue-600 hover:text-blue-800 font-medium">View all</Link>
                    </div>
                    <div v-if="urgentComplaints.length" class="divide-y divide-gray-100">
                        <div v-for="complaint in urgentComplaints" :key="complaint.id" class="px-6 py-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-start justify-between">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ complaint.subject }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ complaint.complaint_code }} &middot;
                                        {{ complaint.complainant?.member_profile?.first_name }} {{ complaint.complainant?.member_profile?.last_name }}
                                    </p>
                                </div>
                                <StatusBadge :status="complaint.priority" />
                            </div>
                            <div class="mt-2 flex items-center justify-between">
                                <span class="text-xs text-gray-500">{{ complaint.category?.name }}</span>
                                <StatusBadge :status="complaint.status" />
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-sm text-gray-400 text-center py-8">No urgent complaints</p>
                </div>

                <!-- Recent requests -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-base font-semibold text-gray-900">Pending Requests</h3>
                        <Link href="/requests" class="text-sm text-blue-600 hover:text-blue-800 font-medium">View all</Link>
                    </div>
                    <div v-if="recentRequests.length" class="divide-y divide-gray-100">
                        <div v-for="request in recentRequests" :key="request.id" class="px-6 py-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-start justify-between">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ request.request_type?.name }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ request.tracking_number }} &middot;
                                        {{ request.requester?.member_profile?.first_name }} {{ request.requester?.member_profile?.last_name }}
                                    </p>
                                </div>
                                <span class="text-xs text-gray-400 whitespace-nowrap ml-2">{{ formatDate(request.submitted_at) }}</span>
                            </div>
                            <div class="mt-2">
                                <StatusBadge :status="request.status" />
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-sm text-gray-400 text-center py-8">No pending requests</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>