<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';

defineProps({
    requestTypes: Array,
});

const form = useForm({
    request_type_id: '',
    purpose: '',
    description: '',
});

const submit = () => {
    form.post('/my-requests');
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="New Request" />

        <div class="max-w-2xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-foreground">New Service Request</h1>
                    <p class="text-sm text-muted-foreground">Request a barangay document or service</p>
                </div>
                <Link href="/my-requests" class="text-sm text-muted-foreground hover:text-foreground">&larr; Back to My Requests</Link>
            </div>

            <form @submit.prevent="submit" class="bg-card rounded-xl border p-6 space-y-5">
                <div>
                    <label class="block text-sm font-medium text-muted-foreground">Document Type *</label>
                    <Select v-model="form.request_type_id" required class="mt-1">
                        <option value="">Select a document type...</option>
                        <option v-for="type in requestTypes" :key="type.id" :value="type.id">
                            {{ type.name }} {{ type.fee > 0 ? `(₱${type.fee})` : '(Free)' }}
                        </option>
                    </Select>
                    <p v-if="form.errors.request_type_id" class="mt-1 text-xs text-destructive">{{ form.errors.request_type_id }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-muted-foreground">Purpose *</label>
                    <Input v-model="form.purpose" type="text" required placeholder="e.g., Employment application" class="mt-1" />
                    <p v-if="form.errors.purpose" class="mt-1 text-xs text-destructive">{{ form.errors.purpose }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-muted-foreground">Additional Details</label>
                    <Textarea v-model="form.description" rows="4" placeholder="Provide any additional information that may help process your request..." class="mt-1" />
                </div>

                <div class="rounded-lg bg-primary/5 border border-primary/10 p-4">
                    <p class="text-xs text-primary">
                        Your request will be assigned a tracking number once submitted.
                        You can track its status from your dashboard.
                    </p>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-2">
                    <Button variant="outline" as-child>
                        <Link href="/my-requests">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Submitting...' : 'Submit Request' }}
                    </Button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>