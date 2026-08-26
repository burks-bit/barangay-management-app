<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { Card, CardContent } from '@/components/ui/card';
import { Plus } from 'lucide-vue-next';

const props = defineProps({
    incident: Object,
    backUrl: { type: String, default: '/incidents' },
});

const statusForm = useForm({
    status: props.incident.status,
    remarks: '',
    actions_taken: props.incident.actions_taken || '',
    notes: props.incident.notes || '',
});

const statuses = [
    { value: 'reported', label: 'Reported' },
    { value: 'verified', label: 'Verified' },
    { value: 'under_response', label: 'Under Response' },
    { value: 'contained', label: 'Contained' },
    { value: 'resolved', label: 'Resolved' },
    { value: 'closed', label: 'Closed' },
];

const canUpdate = (usePage().props.auth?.permissions || []).includes('update incidents');

const updateStatus = () => {
    statusForm.post(`/incidents/${props.incident.id}/status`, {
        preserveScroll: true,
        onSuccess: () => {
            statusForm.remarks = '';
        },
    });
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
        <Head :title="`Incident ${incident.incident_code}`" />

        <div class="max-w-4xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <Link :href="props.backUrl" class="text-sm text-muted-foreground hover:text-foreground">&larr; Back to Incidents</Link>
                <div class="flex items-center gap-2">
                    <StatusBadge :status="incident.status" />
                    <Link :href="`/incidents/blotters/create?incident_id=${incident.id}`" v-if="canUpdate" class="text-xs text-primary hover:text-primary/80 font-medium">
                        <Plus class="h-3 w-3 inline mr-1" /> Record Blotter Entry
                    </Link>
                </div>
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
                            <dd class="font-medium text-foreground mt-0.5">{{ incident.reporter?.name || 'Unknown' }}</dd>
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
                        <div v-if="incident.assignedResponder">
                            <dt class="text-xs text-muted-foreground uppercase tracking-wide">Assigned To</dt>
                            <dd class="font-medium text-foreground mt-0.5">{{ incident.assignedResponder.name }}</dd>
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

            <!-- Attachments -->
            <Card v-if="incident.attachments?.length">
                <CardContent class="p-6">
                    <h2 class="text-sm font-semibold text-foreground uppercase tracking-wide mb-3">Attachments</h2>
                    <div class="flex flex-wrap gap-3">
                        <a v-for="attachment in incident.attachments" :key="attachment.id"
                           :href="`/storage/${attachment.file_path}`" target="_blank"
                           class="text-sm text-primary hover:text-primary/80 font-medium">
                            {{ attachment.original_name || attachment.file_path }}
                        </a>
                    </div>
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

            <!-- Status Update (admin/moderator only) -->
            <Card v-if="canUpdate">
                <CardContent class="p-6">
                    <h2 class="text-sm font-semibold text-foreground uppercase tracking-wide mb-4">Update Incident Status</h2>
                    <form @submit.prevent="updateStatus" class="space-y-4">
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
                                <Input v-model="statusForm.remarks" type="text" placeholder="e.g., Verified on-site, response team dispatched" class="mt-1" />
                                <p v-if="statusForm.errors.remarks" class="mt-1 text-xs text-destructive">{{ statusForm.errors.remarks }}</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">Actions Taken</label>
                            <textarea v-model="statusForm.actions_taken" rows="3"
                                class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground"
                                placeholder="Describe actions taken for this incident..."></textarea>
                            <p v-if="statusForm.errors.actions_taken" class="mt-1 text-xs text-destructive">{{ statusForm.errors.actions_taken }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">Notes</label>
                            <textarea v-model="statusForm.notes" rows="3"
                                class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground"
                                placeholder="Internal notes..."></textarea>
                            <p v-if="statusForm.errors.notes" class="mt-1 text-xs text-destructive">{{ statusForm.errors.notes }}</p>
                        </div>
                        <div class="flex justify-end">
                            <Button type="submit" :disabled="statusForm.processing">
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
