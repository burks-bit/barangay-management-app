<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import WeatherCard from '@/Components/WeatherCard.vue';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    myRequests: Array,
    myComplaints: Array,
    myAssistance: Array,
    announcements: Array,
    notifications: Array,
    unreadCount: Number,
});


const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);

const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Good morning';
    if (hour < 18) return 'Good afternoon';
    return 'Good evening';
});

const firstName = computed(() => {
    return user.value?.member_profile?.first_name || user.value?.name?.split(' ')[0] || 'Resident';
});

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const priorityStyles = {
    emergency: 'bg-red-50 border-red-200',
    important: 'bg-yellow-50 border-yellow-200',
    normal: 'bg-blue-50 border-blue-100',
};

const isAlarmingAnnouncement = (announcement) => {
    const text = `${announcement.title || ''} ${announcement.content || ''}`.toLowerCase();
    return announcement.priority === 'emergency' || /flood\s*warning|flash\s*flood|flood/.test(text);
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="space-y-6">
            <!-- Greeting -->
            <div class="bg-gradient-to-r from-green-600 to-teal-600 rounded-xl p-6 text-white">
                <h1 class="text-2xl font-bold">{{ greeting }}, {{ firstName }}!</h1>
                <p class="text-green-100 mt-1">Here's an overview of your requests and community updates.</p>
            </div>

            <WeatherCard />

            <!-- Emergency announcements -->
            <div v-if="announcements.filter(a => a.priority === 'emergency').length" class="space-y-3">
                <div
                    v-for="announcement in announcements.filter(a => a.priority === 'emergency')"
                    :key="announcement.id"
                    class="rounded-xl border-2 border-red-300 bg-red-50 p-5"
                    :class="{ 'alarm-announcement': isAlarmingAnnouncement(announcement) }"
                >
                    <div class="flex items-start">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-red-500 flex-shrink-0" :class="{ 'alarm-icon': isAlarmingAnnouncement(announcement) }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div class="ml-3">
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-bold text-red-800">{{ announcement.title }}</h3>
                                <span v-if="isAlarmingAnnouncement(announcement)" class="alarm-label">ALERT</span>
                            </div>
                            <p class="text-sm text-red-700 mt-1">{{ announcement.content }}</p>
                            <p class="text-xs text-red-500 mt-2">{{ formatDate(announcement.published_at) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick actions -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <a href="/my-requests/create" class="flex items-center p-4 bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-purple-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    </div>
                    <span class="ml-3 text-sm font-medium text-gray-900">New Request</span>
                </a>
                <a href="/my-complaints/create" class="flex items-center p-4 bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-orange-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M5.07 19H19a2 2 0 001.74-3L13.74 4a2 2 0 00-3.48 0L3.33 16a2 2 0 001.74 3z" /></svg>
                    </div>
                    <span class="ml-3 text-sm font-medium text-gray-900">File Complaint</span>
                </a>
                <a href="/my-assistance/create" class="flex items-center p-4 bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-green-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
                    </div>
                    <span class="ml-3 text-sm font-medium text-gray-900">Request Assistance</span>
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- My Requests -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-base font-semibold text-gray-900">My Requests</h3>
                        <a href="/my-requests" class="text-sm text-blue-600 hover:text-blue-800 font-medium">View all</a>
                    </div>
                    <div v-if="myRequests.length" class="divide-y divide-gray-100">
                        <div v-for="request in myRequests" :key="request.id" class="px-6 py-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ request.request_type?.name }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ request.tracking_number }}</p>
                                </div>
                                <StatusBadge :status="request.status" />
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-sm text-gray-400 text-center py-8">No requests yet</p>
                </div>

                <!-- My Complaints -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-base font-semibold text-gray-900">My Complaints</h3>
                        <a href="/my-complaints" class="text-sm text-blue-600 hover:text-blue-800 font-medium">View all</a>
                    </div>
                    <div v-if="myComplaints.length" class="divide-y divide-gray-100">
                        <div v-for="complaint in myComplaints" :key="complaint.id" class="px-6 py-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ complaint.subject }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ complaint.complaint_code }}</p>
                                </div>
                                <StatusBadge :status="complaint.status" />
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-sm text-gray-400 text-center py-8">No complaints yet</p>
                </div>

                <!-- My Assistance -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-base font-semibold text-gray-900">My Assistance Requests</h3>
                        <a href="/my-assistance" class="text-sm text-blue-600 hover:text-blue-800 font-medium">View all</a>
                    </div>
                    <div v-if="myAssistance.length" class="divide-y divide-gray-100">
                        <div v-for="assistance in myAssistance" :key="assistance.id" class="px-6 py-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ assistance.assistance_type?.name }} Assistance</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ assistance.assistance_code }}</p>
                                </div>
                                <StatusBadge :status="assistance.status" />
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-sm text-gray-400 text-center py-8">No assistance requests yet</p>
                </div>

                <!-- Announcements -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="text-base font-semibold text-gray-900">Announcements</h3>
                    </div>
                    <div v-if="announcements.length" class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
                        <div v-for="announcement in announcements" :key="announcement.id" class="px-6 py-4">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900">{{ announcement.title }}</p>
                                    <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ announcement.content }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ formatDate(announcement.published_at) }}</p>
                                </div>
                                <StatusBadge :status="announcement.priority === 'emergency' ? 'urgent' : announcement.priority === 'important' ? 'high' : 'low'" />
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-sm text-gray-400 text-center py-8">No announcements</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.alarm-announcement {
    animation: alarm-border 1.1s ease-in-out infinite;
}

.alarm-icon {
    animation: alarm-icon 0.8s ease-in-out infinite alternate;
}

.alarm-label {
    border-radius: 9999px;
    background: #dc2626;
    color: white;
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    padding: 0.2rem 0.45rem;
}

@keyframes alarm-border {
    0%, 100% { border-color: #fca5a5; box-shadow: 0 0 0 0 rgb(239 68 68 / 0.2); }
    50% { border-color: #dc2626; box-shadow: 0 0 0 5px rgb(239 68 68 / 0.12); }
}

@keyframes alarm-icon {
    from { transform: rotate(-8deg); }
    to { transform: rotate(8deg); }
}

@media (prefers-reduced-motion: reduce) {
    .alarm-announcement,
    .alarm-icon {
        animation: none;
    }
}
</style>