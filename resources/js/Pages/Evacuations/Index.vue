<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent } from '@/components/ui/card';

defineProps({ events: Array });
const formatDate = (date) => date ? new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : '-';
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Evacuations" />
        <div class="space-y-4">
            <div><h1 class="text-xl font-bold text-foreground">Evacuations</h1><p class="text-sm text-muted-foreground mt-1">Monitor active and completed evacuation events.</p></div>
            <Card class="overflow-hidden">
                <CardContent v-if="events.length" class="divide-y divide-border p-0">
                    <article v-for="event in events" :key="event.id" class="p-5 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                        <div>
                            <h2 class="font-semibold text-foreground">{{ event.evacuation_center?.name }}</h2>
                            <p class="text-xs font-mono text-muted-foreground mt-1">{{ event.event_code }}</p>
                            <p class="text-sm text-muted-foreground mt-2">{{ event.calamity?.name || 'Emergency evacuation' }}</p>
                            <p class="text-xs text-muted-foreground mt-1">Started {{ formatDate(event.started_at) }} | {{ event.evacuation_center?.location }}</p>
                            <p v-if="event.notes" class="text-sm text-muted-foreground mt-3">{{ event.notes }}</p>
                        </div>
                        <div class="sm:text-right">
                            <StatusBadge :status="event.status" />
                            <p class="text-xs text-muted-foreground mt-2">{{ event.current_registrations_count }} residents currently registered</p>
                        </div>
                    </article>
                </CardContent>
                <p v-else class="p-12 text-center text-sm text-muted-foreground">No evacuation events found.</p>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>