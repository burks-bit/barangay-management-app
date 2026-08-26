<script setup>
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';

defineProps({
    links: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <div v-if="links.length > 3" class="flex items-center justify-between border-t bg-card px-4 py-3 sm:px-6 mt-4 rounded-b-xl">
        <div class="flex flex-1 justify-between sm:hidden">
            <Button
                v-for="(link, key) in links"
                :key="key"
                :as="Link"
                :href="link.url || '#'"
                v-show="link.label.includes('Previous') || link.label.includes('Next')"
                variant="outline"
                size="sm"
                :disabled="!link.url"
                v-html="link.label"
            />
        </div>
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                <Button
                    v-for="(link, key) in links"
                    :key="key"
                    :as="Link"
                    :href="link.url || '#'"
                    variant="outline"
                    size="sm"
                    :class="[
                        link.active ? 'z-10 bg-primary text-primary-foreground border-primary' : 'bg-card text-foreground hover:bg-accent',
                        key === 0 ? 'rounded-l-md' : '',
                        key === links.length - 1 ? 'rounded-r-md' : '',
                        !link.url ? 'opacity-50 pointer-events-none' : '',
                    ]"
                    v-html="link.label"
                />
            </nav>
        </div>
    </div>
</template>