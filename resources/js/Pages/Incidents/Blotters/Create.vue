<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';

const props = defineProps({
    puroks: Array,
    incidents: Array,
    prefill: Object,
});

const form = useForm({
    incident_id: props.prefill?.incident_id || '',
    purok_id: '',
    entry_type: 'accident',
    title: '',
    narrative: '',
    location: '',
    incident_datetime: '',
    complainant_name: '',
    complainant_contact: '',
    involved_persons: '',
    injuries_reported: false,
    actions_taken: '',
    remarks: '',
});

const entryTypes = [
    { value: 'accident', label: 'Accident' },
    { value: 'animal_incident', label: 'Animal Incident' },
    { value: 'disturbance', label: 'Disturbance' },
    { value: 'theft', label: 'Theft' },
    { value: 'dispute', label: 'Dispute' },
    { value: 'property_damage', label: 'Property Damage' },
    { value: 'other', label: 'Other' },
];

const submit = () => {
    form.post('/incidents/blotters');
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Record Blotter Entry" />

        <div class="max-w-2xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-foreground">Record Blotter Entry</h1>
                    <p class="text-sm text-muted-foreground">Log an incident into the official barangay blotter</p>
                </div>
                <Link href="/incidents/blotters" class="text-sm text-muted-foreground hover:text-foreground">&larr; Back to Blotters</Link>
            </div>

            <form @submit.prevent="submit" class="bg-card rounded-xl border p-6 space-y-5">
                <!-- Entry type -->
                <div>
                    <label class="block text-sm font-medium text-muted-foreground">Entry Type *</label>
                    <Select v-model="form.entry_type" required class="mt-1">
                        <option v-for="type in entryTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
                    </Select>
                    <p v-if="form.errors.entry_type" class="mt-1 text-xs text-destructive">{{ form.errors.entry_type }}</p>
                </div>

                <!-- Title -->
                <div>
                    <label class="block text-sm font-medium text-muted-foreground">Title / Subject *</label>
                    <Input v-model="form.title" type="text" required placeholder="e.g., Motorcycle accident caused by stray dog" class="mt-1" />
                    <p v-if="form.errors.title" class="mt-1 text-xs text-destructive">{{ form.errors.title }}</p>
                </div>

                <!-- Narrative -->
                <div>
                    <label class="block text-sm font-medium text-muted-foreground">Narrative *</label>
                    <Textarea v-model="form.narrative" rows="6" required placeholder="Describe what happened in detail. Example: A motorcycle rider figured in an accident along Purok 3 when a dog suddenly ran out from a yard onto the road, causing the rider to lose control and fall..." class="mt-1" />
                    <p v-if="form.errors.narrative" class="mt-1 text-xs text-destructive">{{ form.errors.narrative }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Date & time -->
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground">Date & Time of Incident *</label>
                        <Input v-model="form.incident_datetime" type="datetime-local" required class="mt-1" />
                        <p v-if="form.errors.incident_datetime" class="mt-1 text-xs text-destructive">{{ form.errors.incident_datetime }}</p>
                    </div>
                    <!-- Link to incident report -->
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground">Link to Incident Report</label>
                        <Select v-model="form.incident_id" class="mt-1">
                            <option value="">None (standalone entry)...</option>
                            <option v-for="incident in incidents" :key="incident.id" :value="incident.id">
                                {{ incident.incident_code }} — {{ incident.type }} at {{ incident.location }}
                            </option>
                        </Select>
                        <p v-if="form.errors.incident_id" class="mt-1 text-xs text-destructive">{{ form.errors.incident_id }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Complainant -->
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground">Complainant / Reporter Name</label>
                        <Input v-model="form.complainant_name" type="text" placeholder="Person who reported or walked in" class="mt-1" />
                        <p v-if="form.errors.complainant_name" class="mt-1 text-xs text-destructive">{{ form.errors.complainant_name }}</p>
                    </div>
                    <!-- Contact -->
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground">Complainant Contact No.</label>
                        <Input v-model="form.complainant_contact" type="text" placeholder="e.g., 0917 123 4567" class="mt-1" />
                        <p v-if="form.errors.complainant_contact" class="mt-1 text-xs text-destructive">{{ form.errors.complainant_contact }}</p>
                    </div>
                </div>

                <!-- Involved persons -->
                <div>
                    <label class="block text-sm font-medium text-muted-foreground">Persons / Parties Involved</label>
                    <Textarea v-model="form.involved_persons" rows="3" placeholder="List names and roles, e.g., Juan Dela Cruz (motorcycle rider), Pedro Santos (dog owner)..." class="mt-1" />
                    <p v-if="form.errors.involved_persons" class="mt-1 text-xs text-destructive">{{ form.errors.involved_persons }}</p>
                </div>

                <!-- Injuries -->
                <div>
                    <label class="flex items-center">
                        <input v-model="form.injuries_reported" type="checkbox"
                            class="h-4 w-4 text-primary border-input rounded focus:ring-ring" />
                        <span class="ml-2 text-sm text-foreground">Injuries were reported in this incident</span>
                    </label>
                </div>

                <!-- Actions taken -->
                <div>
                    <label class="block text-sm font-medium text-muted-foreground">Actions Taken</label>
                    <Textarea v-model="form.actions_taken" rows="3" placeholder="e.g., Rider given first aid and referred to the rural health unit; dog owner summoned for dialogue..." class="mt-1" />
                    <p v-if="form.errors.actions_taken" class="mt-1 text-xs text-destructive">{{ form.errors.actions_taken }}</p>
                </div>

                <!-- Remarks -->
                <div>
                    <label class="block text-sm font-medium text-muted-foreground">Remarks</label>
                    <Textarea v-model="form.remarks" rows="2" placeholder="Additional notes..." class="mt-1" />
                    <p v-if="form.errors.remarks" class="mt-1 text-xs text-destructive">{{ form.errors.remarks }}</p>
                </div>

                <div class="rounded-lg bg-yellow-50 border border-yellow-100 p-4">
                    <p class="text-xs text-yellow-700">
                        Blotter entries are part of the official barangay record. Ensure all information is accurate
                        before saving. A unique blotter code will be generated automatically.
                    </p>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-2">
                    <Button variant="outline" as-child>
                        <Link href="/incidents/blotters">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Saving...' : 'Record Blotter Entry' }}
                    </Button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>