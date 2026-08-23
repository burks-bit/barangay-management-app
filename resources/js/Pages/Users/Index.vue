<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    users: Array,
    roles: Array,
});

const updateRole = (user, role) => {
    useForm({ role }).put(`/users/${user.id}/role`, { preserveScroll: true });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Users and Roles" />
        <div class="space-y-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Users and Roles</h1>
                <p class="text-sm text-gray-500">Manage account access for barangay users.</p>
            </div>
            <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            <tr><th class="px-6 py-3">Name</th><th class="px-6 py-3">Email</th><th class="px-6 py-3">Role</th><th class="px-6 py-3">Status</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="user in props.users" :key="user.id">
                                <td class="whitespace-nowrap px-6 py-4 font-medium text-gray-900">{{ user.name }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-gray-600">{{ user.email }}</td>
                                <td class="px-6 py-4">
                                    <select :value="user.roles?.[0]?.name" class="w-full max-w-40" @change="updateRole(user, $event.target.value)">
                                        <option v-for="role in props.roles" :key="role" :value="role">{{ role }}</option>
                                    </select>
                                </td>
                                <td class="px-6 py-4"><span :class="user.is_active ? 'text-green-700' : 'text-gray-400'">{{ user.is_active ? 'Active' : 'Inactive' }}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
