<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ReportResults from '@/Components/ReportResults.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';

const props = defineProps({
    report: Object,
    options: Object,
    config: Object,
    results: Object,
});

const datasetLabel = computed(() =>
    props.options.datasets.find((d) => d.value === props.config.dataset)?.label || props.config.dataset
);

const groupLabel = computed(() => {
    const groups = props.options.groups[props.config.dataset] || [];
    return groups.find((g) => g.value === props.config.group_by)?.label || props.config.group_by;
});

const secondaryLabel = computed(() => {
    const id = props.config.filters.secondary_id;
    if (!id) return null;
    const meta = props.options.secondary[props.config.dataset];
    return meta?.options.find((o) => o.id === Number(id))?.name || null;
});

const formatDate = (date) => date ? new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : '—';
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="report.name" />

        <div class="space-y-4">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <Link href="/reports/census" class="text-sm text-muted-foreground hover:text-foreground">&larr; Back to Census Reports</Link>
                    <h1 class="text-xl font-bold text-foreground mt-1">{{ report.name }}</h1>
                    <p v-if="report.description" class="text-sm text-muted-foreground">{{ report.description }}</p>
                </div>
                <div class="flex items-center gap-2 whitespace-nowrap">
                    <Button variant="outline" as-child>
                        <a
                            :href="`/reports/census/${report.id}/print`"
                            target="_blank"
                            rel="noopener"
                        >
                            Print Report
                        </a>
                    </Button>
                    <Button as-child>
                        <Link href="/reports/census/builder">Build Another Report</Link>
                    </Button>
                </div>
            </div>

            <!-- Configuration summary -->
            <Card>
                <CardContent class="p-6">
                    <h2 class="text-sm font-semibold text-foreground mb-3">Report Configuration</h2>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                        <div>
                            <dt class="text-xs text-muted-foreground">Dataset</dt>
                            <dd class="font-medium text-foreground mt-0.5">{{ datasetLabel }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-muted-foreground">Grouped By</dt>
                            <dd class="font-medium text-foreground mt-0.5">{{ groupLabel }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-muted-foreground">Status Filters</dt>
                            <dd class="mt-1">
                                <template v-if="config.filters.statuses.length">
                                    <span
                                        v-for="s in config.filters.statuses"
                                        :key="s"
                                        class="inline-flex items-center mr-1 mb-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-primary/10 text-primary capitalize"
                                    >{{ s.replace(/_/g, ' ') }}</span>
                                </template>
                                <span v-else class="font-medium text-foreground">All statuses</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-muted-foreground">Date Range / {{ options.secondary[config.dataset]?.label || 'Filter' }}</dt>
                            <dd class="font-medium text-foreground mt-0.5">
                                {{ config.filters.from || 'Start' }} – {{ config.filters.to || 'Present' }}
                                <span v-if="secondaryLabel" class="block text-xs text-muted-foreground">{{ secondaryLabel }}</span>
                            </dd>
                        </div>
                    </dl>
                    <p class="text-xs text-muted-foreground mt-4">Created by {{ report.creator?.name || '—' }} on {{ formatDate(report.created_at) }}</p>
                </CardContent>
            </Card>

            <!-- Results -->
            <ReportResults :results="results" :group-label="groupLabel" />
        </div>
    </AuthenticatedLayout>
</template>