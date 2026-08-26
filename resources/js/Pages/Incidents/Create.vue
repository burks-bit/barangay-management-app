<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';

const props = defineProps({
    puroks: Array,
    calamities: Array,
});

const form = useForm({
    calamity_id: '',
    type: 'accident',
    location: '',
    purok_id: '',
    description: '',
    severity: 'moderate',
    incident_datetime: '',
    affected_households: '',
    affected_residents: '',
});

const incidentTypes = [
    { value: 'flood', label: 'Flood' },
    { value: 'fire', label: 'Fire' },
    { value: 'earthquake', label: 'Earthquake' },
    { value: 'landslide', label: 'Landslide' },
    { value: 'storm_surge', label: 'Storm Surge' },
    { value: 'typhoon', label: 'Typhoon' },
    { value: 'accident', label: 'Accident' },
    { value: 'crime', label: 'Crime' },
    { value: 'other', label: 'Other' },
];

const severityLevels = [
    { value: 'low', label: 'Low' },
    { value: 'moderate', label: 'Moderate' },
    { value: 'high', label: 'High' },
    { value: 'severe', label: 'Severe' },
    { value: 'critical', label: 'Critical' },
];

const submit = () => {
    form.post('/my-incidents');
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Report Incident" />

        <div class="max-w-2xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-foreground">Report an Incident</h1>
                    <p class="text-sm text-muted-foreground mt-1">Report an incident to the barangay for response and action.</p>
                </div>
                <Link href="/my-incidents" class="text-sm text-muted-foreground hover:text-foreground">&larr; Back to My Incidents</Link>
            </div>

            <form @submit.prevent="submit" class="bg-card rounded-xl border p-6 space-y-5">
                <!-- Incident Type -->
                <div>
                    <label class="block text-sm font-medium text-muted-foreground">Incident Type *</label>
                    <Select v-model="form.type" required class="mt-1">
                        <option v-for="type in incidentTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
                    </Select>
                    <p v-if="form.errors.type" class="mt-1 text-xs text-destructive">{{ form.errors.type }}</p>
                </div>

                <!-- Location -->
                <div>
                    <label class="block text-sm font-medium text-muted-foreground">Location *</label>
                    <Input v-model="form.location" type="text" required placeholder="e.g., Along Mabini St., near the chapel" class="mt-1" />
                    <p v-if="form.errors.location" class="mt-1 text-xs text-destructive">{{ form.errors.location }}</p>
                </div>

                <!-- Purok -->
                <div>
                    <label class="block text-sm font-medium text-muted-foreground">Purok</label>
                    <Select v-model="form.purok_id" class="mt-1">
                        <option value="">Select purok (optional)...</option>
                        <option v-for="purok in puroks" :key="purok.id" :value="purok.id">{{ purok.name }}</option>
                    </Select>
                    <p v-if="form.errors.purok_id" class="mt-1 text-xs text-destructive">{{ form.errors.purok_id }}</p>
                </div>

                <!-- Date & Time -->
                <div>
                    <label class="block text-sm font-medium text-muted-foreground">Date & Time of Incident *</label>
                    <Input v-model="form.incident_datetime" type="datetime-local" required class="mt-1" />
                    <p v-if="form.errors.incident_datetime" class="mt-1 text-xs text-destructive">{{ form.errors.incident_datetime }}</p>
                </div>

                <!-- Severity -->
                <div>
                    <label class="block text-sm font-medium text-muted-foreground">Severity *</label>
                    <Select v-model="form.severity" required class="mt-1">
                        <option v-for="level in severityLevels" :key="level.value" :value="level.value">{{ level.label }}</option>
                    </Select>
                    <p v-if="form.errors.severity" class="mt-1 text-xs text-destructive">{{ form.errors.severity }}</p>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-muted-foreground">Description *</label>
                    <Textarea v-model="form.description" rows="5" required placeholder="Describe what happened in detail..." class="mt-1" />
                    <p v-if="form.errors.description" class="mt-1 text-xs text-destructive">{{ form.errors.description }}</p>
                </div>

                <!-- Affected Households & Residents -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground">Affected Households</label>
                        <Input v-model="form.affected_households" type="number" min="0" placeholder="e.g., 5" class="mt-1" />
                        <p v-if="form.errors.affected_households" class="mt-1 text-xs text-destructive">{{ form.errors.affected_households }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground">Affected Residents</label>
                        <Input v-model="form.affected_residents" type="number" min="0" placeholder="e.g., 12" class="mt-1" />
                        <p v-if="form.errors.affected_residents" class="mt-1 text-xs text-destructive">{{ form.errors.affected_residents }}</p>
                    </div>
                </div>

                <!-- Link to Calamity -->
                <div v-if="calamities.length">
                    <label class="block text-sm font-medium text-muted-foreground">Link to Calamity (optional)</label>
                    <Select v-model="form.calamity_id" class="mt-1">
                        <option value="">None (standalone incident)...</option>
                        <option v-for="calamity in calamities" :key="calamity.id" :value="calamity.id">
                            {{ calamity.name }} — {{ calamity.type }}
                        </option>
                    </Select>
                    <p v-if="form.errors.calamity_id" class="mt-1 text-xs text-destructive">{{ form.errors.calamity_id }}</p>
                </div>

                <div class="rounded-lg bg-blue-50 border border-blue-100 p-4">
                    <p class="text-xs text-blue-700">
                        Your incident report will be reviewed by barangay officials. You will be notified of any updates.
                        A unique incident code will be generated automatically.
                    </p>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-2">
                    <Button variant="outline" as-child>
                        <Link href="/my-incidents">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Submitting...' : 'Report Incident' }}
                    </Button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
