<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';

const props = defineProps({ barangay: Object });

const form = useForm({
    name: props.barangay.name,
    description: props.barangay.description || '',
    mission: props.barangay.mission || '',
    vision: props.barangay.vision || '',
    address: props.barangay.address || '',
    about: props.barangay.about || '',
    is_active: props.barangay.is_active,
});

const submit = () => {
    form.put(`/barangay/${props.barangay.id}`);
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Edit Barangay Profile" />
        <div class="max-w-4xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-foreground">Edit {{ barangay.name }}</h1>
                    <p class="text-sm text-muted-foreground">Update your barangay's information</p>
                </div>
                <Link :href="`/barangay/${barangay.id}`" class="text-sm text-muted-foreground hover:text-foreground">&larr; Back</Link>
            </div>

            <form @submit.prevent="submit" class="bg-card rounded-xl border p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-muted-foreground mb-1">Barangay Name *</label>
                        <Input v-model="form.name" type="text" required />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-destructive">{{ form.errors.name }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-muted-foreground mb-1">Description</label>
                        <Textarea v-model="form.description" rows="3" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground mb-1">Mission</label>
                        <Textarea v-model="form.mission" rows="4" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground mb-1">Vision</label>
                        <Textarea v-model="form.vision" rows="4" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-muted-foreground mb-1">Address</label>
                        <Input v-model="form.address" type="text" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-muted-foreground mb-1">About Us</label>
                        <Textarea v-model="form.about" rows="5" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="flex items-center">
                            <input v-model="form.is_active" type="checkbox" class="rounded border-input" />
                            <span class="ml-2 text-sm font-medium text-foreground">Active</span>
                        </label>
                    </div>
                </div>
                <div class="flex items-center justify-end space-x-3 pt-4 border-t">
                    <Button variant="outline" as-child>
                        <Link :href="`/barangay/${barangay.id}`">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Saving...' : 'Update Barangay' }}
                    </Button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>