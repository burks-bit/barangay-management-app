<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    user: Object,
    roles: Array,
});

const formatDate = (date) => date ? new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
}) : '-';
</script>

<template>
    <AuthenticatedLayout>
        <Head title="My Profile" />

        <div class="max-w-3xl mx-auto space-y-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900">My Profile</h1>
                <p class="text-sm text-gray-500 mt-1">View your account and resident details.</p>
            </div>

            <section class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-teal-600 px-6 py-7 text-white">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center justify-center w-16 h-16 rounded-full bg-white/20 text-xl font-bold">
                            {{ user.name?.split(' ').map((part) => part[0]).slice(0, 2).join('').toUpperCase() }}
                        </div>
                        <div>
                            <h2 class="text-xl font-bold">{{ user.name }}</h2>
                            <p class="text-blue-100 text-sm">{{ user.email }}</p>
                            <p class="text-blue-100 text-xs mt-1 capitalize">{{ roles.join(', ') || 'Member' }}</p>
                        </div>
                    </div>
                </div>

                <dl class="p-6 grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5 text-sm">
                    <div>
                        <dt class="text-gray-500">Resident ID</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ user.member_profile?.resident_id || '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Account email</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ user.email }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Full name</dt>
                        <dd class="mt-1 font-medium text-gray-900">
                            {{ user.member_profile?.first_name }} {{ user.member_profile?.middle_name }} {{ user.member_profile?.last_name }} {{ user.member_profile?.suffix }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Date of birth</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ formatDate(user.member_profile?.date_of_birth) }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Sex</dt>
                        <dd class="mt-1 font-medium text-gray-900 capitalize">{{ user.member_profile?.sex || '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Civil status</dt>
                        <dd class="mt-1 font-medium text-gray-900 capitalize">{{ user.member_profile?.civil_status || '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Contact number</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ user.member_profile?.contact_number || '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Occupation</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ user.member_profile?.occupation || '-' }}</dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="text-gray-500">Address</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ user.member_profile?.address || '-' }}</dd>
                        <p v-if="user.member_profile?.purok" class="text-xs text-gray-500 mt-1">{{ user.member_profile.purok.name }}</p>
                    </div>
                </dl>

                <div v-if="user.member_profile" class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-sm text-gray-500">Verification status</span>
                    <StatusBadge :status="user.member_profile.verification_status" />
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>