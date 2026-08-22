<script setup>
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-blue-50 via-white to-green-50">
        <div class="w-full sm:max-w-md px-6 py-4">
            <div class="flex flex-col items-center mb-8">
                <div class="flex items-center justify-center w-16 h-16 rounded-full bg-blue-600 text-white mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Barangay Management System</h1>
                <p class="text-sm text-gray-500 mt-1">Community Services Platform</p>
            </div>

            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <slot />
            </div>

            <p class="text-center text-xs text-gray-400 mt-6">
                &copy; {{ new Date().getFullYear() }} Barangay Management System. All rights reserved.
            </p>
        </div>
    </div>
</template>