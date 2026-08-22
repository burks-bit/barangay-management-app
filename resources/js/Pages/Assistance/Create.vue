<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    assistanceTypes: Array,
});

const form = useForm({
    assistance_type_id: '',
    reason: '',
    amount: '',
});

const submit = () => form.post('/my-assistance');
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Request Assistance" />

        <div class="max-w-2xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Request Assistance</h1>
                    <p class="text-sm text-gray-500 mt-1">Submit a request for barangay assistance.</p>
                </div>
                <Link href="/my-assistance" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back</Link>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Assistance type *</label>
                    <select v-model="form.assistance_type_id" required class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        <option value="">Select a type...</option>
                        <option v-for="type in assistanceTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
                    </select>
                    <p v-if="form.errors.assistance_type_id" class="mt-1 text-xs text-red-600">{{ form.errors.assistance_type_id }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Reason *</label>
                    <textarea v-model="form.reason" required rows="6" placeholder="Explain why you need assistance..." class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm text-sm"></textarea>
                    <p v-if="form.errors.reason" class="mt-1 text-xs text-red-600">{{ form.errors.reason }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Estimated amount (optional)</label>
                    <input v-model="form.amount" type="number" min="0" step="0.01" class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm text-sm" />
                    <p v-if="form.errors.amount" class="mt-1 text-xs text-red-600">{{ form.errors.amount }}</p>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <Link href="/my-assistance" class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg">Cancel</Link>
                    <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg disabled:opacity-50">
                        {{ form.processing ? 'Submitting...' : 'Submit Request' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
