<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import WeatherCard from '@/Components/WeatherCard.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

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

const requesterName = (request) => {
    const profile = request.requester?.member_profile || request.resident;
    if (profile) return `${profile.first_name} ${profile.last_name}`.trim();
    return request.requester?.name || '-';
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Moderator Dashboard" />

        <div class="space-y-6">
            <!-- Welcome banner -->
            <div class="bg-gradient-to-r from-primary to-primary/80 rounded-xl p-6 text-primary-foreground">
                <h1 class="text-2xl font-bold">Operations Dashboard</h1>
                <p class="text-primary-foreground/80 mt-1">Your assigned tasks and urgent items requiring attention</p>
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
                <Card class="overflow-hidden">
                    <CardHeader class="border-b flex flex-row items-center justify-between space-y-0">
                        <CardTitle class="text-base">Urgent Complaints</CardTitle>
                        <Link href="/complaints" class="text-sm text-primary hover:text-primary/80 font-medium">View all</Link>
                    </CardHeader>
                    <CardContent v-if="urgentComplaints.length" class="divide-y divide-border p-0">
                        <div v-for="complaint in urgentComplaints" :key="complaint.id" class="px-6 py-4 hover:bg-muted/50 transition-colors">
                            <div class="flex items-start justify-between">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-foreground truncate">{{ complaint.subject }}</p>
                                    <p class="text-xs text-muted-foreground mt-0.5">
                                        {{ complaint.complaint_code }} &middot;
                                        {{ complaint.complainant?.member_profile?.first_name }} {{ complaint.complainant?.member_profile?.last_name }}
                                    </p>
                                </div>
                                <StatusBadge :status="complaint.priority" />
                            </div>
                            <div class="mt-2 flex items-center justify-between">
                                <span class="text-xs text-muted-foreground">{{ complaint.category?.name }}</span>
                                <StatusBadge :status="complaint.status" />
                            </div>
                        </div>
                    </CardContent>
                    <p v-else class="text-sm text-muted-foreground text-center py-8">No urgent complaints</p>
                </Card>

                <!-- Recent requests -->
                <Card class="overflow-hidden">
                    <CardHeader class="border-b flex flex-row items-center justify-between space-y-0">
                        <CardTitle class="text-base">Pending Requests</CardTitle>
                        <Link href="/requests" class="text-sm text-primary hover:text-primary/80 font-medium">View all</Link>
                    </CardHeader>
                    <CardContent v-if="recentRequests.length" class="divide-y divide-border p-0">
                        <div v-for="request in recentRequests" :key="request.id" class="px-6 py-4 hover:bg-muted/50 transition-colors">
                            <div class="flex items-start justify-between">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-foreground truncate">{{ request.request_type?.name }}</p>
                                    <p class="text-xs text-muted-foreground mt-0.5">
                                        {{ request.tracking_number }} &middot;
                                        {{ requesterName(request) }}
                                    </p>
                                </div>
                                <span class="text-xs text-muted-foreground whitespace-nowrap ml-2">{{ formatDate(request.submitted_at) }}</span>
                            </div>
                            <div class="mt-2">
                                <StatusBadge :status="request.status" />
                            </div>
                        </div>
                    </CardContent>
                    <p v-else class="text-sm text-muted-foreground text-center py-8">No pending requests</p>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>