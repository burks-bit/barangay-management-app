<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { Card, CardContent } from '@/components/ui/card';

defineProps({
    puroks: Array,
    households: Array,
});

const form = useForm({
    first_name: '',
    middle_name: '',
    last_name: '',
    suffix: '',
    date_of_birth: '',
    sex: '',
    civil_status: '',
    contact_number: '',
    email: '',
    address: '',
    purok_id: '',
    household_id: '',
    occupation: '',
    residency_status: 'permanent',
    emergency_contact_name: '',
    emergency_contact_number: '',
    create_account: false,
    password: '',
});

const submit = () => {
    form.post('/residents');
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Add Resident" />

        <div class="max-w-4xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-foreground">Add Resident</h1>
                    <p class="text-sm text-muted-foreground">Register a new resident record</p>
                </div>
                <Link href="/residents" class="text-sm text-muted-foreground hover:text-foreground">&larr; Back to Residents</Link>
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
                            <p v-if="form.errors.last_name" class="mt-1 text-xs text-destructive">{{ form.errors.last_name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">Suffix</label>
                            <Input v-model="form.suffix" type="text" placeholder="Jr., Sr., III" class="mt-1" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">Date of Birth *</label>
                            <Input v-model="form.date_of_birth" type="date" required class="mt-1" />
                            <p v-if="form.errors.date_of_birth" class="mt-1 text-xs text-destructive">{{ form.errors.date_of_birth }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">Sex *</label>
                            <Select v-model="form.sex" required class="mt-1">
                                <option value="">Select...</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </Select>
                            <p v-if="form.errors.sex" class="mt-1 text-xs text-destructive">{{ form.errors.sex }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">Civil Status *</label>
                            <Select v-model="form.civil_status" required class="mt-1">
                                <option value="">Select...</option>
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
                            <p v-if="form.errors.address" class="mt-1 px-3 py-2 text-xs text-destructive">{{ form.errors.address }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">Purok *</label>
                            <Select v-model="form.purok_id" required class="mt-1">
                                <option value="">Select...</option>
                                <option v-for="purok in puroks" :key="purok.id" :value="purok.id">{{ purok.name }}</option>
                            </Select>
                            <p v-if="form.errors.purok_id" class="mt-1 px-3 py-2 text-xs text-destructive">{{ form.errors.purok_id }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">Household</label>
                            <Select v-model="form.household_id" class="mt-1">
                                <option value="">None</option>
                                <option v-for="household in households" :key="household.id" :value="household.id">
                                    {{ household.household_code }} - {{ household.head_of_family?.first_name }} {{ household.head_of_family?.last_name }}
                                </option>
                            </Select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">Contact Number</label>
                            <Input v-model="form.contact_number" type="tel" placeholder="09XX XXX XXXX" class="mt-1" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">Email</label>
                            <Input v-model="form.email" type="email" class="mt-1" />
                            <p v-if="form.errors.email" class="mt-1 px-3 py-2 text-xs text-destructive">{{ form.errors.email }}</p>
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

                <!-- Account creation -->
                <div class="rounded-lg bg-muted border p-4">
                    <label class="flex items-center">
                        <input v-model="form.create_account" type="checkbox" class="rounded border-input" />
                        <span class="ml-2 text-sm font-medium text-foreground">Create a user account for this resident</span>
                    </label>
                    <div v-if="form.create_account" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">Email (login) *</label>
                            <Input v-model="form.email" type="email" required class="mt-1" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground">Password *</label>
                            <Input v-model="form.password" type="password" required minlength="8" class="mt-1" />
                            <p v-if="form.errors.password" class="mt-1 px-3 py-2 text-xs text-destructive">{{ form.errors.password }}</p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end space-x-3 pt-4 border-t">
                    <Button variant="outline" as-child>
                        <Link href="/residents">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Saving...' : 'Save Resident' }}
                    </Button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>