<script setup>
import { computed } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

const props = defineProps({
    results: Object,
    groupLabel: { type: String, default: 'Group' },
});

const maxCount = computed(() =>
    Math.max(1, ...(props.results?.rows || []).map((r) => r.count))
);

const maxMonthly = computed(() =>
    Math.max(1, ...(props.results?.monthly || []).map((m) => m.count))
);
</script>

<template>
    <div v-if="results" class="space-y-4">
        <!-- Summary -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <Card>
                <CardContent class="p-4">
                    <p class="text-xs text-muted-foreground">Total Records</p>
                    <p class="text-2xl font-bold text-foreground mt-1">{{ results.total.toLocaleString() }}</p>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="p-4">
                    <p class="text-xs text-muted-foreground">Breakdown Groups</p>
                    <p class="text-2xl font-bold text-foreground mt-1">{{ results.rows.length }}</p>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="p-4">
                    <p class="text-xs text-muted-foreground">Generated At</p>
                    <p class="text-sm font-medium text-foreground mt-2">{{ new Date(results.generated_at).toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' }) }}</p>
                </CardContent>
            </Card>
        </div>

        <!-- Breakdown -->
        <Card class="overflow-hidden">
            <CardHeader class="border-b">
                <CardTitle class="text-sm">Breakdown by {{ groupLabel }}</CardTitle>
            </CardHeader>
            <CardContent v-if="results.rows.length" class="divide-y divide-border p-0">
                <div v-for="row in results.rows" :key="row.label" class="px-6 py-3 flex items-center gap-4">
                    <span class="text-sm text-muted-foreground w-48 shrink-0 truncate" :title="row.label">{{ row.label }}</span>
                    <div class="flex-1 h-5 bg-muted rounded-full overflow-hidden">
                        <div class="h-full bg-primary rounded-full transition-all" :style="{ width: `${(row.count / maxCount) * 100}%` }"></div>
                    </div>
                    <span class="text-sm font-semibold text-foreground w-16 text-right">{{ row.count.toLocaleString() }}</span>
                    <span class="text-xs text-muted-foreground w-14 text-right">{{ results.total ? ((row.count / results.total) * 100).toFixed(1) : 0 }}%</span>
                </div>
            </CardContent>
            <p v-else class="px-6 py-8 text-center text-sm text-muted-foreground">No records match the selected filters.</p>
        </Card>

        <!-- Monthly trend -->
        <Card class="overflow-hidden">
            <CardHeader class="border-b">
                <CardTitle class="text-sm">Last 6 Months Trend</CardTitle>
            </CardHeader>
            <CardContent v-if="results.monthly.length" class="px-6 py-6 flex items-end justify-between gap-3 h-44">
                <div v-for="month in results.monthly" :key="month.month" class="flex-1 flex flex-col items-center gap-2">
                    <span class="text-xs font-medium text-foreground">{{ month.count }}</span>
                    <div class="w-full max-w-[48px] bg-primary rounded-t-md transition-all" :style="{ height: `${Math.max(4, (month.count / maxMonthly) * 100)}%` }"></div>
                    <span class="text-xs text-muted-foreground whitespace-nowrap">{{ new Date(`${month.month}-01`).toLocaleDateString('en-US', { month: 'short', year: '2-digit' }) }}</span>
                </div>
            </CardContent>
            <p v-else class="px-6 py-8 text-center text-sm text-muted-foreground">No records in the last 6 months.</p>
        </Card>
    </div>
</template>