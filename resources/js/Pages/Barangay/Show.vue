<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent } from '@/components/ui/card';

const props = defineProps({
    barangay: Object,
});

const positionLabels = {
    captain: 'Barangay Captain',
    vice_captain: 'Vice Captain',
    kagawad: 'Kagawad',
    secretary: 'Barangay Secretary',
    treasurer: 'Barangay Treasurer',
    sangguniang_kabataan_chairperson: 'SK Chairperson',
    barangay_tanod: 'Barangay Tanod',
    health_worker: 'Health Worker',
    other: 'Other',
};

const showOfficialForm = ref(false);
const editingOfficial = ref(null);

const officialForm = useForm({
    position: 'kagawad',
    first_name: '',
    middle_name: '',
    last_name: '',
    suffix: '',
    sex: 'male',
    contact_number: '',
    email: '',
    committee: '',
    term_start: new Date().getFullYear(),
    term_end: '',
    notes: '',
    is_active: true,
});

const openCreate = () => {
    editingOfficial.value = null;
    officialForm.reset();
    officialForm.clearErrors();
    showOfficialForm.value = true;
};

const openEdit = (official) => {
    editingOfficial.value = official;
    officialForm.setData({
        position: official.position,
        first_name: official.first_name,
        middle_name: official.middle_name || '',
        last_name: official.last_name,
        suffix: official.suffix || '',
        sex: official.sex || 'male',
        contact_number: official.contact_number || '',
        email: official.email || '',
        committee: official.committee || '',
        term_start: official.term_start,
        term_end: official.term_end || '',
        notes: official.notes || '',
        is_active: official.is_active,
    });
    showOfficialForm.value = true;
};

const submitOfficial = () => {
    if (editingOfficial.value) {
        officialForm.put(`/barangay/${props.barangay.id}/officials/${editingOfficial.value.id}`, {
            onSuccess: () => {
                showOfficialForm.value = false;
                editingOfficial.value = null;
            },
        });
    } else {
        officialForm.post(`/barangay/${props.barangay.id}/officials`, {
            onSuccess: () => {
                showOfficialForm.value = false;
            },
        });
    }
};

const deleteOfficial = (official) => {
    if (confirm('Remove this official?')) {
        router.delete(`/barangay/${props.barangay.id}/officials/${official.id}`);
    }
};

const deleteBarangay = () => {
    if (confirm('Delete this barangay profile and all its officials?')) {
        router.delete(`/barangay/${props.barangay.id}`);
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Barangay Details" />
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-foreground">{{ barangay.name }}</h1>
                    <p class="text-sm text-muted-foreground">{{ barangay.address || 'Address not set' }}</p>
                </div>
                <div class="flex gap-2">
                    <Link href="/barangay" class="text-sm text-muted-foreground hover:text-foreground">&larr; Back</Link>
                    <Button variant="secondary" size="sm" as-child>
                        <Link :href="`/barangay/${barangay.id}/edit`">Edit Profile</Link>
                    </Button>
                    <Button variant="destructive" size="sm" @click="deleteBarangay">Delete</Button>
                </div>
            </div>

            <!-- Profile info -->
            <Card>
                <CardContent class="p-6 space-y-4">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wide">About {{ barangay.name }}</h3>
                    <p v-if="barangay.description" class="text-sm text-muted-foreground">{{ barangay.description }}</p>
                    <div v-if="barangay.mission" class="border-t pt-4">
                        <h4 class="text-xs font-medium text-muted-foreground uppercase">Mission</h4>
                        <p class="text-sm text-foreground mt-1">{{ barangay.mission }}</p>
                    </div>
                    <div v-if="barangay.vision" class="border-t pt-4">
                        <h4 class="text-xs font-medium text-muted-foreground uppercase">Vision</h4>
                        <p class="text-sm text-foreground mt-1">{{ barangay.vision }}</p>
                    </div>
                    <div v-if="barangay.about" class="border-t pt-4">
                        <h4 class="text-xs font-medium text-muted-foreground uppercase">About</h4>
                        <p class="text-sm text-foreground mt-1">{{ barangay.about }}</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Officials -->
            <Card class="overflow-hidden">
                <div class="px-6 py-4 border-b flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-foreground">Barangay Officials</h3>
                        <p class="text-xs text-muted-foreground mt-0.5">Elected and appointed officials per term/year</p>
                    </div>
                    <Button size="sm" variant="secondary" @click="openCreate">+ Add Official</Button>
                </div>

                <!-- Official form -->
                <div v-if="showOfficialForm" class="px-6 py-4 border-b bg-muted">
                    <h4 class="text-sm font-semibold text-foreground mb-3">{{ editingOfficial ? 'Edit Official' : 'Add New Official' }}</h4>
                    <form @submit.prevent="submitOfficial" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground mb-1">Position *</label>
                            <Select v-model="officialForm.position">
                                <option v-for="(label, key) in positionLabels" :key="key" :value="key">{{ label }}</option>
                            </Select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground mb-1">First Name *</label>
                            <Input v-model="officialForm.first_name" type="text" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground mb-1">Middle Name</label>
                            <Input v-model="officialForm.middle_name" type="text" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground mb-1">Last Name *</label>
                            <Input v-model="officialForm.last_name" type="text" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground mb-1">Suffix</label>
                            <Input v-model="officialForm.suffix" type="text" placeholder="Jr., Sr., III" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground mb-1">Sex</label>
                            <Select v-model="officialForm.sex">
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </Select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground mb-1">Contact Number</label>
                            <Input v-model="officialForm.contact_number" type="tel" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground mb-1">Email</label>
                            <Input v-model="officialForm.email" type="email" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground mb-1">Committee</label>
                            <Input v-model="officialForm.committee" type="text" placeholder="e.g., Public Safety" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground mb-1">Term Start *</label>
                            <Input v-model="officialForm.term_start" type="number" min="1900" max="2100" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground mb-1">Term End</label>
                            <Input v-model="officialForm.term_end" type="number" min="1900" max="2100" placeholder="Ongoing" />
                        </div>
                        <div class="flex items-end">
                            <label class="flex items-center">
                                <input v-model="officialForm.is_active" type="checkbox" class="rounded border-input" />
                                <span class="ml-2 text-sm font-medium text-foreground">Active</span>
                            </label>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-muted-foreground mb-1">Notes</label>
                            <Textarea v-model="officialForm.notes" rows="2" />
                        </div>
                        <div v-if="Object.keys(officialForm.errors).length" class="md:col-span-3">
                            <p v-for="(err, key) in officialForm.errors" :key="key" class="text-xs text-destructive">{{ err }}</p>
                        </div>
                        <div class="md:col-span-3 flex justify-end gap-2 pt-2">
                            <Button type="button" variant="ghost" @click="showOfficialForm = false">Cancel</Button>
                            <Button type="submit" :disabled="officialForm.processing">
                                {{ officialForm.processing ? 'Saving...' : (editingOfficial ? 'Update Official' : 'Add Official') }}
                            </Button>
                        </div>
                    </form>
                </div>

                <div v-if="barangay.officials?.length" class="divide-y divide-border">
                    <div v-for="official in barangay.officials" :key="official.id" class="px-6 py-4 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-foreground">{{ official.first_name }} {{ official.middle_name ? official.middle_name.charAt(0) + '. ' : '' }}{{ official.last_name }} {{ official.suffix || '' }}</p>
                            <p class="text-xs text-muted-foreground mt-0.5">{{ positionLabels[official.position] || official.position }} <span v-if="official.committee">- {{ official.committee }}</span></p>
                            <p class="text-xs text-muted-foreground mt-0.5">Term: {{ official.term_start }}{{ official.term_end ? ' - ' + official.term_end : ' - Present' }}</p>
                        </div>
                        <div class="flex gap-2">
                            <button @click="openEdit(official)" class="text-primary hover:text-primary/80 text-sm">Edit</button>
                            <button @click="deleteOfficial(official)" class="text-destructive hover:text-destructive/80 text-sm">Delete</button>
                        </div>
                    </div>
                </div>
                <p v-else class="p-12 text-center text-sm text-muted-foreground">No officials added yet. Click "Add Official" to get started.</p>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>