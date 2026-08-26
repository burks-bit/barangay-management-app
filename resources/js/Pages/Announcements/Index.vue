<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

const props = defineProps({
    announcements: Array,
});

const canManage = computed(() => {
    const roles = usePage().props.auth?.roles || [];
    return roles.includes('admin') || roles.includes('moderator');
});

onMounted(() => {
    router.post('/announcements/seen', {}, { preserveScroll: true, preserveState: true });
});

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const typeLabels = {
    calamity_warning: 'Calamity Warning',
    evacuation_notice: 'Evacuation Notice',
    barangay_announcement: 'Barangay Announcement',
    community_event: 'Community Event',
    service_interruption: 'Service Interruption',
    emergency_instruction: 'Emergency Instruction',
    general: 'General',
};

const priorityClass = (priority) => ({
    normal: 'bg-blue-50 text-blue-700 border-blue-200',
    important: 'bg-amber-100 text-amber-700 border-amber-200',
    emergency: 'bg-red-100 text-red-700 border-red-200',
}[priority] || 'bg-gray-100 text-gray-600 border-gray-200');
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Announcements" />

        <div class="max-w-4xl mx-auto space-y-4">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-foreground">Announcements</h1>
                    <p class="text-sm text-muted-foreground mt-1">Updates and notices from the barangay.</p>
                </div>
                <Button v-if="canManage" as-child>
                    <Link href="/announcements/manage">Manage</Link>
                </Button>
            </div>

            <div v-if="announcements.length" class="space-y-3">
                <Card v-for="announcement in announcements" :key="announcement.id">
                    <CardContent class="p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-base font-semibold text-foreground">{{ announcement.title }}</h2>
                                <p class="text-xs text-muted-foreground mt-1">{{ formatDate(announcement.published_at) }}</p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <Badge variant="secondary">{{ typeLabels[announcement.type] || announcement.type }}</Badge>
                                <Badge :class="priorityClass(announcement.priority)" class="capitalize">{{ announcement.priority }}</Badge>
                            </div>
                        </div>
                        <p class="text-sm text-muted-foreground mt-3 whitespace-pre-line">{{ announcement.content }}</p>
                    </CardContent>
                </Card>
            </div>
            <p v-else class="bg-card rounded-xl border p-8 text-center text-sm text-muted-foreground">No announcements</p>
        </div>
    </AuthenticatedLayout>
</template>