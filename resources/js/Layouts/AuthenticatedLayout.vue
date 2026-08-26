<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Bell, ClipboardList, FileText, Info, MessageSquare, LayoutDashboard, Megaphone, Pencil, BarChart3, Shield, History, FileText as FileTextIcon, Users, Home, AlertTriangle, Menu, ChevronDown, Building2, LogOut, User } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';

const page = usePage();
const auth = computed(() => page.props.auth || {});
const user = computed(() => auth.value.user);
const roles = computed(() => auth.value.roles || []);
const permissions = computed(() => auth.value.permissions || []);
const notifications = computed(() => auth.value.notifications || []);
const unreadNotificationCount = computed(() => auth.value.unread_notifications_count || 0);
const newAnnouncementsCount = computed(() => auth.value.new_announcements_count || 0);
const flash = computed(() => page.props.flash || {});
const barangay = computed(() => page.props.barangay || null);
const showingNavigationDropdown = ref(false);
const showingNotifications = ref(false);
const sidebarOpen = ref(false);
let notificationPollingTimer;
const expandedSections = ref({
    Residents: false,
    'Community Services': false,
    'Disaster Management': false,
});

const can = (permission) => permissions.value.includes(permission);
const hasRole = (role) => roles.value.includes(role);
const isMember = computed(() => roles.value.includes('member') && !roles.value.includes('admin') && !roles.value.includes('moderator'));
const toggleSection = (section) => {
    expandedSections.value[section] = !expandedSections.value[section];
};

const navigation = computed(() => {
    const nav = [
        { name: 'Dashboard', href: '/dashboard', icon: 'dashboard', show: true },
    ];

    const residents = [];
    if (can('view residents')) residents.push({ name: 'Residents', href: '/residents' });
    if (can('view households')) residents.push({ name: 'Households', href: '/households' });
    if (can('manage users')) residents.push({ name: 'Puroks', href: '/puroks' });
    if (residents.length) nav.push({ name: 'Residents', icon: 'users', children: residents });

    const services = [];
    if (isMember.value) {
        if (can('view requests')) services.push({ name: 'My Requests', href: '/my-requests' });
        if (can('view complaints')) services.push({ name: 'My Complaints', href: '/my-complaints' });
        if (can('view assistance')) services.push({ name: 'My Assistance', href: '/my-assistance' });
    } else {
        if (can('view requests')) services.push({ name: 'Requests', href: '/requests' });
        if (can('view complaints')) services.push({ name: 'Complaints', href: '/complaints' });
        if (can('view assistance')) services.push({ name: 'Assistance', href: '/assistance' });
    }
    if (hasRole('admin') || hasRole('moderator')) services.push({ name: 'Programs', href: '/programs' });
    if (services.length) nav.push({ name: 'Community Services', icon: 'clipboard', children: services });

    const disaster = [];
    if (user.value) disaster.push({ name: 'Calamities', href: '/calamities' });
    if (isMember.value) {
        if (can('view incidents')) disaster.push({ name: 'My Incidents', href: '/my-incidents' });
    } else {
        if (user.value) disaster.push({ name: 'Incidents', href: '/incidents' });
    }
    if (hasRole('admin') || hasRole('moderator')) disaster.push({ name: 'Incident Blotters', href: '/incidents/blotters' });
    if (user.value) disaster.push({ name: 'Evacuation Centers', href: '/evacuation-centers' });
    if (can('manage evacuations')) disaster.push({ name: 'Evacuations', href: '/evacuations' });
    if (can('view relief inventory')) disaster.push({ name: 'Relief Inventory', href: '/relief-inventory' });
    if (can('manage relief distribution')) disaster.push({ name: 'Relief Distribution', href: '/relief-distributions' });
    if (disaster.length) nav.push({ name: 'Disaster Management', icon: 'alert', children: disaster });

    nav.push({ name: 'Announcements', href: '/announcements', icon: 'megaphone', show: true });
    if (hasRole('admin') || hasRole('moderator')) nav.push({ name: 'Manage Announcements', href: '/announcements/manage', icon: 'pencil' });

    if (can('view reports')) nav.push({ name: 'Reports', href: '/reports/census', icon: 'chart' });

    if (can('manage users')) nav.push({ name: 'Users & Roles', href: '/users', icon: 'shield' });
    if (can('view audit logs')) nav.push({ name: 'Audit Logs', href: '/audit-logs', icon: 'history' });
    if (can('manage settings')) nav.push({ name: 'Barangay Profile', href: '/barangay', icon: 'shield' });
    if (hasRole('admin') || hasRole('moderator')) nav.push({ name: 'Document Types', href: '/request-types', icon: 'document' });

    return nav;
});

const iconMap = {
    dashboard: LayoutDashboard,
    megaphone: Megaphone,
    pencil: Pencil,
    chart: BarChart3,
    shield: Shield,
    history: History,
    document: FileTextIcon,
    users: Users,
    home: Home,
    clipboard: ClipboardList,
    alert: AlertTriangle,
};

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

const notificationLink = (notification) => {
    if (notification.data?.type === 'complaint' && notification.data.complaint_id) {
        return hasRole('member')
            ? `/my-complaints/${notification.data.complaint_id}`
            : `/complaints/${notification.data.complaint_id}`;
    }

    if (notification.data?.type === 'request' && notification.data.request_id) {
        return hasRole('member')
            ? `/my-requests/${notification.data.request_id}`
            : `/requests/${notification.data.request_id}`;
    }

    if (notification.data?.type === 'assistance' && notification.data.assistance_id) {
        return hasRole('member')
            ? `/my-assistance/${notification.data.assistance_id}`
            : `/assistance`;
    }

    if (notification.data?.type === 'incident' && notification.data.incident_id) {
        return hasRole('member')
            ? `/my-incidents/${notification.data.incident_id}`
            : `/incidents/${notification.data.incident_id}`;
    }

    return '/dashboard';
};

const notificationIcon = (notification) => {
    if (notification.data?.type === 'complaint') return MessageSquare;
    if (notification.data?.type === 'request') return FileText;
    if (notification.data?.type === 'assistance') return ClipboardList;
    if (notification.data?.type === 'incident') return AlertTriangle;
    return Info;
};

const notificationIconClass = (notification) => {
    if (notification.data?.type === 'complaint') return 'bg-orange-100 text-orange-700';
    if (notification.data?.type === 'request') return 'bg-blue-100 text-blue-700';
    if (notification.data?.type === 'assistance') return 'bg-green-100 text-green-700';
    if (notification.data?.type === 'incident') return 'bg-red-100 text-red-700';
    return 'bg-gray-100 text-gray-600';
};

const openNotification = (notification) => {
    if (!notification.read_at) {
        router.post(`/notifications/${notification.id}/read`, {}, {
            preserveScroll: true,
            onSuccess: () => router.visit(notificationLink(notification)),
        });
        return;
    }

    router.visit(notificationLink(notification));
};

onMounted(() => {
    notificationPollingTimer = window.setInterval(() => {
        if (user.value) {
            router.reload({ only: ['auth'], preserveState: true, preserveScroll: true });
        }
    }, 30000);
});

onBeforeUnmount(() => {
    window.clearInterval(notificationPollingTimer);
});

const getInitials = (name) => {
    return name
        .split(' ')
        .map((n) => n[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
};

const roleBadge = computed(() => {
    if (hasRole('admin')) return { label: 'Administrator', class: 'bg-purple-100 text-purple-800 border-purple-200' };
    if (hasRole('moderator')) return { label: 'Moderator', class: 'bg-blue-100 text-blue-800 border-blue-200' };
    return { label: 'Member', class: 'bg-green-100 text-green-800 border-green-200' };
});
</script>

<template>
    <div class="min-h-screen bg-muted/40">
        <Head :title="$page.component.replace('/', ' ')" />

        <!-- Sidebar for desktop -->
        <aside
            class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-900 transform transition-transform duration-200 ease-in-out lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex items-center h-16 px-6 border-b border-slate-800">
                <div class="flex items-center">
                    <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-primary text-primary-foreground">
                        <Building2 class="w-5 h-5" aria-hidden="true" />
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
                        class="flex items-center px-3 py-2 text-sm font-medium rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-colors"
                        :class="{ 'bg-slate-800 text-white': $page.url.startsWith(item.href) }"
                    >
                        <component :is="iconMap[item.icon] || LayoutDashboard" class="w-5 h-5 mr-3" aria-hidden="true" />
                        {{ item.name }}
                        <span
                            v-if="item.name === 'Announcements' && newAnnouncementsCount > 0"
                            class="ml-auto inline-flex min-w-[1.25rem] items-center justify-center rounded-full bg-red-600 px-1.5 py-0.5 text-[10px] font-bold leading-none text-white"
                        >
                            {{ newAnnouncementsCount > 99 ? '99+' : newAnnouncementsCount }}
                        </span>
                    </Link>

                    <!-- Group with children -->
                    <div v-else>
                        <button
                            type="button"
                            class="flex w-full items-center justify-between px-3 py-2 text-left text-xs font-bold uppercase tracking-wider text-slate-500 hover:text-slate-300"
                            :aria-expanded="expandedSections[item.name]"
                            @click="toggleSection(item.name)"
                        >
                            <span class="flex items-center">
                                <component :is="iconMap[item.icon] || LayoutDashboard" class="w-5 h-5 mr-3" aria-hidden="true" />
                                {{ item.name }}
                            </span>
                            <ChevronDown
                                class="h-4 w-4 transition-transform"
                                :class="{ 'rotate-180': expandedSections[item.name] }"
                                aria-hidden="true"
                            />
                        </button>
                        <div v-if="expandedSections[item.name]" class="space-y-1">
                            <Link
                                v-for="child in item.children"
                                :key="child.name"
                                :href="child.href"
                                class="flex items-center pl-8 pr-3 py-2 text-sm font-medium rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-colors"
                                :class="{ 'bg-slate-800 text-white': $page.url.startsWith(child.href) }"
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
            class="fixed inset-0 z-30 bg-slate-600 bg-opacity-75 lg:hidden"
            @click="sidebarOpen = false"
        ></div>

        <!-- Main content -->
        <div class="lg:pl-64">
            <!-- Top navigation -->
            <header class="sticky top-0 z-20 bg-card shadow-sm">
                <div class="flex items-center justify-between h-16 px-4 sm:px-6">
                    <div class="flex items-center">
                        <Button
                            variant="ghost"
                            size="icon"
                            class="lg:hidden"
                            @click="sidebarOpen = !sidebarOpen"
                        >
                            <Menu class="h-6 w-6" />
                        </Button>
                        <div class="ml-2 lg:ml-0">
                            <h2 class="text-lg font-semibold text-foreground">
                                {{ $page.component.split('/').pop().replace(/(Index|Show|Create|Edit|My)/g, '').replace(/([A-Z])/g, ' $1').trim() }}
                            </h2>
                        </div>
                    </div>

                    <div class="flex items-center space-x-4">
                        <!-- Notifications -->
                        <div class="relative">
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                aria-label="Notifications"
                                :aria-expanded="showingNotifications"
                                @click="showingNotifications = !showingNotifications; showingNavigationDropdown = false"
                            >
                                <Bell class="h-5 w-5" />
                                <span
                                    v-if="unreadNotificationCount"
                                    class="absolute -right-1 -top-1 flex min-h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-bold leading-none text-white"
                                >
                                    {{ unreadNotificationCount > 99 ? '99+' : unreadNotificationCount }}
                                </span>
                            </Button>

                            <div
                                v-if="showingNotifications"
                                class="fixed left-4 right-4 top-20 z-30 max-h-[min(70vh,28rem)] overflow-hidden rounded-lg bg-card shadow-lg ring-1 ring-border sm:absolute sm:left-auto sm:right-0 sm:top-auto sm:mt-2 sm:w-80 sm:max-w-[calc(100vw-2rem)]"
                            >
                                <div class="flex items-center justify-between border-b px-4 py-3">
                                    <h3 class="text-sm font-semibold text-foreground">Notifications</h3>
                                    <span class="text-xs text-muted-foreground">{{ unreadNotificationCount }} unread</span>
                                </div>
                                <div v-if="notifications.length" class="max-h-[calc(min(70vh,28rem)-4rem)] overflow-y-auto scrollbar-hidden">
                                    <button
                                        v-for="notification in notifications"
                                        :key="notification.id"
                                        type="button"
                                        class="block w-full border-b px-4 py-3 text-left hover:bg-muted"
                                        :class="{ 'bg-primary/5': !notification.read_at }"
                                        @click="openNotification(notification); showingNotifications = false"
                                    >
                                        <div class="flex items-start gap-3">
                                            <span v-if="!notification.read_at" class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-primary"></span>
                                            <span v-else class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full" :class="notificationIconClass(notification)">
                                                <component :is="notificationIcon(notification)" class="h-4 w-4" aria-hidden="true" />
                                            </span>
                                            <span v-if="!notification.read_at" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full" :class="notificationIconClass(notification)">
                                                <component :is="notificationIcon(notification)" class="h-4 w-4" aria-hidden="true" />
                                            </span>
                                            <div class="min-w-0">
                                                <p class="text-sm font-medium text-foreground">{{ notification.data?.title || 'Notification' }}</p>
                                                <p class="mt-1 text-xs text-muted-foreground">{{ notification.data?.message || 'Open notification' }}</p>
                                            </div>
                                        </div>
                                    </button>
                                </div>
                                <p v-else class="px-4 py-8 text-center text-sm text-muted-foreground">No notifications yet.</p>
                            </div>
                        </div>

                        <!-- User menu -->
                        <div class="relative" :class="{ 'dropdown-open': showingNavigationDropdown }">
                            <button
                                class="flex items-center space-x-3 focus:outline-none"
                                @click="showingNavigationDropdown = !showingNavigationDropdown; showingNotifications = false"
                            >
                                <div class="text-right hidden sm:block">
                                    <div class="text-sm font-medium text-foreground">{{ user?.name }}</div>
                                    <Badge :class="roleBadge.class" class="mt-0.5">
                                        {{ roleBadge.label }}
                                    </Badge>
                                </div>
                                <div class="flex items-center justify-center w-9 h-9 rounded-full bg-primary text-primary-foreground text-sm font-semibold">
                                    {{ getInitials(user?.name || 'U') }}
                                </div>
                            </button>

                            <div
                                v-if="showingNavigationDropdown"
                                class="absolute right-0 mt-2 w-48 bg-card rounded-lg shadow-lg py-1 ring-1 ring-border"
                                @click="showingNavigationDropdown = false"
                            >
                                <Link href="/profile" class="flex items-center px-4 py-2 text-sm text-foreground hover:bg-muted"><User class="h-4 w-4 mr-2" /> My Profile</Link>
                                <Link href="/my-requests" class="flex items-center px-4 py-2 text-sm text-foreground hover:bg-muted"><FileText class="h-4 w-4 mr-2" /> My Requests</Link>
                                <Link href="/my-complaints" class="flex items-center px-4 py-2 text-sm text-foreground hover:bg-muted"><MessageSquare class="h-4 w-4 mr-2" /> My Complaints</Link>
                                <Link href="/my-assistance" class="flex items-center px-4 py-2 text-sm text-foreground hover:bg-muted"><ClipboardList class="h-4 w-4 mr-2" /> My Assistance</Link>
                                <Link href="/my-incidents" class="flex items-center px-4 py-2 text-sm text-foreground hover:bg-muted"><AlertTriangle class="h-4 w-4 mr-2" /> My Incidents</Link>
                                <div class="border-t my-1"></div>
                                <button
                                    @click="logout"
                                    class="flex w-full items-center px-4 py-2 text-sm text-destructive hover:bg-muted"
                                >
                                    <LogOut class="h-4 w-4 mr-2" /> Sign Out
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
                        <svg class="w-5 h-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 001.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
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
