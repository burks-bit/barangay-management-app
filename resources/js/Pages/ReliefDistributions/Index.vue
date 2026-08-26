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
        <Head title="Relief Distribution" />
        <div class="space-y-4">
            <div><h1 class="text-xl font-bold text-foreground">Relief Distribution</h1><p class="text-sm text-muted-foreground mt-1">View scheduled and completed relief distributions.</p></div>
            <Card class="overflow-hidden">
                <CardContent v-if="events.length" class="divide-y divide-border p-0">
                    <article v-for="event in events" :key="event.id" class="p-5">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                            <div>
                                <h2 class="font-semibold text-foreground">{{ event.name }}</h2>
                                <p class="text-xs font-mono text-muted-foreground mt-1">{{ event.event_code }}</p>
                                <p class="text-sm text-muted-foreground mt-2">{{ event.location }} | {{ formatDate(event.distribution_date) }}</p>
                                <p v-if="event.calamity" class="text-xs text-muted-foreground mt-1">Related calamity: {{ event.calamity.name }}</p>
                            </div>
                            <div class="sm:text-right">
                                <StatusBadge :status="event.status" />
                                <p class="text-xs text-muted-foreground mt-2">{{ event.recipients_count }} recipients</p>
                            </div>
                        </div>
                        <div v-if="event.items.length" class="flex flex-wrap gap-2 mt-4">
                            <span v-for="item in event.items" :key="item.id" class="rounded-full bg-muted px-3 py-1 text-xs text-muted-foreground">{{ item.inventory_item?.name }}: {{ item.quantity }}</span>
                        </div>
                        <p v-if="event.notes" class="text-sm text-muted-foreground mt-3">{{ event.notes }}</p>
                    </article>
                </CardContent>
                <p v-else class="p-12 text-center text-sm text-muted-foreground">No relief distributions found.</p>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>