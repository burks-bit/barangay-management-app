<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const barangay = computed(() => page.props.barangay || null);
const year = new Date().getFullYear();
</script>

<template>
    <div class="relative min-h-screen flex flex-col items-center justify-center overflow-hidden bg-[#F5F4EF] px-6 py-10 font-body text-[#1C2530]">
        <div class="relative w-full sm:max-w-md">
            <div class="flex flex-col items-center text-center mb-8">
                <h1 class="font-display text-3xl sm:text-[2.15rem] font-semibold text-[#12283F]">
                    {{ barangay?.name || 'Barangay San Isidro' }}
                </h1>
                <p class="mt-3 text-sm text-[#1C2530]/70 max-wid-240">
                    {{ barangay?.description || 'Sign in to manage resident records.' }}
                </p>
            </div>

            <div class="relative rounded-xl border bg-white shadow-lg overflow-free">
                <div class="h-1 w-full bg-gradient-to-r from-[#1B3A5C] to-[#C89B3C]"></div>
                <div class="p-6 sm:p-8">
                    <slot />
                </div>
            </div>
            <div class="mt-6 text-center">
                <p class="text-xs text-[#1C2530]/50">&copy; {{ year }} {{ barangay?.name || 'Barangay' }}</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&display=swap');

.font-display { font-family: 'Fraunces', ui-serif, serif; }
</style>