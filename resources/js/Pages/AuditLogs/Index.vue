<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { Card, CardContent } from '@/components/ui/card';
import { Search, Filter, Eye } from 'lucide-vue-next';

const props = defineProps({
    logs: Object,
    filters: Object,
    modules: Array,
    actions: Array,
    users: Array,
});

const search = ref(props.filters.search || '');
const moduleFilter = ref(props.filters.module || '');
const actionFilter = ref(props.filters.action || '');
const userFilter = ref(props.filters.user_id || '');

let debounceTimer = null;
watch([search, moduleFilter, actionFilter, userFilter], () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get('/audit-logs', {
            search: search.value,
            module: moduleFilter.value,
            action: actionFilter.value,
            user_id: userFilter.value,
        }, {
            preserveState: true,
            replace: true,
        });
    }, 300);
});

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Audit Logs" />

        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-foreground">Audit Logs</h1>
                    <p class="text-sm text-muted-foreground mt-1">Track all system actions and changes.</p>
                </div>
            </div>

            <!-- Filters -->
            <Card>
                <CardContent class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div class="relative">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                            <Input v-model="search" type="text" placeholder="Search actions, modules, users..." class="pl-10" />
                        </div>
                        <Select v-model="moduleFilter">
                            <option value="">All Modules</option>
                            <option v-for="mod in modules" :key="mod" :value="mod">{{ mod }}</option>
                        </Select>
                        <Select v-model="actionFilter">
                            <option value="">All Actions</option>
                            <option v-for="act in actions" :key="act" :value="act">{{ act }}</option>
                        </Select>
                        <Select v-model="userFilter">
                            <option value="">All Users</option>
                            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                        </Select>
                    </div>
                </CardContent>
            </Card>

            <!-- List -->
            <Card class="overflow-hidden">
                <CardContent v-if="logs.data.length" class="p-0">
                    <div class="divide-y divide-border">
                        <div v-for="log in logs.data" :key="log.id" class="p-4 hover:bg-muted/50 transition-colors">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-sm font-medium text-foreground">{{ log.action }}</span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-primary">
                                            {{ log.module }}
                                        </span>
                                        <span v-if="log.record_type" class="text-xs text-muted-foreground font-mono">
                                            {{ log.record_type }} #{{ log.record_id }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-muted-foreground mt-1">
                                        {{ log.user?.name || 'System' }} &middot; {{ formatDate(log.created_at) }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <Button variant="ghost" size="sm" as-child>
                                        <Link :href="`/audit-logs/${log.id}`"><Eye class="h-4 w-4" /> View</Link>
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <Pagination :links="logs.links" />
                </CardContent>
                <p v-else class="text-sm text-muted-foreground text-center py-12">No audit log entries found.</p>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
