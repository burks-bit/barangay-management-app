<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({ puroks: Array });

const form = useForm({
    event_code: '',
    name: '',
    type: 'typhoon',
    description: '',
    started_at: new Date().toISOString().slice(0, 16),
    ended_at: '',
    severity: 'moderate',
    status: 'reported',
    affected_households: 0,
    affected_residents: 0,
    purok_ids: [],
    notes: '',
});

const types = ['typhoon', 'flood', 'earthquake', 'fire', 'landslide', 'storm_surge', 'other'];
const statuses = ['reported', 'active', 'under_response', 'contained', 'resolved', 'archived'];
const severities = ['low', 'moderate', 'high', 'severe', 'critical'];

const togglePurok = (id) => {
    const index = form.purok_ids.indexOf(id);
    if (index === -1) form.purok_ids.push(id);
    else form.purok_ids.splice(index, 1);
};

const submit = () => {
    form.post('/calamities');
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Create Calamity" />
        <div class="max-w-4xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Create Calamity</h1>
                    <p class="text-sm text-gray-500">Record a new calamity event</p>
                </div>
                <Link href="/calamities" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back</Link>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Event Code *</label>
                        <input v-model="form.event_code" type="text" placeholder="CAL-2026-000003" class="w-full px-3 py-2 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" required />
                        <p v-if="form.errors.event_code" class="mt-1 text-xs text-red-600">{{ form.errors.event_code }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                        <input v-model="form.name" type="text" placeholder="e.g., Typhoon Rolly" class="w-full px-3 py-2 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" required />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                        <select v-model="form.type" class="w-full px-3 py-2 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option v-for="t in types" :key="t" :value="t">{{ t.replace('_', ' ') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date *</label>
                        <input v-model="form.started_at" type="datetime-local" class="w-full px-3 py-2 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input v-model="form.ended_at" type="datetime-local" class="w-full px-3 py-2 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Severity</label>
                        <select v-model="form.severity" class="w-full px-3 py-2 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option v-for="s in severities" :key="s" :value="s">{{ s }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select v-model="form.status" class="w-full px-3 py-2 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option v-for="s in statuses" :key="s" :value="s">{{ s.replace('_', ' ') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Affected Households</label>
                        <input v-model="form.affected_households" type="number" min="0" class="w-full px-3 py-2 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Affected Residents</label>
                        <input v-model="form.affected_residents" type="number" min="0" class="w-full px-3 py-2 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea v-model="form.description" rows="3" class="w-full px-3 py-2 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Affected Puroks</label>
                        <div class="flex flex-wrap gap-2">
                            <label v-for="purok in puroks" :key="purok.id" class="inline-flex items-center px-3 py-2 rounded-lg border cursor-pointer" :class="form.purok_ids.includes(purok.id) ? 'bg-blue-50 border-blue-300' : 'border-gray-300'">
                                <input type="checkbox" :checked="form.purok_ids.includes(purok.id)" @change="togglePurok(purok.id)" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" />
                                <span class="ml-2 text-sm text-gray-700">{{ purok.name }}</span>
                            </label>
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea v-model="form.notes" rows="2" class="w-full px-3 py-2 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                    <Link href="/calamities" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</Link>
                    <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50">
                        {{ form.processing ? 'Saving...' : 'Create Calamity' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>