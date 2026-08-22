<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    barangay: Object,
});

const positionLabels = {
    captain: 'Barangay Captain',
    vice_captain: 'Vice Captain',
    kagawad: 'Kagawad',
    secretary: 'Barangay Secretary',
    treasurer: 'Barangay Treasurer',
    sangguniang_kabataan_chairperson: 'SK Chairperson',
    barangay_tanod: 'Barangay Tanod',
    health_worker: 'Health Worker',
    other: 'Other',
};

const showOfficialForm = ref(false);
const editingOfficial = ref(null);

const officialForm = useForm({
    position: 'kagawad',
    first_name: '',
    middle_name: '',
    last_name: '',
    suffix: '',
    sex: 'male',
    contact_number: '',
    email: '',
    committee: '',
    term_start: new Date().getFullYear(),
    term_end: '',
    notes: '',
    is_active: true,
});

const openCreate = () => {
    editingOfficial.value = null;
    officialForm.reset();
    officialForm.clearErrors();
    showOfficialForm.value = true;
};

const openEdit = (official) => {
    editingOfficial.value = official;
    officialForm.setData({
        position: official.position,
        first_name: official.first_name,
        middle_name: official.middle_name || '',
        last_name: official.last_name,
        suffix: official.suffix || '',
        sex: official.sex || 'male',
        contact_number: official.contact_number || '',
        email: official.email || '',
        committee: official.committee || '',
        term_start: official.term_start,
        term_end: official.term_end || '',
        notes: official.notes || '',
        is_active: official.is_active,
    });
    showOfficialForm.value = true;
};

const submitOfficial = () => {
    if (editingOfficial.value) {
        officialForm.put(`/barangay/${props.barangay.id}/officials/${editingOfficial.value.id}`, {
            onSuccess: () => {
                showOfficialForm.value = false;
                editingOfficial.value = null;
            },
        });
    } else {
        officialForm.post(`/barangay/${props.barangay.id}/officials`, {
            onSuccess: () => {
                showOfficialForm.value = false;
            },
        });
    }
};

const deleteOfficial = (official) => {
    if (confirm('Remove this official?')) {
        router.delete(`/barangay/${props.barangay.id}/officials/${official.id}`);
    }
};

const deleteBarangay = () => {
    if (confirm('Delete this barangay profile and all its officials?')) {
        router.delete(`/barangay/${props.barangay.id}`);
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Barangay Details" />
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">{{ barangay.name }}</h1>
                    <p class="text-sm text-gray-500">{{ barangay.address || 'Address not set' }}</p>
                </div>
                <div class="flex gap-2">
                    <Link href="/barangay" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back</Link>
                    <Link :href="`/barangay/${barangay.id}/edit`" class="action-link text-indigo-700">Edit Profile</Link>
                    <button @click="deleteBarangay" class="px-3 py-1.5 bg-red-50 text-red-700 rounded-lg text-xs font-medium hover:bg-red-100">Delete</button>
                </div>
            </div>

            <!-- Profile info -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">About {{ barangay.name }}</h3>
                <p v-if="barangay.description" class="text-sm text-gray-600">{{ barangay.description }}</p>
                <div v-if="barangay.mission" class="border-t border-gray-100 pt-4">
                    <h4 class="text-xs font-medium text-gray-500 uppercase">Mission</h4>
                    <p class="text-sm text-gray-700 mt-1">{{ barangay.mission }}</p>
                </div>
                <div v-if="barangay.vision" class="border-t border-gray-100 pt-4">
                    <h4 class="text-xs font-medium text-gray-500 uppercase">Vision</h4>
                    <p class="text-sm text-gray-700 mt-1">{{ barangay.vision }}</p>
                </div>
                <div v-if="barangay.about" class="border-t border-gray-100 pt-4">
                    <h4 class="text-xs font-medium text-gray-500 uppercase">About</h4>
                    <p class="text-sm text-gray-700 mt-1">{{ barangay.about }}</p>
                </div>
            </div>

            <!-- Officials -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Barangay Officials</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Elected and appointed officials per term/year</p>
                    </div>
                    <button @click="openCreate" class="px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg text-xs font-medium hover:bg-blue-100">+ Add Official</button>
                </div>

                <!-- Official form -->
                <div v-if="showOfficialForm" class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h4 class="text-sm font-semibold text-gray-900 mb-3">{{ editingOfficial ? 'Edit Official' : 'Add New Official' }}</h4>
                    <form @submit.prevent="submitOfficial" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Position *</label>
                            <select v-model="officialForm.position" class="w-full px-3 py-2 rounded-lg border-gray-300 text-sm shadow-sm">
                                <option v-for="(label, key) in positionLabels" :key="key" :value="key">{{ label }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
                            <input v-model="officialForm.first_name" type="text" class="w-full px-3 py-2 rounded-lg border-gray-300 text-sm shadow-sm" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Middle Name</label>
                            <input v-model="officialForm.middle_name" type="text" class="w-full px-3 py-2 rounded-lg border-gray-300 text-sm shadow-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
                            <input v-model="officialForm.last_name" type="text" class="w-full px-3 py-2 rounded-lg border-gray-300 text-sm shadow-sm" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Suffix</label>
                            <input v-model="officialForm.suffix" type="text" placeholder="Jr., Sr., III" class="w-full px-3 py-2 rounded-lg border-gray-300 text-sm shadow-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sex</label>
                            <select v-model="officialForm.sex" class="w-full px-3 py-2 rounded-lg border-gray-300 text-sm shadow-sm">
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                            <input v-model="officialForm.contact_number" type="tel" class="w-full px-3 py-2 rounded-lg border-gray-300 text-sm shadow-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input v-model="officialForm.email" type="email" class="w-full px-3 py-2 rounded-lg border-gray-300 text-sm shadow-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Committee</label>
                            <input v-model="officialForm.committee" type="text" placeholder="e.g., Public Safety" class="w-full px-3 py-2 rounded-lg border-gray-300 text-sm shadow-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Term Start *</label>
                            <input v-model="officialForm.term_start" type="number" min="1900" max="2100" class="w-full px-3 py-2 rounded-lg border-gray-300 text-sm shadow-sm" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Term End</label>
                            <input v-model="officialForm.term_end" type="number" min="1900" max="2100" placeholder="Ongoing" class="w-full px-3 py-2 rounded-lg border-gray-300 text-sm shadow-sm" />
                        </div>
                        <div class="flex items-end">
                            <label class="flex items-center">
                                <input v-model="officialForm.is_active" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" />
                                <span class="ml-2 text-sm font-medium text-gray-700">Active</span>
                            </label>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea v-model="officialForm.notes" rows="2" class="w-full px-3 py-2 rounded-lg border-gray-300 text-sm shadow-sm"></textarea>
                        </div>
                        <div v-if="Object.keys(officialForm.errors).length" class="md:col-span-3">
                            <p v-for="(err, key) in officialForm.errors" :key="key" class="text-xs text-red-600">{{ err }}</p>
                        </div>
                        <div class="md:col-span-3 flex justify-end gap-2 pt-2">
                            <button type="button" @click="showOfficialForm = false" class="px-3 py-2 text-sm text-gray-600 hover:text-gray-800">Cancel</button>
                            <button type="submit" :disabled="officialForm.processing" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                {{ officialForm.processing ? 'Saving...' : (editingOfficial ? 'Update Official' : 'Add Official') }}
                            </button>
                        </div>
                    </form>
                </div>

                <div v-if="barangay.officials?.length" class="divide-y divide-gray-100">
                    <div v-for="official in barangay.officials" :key="official.id" class="px-6 py-4 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ official.first_name }} {{ official.middle_name ? official.middle_name.charAt(0) + '. ' : '' }}{{ official.last_name }} {{ official.suffix || '' }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ positionLabels[official.position] || official.position }} <span v-if="official.committee">- {{ official.committee }}</span></p>
                            <p class="text-xs text-gray-400 mt-0.5">Term: {{ official.term_start }}{{ official.term_end ? ' - ' + official.term_end : ' - Present' }}</p>
                        </div>
                        <div class="flex gap-2">
                            <button @click="openEdit(official)" class="text-indigo-600 hover:text-indigo-900 text-sm">Edit</button>
                            <button @click="deleteOfficial(official)" class="text-red-600 hover:text-red-900 text-sm">Delete</button>
                        </div>
                    </div>
                </div>
                <p v-else class="p-12 text-center text-sm text-gray-400">No officials added yet. Click "Add Official" to get started.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>