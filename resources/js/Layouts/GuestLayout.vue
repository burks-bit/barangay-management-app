<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const barangay = computed(() => page.props.barangay || null);
const year = new Date().getFullYear();
</script>

<template>
    <div class="relative min-h-screen flex flex-col items-center justify-center overflow-hidden bg-muted/40 px-6 py-10">
        <div class="relative w-full sm:max-w-md">
            <div class="flex flex-col items-center text-center mb-8">
                <h1 class="text-3xl sm:text-[2.15rem] font-semibold text-foreground">
                    {{ barangay?.name || 'Barangay San Isidro' }}
                </h1>
                <p class="mt-3 text-sm text-muted-foreground max-w-xs">
                    {{ barangay?.description || 'Sign in to manage resident records.' }}
                </p>
            </div>

            <div class="relative rounded-xl border bg-card shadow-lg overflow-hidden">
                <div class="h-1 w-full bg-gradient-to-r from-primary via-primary/60 to-primary"></div>
                <div class="p-6 sm:p-8">
                    <slot />
                </div>
            </div>
            <div class="mt-6 text-center">
                <p class="text-xs text-muted-foreground">&copy; {{ year }} {{ barangay?.name || 'Barangay' }}</p>
            </div>
        </div>
    </div>
</template>