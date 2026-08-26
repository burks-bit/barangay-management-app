<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Select } from '@/components/ui/select';
import { Card, CardContent } from '@/components/ui/card';
import { Pencil, Trash2, Plus } from 'lucide-vue-next';

defineProps({ calamities: Object, filters: Object });

const statuses = ['reported', 'active', 'under_response', 'contained', 'resolved', 'archived'];
const filter = (event) => router.get('/calamities', { status: event.target.value }, { preserveState: true, replace: true });
const formatDate = (date) => date ? new Date(date).toLocaleDateString() : '-';

const destroy = (calamity) => {
    if (confirm('Delete this calamity?')) router.delete(`/calamities/${calamity.id}`);
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Calamities" />
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-foreground">Calamities</h1>
                    <p class="text-sm text-muted-foreground mt-1">Monitor reported and active calamities.</p>
                </div>
                <div class="flex items-center gap-2">
                    <Button v-if="$page.props.auth?.permissions?.includes('create calamities')" size="sm" as-child>
                        <Link href="/calamities/create"><Plus class="h-4 w-4" /> Add Calamity</Link>
                    </Button>
                </div>
            </div>

            <Select :model-value="filters.status || ''" class="w-full sm:w-auto" @update:model-value="(v) => router.get('/calamities', { status: v }, { preserveState: true, replace: true })">
                <option value="">All statuses</option>
                <option v-for="status in statuses" :key="status" :value="status">{{ status.replaceAll('_',' ') }}</option>
            </Select>

            <Card class="overflow-hidden">
                <CardContent v-if="calamities.data.length" class="divide-y divide-border p-0">
                    <article v-for="calamity in calamities.data" :key="calamity.id" class="p-5">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                            <div>
                                <h2 class="font-semibold text-foreground">{{ calamity.name }}</h2>
                                <p class="text-xs font-mono text-muted-foreground mt-1">{{ calamity.event_code }}</p>
                                <p class="text-sm text-muted-foreground mt-1 capitalize">{{ calamity.type }} | Started {{ formatDate(calamity.started_at) }}</p>
                                <p v-if="calamity.description" class="text-sm text-muted-foreground mt-2">{{ calamity.description }}</p>
                                <div class="flex flex-wrap gap-3 text-xs text-muted-foreground mt-2">
                                    <span>{{ calamity.affected_households }} households</span>
                                    <span>{{ calamity.affected_residents }} residents</span>
                                </div>
                            </div>
                            <div class="text-left sm:text-right">
                                <StatusBadge :status="calamity.status" />
                                <div v-if="$page.props.auth?.permissions?.includes('update calamities') || $page.props.auth?.permissions?.includes('delete calamities')" class="mt-2 flex gap-2 sm:justify-end">
                                    <Button v-if="$page.props.auth?.permissions?.includes('update calamities')" variant="ghost" size="sm" as-child>
                                        <Link :href="`/calamities/${calamity.id}/edit`"><Pencil class="h-4 w-4" /> Edit</Link>
                                    </Button>
                                    <Button v-if="$page.props.auth?.permissions?.includes('delete calamities')" variant="ghost" size="sm" class="text-destructive" @click="destroy(calamity)">
                                        <Trash2 class="h-4 w-4" /> Delete
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </article>
                </CardContent>
                <p v-else class="p-12 text-center text-sm text-muted-foreground">No calamities found.</p>
                <Pagination :links="calamities.links" />
            </Card>
        </div>
    </AuthenticatedLayout>
</template>