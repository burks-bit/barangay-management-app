<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent } from '@/components/ui/card';

const props = defineProps({
    log: Object,
});

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        second: '2-digit',
    });
};

const formatJson = (obj) => {
    if (!obj || typeof obj !== 'object') return '-';
    return JSON.stringify(obj, null, 2);
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Audit Log #${log.id}`" />

        <div class="max-w-4xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <Link href="/audit-logs" class="text-sm text-muted-foreground hover:text-foreground">&larr; Back to Audit Logs</Link>
            </div>

            <!-- Header -->
            <Card>
                <CardContent class="p-6">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                        <div>
                            <h1 class="text-lg font-bold text-foreground">Audit Log #{{ log.id }}</h1>
                            <p class="text-sm text-muted-foreground mt-1">
                                <span class="font-medium">{{ log.action }}</span> on
                                <span class="font-medium">{{ log.module }}</span>
                                <span v-if="log.record_type" class="text-xs text-muted-foreground font-mono">
                                    ({{ log.record_type }} #{{ log.record_id }})
                                </span>
                            </p>
                        </div>
                        <StatusBadge :status="log.module" />
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                        <div>
                            <dt class="text-xs text-muted-foreground uppercase tracking-wide">Performed By</dt>
                            <dd class="font-medium text-foreground mt-0.5">{{ log.user?.name || 'System' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-muted-foreground uppercase tracking-wide">Timestamp</dt>
                            <dd class="font-medium text-foreground mt-0.5">{{ formatDate(log.created_at) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-muted-foreground uppercase tracking-wide">IP Address</dt>
                            <dd class="font-medium text-foreground mt-0.5">{{ log.ip_address || 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-muted-foreground uppercase tracking-wide">Record ID</dt>
                            <dd class="font-medium text-foreground mt-0.5">{{ log.record_id || 'N/A' }}</dd>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Old Values -->
            <Card>
                <CardContent class="p-6">
                    <h2 class="text-sm font-semibold text-foreground uppercase tracking-wide mb-3">Old Values</h2>
                    <pre v-if="log.old_values" class="text-xs text-muted-foreground bg-muted rounded-lg p-4 overflow-x-auto whitespace-pre-wrap">{{ formatJson(log.old_values) }}</pre>
                    <p v-else class="text-sm text-muted-foreground">No previous values (new record).</p>
                </CardContent>
            </Card>

            <!-- New Values -->
            <Card>
                <CardContent class="p-6">
                    <h2 class="text-sm font-semibold text-foreground uppercase tracking-wide mb-3">New Values</h2>
                    <pre v-if="log.new_values" class="text-xs text-muted-foreground bg-muted rounded-lg p-4 overflow-x-auto whitespace-pre-wrap">{{ formatJson(log.new_values) }}</pre>
                    <p v-else class="text-sm text-muted-foreground">No new values.</p>
                </CardContent>
            </Card>

            <!-- User Agent -->
            <Card v-if="log.user_agent">
                <CardContent class="p-6">
                    <h2 class="text-sm font-semibold text-foreground uppercase tracking-wide mb-3">User Agent</h2>
                    <p class="text-xs text-muted-foreground break-all">{{ log.user_agent }}</p>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
