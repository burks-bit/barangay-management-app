<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Download, Eye, X, Plus } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Select } from '@/components/ui/select';
import { Card, CardContent } from '@/components/ui/card';

const props = defineProps({
    requests: Object,
    filters: Object,
});

const status = ref(props.filters.status || '');
const selectedDocument = ref(null);

let debounceTimer = null;
watch(status, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get('/my-requests', { status: status.value }, { preserveState: true, replace: true });
    }, 300);
});

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="My Requests" />

        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-foreground">My Requests</h1>
                    <p class="text-sm text-muted-foreground">Track your barangay document requests</p>
                </div>
                <Button as-child>
                    <Link href="/my-requests/create">
                        <Plus class="h-4 w-4" /> New Request
                    </Link>
                </Button>
            </div>

            <!-- Filter -->
            <Card>
                <CardContent class="p-4">
                    <Select v-model="status">
                        <option value="">All Statuses</option>
                        <option value="submitted">Submitted</option>
                        <option value="for_verification">For Verification</option>
                        <option value="approved">Approved</option>
                        <option value="processing">Processing</option>
                        <option value="ready_for_release">Ready for Release</option>
                        <option value="released">Released</option>
                        <option value="rejected">Rejected</option>
                        <option value="cancelled">Cancelled</option>
                    </Select>
                </CardContent>
            </Card>

            <!-- List -->
            <Card class="overflow-hidden">
                <CardContent v-if="requests.data.length" class="divide-y divide-border p-0">
                    <div v-for="request in requests.data" :key="request.id" class="px-6 py-4 hover:bg-muted/50 transition-colors">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-foreground">{{ request.request_type?.name }}</p>
                                <p class="text-xs text-muted-foreground mt-0.5 font-mono">{{ request.tracking_number }}</p>
                                <p class="text-xs text-muted-foreground mt-1 truncate">{{ request.purpose }}</p>
                            </div>
                            <div class="text-right flex-shrink-0 space-y-2">
                                <StatusBadge :status="request.status" />
                                <p class="text-xs text-muted-foreground">{{ formatDate(request.submitted_at) }}</p>
                                <div v-if="request.status === 'released'" class="flex justify-end gap-2">
                                    <button type="button" title="View document" aria-label="View document" class="icon-button" @click="selectedDocument = request"><Eye class="h-4 w-4" /></button>
                                    <a :href="`/requests/${request.id}/download`" title="Download document" aria-label="Download document" class="icon-button"><Download class="h-4 w-4" /></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
                <p v-else class="text-sm text-muted-foreground text-center py-12">No requests yet. Create your first request!</p>
                <Pagination :links="requests.links" />
            </Card>
            <div v-if="selectedDocument" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="selectedDocument = null">
                <div class="max-h-[85vh] w-full max-w-3xl overflow-hidden rounded-xl bg-card shadow-xl">
                    <div class="flex items-center justify-between border-b px-6 py-4"><h2 class="font-semibold text-foreground">{{ selectedDocument.request_type?.name }}</h2><button type="button" title="Close" aria-label="Close" class="icon-button" @click="selectedDocument = null"><X class="h-4 w-4" /></button></div>
                    <div class="prose max-w-none overflow-y-auto p-6 text-sm" v-html="selectedDocument.document_content"></div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>