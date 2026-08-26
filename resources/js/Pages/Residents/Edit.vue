<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';

const props = defineProps({
    resident: Object,
    puroks: Array,
    households: Array,
});

const form = useForm({
    first_name: props.resident.first_name,
    middle_name: props.resident.middle_name || '',
    last_name: props.resident.last_name,
    suffix: props.resident.suffix || '',
    date_of_birth: props.resident.date_of_birth ? props.resident.date_of_birth.slice(0, 10) : '',
    sex: props.resident.sex,
    civil_status: props.resident.civil_status,
    contact_number: props.resident.contact_number || '',
    address: props.resident.address,
    purok_id: props.resident.purok_id,
    household_id: props.resident.household_id || '',
    occupation: props.resident.occupation || '',
    residency_status: props.resident.residency_status,
    emergency_contact_name: props.resident.emergency_contact_name || '',
    emergency_contact_number: props.resident.emergency_contact_number || '',
});

const submit = () => {
    form.put(`/residents/${props.resident.id}`);
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Edit Resident" />

        <div class="max-w-4xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-foreground">Edit Resident</h1>
                    <p class="text-sm text-muted-foreground">{{ resident.first_name }} {{ resident.last_name }}</p>
                </div>
                <Link :href="`/residents/${resident.id}`" class="text-sm text-muted-foreground hover:text-foreground">&larr; Back to Profile</Link>
            </div>

            <form @submit.prevent="submit" class="bg-card rounded-xl border p-6 space-y-6">
                <!-- Personal Information -->
                <div>
                    <h3 class="text-sm font-semibold text-foreground mb-4">Personal Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">First Name *</label>
                            <Input v-model="form.first_name" type="text" required class="mt-1" />
                            <p v-if="form.errors.first_name" class="mt-1 text-xs text-destructive">{{ form.errors.first_name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">Middle Name</label>
                            <Input v-model="form.middle_name" type="text" class="mt-1" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">Last Name *</label>
                            <Input v-model="form.last_name" type="text" required class="mt-1" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">Suffix</label>
                            <Input v-model="form.suffix" type="text" class="mt-1" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">Date of Birth *</label>
                            <Input v-model="form.date_of_birth" type="date" required class="mt-1" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">Sex *</label>
                            <Select v-model="form.sex" required class="mt-1">
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </Select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">Civil Status *</label>
                            <Select v-model="form.civil_status" required class="mt-1">
                                <option value="single">Single</option>
                                <option value="married">Married</option>
                                <option value="widowed">Widowed</option>
                                <option value="separated">Separated</option>
                                <option value="divorced">Divorced</option>
                            </Select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">Occupation</label>
                            <Input v-model="form.occupation" type="text" class="mt-1" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">Residency Status *</label>
                            <Select v-model="form.residency_status" required class="mt-1">
                                <option value="permanent">Permanent</option>
                                <option value="temporary">Temporary</option>
                                <option value="transient">Transient</option>
                            </Select>
                        </div>
                    </div>
                </div>

                <!-- Address -->
                <div>
                    <h3 class="text-sm font-semibold text-foreground mb-4">Address Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-muted-foreground">Address *</label>
                            <Input v-model="form.address" type="text" required class="mt-1" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">Purok *</label>
                            <Select v-model="form.purok_id" required class="mt-1">
                                <option v-for="purok in puroks" :key="purok.id" :value="purok.id">{{ purok.name }}</option>
                            </Select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">Household</label>
                            <Select v-model="form.household_id" class="mt-1">
                                <option value="">None</option>
                                <option v-for="household in households" :key="household.id" :value="household.id">
                                    {{ household.household_code }}
                                </option>
                            </Select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">Contact Number</label>
                            <Input v-model="form.contact_number" type="tel" class="mt-1" />
                        </div>
                    </div>
                </div>

                <!-- Emergency Contact -->
                <div>
                    <h3 class="text-sm font-semibold text-foreground mb-4">Emergency Contact</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">Contact Name</label>
                            <Input v-model="form.emergency_contact_name" type="text" class="mt-1" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">Contact Number</label>
                            <Input v-model="form.emergency_contact_number" type="tel" class="mt-1" />
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end space-x-3 pt-4 border-t">
                    <Button variant="outline" as-child>
                        <Link :href="`/residents/${resident.id}`">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Saving...' : 'Update Resident' }}
                    </Button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>