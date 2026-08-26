<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import WeatherCard from '@/Components/WeatherCard.vue';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

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
            <div class="bg-gradient-to-r from-primary to-primary/80 rounded-xl p-6 text-primary-foreground">
                <h1 class="text-2xl font-bold">Barangay Overview</h1>
                <p class="text-primary-foreground/80 mt-1">System-wide statistics and operations summary</p>
            </div>

            <WeatherCard />

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
                <Card>
                    <CardHeader>
                        <CardTitle class="text-base">Complaints by Category</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="charts.complaintsByCategory.length" class="space-y-3">
                            <div v-for="item in charts.complaintsByCategory" :key="item.label">
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-muted-foreground">{{ item.label }}</span>
                                    <span class="font-medium text-foreground">{{ item.value }}</span>
                                </div>
                                <div class="w-full bg-muted rounded-full h-2">
                                    <div
                                        class="bg-orange-500 h-2 rounded-full"
                                        :style="{ width: (item.value / Math.max(...charts.complaintsByCategory.map(c => c.value)) * 100) + '%' }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-sm text-muted-foreground text-center py-8">No complaints data</p>
                    </CardContent>
                </Card>

                <!-- Requests by type -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-base">Requests by Type</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="charts.requestsByType.length" class="space-y-3">
                            <div v-for="item in charts.requestsByType" :key="item.label">
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-muted-foreground">{{ item.label }}</span>
                                    <span class="font-medium text-foreground">{{ item.value }}</span>
                                </div>
                                <div class="w-full bg-muted rounded-full h-2">
                                    <div
                                        class="bg-purple-500 h-2 rounded-full"
                                        :style="{ width: (item.value / Math.max(...charts.requestsByType.map(c => c.value)) * 100) + '%' }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-sm text-muted-foreground text-center py-8">No requests data</p>
                    </CardContent>
                </Card>

                <!-- Residents by purok -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-base">Residents by Purok</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="charts.residentsByPurok.length" class="space-y-3">
                            <div v-for="item in charts.residentsByPurok" :key="item.label">
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-muted-foreground">{{ item.label }}</span>
                                    <span class="font-medium text-foreground">{{ item.value }}</span>
                                </div>
                                <div class="w-full bg-muted rounded-full h-2">
                                    <div
                                        class="bg-blue-500 h-2 rounded-full"
                                        :style="{ width: (item.value / Math.max(...charts.residentsByPurok.map(c => c.value)) * 100) + '%' }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-sm text-muted-foreground text-center py-8">No residents data</p>
                    </CardContent>
                </Card>

                <!-- Disaster incidents by type -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-base">Disaster Incidents by Type</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="charts.incidentsByType.length" class="space-y-3">
                            <div v-for="item in charts.incidentsByType" :key="item.label">
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="capitalize text-muted-foreground">{{ item.label }}</span>
                                    <span class="font-medium text-foreground">{{ item.value }}</span>
                                </div>
                                <div class="w-full bg-muted rounded-full h-2">
                                    <div
                                        class="bg-red-500 h-2 rounded-full"
                                        :style="{ width: (item.value / Math.max(...charts.incidentsByType.map(c => c.value)) * 100) + '%' }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-sm text-muted-foreground text-center py-8">No incidents data</p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>