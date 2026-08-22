<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({ profiles: Array });

const destroy = (profile) => {
    if (confirm('Delete this barangay profile?')) {
        router.delete(`/barangay/${profile.id}`);
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Barangay Profile" />
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Barangay Profiles</h1>
                    <p class="text-sm text-gray-500">Manage the information of your barangay</p>
                </div>
                <Link href="/barangay/create" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-wider hover:bg-blue-700">
                    + New Profile
                </Link>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <article v-for="profile in profiles" :key="profile.id" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="font-semibold text-lg text-gray-900">{{ profile.name }}</h2>
                    <p class="text-sm text-gray-500 mt-1">{{ profile.address || 'No address' }}</p>
                    <p class="text-xs text-gray-400 mt-2">{{ profile.officials_count }} officials</p>
                    <div class="mt-4 flex gap-2">
                        <Link :href="`/barangay/${profile.id}`" class="action-link text-blue-700">View</Link>
                        <Link :href="`/barangay/${profile.id}/edit`" class="action-link text-indigo-700">Edit</Link>
                        <button @click="destroy(profile)" class="px-3 py-1.5 bg-red-50 text-red-700 rounded-lg text-xs font-medium hover:bg-red-100">Delete</button>
                    </div>
                </article>
            </div>
            <p v-if="!profiles.length" class="bg-white rounded-xl border border-gray-100 p-12 text-center text-sm text-gray-400">No barangay profiles found. Create one to get started.</p>
        </div>
    </AuthenticatedLayout>
</template>
</｜DSML｜tool>