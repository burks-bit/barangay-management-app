<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';

const form = useForm({
    name: '',
    description: '',
    mission: '',
    vision: '',
    address: '',
    about: '',
    is_active: true,
});

const submit = () => {
    form.post('/barangay');
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Create Barangay Profile" />
        <div class="max-w-4xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-foreground">Create Barangay Profile</h1>
                    <p class="text-sm text-muted-foreground">Set up your barangay's basic information</p>
                </div>
                <Link href="/barangay" class="text-sm text-muted-foreground hover:text-foreground">&larr; Back</Link>
            </div>

            <form @submit.prevent="submit" class="bg-card rounded-xl border p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-muted-foreground mb-1">Barangay Name *</label>
                        <Input v-model="form.name" type="text" placeholder="e.g., Barangay San Isidro" required />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-destructive">{{ form.errors.name }}</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-muted-foreground mb-1">Description</label>
                        <Textarea v-model="form.description" rows="3" placeholder="Brief description of your barangay" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-muted-foreground mb-1">Mission</label>
                        <Textarea v-model="form.mission" rows="4" placeholder="The mission of the barangay" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-muted-foreground mb-1">Vision</label>
                        <Textarea v-model="form.vision" rows="4" placeholder="The vision of the barangay" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-muted-foreground mb-1">Address</label>
                        <Input v-model="form.address" type="text" placeholder="Barangay Hall address" />
                        <p v-if="form.errors.address" class="mt-1 text-xs text-destructive">{{ form.errors.address }}</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-muted-foreground mb-1">About Us</label>
                        <Textarea v-model="form.about" rows="5" placeholder="More information about your barangay" />
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
                        <Link href="/barangay">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Saving...' : 'Save Barangay' }}
                    </Button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>