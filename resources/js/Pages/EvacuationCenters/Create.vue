<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    location: '',
    capacity: 100,
    contact_person: '',
    contact_number: '',
    status: 'available',
    notes: '',
});

const submit = () => {
    form.post('/evacuation-centers');
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Create Center" />
        <div class="max-w-4xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Create Evacuation Center</h1>
                    <p class="text-sm text-gray-500">Add a new evacuation facility</p>
                </div>
                <Link href="/evacuation-centers" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back</Link>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                    <input v-model="form.name" type="text" required class="w-full px-3 py-2 rounded-lg border-gray-300 text-sm" />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Location *</label>
                    <input v-model="form.location" type="text" required class="w-full px-3 py-2 rounded-lg border-gray-300 text-sm" />
                    <p v-if="form.errors.location" class="mt-1 text-xs text-red-600">{{ form.errors.location }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Capacity *</label>
                    <input v-model="form.capacity" type="number" min="0" required class="w-full px-3 py-2 rounded-lg border-gray-300 text-sm" />
                    <p v-if="form.errors.capacity" class="mt-1 text-xs text-red-600">{{ form.errors.capacity }}</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contact Person</label>
                        <input v-model="form.contact_person" type="text" class="w-full px-3 py-2 rounded-lg border-gray-300 text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                        <input v-model="form.contact_number" type="tel" class="w-full px-3 py-2 rounded-lg border-gray-300 text-sm" />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select v-model="form.status" class="w-full px-3 py-2 rounded-lg border-gray-300 text-sm">
                        <option value="available">Available</option>
                        <option value="occupied">Occupied</option>
                        <option value="full">Full</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea v-model="form.notes" rows="3" class="w-full px-3 py-2 rounded-lg border-gray-300 text-sm"></textarea>
                </div>
                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                    <Link href="/evacuation-centers" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</Link>
                    <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50">
                        {{ form.processing ? 'Saving...' : 'Create Center' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>