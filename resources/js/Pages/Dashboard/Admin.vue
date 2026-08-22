<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    stats: Object,
    charts: Object,
});

</script>

<template>
    <AuthenticatedLayout>
        <Head title="Admin Dashboard" />

        <div class="space-y-6">
            <!-- Welcome banner -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl p-6 text-white">
                <h1 class="text-2xl font-bold">Barangay Overview</h1>
                <p class="text-blue-100 mt-1">System-wide statistics and operations summary</p>
            </div>

            <!-- Stats grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <StatCard title="Total Residents" :value="stats.total_residents" icon="users" color="blue" href="/residents" />
                <StatCard title="Total Households" :value="stats.total_households" icon="home" color="indigo" href="/households" />
                <StatCard title="Pending Verifications" :value="stats.pending_verifications" icon="check" color="yellow" href="/residents?verification_status=pending" />
                <StatCard title="Open Complaints" :value="stats.open_complaints" icon="clipboard" color="orange" href="/complaints" />
                <StatCard title="Pending Requests" :value="stats.pending_requests" icon="clipboard" color="purple" href="/requests" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <StatCard title="Active Calamities" :value="stats.active_calamities" icon="alert" color="red" href="/calamities" />
                <StatCard title="Active Incidents" :value="stats.active_incidents" icon="alert" color="orange" href="/incidents" />
                <StatCard title="Current Evacuees" :value="stats.current_evacuees" icon="home" color="teal" href="/evacuations" />
                <StatCard title="Low Stock Items" :value="stats.low_stock_items" icon="box" color="red" href="/relief-inventory" />
                <StatCard title="Pending Assistance" :value="stats.pending_assistance" icon="check" color="green" href="/assistance" />
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Complaints by category -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Complaints by Category</h3>
                    <div v-if="charts.complaintsByCategory.length" class="space-y-3">
                        <div v-for="item in charts.complaintsByCategory" :key="item.label">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600">{{ item.label }}</span>
                                <span class="font-medium text-gray-900">{{ item.value }}</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div
                                    class="bg-orange-500 h-2 rounded-full"
                                    :style="{ width: (item.value / Math.max(...charts.complaintsByCategory.map(c => c.value)) * 100) + '%' }"
                                ></div>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-sm text-gray-400 text-center py-8">No complaints data</p>
                </div>

                <!-- Requests by type -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Requests by Type</h3>
                    <div v-if="charts.requestsByType.length" class="space-y-3">
                        <div v-for="item in charts.requestsByType" :key="item.label">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600">{{ item.label }}</span>
                                <span class="font-medium text-gray-900">{{ item.value }}</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div
                                    class="bg-purple-500 h-2 rounded-full"
                                    :style="{ width: (item.value / Math.max(...charts.requestsByType.map(c => c.value)) * 100) + '%' }"
                                ></div>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-sm text-gray-400 text-center py-8">No requests data</p>
                </div>

                <!-- Residents by purok -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Residents by Purok</h3>
                    <div v-if="charts.residentsByPurok.length" class="space-y-3">
                        <div v-for="item in charts.residentsByPurok" :key="item.label">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600">{{ item.label }}</span>
                                <span class="font-medium text-gray-900">{{ item.value }}</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div
                                    class="bg-blue-500 h-2 rounded-full"
                                    :style="{ width: (item.value / Math.max(...charts.residentsByPurok.map(c => c.value)) * 100) + '%' }"
                                ></div>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-sm text-gray-400 text-center py-8">No residents data</p>
                </div>

                <!-- Disaster incidents by type -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Disaster Incidents by Type</h3>
                    <div v-if="charts.incidentsByType.length" class="space-y-3">
                        <div v-for="item in charts.incidentsByType" :key="item.label">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="capitalize text-gray-600">{{ item.label }}</span>
                                <span class="font-medium text-gray-900">{{ item.value }}</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div
                                    class="bg-red-500 h-2 rounded-full"
                                    :style="{ width: (item.value / Math.max(...charts.incidentsByType.map(c => c.value)) * 100) + '%' }"
                                ></div>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-sm text-gray-400 text-center py-8">No incidents data</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>