<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    households: Object,
    filters: Object,
    puroks: Array,
});

const search = ref(props.filters.search || '');
const purokId = ref(props.filters.purok_id || '');
let debounceTimer = null;

watch([search, purokId], () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get('/households', {
            search: search.value,
            purok_id: purokId.value,
        }, { preserveState: true, replace: true });
    }, 300);
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Households" />

        <div class="space-y-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Households</h1>
                <p class="text-sm text-gray-500 mt-1">View registered household records.</p>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                <input v-model="search" type="search" placeholder="Search code, address, or head of family..." class="px-3 py-2 rounded-lg border-gray-300 shadow-sm text-sm" />
                <select v-model="purokId" class="px-3 py-2 rounded-lg border-gray-300 shadow-sm text-sm">
                    <option value="">All Puroks</option>
                    <option v-for="purok in puroks" :key="purok.id" :value="purok.id">{{ purok.name }}</option>
                </select>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Household</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Head of family</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Address</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Members</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="household in households.data" :key="household.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-mono text-gray-900">{{ household.household_code }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ household.head_of_family ? `${household.head_of_family.first_name} ${household.head_of_family.last_name}` : '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ household.address }}<span class="block text-xs text-gray-400">{{ household.purok?.name || '-' }}</span></td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ household.members_count }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ household.contact_number || '-' }}</td>
                            </tr>
                            <tr v-if="!households.data.length">
                                <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-400">No households found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination :links="households.links" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>