<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { Card, CardContent } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Plus } from 'lucide-vue-next';

const props = defineProps({
    households: Object,
    filters: Object,
    puroks: Array,
});

const search = ref(props.filters.search || '');
const purokId = ref(props.filters.purok_id || '');
let debounceTimer = null;

watch([search, purokId], () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get('/households', {
            search: search.value,
            purok_id: purokId.value,
        }, { preserveState: true, replace: true });
    }, 300);
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Households" />

        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-foreground">Households</h1>
                    <p class="text-sm text-muted-foreground mt-1">View registered household records.</p>
                </div>
                <Button v-if="$page.props.auth?.permissions?.includes('create households')" as-child>
                    <Link href="/households/create"><Plus class="h-4 w-4" /> Register Household</Link>
                </Button>
            </div>

            <Card>
                <CardContent class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                    <Input v-model="search" type="search" placeholder="Search code, address, or head of family..." />
                    <Select v-model="purokId">
                        <option value="">All Puroks</option>
                        <option v-for="purok in puroks" :key="purok.id" :value="purok.id">{{ purok.name }}</option>
                    </Select>
                </CardContent>
            </Card>

            <Card class="overflow-hidden">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Household</TableHead>
                            <TableHead>Head of family</TableHead>
                            <TableHead>Address</TableHead>
                            <TableHead>Members</TableHead>
                            <TableHead>Contact</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="household in households.data" :key="household.id">
                            <TableCell class="font-mono text-foreground">{{ household.household_code }}</TableCell>
                            <TableCell class="text-foreground">{{ household.head_of_family ? `${household.head_of_family.first_name} ${household.head_of_family.last_name}` : '-' }}</TableCell>
                            <TableCell class="text-muted-foreground">{{ household.address }}<span class="block text-xs text-muted-foreground">{{ household.purok?.name || '-' }}</span></TableCell>
                            <TableCell class="text-muted-foreground">{{ household.members_count }}</TableCell>
                            <TableCell class="text-muted-foreground">{{ household.contact_number || '-' }}</TableCell>
                        </TableRow>
                        <TableRow v-if="!households.data.length">
                            <TableCell colspan="5" class="py-12 text-center text-muted-foreground">No households found.</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
                <Pagination :links="households.links" />
            </Card>
        </div>
    </AuthenticatedLayout>
</template>