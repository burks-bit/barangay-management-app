<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ centers: Array, userHousehold: Object });
const page = usePage();
const permissions = ref(page.props.auth?.permissions || []);
const roles = ref(page.props.auth?.roles || []);

const canManage = () => permissions.value.includes('create evacuation centers') || permissions.value.includes('update evacuation centers');
const isMember = () => roles.value.includes('member');

const selectCenter = (centerId) => {
    if (confirm('Evacuate your household to this center?')) {
        router.post('/evacuation-centers/select', { evacuation_center_id: centerId });
    }
};

const returnHome = () => {
    if (confirm('Return your household home from evacuation?')) {
        router.post('/evacuation-centers/return');
    }
};

const canSelectCenter = (center) => {
    return center.status !== 'full' && center.status !== 'closed' && center.current_occupancy < center.capacity;
};

const destroy = (center) => {
    if (confirm('Delete this evacuation center?')) {
        router.delete(`/evacuation-centers/${center.id}`);
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Evacuation Centers" />
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Evacuation Centers</h1>
                    <p class="text-sm text-gray-500 mt-1">View evacuation facilities and capacity.</p>
                </div>
                <div v-if="canManage()" class="flex gap-2">
                    <Link href="/evacuation-centers/create" class="px-4 py-2 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700">+ Add Center</Link>
                </div>
            </div>

            <div v-if="userHousehold" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">My Household Evacuation</h3>
                        <p class="text-xs text-gray-500 mt-1">Household: {{ userHousehold.household_code }}</p>
                        <p class="text-sm mt-2">
                            <span class="text-gray-600">Status: </span>
                            <span class="font-medium" :class="userHousehold.evacuation_status === 'evacuated' ? 'text-blue-600' : 'text-gray-600'">
                                {{ userHousehold.evacuation_status === 'evacuated' ? 'Evacuated to ' + (userHousehold.evacuation_center?.name || 'center') : 'Not evacuated' }}
                            </span>
                        </p>
                    </div>
                    <button v-if="userHousehold.evacuation_status === 'evacuated'" @click="returnHome" class="px-4 py-2 bg-yellow-600 text-white text-xs font-medium rounded-lg hover:bg-yellow-700">Return Home</button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <article v-for="center in centers" :key="center.id" class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div><h2 class="font-semibold text-gray-900">{{ center.name }}</h2><p class="text-sm text-gray-500 mt-1">{{ center.location }}</p></div>
                        <StatusBadge :status="center.status" />
                    </div>
                    <dl class="grid grid-cols-2 gap-3 mt-5 text-sm">
                        <div><dt class="text-gray-500">Capacity</dt><dd class="font-medium text-gray-900">{{ center.capacity }}</dd></div>
                        <div><dt class="text-gray-500">Current occupancy</dt><dd class="font-medium text-gray-900">{{ center.current_occupancy }}</dd></div>
                        <div><dt class="text-gray-500">Available spaces</dt><dd class="font-medium text-green-700">{{ Math.max(0, center.capacity - center.current_occupancy) }}</dd></div>
                        <div><dt class="text-gray-500">Active evacuations</dt><dd class="font-medium text-gray-900">{{ center.active_events_count }}</dd></div>
                    </dl>
                    <p v-if="center.contact_person || center.contact_number" class="text-xs text-gray-500 mt-4">Contact: {{ center.contact_person || '-' }} {{ center.contact_number ? `(${center.contact_number})` : '' }}</p>

                    <div class="mt-4 flex items-center gap-2" v-if="canManage()">
                        <Link :href="`/evacuation-centers/${center.id}/edit`" class="action-link text-indigo-700">Edit</Link>
                        <button @click="destroy(center)" class="px-3 py-1.5 bg-red-50 text-red-700 rounded-lg text-xs font-medium hover:bg-red-100">Delete</button>
                    </div>
                    <div v-else-if="isMember()" class="mt-4">
                        <button v-if="userHousehold && userHousehold.evacuation_status !== 'evacuated' && canSelectCenter(center)" @click="selectCenter(center.id)" class="w-full px-4 py-2 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700">Evacuate</button>
                        <p v-else-if="userHousehold && userHousehold.evacuation_status !== 'evacuated'" class="text-xs text-red-600">This center is full or closed.</p>
                        <p v-else-if="!userHousehold" class="text-xs text-gray-400">No household associated with your account.</p>
                    </div>
                </article>
            </div>
            <p v-if="!centers.length" class="bg-white rounded-xl border border-gray-100 p-12 text-center text-sm text-gray-400">No evacuation centers found.</p>
        </div>
    </AuthenticatedLayout>
</template>