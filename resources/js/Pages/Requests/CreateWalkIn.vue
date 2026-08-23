<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

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
                    <h1 class="text-xl font-bold text-gray-900">New Walk-in Request</h1>
                    <p class="text-sm text-gray-500">Encode a service request on behalf of a resident who cannot apply online</p>
                </div>
                <Link href="/requests" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to Requests</Link>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-5">
                <!-- Resident picker -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Resident *</label>
                    <p class="text-xs text-gray-500 mb-2">Search for the resident making this request at the barangay office.</p>

                    <template v-if="!selectedResident">
                        <input
                            v-model="residentSearch"
                            type="text"
                            placeholder="Search by name, resident ID, or contact number..."
                            autocomplete="off"
                            @focus="showResults = true"
                            @blur="showResults = false"
                            class="px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                        />
                        <div
                            v-if="showResults"
                            class="mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto z-10 relative"
                        >
                            <button
                                v-for="resident in filteredResidents"
                                :key="resident.id"
                                type="button"
                                class="w-full text-left px-4 py-2.5 hover:bg-blue-50 border-b border-gray-50 last:border-0"
                                @mousedown.prevent="selectResident(resident)"
                            >
                                <span class="block text-sm font-medium text-gray-900">{{ fullName(resident) }}</span>
                                <span class="block text-xs text-gray-500">
                                    ID: {{ resident.resident_id || '—' }}
                                    <template v-if="resident.purok?.name"> &middot; Purok {{ resident.purok.name }}</template>
                                    <template v-if="resident.user_id"> &middot; Has account</template>
                                </span>
                            </button>
                            <p v-if="!filteredResidents.length" class="px-4 py-3 text-sm text-gray-400">
                                No residents found. Add the resident first under Residents.
                            </p>
                        </div>
                        <p v-if="form.errors.member_profile_id" class="mt-1 text-xs text-red-600">{{ form.errors.member_profile_id }}</p>
                    </template>

                    <div v-else class="flex items-center justify-between rounded-lg bg-blue-50 border border-blue-200 px-4 py-3">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ fullName(selectedResident) }}</p>
                            <p class="text-xs text-gray-500">
                                ID: {{ selectedResident.resident_id || '—' }}
                                <template v-if="selectedResident.purok?.name"> &middot; Purok {{ selectedResident.purok.name }}</template>
                                <template v-if="selectedResident.user_id"> &middot; Has account (will be notified of updates)</template>
                            </p>
                        </div>
                        <button type="button" @click="clearResident" class="text-xs font-medium text-red-600 hover:text-red-700">
                            Change
                        </button>
                    </div>
                </div>

                <!-- Document type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Document Type *</label>
                    <select v-model="form.request_type_id" required
                        class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        <option value="">Select a document type...</option>
                        <option v-for="type in requestTypes" :key="type.id" :value="type.id">
                            {{ type.name }} {{ type.fee > 0 ? `(₱${type.fee})` : '(Free)' }}
                        </option>
                    </select>
                    <p v-if="form.errors.request_type_id" class="mt-1 text-xs text-red-600">{{ form.errors.request_type_id }}</p>
                </div>

                <!-- Purpose -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Purpose *</label>
                    <input v-model="form.purpose" type="text" required
                        placeholder="e.g., Employment application"
                        class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" />
                    <p v-if="form.errors.purpose" class="mt-1 text-xs text-red-600">{{ form.errors.purpose }}</p>
                </div>

                <!-- Additional details -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Additional Details</label>
                    <textarea v-model="form.description" rows="4"
                        placeholder="Provide any additional information that may help process this request..."
                        class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"></textarea>
                </div>

                <div class="rounded-lg bg-amber-50 border border-amber-100 p-4">
                    <p class="text-xs text-amber-700">
                        This request will be recorded as a <strong>walk-in request</strong> encoded by you on behalf of the resident.
                        It will receive a tracking number and follow the same processing workflow as online requests.
                    </p>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-2">
                    <Link href="/requests" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing"
                        class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50">
                        {{ form.processing ? 'Creating...' : 'Create Walk-in Request' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>