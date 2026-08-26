<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { Card, CardContent } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';

const props = defineProps({ items: Object, categories: Array, filters: Object });
const search = ref(props.filters.search || '');
const category = ref(props.filters.category || '');
let timer = null;
watch([search, category], () => {
    clearTimeout(timer);
    timer = setTimeout(() => router.get('/relief-inventory', { search: search.value, category: category.value }, { preserveState: true, replace: true }), 300);
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Relief Inventory" />
        <div class="space-y-4">
            <div><h1 class="text-xl font-bold text-foreground">Relief Inventory</h1><p class="text-sm text-muted-foreground mt-1">View available relief supplies and stock levels.</p></div>
            <Card>
                <CardContent class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                    <Input v-model="search" type="search" placeholder="Search item or SKU..." />
                    <Select v-model="category"><option value="">All categories</option><option v-for="itemCategory in categories" :key="itemCategory" :value="itemCategory">{{ itemCategory }}</option></Select>
                </CardContent>
            </Card>
            <Card class="overflow-hidden">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Item</TableHead>
                            <TableHead>Category</TableHead>
                            <TableHead>Stock</TableHead>
                            <TableHead>Reorder level</TableHead>
                            <TableHead>Status</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="item in items.data" :key="item.id" class="hover:bg-muted/50">
                            <TableCell><p class="text-sm font-medium text-foreground">{{ item.name }}</p><p class="text-xs font-mono text-muted-foreground">{{ item.sku }} | per {{ item.unit }}</p></TableCell>
                            <TableCell class="text-muted-foreground capitalize">{{ item.category || '-' }}</TableCell>
                            <TableCell class="font-medium text-foreground">{{ item.current_stock }}</TableCell>
                            <TableCell class="text-muted-foreground">{{ item.reorder_level }}</TableCell>
                            <TableCell>
                                <Badge :class="item.current_stock <= item.reorder_level ? 'bg-red-100 text-red-700 border-red-200' : 'bg-green-100 text-green-700 border-green-200'">
                                    {{ item.current_stock <= item.reorder_level ? 'Low stock' : 'Available' }}
                                </Badge>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="!items.data.length"><TableCell colspan="5" class="py-12 text-center text-muted-foreground">No inventory items found.</TableCell></TableRow>
                    </TableBody>
                </Table>
                <Pagination :links="items.links" />
            </Card>
        </div>
    </AuthenticatedLayout>
</template>