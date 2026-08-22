<script setup>
const year = new Date().getFullYear();
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
                <span class="absolute -top-px -left-px h-3 w-3 border-t-2 border-l-2 border-[#C89B3C]/60 rounded-tl-sm"></span>
                <span class="absolute -top-px -right-px h-3 w-3 border-t-2 border-r-2 border-[#C89B3C]/60 rounded-tr-sm"></span>
                <span class="absolute -bottom-px -left-px h-3 w-3 border-b-2 border-l-2 border-[#C89B3C]/60 rounded-bl-sm"></span>
                <span class="absolute -bottom-px -right-px h-3 w-3 border-b-2 border-r-2 border-[#C89B3C]/60 rounded-br-sm"></span>

                <div class="h-1 w-full bg-gradient-to-r from-[#1B3A5C] via-[#C89B3C] to-[#1B3A5C]"></div>

                <div class="p-6 sm:p-8">
                    <slot />
                </div>
            </div>

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