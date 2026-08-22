<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    description: '',
    mission: '',
    vision: '',
    address: '',
    about: '',
    is_active: true,
});

const submit = () => {
    form.post('/barangay');
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Create Barangay Profile" />
        <div class="max-w-4xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Create Barangay Profile</h1>
                    <p class="text-sm text-gray-500">Set up your barangay's basic information</p>
                </div>
                <Link href="/barangay" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back</Link>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Barangay Name *</label>
                        <input v-model="form.name" type="text" placeholder="e.g., Barangay San Isidro" class="w-full px-3 py-2 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" required />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea v-model="form.description" rows="3" placeholder="Brief description of your barangay" class="w-full px-3 py-2 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mission</label>
                        <textarea v-model="form.mission" rows="4" placeholder="The mission of the barangay" class="w-full px-3 py-2 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Vision</label>
                        <textarea v-model="form.vision" rows="4" placeholder="The vision of the barangay" class="w-full px-3 py-2 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"></textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <input v-model="form.address" type="text" placeholder="Barangay Hall address" class="w-full px-3 py-2 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" />
                        <p v-if="form.errors.address" class="mt-1 text-xs text-red-600">{{ form.errors.address }}</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">About Us</label>
                        <textarea v-model="form.about" rows="5" placeholder="More information about your barangay" class="w-full px-3 py-2 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"></textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="flex items-center">
                            <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" />
                            <span class="ml-2 text-sm font-medium text-gray-700">Active</span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                    <Link href="/barangay" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</Link>
                    <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50">
                        {{ form.processing ? 'Saving...' : 'Save Barangay' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>