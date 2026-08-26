<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent } from '@/components/ui/card';

const props = defineProps({
    incident: Object,
    backUrl: { type: String, default: '/my-incidents' },
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
        <Head :title="`Incident ${incident.incident_code}`" />

        <div class="max-w-4xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <Link :href="props.backUrl" class="text-sm text-muted-foreground hover:text-foreground">&larr; Back to My Incidents</Link>
                <StatusBadge :status="incident.status" />
            </div>

            <!-- Header card -->
            <Card>
                <CardContent class="p-6">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                        <div>
                            <h1 class="text-lg font-bold text-foreground">{{ incident.incident_code }}</h1>
                            <p class="text-base font-semibold text-foreground mt-1 capitalize">{{ incident.type }}</p>
                        </div>
                        <StatusBadge :status="incident.severity" />
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                        <div>
                            <dt class="text-xs text-muted-foreground uppercase tracking-wide">Location</dt>
                            <dd class="font-medium text-foreground mt-0.5">{{ incident.location }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-muted-foreground uppercase tracking-wide">Purok</dt>
                            <dd class="font-medium text-foreground mt-0.5">{{ incident.purok?.name || 'Barangay-wide' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-muted-foreground uppercase tracking-wide">Date & Time</dt>
                            <dd class="font-medium text-foreground mt-0.5">{{ formatDate(incident.incident_datetime) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-muted-foreground uppercase tracking-wide">Reported By</dt>
                            <dd class="font-medium text-foreground mt-0.5">{{ incident.reporter?.name || 'You' }}</dd>
                        </div>
                        <div v-if="incident.calamity">
                            <dt class="text-xs text-muted-foreground uppercase tracking-wide">Linked Calamity</dt>
                            <dd class="font-medium text-foreground mt-0.5">{{ incident.calamity.name }}</dd>
                        </div>
                        <div v-if="incident.affected_households > 0">
                            <dt class="text-xs text-muted-foreground uppercase tracking-wide">Affected Households</dt>
                            <dd class="font-medium text-foreground mt-0.5">{{ incident.affected_households }}</dd>
                        </div>
                        <div v-if="incident.affected_residents > 0">
                            <dt class="text-xs text-muted-foreground uppercase tracking-wide">Affected Residents</dt>
                            <dd class="font-medium text-foreground mt-0.5">{{ incident.affected_residents }}</dd>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Description -->
            <Card>
                <CardContent class="p-6">
                    <h2 class="text-sm font-semibold text-foreground uppercase tracking-wide mb-3">Description</h2>
                    <p class="text-sm text-muted-foreground whitespace-pre-line leading-relaxed">{{ incident.description }}</p>
                </CardContent>
            </Card>

            <!-- Actions Taken -->
            <Card v-if="incident.actions_taken">
                <CardContent class="p-6">
                    <h2 class="text-sm font-semibold text-foreground uppercase tracking-wide mb-3">Actions Taken</h2>
                    <p class="text-sm text-muted-foreground whitespace-pre-line leading-relaxed">{{ incident.actions_taken }}</p>
                </CardContent>
            </Card>

            <!-- Notes -->
            <Card v-if="incident.notes">
                <CardContent class="p-6">
                    <h2 class="text-sm font-semibold text-foreground uppercase tracking-wide mb-3">Notes</h2>
                    <p class="text-sm text-muted-foreground whitespace-pre-line leading-relaxed">{{ incident.notes }}</p>
                </CardContent>
            </Card>

            <!-- Status History -->
            <Card>
                <CardContent class="pt-6">
                    <h3 class="text-sm font-semibold text-foreground mb-4">Status History</h3>
                    <ol v-if="incident.status_histories?.length" class="relative border-l ml-3 space-y-6">
                        <li v-for="(history, index) in incident.status_histories" :key="history.id" class="ml-6">
                            <span
                                class="absolute flex items-center justify-center w-6 h-6 rounded-full -left-3 ring-4 ring-card"
                                :class="index === 0 ? 'bg-primary' : 'bg-muted'"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-white" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-medium text-foreground capitalize">{{ (history.to_status || '').replace(/_/g, ' ') }}</p>
                                    <p v-if="history.remarks" class="text-xs text-muted-foreground mt-0.5">{{ history.remarks }}</p>
                                    <p class="text-xs text-muted-foreground mt-1">by {{ history.user?.name || 'System' }}</p>
                                </div>
                                <span class="text-xs text-muted-foreground whitespace-nowrap">{{ formatDate(history.created_at) }}</span>
                            </div>
                        </li>
                    </ol>
                    <p v-else class="text-sm text-muted-foreground">No status history yet.</p>
                </CardContent>
            </Card>

            <!-- Meta -->
            <Card>
                <CardContent class="p-6 text-sm space-y-2">
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Reported on</span>
                        <span class="font-medium text-foreground">{{ formatDate(incident.created_at) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Last updated</span>
                        <span class="font-medium text-foreground">{{ formatDate(incident.updated_at) }}</span>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
