<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    puroks: Array,
    residents: Array,
    evacuationCenters: Array,
});

const form = useForm({
    household_code: '',
    address: '',
    purok_id: '',
    contact_number: '',
    head_of_family_id: '',
    evacuation_center_id: '',
    evacuation_status: 'none',
    notes: '',
});

const submit = () => form.post('/households');
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Register Household" />
        <div class="max-w-2xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div><h1 class="text-xl font-bold text-gray-900">Register Household</h1><p class="text-sm text-gray-500 mt-1">Create a household record.</p></div>
                <Link href="/households" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back</Link>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-gray-700">Household code *</label><input v-model="form.household_code" required type="text" placeholder="HH-0001" class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 text-sm" /><p v-if="form.errors.household_code" class="mt-1 text-xs text-red-600">{{ form.errors.household_code }}</p></div>
                    <div><label class="block text-sm font-medium text-gray-700">Purok *</label><select v-model="form.purok_id" required class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 text-sm"><option value="">Select Purok...</option><option v-for="purok in puroks" :key="purok.id" :value="purok.id">{{ purok.name }}</option></select><p v-if="form.errors.purok_id" class="mt-1 text-xs text-red-600">{{ form.errors.purok_id }}</p></div>
                </div>
                <div><label class="block text-sm font-medium text-gray-700">Address *</label><input v-model="form.address" required type="text" class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 text-sm" /><p v-if="form.errors.address" class="mt-1 text-xs text-red-600">{{ form.errors.address }}</p></div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-gray-700">Head of family</label><select v-model="form.head_of_family_id" class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 text-sm"><option value="">Select resident...</option><option v-for="resident in residents" :key="resident.id" :value="resident.id">{{ resident.first_name }} {{ resident.middle_name }} {{ resident.last_name }}</option></select></div>
                    <div><label class="block text-sm font-medium text-gray-700">Contact number</label><input v-model="form.contact_number" type="tel" class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 text-sm" /></div>
                </div>
                <div class="border-t border-gray-100 pt-5"><h2 class="text-sm font-semibold text-gray-900">Evacuation details</h2><div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3"><div><label class="block text-sm font-medium text-gray-700">Status</label><select v-model="form.evacuation_status" class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 text-sm"><option value="none">Not evacuated</option><option value="evacuated">Evacuated</option><option value="returned">Returned</option></select></div><div><label class="block text-sm font-medium text-gray-700">Evacuation center</label><select v-model="form.evacuation_center_id" class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 text-sm"><option value="">Select center...</option><option v-for="center in evacuationCenters" :key="center.id" :value="center.id">{{ center.name }} ({{ center.current_occupancy }}/{{ center.capacity }})</option></select></div></div></div>
                <div><label class="block text-sm font-medium text-gray-700">Notes</label><textarea v-model="form.notes" rows="3" class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 text-sm"></textarea></div>
                <div class="flex justify-end gap-3 pt-2"><Link href="/households" class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-lg">Cancel</Link><button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg disabled:opacity-50">{{ form.processing ? 'Saving...' : 'Register Household' }}</button></div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>