<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Select } from '@/components/ui/select';
import { Card, CardContent } from '@/components/ui/card';
import { Plus } from 'lucide-vue-next';

defineProps({
    requests: Object,
    filters: Object,
});

const formatDate = (date) => date ? new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : '-';
</script>

<template>
    <AuthenticatedLayout>
        <Head title="My Assistance Requests" />

        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-foreground">My Assistance Requests</h1>
                    <p class="text-sm text-muted-foreground mt-1">Track assistance requests you have submitted.</p>
                </div>
                <Button as-child>
                    <Link href="/my-assistance/create"><Plus class="h-4 w-4" /> Request Assistance</Link>
                </Button>
            </div>

            <Card>
                <CardContent class="p-4">
                    <Select :model-value="filters.status || ''" @update:model-value="(v) => router.get('/my-assistance', { status: v }, { preserveState: true, replace: true })">
                        <option value="">All Statuses</option>
                        <option v-for="status in ['submitted', 'for_verification', 'under_assessment', 'approved', 'rejected', 'for_release', 'released', 'cancelled']" :key="status" :value="status">{{ status.replaceAll('_', ' ') }}</option>
                    </Select>
                </CardContent>
            </Card>

            <Card class="overflow-hidden">
                <CardContent v-if="requests.data.length" class="divide-y divide-border p-0">
                    <div v-for="request in requests.data" :key="request.id" class="px-6 py-4 flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-foreground">{{ request.assistanceType?.name }}</p>
                            <p class="text-xs font-mono text-muted-foreground mt-1">{{ request.assistance_code }}</p>
                            <p class="text-sm text-muted-foreground mt-2">{{ request.reason }}</p>
                            <p class="text-xs text-muted-foreground mt-2">Submitted {{ formatDate(request.created_at) }}</p>
                        </div>
                        <StatusBadge :status="request.status" />
                    </div>
                </CardContent>
                <p v-else class="text-sm text-muted-foreground text-center py-12">No assistance requests yet.</p>
                <Pagination :links="requests.links" />
            </Card>
        </div>
    </AuthenticatedLayout>
</template>