<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ReportResults from '@/Components/ReportResults.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent } from '@/components/ui/card';

const props = defineProps({
    options: Object,
    config: Object,
    results: Object,
});

// --- Builder configuration ----------------------------------------------
const dataset = ref(props.config.dataset);
const groupBy = ref(props.config.group_by);
const selectedStatuses = ref([...props.config.filters.statuses]);
const fromDate = ref(props.config.filters.from || '');
const toDate = ref(props.config.filters.to || '');
const secondaryId = ref(props.config.filters.secondary_id || '');

const groupOptions = computed(() => props.options.groups[dataset.value] || []);
const statusOptions = computed(() => props.options.statuses[dataset.value] || []);
const secondaryMeta = computed(() => props.options.secondary[dataset.value] || null);

// Reset grouping when the dataset changes.
watch(dataset, () => {
    groupBy.value = 'status';
    selectedStatuses.value = [];
});

const toggleStatus = (value) => {
    const index = selectedStatuses.value.indexOf(value);
    if (index === -1) {
        selectedStatuses.value.push(value);
    } else {
        selectedStatuses.value.splice(index, 1);
    }
};

const buildParams = () => ({
    run: 1,
    dataset: dataset.value,
    group_by: groupBy.value,
    statuses: selectedStatuses.value,
    from: fromDate.value,
    to: toDate.value,
    secondary_id: secondaryId.value,
});

const runReport = () => {
    router.get('/reports/census/builder', buildParams(), { preserveState: true });
};

// Opens the current configuration as a printable PDF in a new tab.
const printUrl = computed(() => {
    const params = new URLSearchParams();
    Object.entries(buildParams()).forEach(([key, value]) => {
        if (Array.isArray(value)) {
            value.forEach((v) => params.append(`${key}[]`, v));
        } else if (value !== '' && value !== null && value !== undefined) {
            params.append(key, value);
        }
    });
    return `/reports/census/builder/print?${params.toString()}`;
});

// --- Save as named report ------------------------------------------------
const showSaveForm = ref(false);
const saveForm = useForm({
    name: '',
    description: '',
    dataset: dataset.value,
    group_by: groupBy.value,
    'filters.statuses': [],
    'filters.from': '',
    'filters.to': '',
    'filters.secondary_id': '',
});

const openSaveForm = () => {
    saveForm.dataset = dataset.value;
    saveForm.group_by = groupBy.value;
    saveForm['filters.statuses'] = [...selectedStatuses.value];
    saveForm['filters.from'] = fromDate.value;
    saveForm['filters.to'] = toDate.value;
    saveForm['filters.secondary_id'] = secondaryId.value || '';
    showSaveForm.value = true;
};

const submitSave = () => {
    saveForm.post('/reports/census');
};

const groupLabel = computed(() => {
    const found = groupOptions.value.find((g) => g.value === groupBy.value);
    return found ? found.label : 'Group';
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Census Builder" />

        <div class="space-y-4">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <Link href="/reports/census" class="text-sm text-muted-foreground hover:text-foreground">&larr; Back to Census Reports</Link>
                    <h1 class="text-xl font-bold text-foreground mt-1">Census Builder</h1>
                    <p class="text-sm text-muted-foreground">Gather analytics for raised requests, complaints, and assistance — filtered by statuses. Save any configuration as a reusable report.</p>
                </div>
            </div>

            <!-- Configuration -->
            <Card>
                <CardContent class="p-6 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">Dataset *</label>
                            <Select v-model="dataset" class="mt-1">
                                <option v-for="d in options.datasets" :key="d.value" :value="d.value">{{ d.label }}</option>
                            </Select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">Group By *</label>
                            <Select v-model="groupBy" class="mt-1">
                                <option v-for="g in groupOptions" :key="g.value" :value="g.value">{{ g.label }}</option>
                            </Select>
                        </div>
                        <div v-if="secondaryMeta">
                            <label class="block text-sm font-medium text-muted-foreground">{{ secondaryMeta.label }} Filter</label>
                            <Select v-model="secondaryId" class="mt-1">
                                <option value="">All {{ secondaryMeta.label }}s</option>
                                <option v-for="opt in secondaryMeta.options" :key="opt.id" :value="opt.id">{{ opt.name }}</option>
                            </Select>
                        </div>
                    </div>

                    <!-- Status filters -->
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground mb-2">Status Filters <span class="text-xs text-muted-foreground">(none selected = all statuses)</span></label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="s in statusOptions"
                                :key="s.value"
                                type="button"
                                @click="toggleStatus(s.value)"
                                class="px-3 py-1.5 rounded-full text-xs font-medium border transition-colors capitalize"
                                :class="selectedStatuses.includes(s.value)
                                    ? 'bg-primary text-primary-foreground border-primary'
                                    : 'bg-card text-foreground border-input hover:border-primary/40'"
                            >
                                {{ s.label }}
                            </button>
                        </div>
                    </div>

                    <!-- Date range -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-xl">
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">From Date</label>
                            <Input v-model="fromDate" type="date" class="mt-1" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">To Date</label>
                            <Input v-model="toDate" type="date" class="mt-1" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <Button
                            v-if="results"
                            variant="outline"
                            as-child
                        >
                            <a
                                :href="printUrl"
                                target="_blank"
                                rel="noopener"
                            >
                                Print Report
                            </a>
                        </Button>
                        <Button variant="outline" type="button" @click="openSaveForm">
                            Save Report
                        </Button>
                        <Button type="button" @click="runReport">
                            Run Report
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Results -->
            <ReportResults :results="results" :group-label="groupLabel" />
            <p v-if="!results" class="text-center text-sm text-muted-foreground py-6">Configure the filters above and click "Run Report" to generate analytics.</p>

            <!-- Save form modal -->
            <div v-if="showSaveForm" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 p-4" @click.self="showSaveForm = false">
                <form @submit.prevent="submitSave" class="bg-card rounded-xl shadow-lg p-6 w-full max-w-md space-y-4">
                    <h2 class="text-sm font-semibold text-foreground">Save Current Configuration as Report</h2>
                    <p class="text-xs text-muted-foreground">The currently selected dataset, grouping, and filters will be saved.</p>
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground">Report Name *</label>
                        <Input v-model="saveForm.name" required type="text" placeholder="e.g., Pending Requests by Type" class="mt-1" />
                        <p v-if="saveForm.errors.name" class="text-xs text-destructive mt-1">{{ saveForm.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground">Description</label>
                        <Textarea v-model="saveForm.description" rows="2" class="mt-1" />
                    </div>
                    <div class="rounded-lg bg-primary/5 border p-3 text-xs text-primary">
                        Dataset: <strong>{{ options.datasets.find((d) => d.value === saveForm.dataset)?.label }}</strong> ·
                        Grouped by: <strong>{{ groupLabel }}</strong> ·
                        Statuses: <strong>{{ saveForm['filters.statuses'].length ? saveForm['filters.statuses'].map((s) => s.replace(/_/g, ' ')).join(', ') : 'All' }}</strong>
                    </div>
                    <div class="flex justify-end gap-3">
                        <Button type="button" variant="outline" @click="showSaveForm = false">Cancel</Button>
                        <Button type="submit" :disabled="saveForm.processing">
                            {{ saveForm.processing ? 'Saving...' : 'Save Report' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>