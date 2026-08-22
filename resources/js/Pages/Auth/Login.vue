<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: Boolean,
    barangay: Object,
});

const year = new Date().getFullYear();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <div class="relative min-h-screen flex flex-col items-center justify-center overflow-hidden bg-[#F5F4EF] px-6 py-10 font-body text-[#1C2530]">

        <!-- security-print rosette texture, like the guilloché pattern on official documents -->
        <svg class="pointer-events-none absolute inset-0 h-full w-full opacity-[0.05]" aria-hidden="true">
            <defs>
                <pattern id="brgy-rosette" width="140" height="140" patternUnits="userSpaceOnUse">
                    <circle cx="70" cy="70" r="58" fill="none" stroke="#1B3A5C" stroke-width="1" />
                    <circle cx="70" cy="70" r="42" fill="none" stroke="#1B3A5C" stroke-width="1" />
                    <circle cx="70" cy="70" r="26" fill="none" stroke="#1B3A5C" stroke-width="1" />
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#brgy-rosette)" />
        </svg>

        <div class="relative w-full sm:max-w-md">

            <div class="flex flex-col items-center text-center mb-8">
                <div class="mb-5 h-20 w-20">
                    <svg viewBox="0 0 100 100" class="h-full w-full drop-shadow-sm" aria-hidden="true">
                        <defs>
                            <linearGradient id="brgy-blue" x1="0" y1="0" x2="1" y2="1">
                                <stop offset="0%" stop-color="#24507C" />
                                <stop offset="100%" stop-color="#12283F" />
                            </linearGradient>
                        </defs>

                        <!-- sun rays, breaking from behind the seal -->
                        <g stroke="#C89B3C" stroke-width="3" stroke-linecap="round">
                            <line x1="50" y1="2" x2="50" y2="14" />
                            <line x1="50" y1="86" x2="50" y2="98" />
                            <line x1="2" y1="50" x2="14" y2="50" />
                            <line x1="86" y1="50" x2="98" y2="50" />
                            <line x1="15" y1="15" x2="23.5" y2="23.5" />
                            <line x1="76.5" y1="76.5" x2="85" y2="85" />
                            <line x1="85" y1="15" x2="76.5" y2="23.5" />
                            <line x1="23.5" y1="76.5" x2="15" y2="85" />
                        </g>

                        <!-- dashed authentication ring -->
                        <circle cx="50" cy="50" r="34" fill="none" stroke="#C89B3C" stroke-width="1.5" stroke-dasharray="2 4" opacity="0.8" />

                        <!-- seal body -->
                        <circle cx="50" cy="50" r="29" fill="url(#brgy-blue)" />

                        <!-- bayanihan mark: neighboring houses behind a central one -->
                        <g fill="#C89B3C" opacity="0.85">
                            <path d="M35 46 L40 54 L37 54 L37 60 L33 60 L33 54 L30 54 Z" />
                            <path d="M65 46 L70 54 L67 54 L67 60 L63 60 L63 54 L60 54 Z" />
                        </g>
                        <path fill="#F5F4EF" d="M50 40 L59 50 L54 50 L54 58 L46 58 L46 50 L41 50 Z" />
                    </svg>
                </div>

                <p class="font-utility text-[11px] tracking-[0.2em] text-[#C89B3C] uppercase mb-2">
                    Local Government Unit &middot; Digital Services
                </p>
                <h1 class="font-display text-3xl sm:text-[2.15rem] font-semibold leading-tight text-[#12283F]">
                    Barangay Management
                    <span class="block sm:inline text-[#1B3A5C]">&amp; Community Services</span>
                </h1>
                <p class="mt-3 text-sm text-[#1C2530]/70 max-w-xs">
                    Sign in to manage resident records, certificates, and community programs.
                </p>
            </div>

            <div class="relative rounded-xl border border-[#1B3A5C]/10 bg-white shadow-[0_20px_50px_-20px_rgba(18,40,63,0.25)] overflow-hidden">
                <span class="absolute top-2.5 left-2.5 h-2.5 w-2.5 border-t-2 border-l-2 border-[#C89B3C]/60"></span>
                <span class="absolute top-2.5 right-2.5 h-2.5 w-2.5 border-t-2 border-r-2 border-[#C89B3C]/60"></span>
                <span class="absolute bottom-2.5 left-2.5 h-2.5 w-2.5 border-b-2 border-l-2 border-[#C89B3C]/60"></span>
                <span class="absolute bottom-2.5 right-2.5 h-2.5 w-2.5 border-b-2 border-r-2 border-[#C89B3C]/60"></span>

                <div class="h-1 w-full rounded-t-xl bg-gradient-to-r from-[#1B3A5C] via-[#C89B3C] to-[#1B3A5C]"></div>
                
                <div class="p-6 sm:p-8">
                    <form @submit.prevent="submit">
                        <div>
                            <label for="email" class="block text-sm font-medium text-[#1C2530]">Email address</label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                name="email"
                                required
                                autofocus
                                autocomplete="username"
                                class="mt-1 block w-full rounded-lg border border-[#1B3A5C]/15 px-3 py-2 shadow-sm focus:border-[#1B3A5C] focus:outline-none focus:ring-1 focus:ring-[#1B3A5C]/30 sm:text-sm"
                                placeholder="you@example.com"
                            />
                            <p v-if="form.errors.email" class="mt-2 text-sm text-[#B3261E]">{{ form.errors.email }}</p>
                        </div>

                        <div class="mt-4">
                            <label for="password" class="block text-sm font-medium text-[#1C2530]">Password</label>
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                class="mt-1 block w-full rounded-lg border border-[#1B3A5C]/15 px-3 py-2 shadow-sm focus:border-[#1B3A5C] focus:outline-none focus:ring-1 focus:ring-[#1B3A5C]/30 sm:text-sm"
                                placeholder="••••••••"
                            />
                            <p v-if="form.errors.password" class="mt-2 text-sm text-[#B3261E]">{{ form.errors.password }}</p>
                        </div>

                        <div class="flex items-center justify-between mt-4">
                            <label class="flex items-center">
                                <input
                                    v-model="form.remember"
                                    type="checkbox"
                                    name="remember"
                                    class="rounded border-[#1B3A5C]/30 accent-[#1B3A5C] focus:ring-[#1B3A5C]/30"
                                />
                                <span class="ml-2 text-sm text-[#1C2530]/70">Remember me</span>
                            </label>

                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="text-sm font-medium text-[#1B3A5C] hover:text-[#12283F] hover:underline"
                            >
                                Forgot password?
                            </Link>
                        </div>

                        <button
                            type="submit"
                            class="w-full mt-6 flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-[#1B3A5C] hover:bg-[#12283F] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#C89B3C] disabled:opacity-50 transition-colors"
                            :disabled="form.processing"
                        >
                            <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ form.processing ? 'Signing in...' : 'Sign in' }}
                        </button>
                    </form>
                </div>
            </div>

            <section v-if="barangay" class="mt-6 rounded-xl border border-[#1B3A5C]/15 bg-white/90 p-5 shadow-sm">
                <div class="border-b border-[#1B3A5C]/10 pb-3">
                    <p class="font-utility text-[10px] tracking-[0.16em] text-[#C89B3C] uppercase">Official Barangay Information</p>
                    <h2 class="mt-1 font-display text-xl font-semibold text-[#12283F]">{{ barangay.name }}</h2>
                    <p v-if="barangay.address" class="mt-1 text-sm text-[#1C2530]/65">{{ barangay.address }}</p>
                </div>
                <div class="mt-4 space-y-3 text-sm text-[#1C2530]/75">
                    <p v-if="barangay.description || barangay.about">{{ barangay.description || barangay.about }}</p>
                    <div v-if="barangay.mission"><p class="font-semibold text-[#12283F]">Mission</p><p class="mt-1">{{ barangay.mission }}</p></div>
                    <div v-if="barangay.vision"><p class="font-semibold text-[#12283F]">Vision</p><p class="mt-1">{{ barangay.vision }}</p></div>
                    <div v-if="barangay.active_officials?.length"><p class="font-semibold text-[#12283F]">Barangay Officials</p><ul class="mt-1 grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1"><li v-for="official in barangay.active_officials" :key="official.id">{{ official.position.replaceAll('_', ' ') }}: {{ official.first_name }} {{ official.middle_name ? official.middle_name + ' ' : '' }}{{ official.last_name }}{{ official.suffix ? ` ${official.suffix}` : '' }}</li></ul></div>
                </div>
            </section>

            

            <div class="mt-6 text-center">
                <p class="text-xs text-[#1C2530]/50">
                    &copy; {{ year }} Barangay Management System
                </p>
                <p class="font-utility text-[10px] tracking-wide text-[#1C2530]/35 mt-1">
                    Protected under the Data Privacy Act of 2012 (RA 10173)
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Public+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@500&display=swap');

.font-display { font-family: 'Fraunces', ui-serif, Georgia, serif; font-optical-sizing: auto; }
.font-body { font-family: 'Public Sans', ui-sans-serif, system-ui, sans-serif; }
.font-utility { font-family: 'IBM Plex Mono', ui-monospace, monospace; }
</style>