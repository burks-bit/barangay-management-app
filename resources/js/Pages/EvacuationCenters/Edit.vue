<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';

const props = defineProps({ center: Object });

const form = useForm({
    name: props.center.name,
    location: props.center.location,
    capacity: props.center.capacity,
    contact_person: props.center.contact_person || '',
    contact_number: props.center.contact_number || '',
    status: props.center.status,
    notes: props.center.notes || '',
});

const submit = () => {
    form.put(`/evacuation-centers/${props.center.id}`);
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Edit Center" />
        <div class="max-w-4xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-foreground">Edit {{ center.name }}</h1>
                    <p class="text-sm text-muted-foreground">Update evacuation center details</p>
                </div>
                <Link href="/evacuation-centers" class="text-sm text-muted-foreground hover:text-foreground">&larr; Back</Link>
            </div>

            <form @submit.prevent="submit" class="bg-card rounded-xl border p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-muted-foreground mb-1">Name *</label>
                    <Input v-model="form.name" type="text" required />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-destructive">{{ form.errors.name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-muted-foreground mb-1">Location *</label>
                    <Input v-model="form.location" type="text" required />
                    <p v-if="form.errors.location" class="mt-1 text-xs text-destructive">{{ form.errors.location }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-muted-foreground mb-1">Capacity *</label>
                    <Input v-model="form.capacity" type="number" min="0" required />
                    <p v-if="form.errors.capacity" class="mt-1 text-xs text-destructive">{{ form.errors.capacity }}</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground mb-1">Contact Person</label>
                        <Input v-model="form.contact_person" type="text" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground mb-1">Contact Number</label>
                        <Input v-model="form.contact_number" type="tel" />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-muted-foreground mb-1">Status</label>
                    <Select v-model="form.status">
                        <option value="available">Available</option>
                        <option value="occupied">Occupied</option>
                        <option value="full">Full</option>
                        <option value="closed">Closed</option>
                    </Select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-muted-foreground mb-1">Notes</label>
                    <Textarea v-model="form.notes" rows="3" />
                </div>
                <div class="flex items-center justify-end space-x-3 pt-4 border-t">
                    <Button variant="outline" as-child>
                        <Link href="/evacuation-centers">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Saving...' : 'Update Center' }}
                    </Button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>