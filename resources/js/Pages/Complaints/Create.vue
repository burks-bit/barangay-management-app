<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    categories: Array,
});

const form = useForm({
    category_id: '',
    subject: '',
    description: '',
    location: '',
    incident_datetime: '',
    priority: 'medium',
});

const submit = () => {
    form.post('/my-complaints');
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="File Complaint" />

        <div class="max-w-2xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">File a Complaint</h1>
                    <p class="text-sm text-gray-500">Report an issue to the barangay</p>
                </div>
                <Link href="/my-complaints" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to My Complaints</Link>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Category *</label>
                    <select v-model="form.category_id" required
                        class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        <option value="">Select a category...</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                    </select>
                    <p v-if="form.errors.category_id" class="mt-1 text-xs text-red-600">{{ form.errors.category_id }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Subject *</label>
                    <input v-model="form.subject" type="text" required
                        placeholder="Brief summary of the issue"
                        class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" />
                    <p v-if="form.errors.subject" class="mt-1 text-xs text-red-600">{{ form.errors.subject }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Description *</label>
                    <textarea v-model="form.description" rows="5" required
                        placeholder="Describe what happened in detail..."
                        class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"></textarea>
                    <p v-if="form.errors.description" class="mt-1 text-xs text-red-600">{{ form.errors.description }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Location *</label>
                        <input v-model="form.location" type="text" required
                            placeholder="e.g., Purok 3, near the basketball court"
                            class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" />
                        <p v-if="form.errors.location" class="mt-1 text-xs text-red-600">{{ form.errors.location }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date & Time of Incident *</label>
                        <input v-model="form.incident_datetime" type="datetime-local" required
                            class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" />
                        <p v-if="form.errors.incident_datetime" class="mt-1 text-xs text-red-600">{{ form.errors.incident_datetime }}</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Priority *</label>
                    <div class="mt-2 flex space-x-4">
                        <label v-for="level in ['low', 'medium', 'high', 'urgent']" :key="level" class="flex items-center">
                            <input v-model="form.priority" type="radio" :value="level"
                                class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500" />
                            <span class="ml-2 text-sm text-gray-700 capitalize">{{ level }}</span>
                        </label>
                    </div>
                </div>

                <div class="rounded-lg bg-yellow-50 border border-yellow-100 p-4">
                    <p class="text-xs text-yellow-700">
                        Please provide accurate information. False complaints may be subject to barangay review.
                        Your complaint will be reviewed by barangay staff.
                    </p>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-2">
                    <Link href="/my-complaints" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing"
                        class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50">
                        {{ form.processing ? 'Submitting...' : 'Submit Complaint' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>