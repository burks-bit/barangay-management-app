<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { Card, CardContent } from '@/components/ui/card';
import { Plus } from 'lucide-vue-next';

const props = defineProps({
    blotters: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const entryType = ref(props.filters.entry_type || '');

watch([search, status, entryType], () => {
    router.get('/incidents/blotters', {
        search: search.value,
        status: status.value,
        entry_type: entryType.value,
    }, {
        preserveState: true,
        replace: true,
    });
});

const entryTypes = [
    { value: '', label: 'All Types' },
    { value: 'accident', label: 'Accident' },
    { value: 'animal_incident', label: 'Animal Incident' },
    { value: 'disturbance', label: 'Disturbance' },
    { value: 'theft', label: 'Theft' },
    { value: 'dispute', label: 'Dispute' },
    { value: 'property_damage', label: 'Property Damage' },
    { value: 'other', label: 'Other' },
];

const statuses = [
    { value: '', label: 'All Statuses' },
    { value: 'recorded', label: 'Recorded' },
    { value: 'under_investigation', label: 'Under Investigation' },
    { value: 'settled', label: 'Settled' },
    { value: 'referred', label: 'Referred' },
    { value: 'closed', label: 'Closed' },
];

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

const destroyBlotter = (blotter) => {
    if (confirm(`Delete blotter entry ${blotter.blotter_code}? This action cannot be undone.`)) {
        router.delete(`/incidents/blotters/${blotter.id}`, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Incident Blotters" />

        <div class="max-w-6xl mx-auto space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-foreground">Incident Blotters</h1>
                    <p class="text-sm text-muted-foreground mt-1">Official barangay blotter logbook of recorded incidents.</p>
                </div>
                <Button as-child>
                    <Link href="/incidents/blotters/create">
                        <Plus class="h-4 w-4" /> Record Blotter Entry
                    </Link>
                </Button>
            </div>

            <!-- Filters -->
            <Card>
                <CardContent class="p-4 grid grid-cols-1 md:grid-cols-3 gap-3">
                    <Input v-model="search" type="text" placeholder="Search code, title, narrative, location..." />
                    <Select v-model="entryType">
                        <option v-for="type in entryTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
                    </Select>
                    <Select v-model="status">
                        <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                    </Select>
                </CardContent>
            </Card>

            <!-- List -->
            <Card v-if="blotters.data.length" class="overflow-hidden">
                <CardContent class="divide-y divide-border p-0">
                    <article v-for="blotter in blotters.data" :key="blotter.id" class="p-5 hover:bg-muted/50 transition-colors">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h2 class="text-base font-semibold text-foreground">{{ blotter.blotter_code }}</h2>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-primary capitalize">
                                        {{ blotter.entry_type.replace(/_/g, ' ') }}
                                    </span>
                                    <span v-if="blotter.injuries_reported"
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700">
                                        Injuries Reported
                                    </span>
                                </div>
                                <p class="text-sm font-medium text-foreground mt-1">{{ blotter.title }}</p>
                                <p class="text-xs text-muted-foreground mt-1">
                                    {{ formatDate(blotter.incident_datetime) }} &middot; {{ blotter.location }}
                                    <template v-if="blotter.purok"> &middot; {{ blotter.purok.name }}</template>
                                </p>
                                <p class="text-sm text-muted-foreground mt-2 line-clamp-2">{{ blotter.narrative }}</p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <StatusBadge :status="blotter.status" />
                            </div>
                        </div>
                        <div class="flex items-center justify-between mt-3 pt-3 border-t">
                            <p class="text-xs text-muted-foreground">Recorded by {{ blotter.recorder?.name || '-' }}</p>
                            <div class="flex items-center gap-3">
                                <button @click="destroyBlotter(blotter)"
                                    class="text-xs text-destructive hover:text-destructive/80 font-medium">Delete</button>
                                <Link :href="`/incidents/blotters/${blotter.id}`"
                                    class="text-xs text-primary hover:text-primary/80 font-medium">View Details &rarr;</Link>
                            </div>
                        </div>
                    </article>
                </CardContent>

                <!-- Pagination -->
                <div v-if="blotters.last_page > 1" class="flex items-center justify-between px-5 py-3 border-t bg-muted">
                    <p class="text-xs text-muted-foreground">
                        Showing {{ blotters.from }} to {{ blotters.to }} of {{ blotters.total }} entries
                    </p>
                    <div class="flex items-center gap-1">
                        <Link v-for="(link, i) in blotters.links" :key="i" :href="link.url || '#'" v-html="link.label"
                            class="px-3 py-1 text-xs rounded-md"
                            :class="[
                                link.active ? 'bg-primary text-primary-foreground' : 'bg-card text-foreground hover:bg-muted border',
                                !link.url ? 'opacity-40 pointer-events-none' : '',
                            ]" />
                    </div>
                </div>
            </Card>
            <p v-else class="bg-card rounded-xl border p-8 text-center text-sm text-muted-foreground">
                No blotter entries found
            </p>
        </div>
    </AuthenticatedLayout>
</template>