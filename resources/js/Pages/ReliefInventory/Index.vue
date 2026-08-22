<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

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
            <div><h1 class="text-xl font-bold text-gray-900">Relief Inventory</h1><p class="text-sm text-gray-500 mt-1">View available relief supplies and stock levels.</p></div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                <input v-model="search" type="search" placeholder="Search item or SKU..." class="px-3 py-2 rounded-lg border-gray-300 text-sm" />
                <select v-model="category" class="px-3 py-2 rounded-lg border-gray-300 text-sm"><option value="">All categories</option><option v-for="itemCategory in categories" :key="itemCategory" :value="itemCategory">{{ itemCategory }}</option></select>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200"><thead class="bg-gray-50"><tr><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reorder level</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th></tr></thead>
                <tbody class="divide-y divide-gray-100"><tr v-for="item in items.data" :key="item.id" class="hover:bg-gray-50"><td class="px-6 py-4"><p class="text-sm font-medium text-gray-900">{{ item.name }}</p><p class="text-xs font-mono text-gray-500">{{ item.sku }} | per {{ item.unit }}</p></td><td class="px-6 py-4 text-sm text-gray-600 capitalize">{{ item.category || '-' }}</td><td class="px-6 py-4 text-sm font-medium text-gray-900">{{ item.current_stock }}</td><td class="px-6 py-4 text-sm text-gray-600">{{ item.reorder_level }}</td><td class="px-6 py-4"><span :class="item.current_stock <= item.reorder_level ? 'text-red-700 bg-red-50' : 'text-green-700 bg-green-50'" class="rounded-full px-2 py-1 text-xs font-medium">{{ item.current_stock <= item.reorder_level ? 'Low stock' : 'Available' }}</span></td></tr><tr v-if="!items.data.length"><td colspan="5" class="px-6 py-12 text-center text-sm text-gray-400">No inventory items found.</td></tr></tbody></table></div>
                <Pagination :links="items.links" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
