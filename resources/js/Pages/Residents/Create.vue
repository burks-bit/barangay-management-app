<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    puroks: Array,
    households: Array,
});

const form = useForm({
    first_name: '',
    middle_name: '',
    last_name: '',
    suffix: '',
    date_of_birth: '',
    sex: '',
    civil_status: '',
    contact_number: '',
    email: '',
    address: '',
    purok_id: '',
    household_id: '',
    occupation: '',
    residency_status: 'permanent',
    emergency_contact_name: '',
    emergency_contact_number: '',
    create_account: false,
    password: '',
});

const submit = () => {
    form.post('/residents');
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Add Resident" />

        <div class="max-w-4xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Add Resident</h1>
                    <p class="text-sm text-gray-500">Register a new resident record</p>
                </div>
                <Link href="/residents" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to Residents</Link>
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
                            <p v-if="form.errors.last_name" class="mt-1 text-xs text-red-600">{{ form.errors.last_name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Suffix</label>
                            <input v-model="form.suffix" type="text" placeholder="Jr., Sr., III"
                                class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date of Birth *</label>
                            <input v-model="form.date_of_birth" type="date" required
                                class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" />
                            <p v-if="form.errors.date_of_birth" class="mt-1 text-xs text-red-600">{{ form.errors.date_of_birth }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Sex *</label>
                            <select v-model="form.sex" required
                                class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="">Select...</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                            <p v-if="form.errors.sex" class="mt-1 text-xs text-red-600">{{ form.errors.sex }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Civil Status *</label>
                            <select v-model="form.civil_status" required
                                class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="">Select...</option>
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
                            <p v-if="form.errors.address" class="mt-1 px-3 py-2 text-xs text-red-600">{{ form.errors.address }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Purok *</label>
                            <select v-model="form.purok_id" required
                                class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="">Select...</option>
                                <option v-for="purok in puroks" :key="purok.id" :value="purok.id">{{ purok.name }}</option>
                            </select>
                            <p v-if="form.errors.purok_id" class="mt-1 px-3 py-2 text-xs text-red-600">{{ form.errors.purok_id }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Household</label>
                            <select v-model="form.household_id"
                                class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="">None</option>
                                <option v-for="household in households" :key="household.id" :value="household.id">
                                    {{ household.household_code }} - {{ household.head_of_family?.first_name }} {{ household.head_of_family?.last_name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Contact Number</label>
                            <input v-model="form.contact_number" type="tel" placeholder="09XX XXX XXXX"
                                class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input v-model="form.email" type="email"
                                class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" />
                            <p v-if="form.errors.email" class="mt-1 px-3 py-2 text-xs text-red-600">{{ form.errors.email }}</p>
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

                <!-- Account creation -->
                <div class="rounded-lg bg-gray-50 border border-gray-200 p-4">
                    <label class="flex items-center">
                        <input v-model="form.create_account" type="checkbox"
                            class="px-3 py-2 rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" />
                        <span class="ml-2 text-sm font-medium text-gray-700">Create a user account for this resident</span>
                    </label>
                    <div v-if="form.create_account" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email (login) *</label>
                            <input v-model="form.email" type="email" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Password *</label>
                            <input v-model="form.password" type="password" required minlength="8"
                                class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" />
                            <p v-if="form.errors.password" class="mt-1 px-3 py-2 text-xs text-red-600">{{ form.errors.password }}</p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                    <Link href="/residents" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing"
                        class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50">
                        {{ form.processing ? 'Saving...' : 'Save Resident' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>