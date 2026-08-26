<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';

const props = defineProps({ centers: Array, userHousehold: Object });
const page = usePage();
const permissions = ref(page.props.auth?.permissions || []);
const roles = ref(page.props.auth?.roles || []);

const canManage = () => permissions.value.includes('create evacuation centers') || permissions.value.includes('update evacuation centers');
const isMember = () => roles.value.includes('member');

const selectCenter = (centerId) => {
    if (confirm('Evacuate your household to this center?')) {
        router.post('/evacuation-centers/select', { evacuation_center_id: centerId });
    }
};

const returnHome = () => {
    if (confirm('Return your household home from evacuation?')) {
        router.post('/evacuation-centers/return');
    }
};

const canSelectCenter = (center) => {
    return center.status !== 'full' && center.status !== 'closed' && center.current_occupancy < center.capacity;
};

const destroy = (center) => {
    if (confirm('Delete this evacuation center?')) {
        router.delete(`/evacuation-centers/${center.id}`);
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Evacuation Centers" />
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-foreground">Evacuation Centers</h1>
                    <p class="text-sm text-muted-foreground mt-1">View evacuation facilities and capacity.</p>
                </div>
                <div v-if="canManage()" class="flex gap-2">
                    <Button size="sm" as-child>
                        <Link href="/evacuation-centers/create"><Plus class="h-4 w-4" /> Add Center</Link>
                    </Button>
                </div>
            </div>

            <div v-if="userHousehold" class="bg-card rounded-xl border p-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-semibold text-foreground">My Household Evacuation</h3>
                        <p class="text-xs text-muted-foreground mt-1">Household: {{ userHousehold.household_code }}</p>
                        <p class="text-sm mt-2">
                            <span class="text-muted-foreground">Status: </span>
                            <span class="font-medium" :class="userHousehold.evacuation_status === 'evacuated' ? 'text-primary' : 'text-muted-foreground'">
                                {{ userHousehold.evacuation_status === 'evacuated' ? 'Evacuated to ' + (userHousehold.evacuation_center?.name || 'center') : 'Not evacuated' }}
                            </span>
                        </p>
                    </div>
                    <Button v-if="userHousehold.evacuation_status === 'evacuated'" size="sm" class="bg-yellow-600 hover:bg-yellow-700" @click="returnHome">
                        Return Home
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <Card v-for="center in centers" :key="center.id">
                    <CardContent class="p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div><h2 class="font-semibold text-foreground">{{ center.name }}</h2><p class="text-sm text-muted-foreground mt-1">{{ center.location }}</p></div>
                            <StatusBadge :status="center.status" />
                        </div>
                        <dl class="grid grid-cols-2 gap-3 mt-5 text-sm">
                            <div><dt class="text-muted-foreground">Capacity</dt><dd class="font-medium text-foreground">{{ center.capacity }}</dd></div>
                            <div><dt class="text-muted-foreground">Current occupancy</dt><dd class="font-medium text-foreground">{{ center.current_occupancy }}</dd></div>
                            <div><dt class="text-muted-foreground">Available spaces</dt><dd class="font-medium text-green-700">{{ Math.max(0, center.capacity - center.current_occupancy) }}</dd></div>
                            <div><dt class="text-muted-foreground">Active evacuations</dt><dd class="font-medium text-foreground">{{ center.active_events_count }}</dd></div>
                        </dl>
                        <p v-if="center.contact_person || center.contact_number" class="text-xs text-muted-foreground mt-4">Contact: {{ center.contact_person || '-' }} {{ center.contact_number ? `(${center.contact_number})` : '' }}</p>

                        <div class="mt-4 flex items-center gap-2" v-if="canManage()">
                            <Button variant="ghost" size="sm" as-child>
                                <Link :href="`/evacuation-centers/${center.id}/edit`"><Pencil class="h-4 w-4" /> Edit</Link>
                            </Button>
                            <Button variant="ghost" size="sm" class="text-destructive" @click="destroy(center)"><Trash2 class="h-4 w-4" /> Delete</Button>
                        </div>
                        <div v-else-if="isMember()" class="mt-4">
                            <Button v-if="userHousehold && userHousehold.evacuation_status !== 'evacuated' && canSelectCenter(center)" size="sm" class="w-full" @click="selectCenter(center.id)">
                                Evacuate
                            </Button>
                            <p v-else-if="userHousehold && userHousehold.evacuation_status !== 'evacuated'" class="text-xs text-destructive">This center is full or closed.</p>
                            <p v-else-if="!userHousehold" class="text-xs text-muted-foreground">No household associated with your account.</p>
                        </div>
                    </CardContent>
                </Card>
            </div>
            <p v-if="!centers.length" class="bg-card rounded-xl border p-12 text-center text-sm text-muted-foreground">No evacuation centers found.</p>
        </div>
    </AuthenticatedLayout>
</template>