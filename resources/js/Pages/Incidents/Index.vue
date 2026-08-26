<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { Card, CardContent } from '@/components/ui/card';
import { Plus, Search } from 'lucide-vue-next';

const props = defineProps({
    incidents: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const typeFilter = ref(props.filters.type || '');
const severityFilter = ref(props.filters.severity || '');

let debounceTimer = null;
watch([search, statusFilter, typeFilter, severityFilter], () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get('/incidents', {
            search: search.value,
            status: statusFilter.value,
            type: typeFilter.value,
            severity: severityFilter.value,
        }, {
            preserveState: true,
            replace: true,
        });
    }, 300);
});

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Active Incidents" />

        <div class="max-w-5xl mx-auto space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-foreground">Active Incidents</h1>
                    <p class="text-sm text-muted-foreground mt-1">Current incidents reported in the barangay.</p>
                </div>
                <Button size="sm" as-child>
                    <Link href="/incidents/blotters/create">
                        <Plus class="h-4 w-4" /> Record Blotter Entry
                    </Link>
                </Button>
            </div>

            <!-- Filters -->
            <Card>
                <CardContent class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div class="relative">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                            <Input v-model="search" type="text" placeholder="Search incidents..." class="pl-10" />
                        </div>
                        <Select v-model="statusFilter">
                            <option value="">All Statuses</option>
                            <option value="reported">Reported</option>
                            <option value="verified">Verified</option>
                            <option value="under_response">Under Response</option>
                            <option value="contained">Contained</option>
                        </Select>
                        <Select v-model="typeFilter">
                            <option value="">All Types</option>
                            <option value="flood">Flood</option>
                            <option value="fire">Fire</option>
                            <option value="earthquake">Earthquake</option>
                            <option value="landslide">Landslide</option>
                            <option value="storm_surge">Storm Surge</option>
                            <option value="typhoon">Typhoon</option>
                            <option value="accident">Accident</option>
                            <option value="crime">Crime</option>
                            <option value="other">Other</option>
                        </Select>
                        <Select v-model="severityFilter">
                            <option value="">All Severities</option>
                            <option value="low">Low</option>
                            <option value="moderate">Moderate</option>
                            <option value="high">High</option>
                            <option value="severe">Severe</option>
                            <option value="critical">Critical</option>
                        </Select>
                    </div>
                </CardContent>
            </Card>

            <!-- List -->
            <Card class="overflow-hidden">
                <CardContent v-if="incidents.data.length" class="p-0">
                    <div class="divide-y divide-border">
                        <article v-for="incident in incidents.data" :key="incident.id" class="p-5">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                                <div>
                                    <h2 class="text-base font-semibold text-foreground">{{ incident.incident_code }}</h2>
                                    <p class="text-sm text-muted-foreground mt-1 capitalize">{{ incident.type }} at {{ incident.location }}</p>
                                    <p class="text-xs text-muted-foreground mt-1">{{ incident.purok?.name || 'Barangay-wide' }} | {{ formatDate(incident.incident_datetime) }}</p>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <StatusBadge :status="incident.severity || incident.status" />
                                    <Link :href="`/incidents/${incident.id}`" class="text-xs text-primary hover:text-primary/80 font-medium">View</Link>
                                </div>
                            </div>
                            <p class="text-sm text-muted-foreground mt-3">{{ incident.description }}</p>
                        </article>
                    </div>
                    <Pagination :links="incidents.links" />
                </CardContent>
                <p v-else class="text-sm text-muted-foreground text-center py-12">No active incidents</p>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>