<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { Card, CardContent } from '@/components/ui/card';

const props = defineProps({
    blotter: Object,
});

const statusForm = useForm({
    status: props.blotter.status,
    remarks: '',
});

const statuses = [
    { value: 'recorded', label: 'Recorded' },
    { value: 'under_investigation', label: 'Under Investigation' },
    { value: 'settled', label: 'Settled' },
    { value: 'referred', label: 'Referred' },
    { value: 'closed', label: 'Closed' },
];

const updateStatus = () => {
    statusForm.post(`/incidents/blotters/${props.blotter.id}/status`, {
        preserveScroll: true,
        onSuccess: () => {
            statusForm.remarks = '';
        },
    });
};

const destroyBlotter = () => {
    if (confirm(`Delete blotter entry ${props.blotter.blotter_code}? This action cannot be undone.`)) {
        router.delete(`/incidents/blotters/${props.blotter.id}`);
    }
};

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
        <Head :title="`Blotter ${blotter.blotter_code}`" />

        <div class="max-w-3xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <Link href="/incidents/blotters" class="text-sm text-muted-foreground hover:text-foreground">&larr; Back to Blotters</Link>
                <button @click="destroyBlotter"
                    class="text-sm text-destructive hover:text-destructive/80 font-medium">Delete Entry</button>
            </div>

            <!-- Header card -->
            <Card>
                <CardContent class="p-6">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                        <div>
                            <h1 class="text-lg font-bold text-foreground">{{ blotter.blotter_code }}</h1>
                            <p class="text-base font-semibold text-foreground mt-1">{{ blotter.title }}</p>
                        </div>
                        <StatusBadge :status="blotter.status" />
                    </div>
                    <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <p class="text-xs text-muted-foreground uppercase tracking-wide">Entry Type</p>
                            <p class="mt-0.5 capitalize">{{ blotter.entry_type.replace(/_/g, ' ') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground uppercase tracking-wide">Incident Date & Time</p>
                            <p class="mt-0.5">{{ formatDate(blotter.incident_datetime) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground uppercase tracking-wide">Location</p>
                            <p class="mt-0.5">{{ blotter.location }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground uppercase tracking-wide">Purok</p>
                            <p class="mt-0.5">{{ blotter.purok?.name || '-' }}</p>
                        </div>
                    </div>
                    <div v-if="blotter.injuries_reported"
                        class="mt-4 inline-flex items-center px-3 py-1 rounded-full bg-red-50 border border-red-100 text-xs font-medium text-red-700">
                        Injuries were reported in this incident
                    </div>
                </CardContent>
            </Card>

            <!-- Narrative -->
            <Card>
                <CardContent class="p-6">
                    <h2 class="text-sm font-bold text-foreground uppercase tracking-wide">Narrative of the Incident</h2>
                    <p class="mt-3 text-sm text-muted-foreground whitespace-pre-line leading-relaxed">{{ blotter.narrative }}</p>
                </CardContent>
            </Card>

            <!-- Parties & complainant -->
            <Card>
                <CardContent class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h2 class="text-sm font-bold text-foreground uppercase tracking-wide">Complainant / Reporter</h2>
                        <p class="mt-2 text-sm text-muted-foreground">{{ blotter.complainant_name || 'Not specified' }}</p>
                        <p v-if="blotter.complainant_contact" class="text-sm text-muted-foreground mt-0.5">{{ blotter.complainant_contact }}</p>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-foreground uppercase tracking-wide">Persons / Parties Involved</h2>
                        <p class="mt-2 text-sm text-muted-foreground whitespace-pre-line">{{ blotter.involved_persons || 'Not specified' }}</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Actions taken -->
            <Card>
                <CardContent class="p-6">
                    <h2 class="text-sm font-bold text-foreground uppercase tracking-wide">Actions Taken</h2>
                    <p class="mt-3 text-sm text-muted-foreground whitespace-pre-line">{{ blotter.actions_taken || 'No actions recorded yet.' }}</p>
                </CardContent>
            </Card>

            <!-- Status update -->
            <Card>
                <CardContent class="p-6">
                    <h2 class="text-sm font-bold text-foreground uppercase tracking-wide">Update Status</h2>
                    <form @submit.prevent="updateStatus" class="mt-4 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-muted-foreground">Status *</label>
                                <Select v-model="statusForm.status" required class="mt-1">
                                    <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                                </Select>
                                <p v-if="statusForm.errors.status" class="mt-1 text-xs text-destructive">{{ statusForm.errors.status }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-muted-foreground">Remarks</label>
                                <Input v-model="statusForm.remarks" type="text" placeholder="e.g., Amicable settlement reached between parties" class="mt-1" />
                                <p v-if="statusForm.errors.remarks" class="mt-1 text-xs text-destructive">{{ statusForm.errors.remarks }}</p>
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <Button type="submit" :disabled="statusForm.processing || statusForm.status === blotter.status && !statusForm.remarks">
                                {{ statusForm.processing ? 'Updating...' : 'Update Status' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <!-- Meta -->
            <Card>
                <CardContent class="p-6 text-sm space-y-2">
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Recorded by</span>
                        <span class="font-medium text-foreground">{{ blotter.recorder?.name || '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Recorded on</span>
                        <span class="font-medium text-foreground">{{ formatDate(blotter.created_at) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Settled at</span>
                        <span class="font-medium text-foreground">{{ formatDate(blotter.settled_at) }}</span>
                    </div>
                    <div v-if="blotter.remarks" class="flex justify-between gap-6 pt-2 border-t">
                        <span class="text-muted-foreground shrink-0">Remarks</span>
                        <span class="text-right text-foreground">{{ blotter.remarks }}</span>
                    </div>
                    <div v-if="blotter.incident" class="flex justify-between gap-6 pt-2 border-t">
                        <span class="text-muted-foreground shrink-0">Linked Incident Report</span>
                        <span class="text-right font-medium text-primary">{{ blotter.incident.incident_code }} — {{ blotter.incident.type }}</span>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>