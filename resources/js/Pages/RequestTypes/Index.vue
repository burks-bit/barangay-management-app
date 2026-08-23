<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ requestTypes: Array });
const editingId = ref(null);
const form = useForm({ name: '', slug: '', description: '', fee: 0, is_active: true });

const edit = (requestType) => {
    editingId.value = requestType.id;
    form.name = requestType.name;
    form.slug = requestType.slug;
    form.description = requestType.description || '';
    form.fee = requestType.fee;
    form.is_active = requestType.is_active;
};

const reset = () => {
    editingId.value = null;
    form.reset();
    form.is_active = true;
};

const submit = () => {
    if (editingId.value) {
        form.put(`/request-types/${editingId.value}`, { onSuccess: reset });
    } else {
        form.post('/request-types', { onSuccess: reset });
    }
};

const remove = (requestType) => {
    if (confirm(`Delete ${requestType.name}?`)) router.delete(`/request-types/${requestType.id}`);
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Document Types" />
        <div class="space-y-4">
            <div><h1 class="text-xl font-bold text-gray-900">Document Types</h1><p class="text-sm text-gray-500 mt-1">Manage document names, descriptions, prices, and availability.</p></div>
            <form @submit.prevent="submit" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
                <h2 class="text-sm font-semibold text-gray-900">{{ editingId ? 'Edit document type' : 'Add document type' }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-gray-700">Name *</label><input v-model="form.name" required type="text" class="w-full" /><p v-if="form.errors.name" class="text-xs text-red-600">{{ form.errors.name }}</p></div>
                    <div><label class="block text-sm font-medium text-gray-700">Slug *</label><input v-model="form.slug" required type="text" class="w-full" /><p v-if="form.errors.slug" class="text-xs text-red-600">{{ form.errors.slug }}</p></div>
                    <div><label class="block text-sm font-medium text-gray-700">Fee *</label><input v-model="form.fee" required type="number" min="0" step="0.01" class="w-full" /><p v-if="form.errors.fee" class="text-xs text-red-600">{{ form.errors.fee }}</p></div>
                    <label class="flex items-center gap-2 self-end text-sm text-gray-700"><input v-model="form.is_active" type="checkbox" /> Active</label>
                </div>
                <div><label class="block text-sm font-medium text-gray-700">Description</label><textarea v-model="form.description" rows="3" class="w-full"></textarea></div>
                <div class="flex justify-end gap-3"><button v-if="editingId" type="button" @click="reset" class="border border-gray-300 text-gray-700">Cancel</button><button type="submit" :disabled="form.processing" class="bg-blue-600 text-white hover:bg-blue-700">{{ form.processing ? 'Saving...' : editingId ? 'Update Type' : 'Add Type' }}</button></div>
            </form>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"><div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200"><thead class="bg-gray-50"><tr><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Slug</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fee</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th><th class="px-6 py-3"></th></tr></thead><tbody class="divide-y divide-gray-100"><tr v-for="requestType in props.requestTypes" :key="requestType.id"><td class="px-6 py-4 text-sm font-medium text-gray-900">{{ requestType.name }}</td><td class="px-6 py-4 text-sm font-mono text-gray-500">{{ requestType.slug }}</td><td class="px-6 py-4 text-sm text-gray-600">{{ Number(requestType.fee).toFixed(2) }}</td><td class="px-6 py-4 text-sm" :class="requestType.is_active ? 'text-green-700' : 'text-gray-500'">{{ requestType.is_active ? 'Active' : 'Inactive' }}</td><td class="px-6 py-4 text-right space-x-2"><button @click="edit(requestType)" class="bg-indigo-50 text-indigo-700 hover:bg-indigo-100">Edit</button><button @click="remove(requestType)" class="bg-red-50 text-red-700 hover:bg-red-100">Delete</button></td></tr><tr v-if="!props.requestTypes.length"><td colspan="5" class="px-6 py-10 text-center text-sm text-gray-400">No document types found.</td></tr></tbody></table></div></div>
        </div>
    </AuthenticatedLayout>
</template>
