<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';

const props = defineProps({
    residents: Array,
    requestTypes: Array,
});

const form = useForm({
    member_profile_id: '',
    request_type_id: '',
    purpose: '',
    description: '',
});

// --- Resident search -----------------------------------------------------
const residentSearch = ref('');
const showResults = ref(false);
const selectedResident = ref(null);

const fullName = (r) => {
    if (!r) return '';
    let name = [r.first_name, r.middle_name, r.last_name].filter(Boolean).join(' ');
    return r.suffix ? `${name} ${r.suffix}` : name;
};

const filteredResidents = computed(() => {
    const q = residentSearch.value.trim().toLowerCase();
    let list = props.residents;
    if (q) {
        list = list.filter((r) =>
            fullName(r).toLowerCase().includes(q) ||
            (r.resident_id || '').toLowerCase().includes(q) ||
            (r.contact_number || '').toLowerCase().includes(q)
        );
    }
    return list.slice(0, 8);
});

const selectResident = (resident) => {
    selectedResident.value = resident;
    form.member_profile_id = resident.id;
    residentSearch.value = '';
    showResults.value = false;
};

const clearResident = () => {
    selectedResident.value = null;
    form.member_profile_id = '';
};
// -------------------------------------------------------------------------

const submit = () => {
    form.post('/requests');
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="New Walk-in Request" />

        <div class="max-w-2xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-foreground">New Walk-in Request</h1>
                    <p class="text-sm text-muted-foreground">Encode a service request on behalf of a resident who cannot apply online</p>
                </div>
                <Link href="/requests" class="text-sm text-muted-foreground hover:text-foreground">&larr; Back to Requests</Link>
            </div>

            <form @submit.prevent="submit" class="bg-card rounded-xl border p-6 space-y-5">
                <!-- Resident picker -->
                <div>
                    <label class="block text-sm font-medium text-muted-foreground">Resident *</label>
                    <p class="text-xs text-muted-foreground mb-2">Search for the resident making this request at the barangay office.</p>

                    <template v-if="!selectedResident">
                        <Input
                            v-model="residentSearch"
                            type="text"
                            placeholder="Search by name, resident ID, or contact number..."
                            autocomplete="off"
                            @focus="showResults = true"
                            @blur="showResults = false"
                        />
                        <div
                            v-if="showResults"
                            class="mt-1 bg-card border rounded-lg shadow-lg max-h-60 overflow-y-auto z-10 relative"
                        >
                            <button
                                v-for="resident in filteredResidents"
                                :key="resident.id"
                                type="button"
                                class="w-full text-left px-4 py-2.5 hover:bg-muted border-b last:border-0"
                                @mousedown.prevent="selectResident(resident)"
                            >
                                <span class="block text-sm font-medium text-foreground">{{ fullName(resident) }}</span>
                                <span class="block text-xs text-muted-foreground">
                                    ID: {{ resident.resident_id || '—' }}
                                    <template v-if="resident.purok?.name"> &middot; Purok {{ resident.purok.name }}</template>
                                    <template v-if="resident.user_id"> &middot; Has account</template>
                                </span>
                            </button>
                            <p v-if="!filteredResidents.length" class="px-4 py-3 text-sm text-muted-foreground">
                                No residents found. Add the resident first under Residents.
                            </p>
                        </div>
                        <p v-if="form.errors.member_profile_id" class="mt-1 text-xs text-destructive">{{ form.errors.member_profile_id }}</p>
                    </template>

                    <div v-else class="flex items-center justify-between rounded-lg bg-primary/5 border px-4 py-3">
                        <div>
                            <p class="text-sm font-medium text-foreground">{{ fullName(selectedResident) }}</p>
                            <p class="text-xs text-muted-foreground">
                                ID: {{ selectedResident.resident_id || '—' }}
                                <template v-if="selectedResident.purok?.name"> &middot; Purok {{ selectedResident.purok.name }}</template>
                                <template v-if="selectedResident.user_id"> &middot; Has account (will be notified of updates)</template>
                            </p>
                        </div>
                        <button type="button" @click="clearResident" class="text-xs font-medium text-destructive hover:text-destructive/80">
                            Change
                        </button>
                    </div>
                </div>

                <!-- Document type -->
                <div>
                    <label class="block text-sm font-medium text-muted-foreground">Document Type *</label>
                    <Select v-model="form.request_type_id" required class="mt-1">
                        <option value="">Select a document type...</option>
                        <option v-for="type in requestTypes" :key="type.id" :value="type.id">
                            {{ type.name }} {{ type.fee > 0 ? `(₱${type.fee})` : '(Free)' }}
                        </option>
                    </Select>
                    <p v-if="form.errors.request_type_id" class="mt-1 text-xs text-destructive">{{ form.errors.request_type_id }}</p>
                </div>

                <!-- Purpose -->
                <div>
                    <label class="block text-sm font-medium text-muted-foreground">Purpose *</label>
                    <Input v-model="form.purpose" type="text" required placeholder="e.g., Employment application" class="mt-1" />
                    <p v-if="form.errors.purpose" class="mt-1 text-xs text-destructive">{{ form.errors.purpose }}</p>
                </div>

                <!-- Additional details -->
                <div>
                    <label class="block text-sm font-medium text-muted-foreground">Additional Details</label>
                    <Textarea v-model="form.description" rows="4" placeholder="Provide any additional information that may help process this request..." class="mt-1" />
                </div>

                <div class="rounded-lg bg-amber-50 border border-amber-100 p-4">
                    <p class="text-xs text-amber-700">
                        This request will be recorded as a <strong>walk-in request</strong> encoded by you on behalf of the resident.
                        It will receive a tracking number and follow the same processing workflow as online requests.
                    </p>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-2">
                    <Button variant="outline" as-child>
                        <Link href="/requests">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Creating...' : 'Create Walk-in Request' }}
                    </Button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>