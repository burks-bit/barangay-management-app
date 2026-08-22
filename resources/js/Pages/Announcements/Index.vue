<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    announcements: Array,
});

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Announcements" />

        <div class="max-w-4xl mx-auto space-y-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Announcements</h1>
                <p class="text-sm text-gray-500 mt-1">Updates and notices from the barangay.</p>
            </div>

            <div v-if="announcements.length" class="space-y-3">
                <article
                    v-for="announcement in announcements"
                    :key="announcement.id"
                    class="bg-white rounded-xl shadow-sm border border-gray-100 p-5"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-base font-semibold text-gray-900">{{ announcement.title }}</h2>
                            <p class="text-xs text-gray-500 mt-1">{{ formatDate(announcement.published_at) }}</p>
                        </div>
                        <span class="text-xs font-medium capitalize text-gray-500">{{ announcement.priority }}</span>
                    </div>
                    <p class="text-sm text-gray-700 mt-3 whitespace-pre-line">{{ announcement.content }}</p>
                </article>
            </div>
            <p v-else class="bg-white rounded-xl border border-gray-100 p-8 text-center text-sm text-gray-400">No announcements</p>
        </div>
    </AuthenticatedLayout>
</template>
