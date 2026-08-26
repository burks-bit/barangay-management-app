<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { Card, CardContent } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Plus, Eye, Pencil, CheckCircle2 } from 'lucide-vue-next';

const props = defineProps({
    residents: Object,
    filters: Object,
    puroks: Array,
});

const search = ref(props.filters.search || '');
const purokId = ref(props.filters.purok_id || '');
const verificationStatus = ref(props.filters.verification_status || '');

let debounceTimer = null;
watch([search, purokId, verificationStatus], () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get('/residents', {
            search: search.value,
            purok_id: purokId.value,
            verification_status: verificationStatus.value,
        }, {
            preserveState: true,
            replace: true,
        });
    }, 300);
});

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Residents" />

        <div class="space-y-4">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-foreground">Residents</h1>
                    <p class="text-sm text-muted-foreground">Manage resident records and verifications</p>
                </div>
                <Button
                    v-if="$page.props.auth?.permissions?.includes('create residents')"
                    as-child
                >
                    <Link href="/residents/create" class="inline-flex items-center gap-2">
                        <Plus class="h-4 w-4" /> Add Resident
                    </Link>
                </Button>
            </div>

            <!-- Filters -->
            <Card>
                <CardContent class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <Input
                            v-model="search"
                            type="text"
                            placeholder="Search by name, ID, or contact..."
                        />
                        <Select v-model="purokId">
                            <option value="">All Puroks</option>
                            <option v-for="purok in puroks" :key="purok.id" :value="purok.id">{{ purok.name }}</option>
                        </Select>
                        <Select v-model="verificationStatus">
                            <option value="">All Verification Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="verified">Verified</option>
                            <option value="rejected">Rejected</option>
                            <option value="inactive">Inactive</option>
                        </Select>
                    </div>
                </CardContent>
            </Card>

            <!-- Table -->
            <Card class="overflow-hidden">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Resident ID</TableHead>
                            <TableHead>Name</TableHead>
                            <TableHead>Purok</TableHead>
                            <TableHead>Contact</TableHead>
                            <TableHead>Verification</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="resident in residents.data" :key="resident.id">
                            <TableCell class="font-mono text-muted-foreground">{{ resident.resident_id }}</TableCell>
                            <TableCell>
                                <div class="flex items-center">
                                    <div class="flex items-center justify-center w-8 h-8 rounded-full bg-primary/10 text-primary text-xs font-semibold mr-3">
                                        {{ (resident.first_name[0] || '') + (resident.last_name[0] || '') }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-foreground">
                                            {{ resident.first_name }} {{ resident.middle_name ? resident.middle_name.charAt(0) + '. ' : '' }}{{ resident.last_name }}
                                        </div>
                                        <div class="text-xs text-muted-foreground capitalize">{{ resident.sex }} &middot; {{ resident.age ?? '-' }} yrs</div>
                                    </div>
                                </div>
                            </TableCell>
                            <TableCell class="text-muted-foreground">{{ resident.purok?.name || '-' }}</TableCell>
                            <TableCell class="text-muted-foreground">{{ resident.contact_number || '-' }}</TableCell>
                            <TableCell><StatusBadge :status="resident.verification_status" /></TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-2">
                                    <Button variant="ghost" size="sm" as-child>
                                        <Link :href="`/residents/${resident.id}`"><Eye class="h-4 w-4" /> View</Link>
                                    </Button>
                                    <Button
                                        v-if="$page.props.auth?.permissions?.includes('update residents')"
                                        variant="ghost"
                                        size="sm"
                                        as-child
                                    >
                                        <Link :href="`/residents/${resident.id}/edit`"><Pencil class="h-4 w-4" /> Edit</Link>
                                    </Button>
                                    <Button
                                        v-if="$page.props.auth?.permissions?.includes('verify residents') && resident.verification_status === 'pending'"
                                        variant="ghost"
                                        size="sm"
                                        class="text-green-600 hover:text-green-900"
                                        @click="router.post(`/residents/${resident.id}/verify`)"
                                    >
                                        <CheckCircle2 class="h-4 w-4" /> Verify
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="!residents.data.length">
                            <TableCell colspan="6" class="py-12 text-center text-muted-foreground">No residents found</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
                <Pagination :links="residents.links" />
            </Card>
        </div>
    </AuthenticatedLayout>
</template>