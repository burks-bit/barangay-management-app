<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <div v-if="links.length > 3" class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 mt-4 rounded-b-xl">
        <div class="flex flex-1 justify-between sm:hidden">
            <Link
                v-for="(link, key) in links"
                :key="key"
                :href="link.url || '#'"
                v-show="link.label.includes('Previous') || link.label.includes('Next')"
                class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                :class="{ 'opacity-50 cursor-not-allowed': !link.url }"
                v-html="link.label"
            />
        </div>
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                <Link
                    v-for="(link, key) in links"
                    :key="key"
                    :href="link.url || '#'"
                    class="relative inline-flex items-center px-4 py-2 text-sm font-medium border"
                    :class="[
                        link.active
                            ? 'z-10 bg-blue-600 border-blue-600 text-white'
                            : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50',
                        key === 0 ? 'rounded-l-md' : '',
                        key === links.length - 1 ? 'rounded-r-md' : '',
                        !link.url ? 'opacity-50 cursor-not-allowed' : '',
                    ]"
                    v-html="link.label"
                />
            </nav>
        </div>
    </div>
</template>