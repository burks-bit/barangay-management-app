<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    resident: Object,
    puroks: Array,
    households: Array,
});

const form = useForm({
    first_name: props.resident.first_name,
    middle_name: props.resident.middle_name || '',
    last_name: props.resident.last_name,
    suffix: props.resident.suffix || '',
    date_of_birth: props.resident.date_of_birth ? props.resident.date_of_birth.slice(0, 10) : '',
    sex: props.resident.sex,
    civil_status: props.resident.civil_status,
    contact_number: props.resident.contact_number || '',
    address: props.resident.address,
    purok_id: props.resident.purok_id,
    household_id: props.resident.household_id || '',
    occupation: props.resident.occupation || '',
    residency_status: props.resident.residency_status,
    emergency_contact_name: props.resident.emergency_contact_name || '',
    emergency_contact_number: props.resident.emergency_contact_number || '',
});

const submit = () => {
    form.put(`/residents/${props.resident.id}`);
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Edit Resident" />

        <div class="max-w-4xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Edit Resident</h1>
                    <p class="text-sm text-gray-500">{{ resident.first_name }} {{ resident.last_name }}</p>
                </div>
                <Link :href="`/residents/${resident.id}`" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to Profile</Link>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
                <!-- Personal Information -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 mb-4">Personal Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">First Name *</label>
                            <input v-model="form.first_name" type="text" required
                                class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" />
                            <p v-if="form.errors.first_name" class="mt-1 text-xs text-red-600">{{ form.errors.first_name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Middle Name</label>
                            <input v-model="form.middle_name" type="text"
                                class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Last Name *</label>
                            <input v-model="form.last_name" type="text" required
                                class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Suffix</label>
                            <input v-model="form.suffix" type="text"
                                class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date of Birth *</label>
                            <input v-model="form.date_of_birth" type="date" required
                                class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Sex *</label>
                            <select v-model="form.sex" required
                                class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Civil Status *</label>
                            <select v-model="form.civil_status" required
                                class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="single">Single</option>
                                <option value="married">Married</option>
                                <option value="widowed">Widowed</option>
                                <option value="separated">Separated</option>
                                <option value="divorced">Divorced</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Occupation</label>
                            <input v-model="form.occupation" type="text"
                                class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Residency Status *</label>
                            <select v-model="form.residency_status" required
                                class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="permanent">Permanent</option>
                                <option value="temporary">Temporary</option>
                                <option value="transient">Transient</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Address -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 mb-4">Address Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Address *</label>
                            <input v-model="form.address" type="text" required
                                class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Purok *</label>
                            <select v-model="form.purok_id" required
                                class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option v-for="purok in puroks" :key="purok.id" :value="purok.id">{{ purok.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Household</label>
                            <select v-model="form.household_id"
                                class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="">None</option>
                                <option v-for="household in households" :key="household.id" :value="household.id">
                                    {{ household.household_code }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Contact Number</label>
                            <input v-model="form.contact_number" type="tel"
                                class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" />
                        </div>
                    </div>
                </div>

                <!-- Emergency Contact -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 mb-4">Emergency Contact</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Contact Name</label>
                            <input v-model="form.emergency_contact_name" type="text"
                                class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Contact Number</label>
                            <input v-model="form.emergency_contact_number" type="tel"
                                class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" />
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                    <Link :href="`/residents/${resident.id}`" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing"
                        class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50">
                        {{ form.processing ? 'Saving...' : 'Update Resident' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>