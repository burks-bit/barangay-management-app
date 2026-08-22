<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

const page = usePage();
const auth = computed(() => page.props.auth || {});
const user = computed(() => auth.value.user);
const roles = computed(() => auth.value.roles || []);
const permissions = computed(() => auth.value.permissions || []);
const flash = computed(() => page.props.flash || {});
const barangay = computed(() => page.props.barangay || null);
// console.log('AuthenticatedLayout props', { auth: auth.value, user: user.value, roles: roles.value, permissions: permissions.value, flash: flash.value });
const showingNavigationDropdown = ref(false);
const sidebarOpen = ref(false);
const expandedSections = ref({
    Residents: false,
    'Community Services': false,
    'Disaster Management': false,
});

const can = (permission) => permissions.value.includes(permission);
const hasRole = (role) => roles.value.includes(role);
const toggleSection = (section) => {
    expandedSections.value[section] = !expandedSections.value[section];
};

const navigation = computed(() => {
    const nav = [
        { name: 'Dashboard', href: '/dashboard', icon: 'dashboard', show: true },
    ];

    // Residents section
    const residents = [];
    if (can('view residents')) residents.push({ name: 'Residents', href: '/residents' });
    if (can('view households')) residents.push({ name: 'Households', href: '/households' });
    if (can('manage users')) residents.push({ name: 'Puroks', href: '/puroks' });
    if (residents.length) nav.push({ name: 'Residents', icon: 'users', children: residents });

    // Community Services
    const services = [];
    if (can('view requests')) services.push({ name: 'Requests', href: '/requests' });
    if (can('view complaints')) services.push({ name: 'Complaints', href: '/complaints' });
    if (can('view assistance')) services.push({ name: 'Assistance', href: '/assistance' });
    if (services.length) nav.push({ name: 'Community Services', icon: 'clipboard', children: services });

    // Disaster Management
    const disaster = [];
    if (user.value) disaster.push({ name: 'Calamities', href: '/calamities' });
    if (user.value) disaster.push({ name: 'Incidents', href: '/incidents' });
    if (user.value) disaster.push({ name: 'Evacuation Centers', href: '/evacuation-centers' });
    if (can('manage evacuations')) disaster.push({ name: 'Evacuations', href: '/evacuations' });
    if (can('view relief inventory')) disaster.push({ name: 'Relief Inventory', href: '/relief-inventory' });
    if (can('manage relief distribution')) disaster.push({ name: 'Relief Distribution', href: '/relief-distributions' });
    if (disaster.length) nav.push({ name: 'Disaster Management', icon: 'alert', children: disaster });

    // Communication
    nav.push({ name: 'Announcements', href: '/announcements', icon: 'megaphone', show: true });

    // Reports
    if (can('view reports')) nav.push({ name: 'Reports', href: '/reports', icon: 'chart' });

    // Admin only
    if (can('manage users')) nav.push({ name: 'Users & Roles', href: '/users', icon: 'shield' });
    if (can('view audit logs')) nav.push({ name: 'Audit Logs', href: '/audit-logs', icon: 'history' });
    if (can('manage settings')) nav.push({ name: 'Barangay Profile', href: '/barangay', icon: 'shield' });

    return nav;
});

const openActiveSection = (url) => {
    const activeSection = navigation.value.find((item) =>
        item.children?.some((child) => url.startsWith(child.href))
    );

    if (activeSection) {
        expandedSections.value[activeSection.name] = true;
    }
};

watch(() => page.url, openActiveSection, { immediate: true });

const logout = () => {
    router.post('/logout');
};

const getInitials = (name) => {
    return name
        .split(' ')
        .map((n) => n[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
};

const roleBadge = computed(() => {
    if (hasRole('admin')) return { label: 'Administrator', class: 'bg-purple-100 text-purple-800' };
    if (hasRole('moderator')) return { label: 'Moderator', class: 'bg-blue-100 text-blue-800' };
    return { label: 'Member', class: 'bg-green-100 text-green-800' };
});
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <Head :title="$page.component.replace('/', ' ')" />

        <!-- Sidebar for desktop -->
        <aside
            class="fixed inset-y-0 left-0 z-40 w-64 bg-gray-900 transform transition-transform duration-200 ease-in-out lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex items-center h-16 px-6 border-b border-gray-800">
                <div class="flex items-center">
                    <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-blue-600 text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <span class="ml-3 text-white font-semibold">{{ barangay?.name || 'Barangay MS' }}</span>
                </div>
            </div>

            <nav class="mt-4 px-3 space-y-1 overflow-y-auto scrollbar-hidden h-[calc(100vh-4rem)] pb-6">
                <template v-for="item in navigation" :key="item.name">
                    <!-- Simple link -->
                    <Link
                        v-if="!item.children"
                        :href="item.href"
                        class="flex items-center px-3 py-2 text-sm font-medium rounded-lg text-gray-300 hover:bg-gray-800 hover:text-white transition-colors"
                        :class="{ 'bg-gray-800 text-white': $page.url.startsWith(item.href) }"
                    >
                        <span class="w-5 h-5 mr-3 inline-flex items-center justify-center">
                            <svg v-if="item.icon === 'dashboard'" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                            <svg v-else-if="item.icon === 'megaphone'" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                            <svg v-else-if="item.icon === 'chart'" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                            <svg v-else-if="item.icon === 'shield'" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                            <svg v-else-if="item.icon === 'history'" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </span>
                        {{ item.name }}
                    </Link>

                    <!-- Group with children -->
                    <div v-else>
                        <button
                            type="button"
                            class="flex w-full items-center justify-between px-3 py-2 text-left text-xs font-bold uppercase tracking-wider text-gray-500 hover:text-gray-300"
                            :aria-expanded="expandedSections[item.name]"
                            @click="toggleSection(item.name)"
                        >
                            {{ item.name }}
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 transition-transform"
                                :class="{ 'rotate-180': expandedSections[item.name] }"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                                aria-hidden="true"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div v-if="expandedSections[item.name]" class="space-y-1">
                            <Link
                                v-for="child in item.children"
                                :key="child.name"
                                :href="child.href"
                                class="flex items-center pl-8 pr-3 py-2 text-sm font-medium rounded-lg text-gray-300 hover:bg-gray-800 hover:text-white transition-colors"
                                :class="{ 'bg-gray-800 text-white': $page.url.startsWith(child.href) }"
                            >
                                {{ child.name }}
                            </Link>
                        </div>
                    </div>
                </template>
            </nav>
        </aside>

        <!-- Sidebar overlay for mobile -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-30 bg-gray-600 bg-opacity-75 lg:hidden"
            @click="sidebarOpen = false"
        ></div>

        <!-- Main content -->
        <div class="lg:pl-64">
            <!-- Top navigation -->
            <header class="sticky top-0 z-20 bg-white shadow-sm">
                <div class="flex items-center justify-between h-16 px-4 sm:px-6">
                    <div class="flex items-center">
                        <button
                            class="lg:hidden p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100"
                            @click="sidebarOpen = !sidebarOpen"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <div class="ml-2 lg:ml-0">
                            <h2 class="text-lg font-semibold text-gray-900">
                                {{ $page.component.split('/').pop().replace(/(Index|Show|Create|Edit|My)/g, '').replace(/([A-Z])/g, ' $1').trim() }}
                            </h2>
                        </div>
                    </div>

                    <div class="flex items-center space-x-4">
                        <!-- User menu -->
                        <div class="relative" :class="{ 'dropdown-open': showingNavigationDropdown }">
                            <button
                                class="flex items-center space-x-3 focus:outline-none"
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                            >
                        <div class="text-right hidden sm:block">
                                    <div class="text-sm font-medium text-gray-900">{{ user?.name }}</div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" :class="roleBadge.class">
                                        {{ roleBadge.label }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-center w-9 h-9 rounded-full bg-blue-600 text-white text-sm font-semibold">
                                    {{ getInitials(user?.name || 'U') }}
                                </div>
                            </button>

                            <div
                                v-if="showingNavigationDropdown"
                                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-1 ring-1 ring-black ring-opacity-5"
                                @click="showingNavigationDropdown = false"
                            >
                                <Link href="/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My Profile</Link>
                                <Link href="/my-requests" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My Requests</Link>
                                <Link href="/my-complaints" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My Complaints</Link>
                                <div class="border-t border-gray-100 my-1"></div>
                                <button
                                    @click="logout"
                                    class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100"
                                >
                                    Sign Out
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Flash messages -->
            <div v-if="flash.success" class="mx-4 sm:mx-6 mt-4">
                <div class="rounded-lg bg-green-50 border border-green-200 p-4">
                    <div class="flex">
                        <svg class="w-5 h-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                        <p class="ml-3 text-sm text-green-700">{{ flash.success }}</p>
                    </div>
                </div>
            </div>
            <div v-if="flash.error" class="mx-4 sm:mx-6 mt-4">
                <div class="rounded-lg bg-red-50 border border-red-200 p-4">
                    <div class="flex">
                        <svg class="w-5 h-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                        <p class="ml-3 text-sm text-red-700">{{ flash.error }}</p>
                    </div>
                </div>
            </div>

            <!-- Page content -->
            <main class="p-4 sm:p-6">
                <slot />
            </main>
        </div>
    </div>
</template>