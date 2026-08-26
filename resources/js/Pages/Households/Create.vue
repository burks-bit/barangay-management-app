<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';

const props = defineProps({
    puroks: Array,
    residents: Array,
    evacuationCenters: Array,
});

const form = useForm({
    household_code: '',
    address: '',
    purok_id: '',
    contact_number: '',
    head_of_family_id: '',
    evacuation_center_id: '',
    evacuation_status: 'none',
    notes: '',
});

const submit = () => form.post('/households');
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Register Household" />
        <div class="max-w-2xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div><h1 class="text-xl font-bold text-foreground">Register Household</h1><p class="text-sm text-muted-foreground mt-1">Create a household record.</p></div>
                <Link href="/households" class="text-sm text-muted-foreground hover:text-foreground">&larr; Back</Link>
            </div>

            <form @submit.prevent="submit" class="bg-card rounded-xl border p-6 space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-muted-foreground">Household code *</label><Input v-model="form.household_code" required type="text" placeholder="HH-0001" class="mt-1" /><p v-if="form.errors.household_code" class="mt-1 text-xs text-destructive">{{ form.errors.household_code }}</p></div>
                    <div><label class="block text-sm font-medium text-muted-foreground">Purok *</label><Select v-model="form.purok_id" required class="mt-1"><option value="">Select Purok...</option><option v-for="purok in puroks" :key="purok.id" :value="purok.id">{{ purok.name }}</option></Select><p v-if="form.errors.purok_id" class="mt-1 text-xs text-destructive">{{ form.errors.purok_id }}</p></div>
                </div>
                <div><label class="block text-sm font-medium text-muted-foreground">Address *</label><Input v-model="form.address" required type="text" class="mt-1" /><p v-if="form.errors.address" class="mt-1 text-xs text-destructive">{{ form.errors.address }}</p></div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-muted-foreground">Head of family</label><Select v-model="form.head_of_family_id" class="mt-1"><option value="">Select resident...</option><option v-for="resident in residents" :key="resident.id" :value="resident.id">{{ resident.first_name }} {{ resident.middle_name }} {{ resident.last_name }}</option></Select></div>
                    <div><label class="block text-sm font-medium text-muted-foreground">Contact number</label><Input v-model="form.contact_number" type="tel" class="mt-1" /></div>
                </div>
                <div class="border-t pt-5"><h2 class="text-sm font-semibold text-foreground">Evacuation details</h2><div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3"><div><label class="block text-sm font-medium text-muted-foreground">Status</label><Select v-model="form.evacuation_status" class="mt-1"><option value="none">Not evacuated</option><option value="evacuated">Evacuated</option><option value="returned">Returned</option></Select></div><div><label class="block text-sm font-medium text-muted-foreground">Evacuation center</label><Select v-model="form.evacuation_center_id" class="mt-1"><option value="">Select center...</option><option v-for="center in evacuationCenters" :key="center.id" :value="center.id">{{ center.name }} ({{ center.current_occupancy }}/{{ center.capacity }})</option></Select></div></div></div>
                <div><label class="block text-sm font-medium text-muted-foreground">Notes</label><Textarea v-model="form.notes" rows="3" class="mt-1" /></div>
                <div class="flex justify-end gap-3 pt-2"><Button variant="outline" as-child><Link href="/households">Cancel</Link></Button><Button type="submit" :disabled="form.processing">{{ form.processing ? 'Saving...' : 'Register Household' }}</Button></div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>