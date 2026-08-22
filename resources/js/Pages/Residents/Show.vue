<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    resident: Object,
});

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
};

</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`${resident.first_name} ${resident.last_name}`" />

        <div class="max-w-4xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <Link href="/residents" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to Residents</Link>
                <div class="flex items-center space-x-2">
                    <Link
                        v-if="$page.props.auth?.permissions?.includes('update residents')"
                        :href="`/residents/${resident.id}/edit`"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700"
                    >Edit</Link>
                    <button
                        v-if="$page.props.auth?.permissions?.includes('verify residents') && resident.verification_status === 'pending'"
                        @click="router.post(`/residents/${resident.id}/verify`)"
                        class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700"
                    >Verify Resident</button>
                </div>
            </div>

            <!-- Profile header -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-8">
                    <div class="flex items-center">
                        <div class="flex items-center justify-center w-20 h-20 rounded-full bg-white/20 text-white text-2xl font-bold backdrop-blur">
                            {{ (resident.first_name[0] || '') + (resident.last_name[0] || '') }}
                        </div>
                        <div class="ml-5 text-white">
                            <h1 class="text-2xl font-bold">{{ resident.first_name }} {{ resident.middle_name }} {{ resident.last_name }} {{ resident.suffix || '' }}</h1>
                            <p class="text-blue-100 text-sm mt-1 font-mono">{{ resident.resident_id }}</p>
                            <div class="mt-2"><StatusBadge :status="resident.verification_status" /></div>
                        </div>
                    </div>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Personal info -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-3">Personal Information</h3>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between py-1 border-b border-gray-50">
                                <dt class="text-gray-500">Date of Birth</dt>
                                <dd class="text-gray-900 font-medium">{{ formatDate(resident.date_of_birth) }}</dd>
                            </div>
                            <div class="flex justify-between py-1 border-b border-gray-50">
                                <dt class="text-gray-500">Age</dt>
                                <dd class="text-gray-900 font-medium">{{ resident.date_of_birth ? Math.floor((Date.now() - new Date(resident.date_of_birth)) / 31557600000) : '-' }}</dd>
                            </div>
                            <div class="flex justify-between py-1 border-b border-gray-50">
                                <dt class="text-gray-500">Sex</dt>
                                <dd class="text-gray-900 font-medium capitalize">{{ resident.sex }}</dd>
                            </div>
                            <div class="flex justify-between py-1 border-b border-gray-50">
                                <dt class="text-gray-500">Civil Status</dt>
                                <dd class="text-gray-900 font-medium capitalize">{{ resident.civil_status }}</dd>
                            </div>
                            <div class="flex justify-between py-1 border-b border-gray-50">
                                <dt class="text-gray-500">Occupation</dt>
                                <dd class="text-gray-900 font-medium">{{ resident.occupation || '-' }}</dd>
                            </div>
                            <div class="flex justify-between py-1">
                                <dt class="text-gray-500">Residency Status</dt>
                                <dd class="text-gray-900 font-medium capitalize">{{ resident.residency_status }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Contact & address -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-3">Contact & Address</h3>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between py-1 border-b border-gray-50">
                                <dt class="text-gray-500">Address</dt>
                                <dd class="text-gray-900 font-medium text-right max-w-[60%]">{{ resident.address }}</dd>
                            </div>
                            <div class="flex justify-between py-1 border-b border-gray-50">
                                <dt class="text-gray-500">Purok</dt>
                                <dd class="text-gray-900 font-medium">{{ resident.purok?.name || '-' }}</dd>
                            </div>
                            <div class="flex justify-between py-1 border-b border-gray-50">
                                <dt class="text-gray-500">Household</dt>
                                <dd class="text-gray-900 font-medium">{{ resident.household?.household_code || '-' }}</dd>
                            </div>
                            <div class="flex justify-between py-1 border-b border-gray-50">
                                <dt class="text-gray-500">Contact Number</dt>
                                <dd class="text-gray-900 font-medium">{{ resident.contact_number || '-' }}</dd>
                            </div>
                            <div class="flex justify-between py-1 border-b border-gray-50">
                                <dt class="text-gray-500">Email</dt>
                                <dd class="text-gray-900 font-medium">{{ resident.email || resident.user?.email || '-' }}</dd>
                            </div>
                            <div class="flex justify-between py-1">
                                <dt class="text-gray-500">Emergency Contact</dt>
                                <dd class="text-gray-900 font-medium text-right">
                                    {{ resident.emergency_contact_name || '-' }}
                                    <span v-if="resident.emergency_contact_number" class="block text-xs text-gray-500">{{ resident.emergency_contact_number }}</span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Verification info -->
                <div v-if="resident.verified_at" class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                    <p class="text-xs text-gray-500">
                        {{ resident.verification_status === 'verified' ? 'Verified' : 'Reviewed' }}
                        by <span class="font-medium text-gray-700">{{ resident.verifier?.name || 'System' }}</span>
                        on {{ formatDate(resident.verified_at) }}
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>