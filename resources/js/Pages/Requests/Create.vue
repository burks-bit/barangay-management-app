<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    requestTypes: Array,
});

const form = useForm({
    request_type_id: '',
    purpose: '',
    description: '',
});

const submit = () => {
    form.post('/my-requests');
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="New Request" />

        <div class="max-w-2xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">New Service Request</h1>
                    <p class="text-sm text-gray-500">Request a barangay document or service</p>
                </div>
                <Link href="/my-requests" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to My Requests</Link>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-5">
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

                <div>
                    <label class="block text-sm font-medium text-gray-700">Purpose *</label>
                    <input v-model="form.purpose" type="text" required
                        placeholder="e.g., Employment application"
                        class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" />
                    <p v-if="form.errors.purpose" class="mt-1 text-xs text-red-600">{{ form.errors.purpose }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Additional Details</label>
                    <textarea v-model="form.description" rows="4"
                        placeholder="Provide any additional information that may help process your request..."
                        class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"></textarea>
                </div>

                <div class="rounded-lg bg-blue-50 border border-blue-100 p-4">
                    <p class="text-xs text-blue-700">
                        Your request will be assigned a tracking number once submitted.
                        You can track its status from your dashboard.
                    </p>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-2">
                    <Link href="/my-requests" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing"
                        class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50">
                        {{ form.processing ? 'Submitting...' : 'Submit Request' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>