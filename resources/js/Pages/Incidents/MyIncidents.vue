<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Select } from '@/components/ui/select';
import { Card, CardContent } from '@/components/ui/card';
import { Plus } from 'lucide-vue-next';

const props = defineProps({
    incidents: Object,
    filters: Object,
});

const status = ref(props.filters.status || '');

let debounceTimer = null;
watch(status, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get('/my-incidents', { status: status.value }, { preserveState: true, replace: true });
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
        <Head title="My Incidents" />

        <div class="max-w-5xl mx-auto space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-foreground">My Incidents</h1>
                    <p class="text-sm text-muted-foreground mt-1">Incidents you have reported to the barangay.</p>
                </div>
                <Button as-child>
                    <Link href="/my-incidents/create">
                        <Plus class="h-4 w-4" /> Report Incident
                    </Link>
                </Button>
            </div>

            <!-- Filter -->
            <Card>
                <CardContent class="p-4">
                    <Select v-model="status">
                        <option value="">All Statuses</option>
                        <option value="reported">Reported</option>
                        <option value="verified">Verified</option>
                        <option value="under_response">Under Response</option>
                        <option value="contained">Contained</option>
                        <option value="resolved">Resolved</option>
                        <option value="closed">Closed</option>
                    </Select>
                </CardContent>
            </Card>

            <!-- List -->
            <Card class="overflow-hidden">
                <CardContent v-if="incidents.data.length" class="divide-y divide-border p-0">
                    <div v-for="incident in incidents.data" :key="incident.id" class="p-5 hover:bg-muted/50 transition-colors">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                            <div>
                                <h2 class="text-base font-semibold text-foreground">{{ incident.incident_code }}</h2>
                                <p class="text-sm text-muted-foreground mt-1 capitalize">{{ incident.type }} at {{ incident.location }}</p>
                                <p class="text-xs text-muted-foreground mt-1">{{ incident.purok?.name || 'Barangay-wide' }} | {{ formatDate(incident.incident_datetime) }}</p>
                                <p class="text-sm text-muted-foreground mt-2 line-clamp-2">{{ incident.description }}</p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <StatusBadge :status="incident.severity || incident.status" />
                                <Link :href="`/my-incidents/${incident.id}`" class="text-xs text-primary hover:text-primary/80 font-medium">View Details</Link>
                            </div>
                        </div>
                    </div>
                </CardContent>
                <p v-else class="text-sm text-muted-foreground text-center py-12">
                    No incidents reported yet.
                    <Link href="/my-incidents/create" class="text-primary hover:text-primary/80 font-medium"> Report your first incident</Link>
                </p>
                <Pagination :links="incidents.links" />
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
